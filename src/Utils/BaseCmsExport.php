<?php

namespace Juanfv2\BaseCms\Utils;

interface BaseCmsExport
{
    public const TO_BROWSER = 'browser';

    public const TO_FILE = 'file';

    public const TO_STRING = 'string';

    public function initialize($headers);

    public function finalize();

    public function addRow($row);
}
