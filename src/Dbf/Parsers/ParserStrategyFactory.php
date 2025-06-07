<?php

namespace App\Dbf\Parsers;

use App\Database\Connection;
use App\Dbf\Parsers\AltCzParser;

class ParserStrategyFactory
{
    public static function create(string $fileName, Connection $db): AbstractDbfParser {
        return match (basename($fileName)) {
            'oems_out.dbf' => new OemsParser($db, $fileName),
            'alt_cz.dbf' => new AltCzParser($db, $fileName),
            'roz_cz.dbf' => new RozCzParser($db, $fileName),
            'FIRMS.DBF' => new FirmParser($db, $fileName),
            'ASS.DBF' => new DetailParser($db, $fileName),
            'DETAL.DBF' => new PriceParser($db, $fileName),
            'stk.dbf' => new StkParser($db, $fileName),
            default => throw new \RuntimeException("No parser available for file: {$fileName}"),
        };
    }
}