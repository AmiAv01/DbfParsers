<?php

namespace App\Dbf\Parsers;

use App\Database\Connection;
use org\majkel\dbase\Record;

class DetailParser extends AbstractDbfParser
{
    private array $detailBatch = [];
    private array $layoutBatch = [];

    public function __construct(Connection $db, string $filePath) {
        parent::__construct($db, $filePath);
    }

    protected function processRecord(Record $record): void
    {
        $this->detailBatch[] = [
            'code' => $record['CODE'],
            'extcode' => $record['EXTCODE'],
            'extname' => $record['EXTNAME'],
            'type' => $this->dbf->convertEncoding($record['TYPE']),
            'comment' => $this->dbf->convertEncoding($record['COMMENT']),
            'foto' => $record['FOTO'],
            'invoice' => $record['INVOICE'],
            'netto' => $record['NETTO'],
            'oem' => $record['OEM'],
            'baza' => $record['BAZA'],
            'cena' => $record['CENA'],
            'prod' => $record['PROD'],
            'typec' => $this->dbf->convertEncoding($record['TYPEC']),
            'bp' => $record['BP'],
            'cargo' => $record['CARGO'],
            'e' => $record['E'],
            'hs' => $record['HS'],
            'firms' => $record['FIRMS'],
            'ptype' => $record['PTYPE'],
            'acode' => $record['ACODE']
        ];

        $this->layoutBatch[] = $record['ACODE'];

        if (count($this->detailBatch) >= self::BATCH_SIZE) {
            $this->flushBatches();
        }
    }

    public function process(): void
    {
        parent::process();
        $this->flushBatches();
    }

    private function flushBatches(): void
    {
        if (!empty($this->detailBatch)) {
            $this->flushDetailsBatch();
        }

        if (!empty($this->layoutBatch)) {
            $this->flushLayoutBatch();
        }
    }

    private function flushDetailsBatch(): void
    {
        $values = [];
        foreach ($this->detailBatch as $item) {
            $values[] = sprintf(
                "('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NULL, '%s', '%s', '%s')",
                $this->db->escape($item['code']),
                $this->db->escape($item['extcode']),
                $this->db->escape($item['extname']),
                $this->db->escape($item['type']),
                $this->db->escape($item['comment']),
                $this->db->escape($item['foto']),
                $this->db->escape($item['invoice']),
                $this->db->escape($item['netto']),
                $this->db->escape($item['oem']),
                $this->db->escape($item['baza']),
                $this->db->escape($item['cena']),
                $this->db->escape($item['prod']),
                $this->db->escape($item['typec']),
                $this->db->escape($item['bp']),
                $this->db->escape($item['cargo']),
                $this->db->escape($item['e']),
                $this->db->escape($item['hs']),
                $this->db->escape($item['firms']),
                $this->db->escape($item['ptype']),
                $this->db->escape($item['acode'])
            );
        }

        $sql = "INSERT IGNORE INTO detail(
                dt_code, dt_extcode, dt_extname, dt_type, dt_comment, 
                dt_foto, dt_invoice, dt_netto, dt_oem, dt_baza, dt_cena, 
                dt_prod, dt_typec, dt_bp, dt_cargo, dt_e, dt_hs, dt_datep, 
                fr_code, dt_tp_ptype, lt_dt_acode
            ) VALUES " . implode(',', $values);

        $this->db->query($sql);
        $this->detailBatch = [];
    }

    private function flushLayoutBatch(): void
    {
        $values = array_map(
            fn($acode) => sprintf("('%s')", $this->db->escape($acode)),
            array_unique($this->layoutBatch)
        );

        $sql = "INSERT IGNORE INTO layout_for_details(lt_dt_acode) VALUES " . implode(',', $values);
        $this->db->query($sql);
        $this->layoutBatch = [];
    }
}