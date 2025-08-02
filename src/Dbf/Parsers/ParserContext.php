<?php

namespace App\Dbf\Parsers;

class ParserContext
{
    private AbstractDbfParser $parser;

    public function __construct(AbstractDbfParser $parser)
    {
       $this->parser = $parser;
    }

    public function setParser(AbstractDbfParser $parser): void {
        $this->parser = $parser;
    }

    public function execute(string $fileName, string $archivePath): void{
        $startTime = microtime(true);
        echo "Importing {$fileName} from {$archivePath}\n";
        $this->parser->process();
        echo "Import completed successfully!\n";
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        echo "Время выполнения: " . round($executionTime, 4) . " секунд\n";
        echo "Дата выполнения: " . date('Y-m-d H:i:s', (int)$endTime) . "\n";
    }
}