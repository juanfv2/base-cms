<?php

namespace Juanfv2\BaseCms\Utils;

interface BaseCmsExport
{
    const TO_BROWSER = 'browser';

    const TO_FILE = 'file';

    const TO_STRING = 'string';

    public function initialize($headers);

    public function finalize();

    public function addRow($row);
}
