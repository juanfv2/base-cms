<?php

namespace Juanfv2\BaseCms\Utils;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ExportDataCSV
 * Exports to CSV (comma separated value) format.
 */
class BaseCmsExportCSV implements BaseCmsExport
{
    protected $stringData;

    protected $exportTo;

    protected $filename;

    private $file;

    public function __construct($filename = 'export-data', $extension = 'csv', $exportTo = self::TO_BROWSER)
    {
        if (! in_array($exportTo, ['browser', 'file', 'string'])) {
            throw new Exception("$exportTo is not a valid ExportData export type");
        }
        $this->exportTo = $exportTo;
        $this->filename = $filename.'.'.Str::lower($extension);
    }

    public function initialize($headers)
    {
        switch ($this->exportTo) {
            case self::TO_STRING:
                $this->stringData = '';
                break;
            default:
                // case 'file':
                // case 'browser':
                $this->file = fopen('php://memory', 'wb');
                break;
        }

        $this->addRow($headers);
    }

    public function finalize()
    {
        switch ($this->exportTo) {
            case self::TO_STRING:
                // do nothing
                return $this->stringData;
            case self::TO_FILE:
                $this->send2file($this->filename);
                break;
            default:
                return $this->send2browser();
        }
    }

    public function addRow($row)
    {
        // $this->write($this->generateRow($row));
        if (empty($row)) {
            return;
        }

        switch ($this->exportTo) {
            case self::TO_STRING:
                $this->stringData .= $this->generateRow($row);
                break;
            default:
                // case 'file':
                // case 'browser':
                fputcsv($this->file, $row);
                break;
        }
    }

    /* -------------------------------------------------------------------------- */
    /* utils                                                                      */
    /* -------------------------------------------------------------------------- */
    public function send2browser()
    {
        $uid = 'csv-file-'.microtime(true);
        $path = "csv-temp/$uid";

        $this->send2file($path);

        $p = storage_path("app/$path");

        return response()
            ->download($p, $this->filename)
            ->deleteFileAfterSend(true)
            // ..
;
    }

    public function send2file($pathCsv)
    {
        fseek($this->file, 0);

        Storage::disk('local')->put($pathCsv, $this->file);
    }

    public function generateRow($row)
    {
        foreach ($row as $key => $value) {
            // Escape inner quotes and wrap all contents in new quotes.
            // Note that we are using \" to escape double quote not ""
            $row[$key] = '"'.str_replace('"', '""', $value).'"';
        }

        return implode(',', $row)."\n";
    }
}
