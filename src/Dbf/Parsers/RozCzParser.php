<?php

namespace App\Dbf\Parsers;

use App\Database\Connection;
use org\majkel\dbase\Record;

class RozCzParser extends AbstractDbfParser
{
    private array $rozCzBatch = [];
    private array $columnsToProcess = [];

    private array $recordsMeaning = [
        "RAUT" => "Соленоид", "RAUT_CG" => "Соленоид", "RAUT1" => "Соленоид", "RAUT1_CG" => "Соленоид",
        "RBEN" => "Привод", "RBEN_GH" => "Привод", "RBEN_CG" => "Привод",
        "RWIR" => "Якорь", "RWIR_OM_OR" => "Якорь", "RWIR_CG" => "Якорь",
        "RUZW" => "Статор", "RUZW_OM_OR" => "Статор", "RUZW_CG" => "Статор",
        "RTRZ" => "Щеткодержатель", "RTRZ_IK" => "Щеткодержатель", "RTRZ_CG" => "Щеткодержатель",
        "RSZC" => "Щетки", "RSZC_EU" => "Щетки", "RSZC_CG" => "Щетки",
        "RGLO" => "Крышка передняя", "RGLO_CG" => "Крышка передняя",
        "RDEK" => "Крышка задняя", "RDEK_CG" => "Крышка задняя",
        "RTULP" => "Втулка", "RTULP_CG" => "Втулка", "RTULT" => "Втулка", "RTULT_CG" => "Втулка",
        "RTUL_ZES" => "Втулка", "RTUL_CG" => "Втулка",
        "RWID" => "Вилка", "RWID_CG" => "Вилка",
        "RPOD" => "Крышка", "RPOD_CG" => "Крышка",
        "RPRZ" => "Планетарная передача", "RPRZ_GH" => "Планетарная передача", "RPRZ_CG" => "Планетарная передача",
        "RBIE" => "Шестерня", "RBIE_CG" => "Шестерня",
        "RSPR" => "Прочее", "RSPR_CG" => "Прочее",
        "RLOZ" => "Подшипник", "RLOZ_CG" => "Подшипник",
        "RREP1" => "Прочее", "RREP1_CG" => "Прочее",
        "RREP2" => "Прочее", "RREP2_CG" => "Прочее",
        "RREP3" => "Прочее"
    ];

    public function __construct(Connection $db, string $filePath)
    {
        parent::__construct($db, $filePath);
        $this->initializeColumns();
    }

    private function initializeColumns(): void
    {
        $allColumns = $this->dbf->getFields();
        $this->columnsToProcess = array_slice($allColumns, 5); // Пропускаем первые 5 колонок
    }

    protected function processRecord(Record $record): void
    {
        $isEmpty = true;

        foreach ($this->columnsToProcess as $columnName) {
            $fieldValue = $record[$columnName] ?? '';

            if ($fieldValue !== "") {
                $isEmpty = false;
                $this->processField($record, $columnName, $fieldValue);
            }
        }

        // Если все поля пустые - добавляем запись с пустыми значениями
        if ($isEmpty) {
            $this->addEmptyRecord($record);
        }

        if (count($this->rozCzBatch) >= self::BATCH_SIZE) {
            $this->flushBatch();
        }
    }

    private function processField(Record $record, string $columnName, string $fieldValue): void
    {
        $detailCodes = array_filter(
            array_map('trim', explode(",", $fieldValue)),
            fn($code) => !empty($code)
        );

        foreach ($detailCodes as $detailCode) {
            $this->addToBatch($record, $columnName, $detailCode);
        }
    }

    private function addToBatch(Record $record, string $columnName, string $detailCode): void
    {
        $datep = $record['DATEP']->format('d/m/Y');
        $typec = $this->recordsMeaning[$columnName] ?? 'Прочее';

        if (str_contains($detailCode, '#')) {
            $dt_brand = substr($detailCode, 0, strpos($detailCode, '#'));
            $dt_code = substr($detailCode, strpos($detailCode, '#') + 1);
            $img = $detailCode;
        } else {
            $dt_brand = 'CARGO';
            $dt_code = $detailCode;
            $img = "CARGO#{$detailCode}";
        }

        $this->rozCzBatch[] = [
            'datep' => $datep,
            'tmp' => $record['TMP'],
            'hcparts' => $record['HCPARTS'],
            'brand' => $record['BRAND'],
            'typec' => $typec,
            'dt_brand' => $dt_brand,
            'dt_code' => $dt_code,
            'img' => $img
        ];
    }

    private function addEmptyRecord(Record $record): void
    {
        $this->rozCzBatch[] = [
            'datep' => $record['DATEP']->format('d/m/Y'),
            'tmp' => $record['TMP'],
            'hcparts' => $record['HCPARTS'],
            'brand' => $record['BRAND'],
            'typec' => '',
            'dt_brand' => '',
            'dt_code' => '',
            'img' => ''
        ];
    }

    public function process(): void
    {
        parent::process();
        $this->flushBatch(); // Вставляем оставшиеся записи
    }

    private function flushBatch(): void
    {
        if (empty($this->rozCzBatch)) {
            return;
        }

        $values = array_map(
            fn($item) => sprintf(
                "('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                $this->db->escape($item['datep']),
                $this->db->escape($item['tmp']),
                $this->db->escape($item['hcparts']),
                $this->db->escape($item['brand']),
                $this->db->escape($item['typec']),
                $this->db->escape($item['dt_brand']),
                $this->db->escape($item['dt_code']),
                $this->db->escape($item['img'])
            ),
            $this->rozCzBatch
        );

        $sql = "INSERT IGNORE INTO roz_cz(
                datep, tmp, hcparts, brand, typec, dt_brand, dt_code, img
            ) VALUES " . implode(',', $values);

        $this->db->query($sql);
        $this->rozCzBatch = [];
    }
}