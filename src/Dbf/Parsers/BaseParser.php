<?php

namespace App\Dbf\Parsers;

interface BaseParser
{
    public function process(): void;
}