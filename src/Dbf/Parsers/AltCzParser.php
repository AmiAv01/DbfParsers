<?php

namespace App\Dbf\Parsers;

use App\Database\Connection;
use org\majkel\dbase\Record;

class AltCzParser extends AbstractDbfParser
{
    private array $altCzBatch = [];
    private array $columnsToProcess = [];

    private array $recordsMeaning = [
        "AWIR" => "Якорь", "AWIR_OM_OR" => "Якорь", "AWIR_CG" => "Якорь",
        "AUZW" => "Статор", "AUZW_OM_OR" => "Статор", "AUZW_CG" => "Статор",
        "AREG" => "Регулятор напряжения", "AREG_HU_TR" => "Регулятор напряжения",
        "AREG_CG" => "Регулятор напряжения",
        "APRO" => "Выпрямитель", "APRO_HU_TR" => "Выпрямитель", "APRO_CG" => "Выпрямитель",
        "ALOZP" => "Подшипник", "ALOZP_CG" => "Подшипник", "ALOZT" => "Подшипник",
        "ALOZT_CG" => "Подшипник", "ADEKP" => "Крышка передняя", "ADEKP_CG" => "Крышка передняя",
        "ADEKT" => "Крышка задняя", "ADEKT_CG" => "Крышка задняя",
        "APOK" => "Крышка пластиковая", "APOK_CG" => "Крышка пластиковая",
        "ASZC" => "Щетки", "ASZC_EU" => "Щетки",
        "ATRZ" => "Щеткодержатель", "ATRZ_HU" => "Щеткодержатель",
        "ATRZ_CG" => "Щеткодержатель",
        "AKOL" => "Шкив", "AKOL_CG" => "Шкив",
        "AREP1" => "Прочее", "AREP1_CG" => "Прочее",
        "AREP2" => "Прочее", "AREP2_CG" => "Прочее"
    ];

    public function __construct(Connection $db, string $filePath)
    {
        parent::__construct($db, $filePath);
        $this->initializeColumns();
    }

    private function initializeColumns(): void
    {
        $allColumns = $this->dbf->getFields();
        $this->columnsToProcess = array_slice($allColumns, 5);
    }

    protected function processRecord(Record $record): void
    {
        foreach ($this->columnsToProcess as $columnName) {
            $fieldValue = $record[$columnName] ?? '';

            if ($fieldValue !== "") {
                $this->processField($record, $columnName, $fieldValue);
            }
        }

        if (count($this->altCzBatch) >= self::BATCH_SIZE) {
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

        $this->altCzBatch[] = [
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

    public function process(): void
    {
        parent::process();
        $this->flushBatch();
    }

    private function flushBatch(): void
    {
        if (empty($this->altCzBatch)) {
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
            $this->altCzBatch
        );

        $sql = "INSERT IGNORE INTO alt_cz(
                datep, tmp, hcparts, brand, typec, dt_brand, dt_code, img
            ) VALUES " . implode(',', $values);

        $this->db->query($sql);
        $this->altCzBatch = [];
    }
}