<?php

namespace Juanfv2\BaseCms\Utils;

use Exception;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * BaseCmsExport
 * Maatwebsite\Excel "required"
 *
 */
class BaseCmsExport implements FromCollection, WithHeadings
{
    const TO_BROWSER = 'browser';
    const TO_FILE    = 'file';
    const TO_STRING  = 'string';
    private $collection;
    private $headers;

    protected $stringData;
    protected $exportTo;
    public $filename;
    public $extension;

    public function __construct($filename = "export-data", $extension = Excel::CSV, $exportTo = self::TO_BROWSER)
    {
        if (!in_array($exportTo, array('browser', 'file', 'string'))) {
            throw new Exception("$exportTo is not a valid ExportData export type");
        }
        $this->exportTo  = $exportTo;
        $this->filename  = $filename;
        $this->extension = $extension;
    }

    public function initialize($headers)
    {
        $this->headers = $headers;

        switch ($this->exportTo) {
            case self::TO_STRING:
                $this->stringData = '';
                break;
            default:
                // case 'file':
                // case 'browser':
                $this->collection = new Collection();
                break;
        }
    }

    public function finalize()
    {

        $ext = Str::lower($this->extension);
        switch ($this->exportTo) {
            case self::TO_STRING:
                return $this->stringData;
            case self::TO_FILE:
                return \Maatwebsite\Excel\Facades\Excel::store($this, "{$this->filename}.{$ext}", 'local', $this->extension);
            default:
                return \Maatwebsite\Excel\Facades\Excel::download($this, "{$this->filename}.{$ext}", $this->extension);
        }
    }

    public function addRow($row)
    {
        $this->collection->push($row);
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
