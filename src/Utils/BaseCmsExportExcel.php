<?php

namespace Juanfv2\BaseCms\Utils;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * BaseCmsExportCSV
 * Maatwebsite\Excel "required"
 */
class BaseCmsExportExcel implements BaseCmsExport, FromCollection, WithHeadings
{
    protected $stringData;

    protected $exportTo;

    private ?\Illuminate\Support\Collection $collection = null;

    private $headers;

    private readonly string $extension;

    public function __construct(protected $filename = 'export-data', $extension = 'csv', $exportTo = self::TO_BROWSER)
    {
        if (! in_array($exportTo, ['browser', 'file', 'string'])) {
            throw new Exception("$exportTo is not a valid ExportData export type");
        }
        $this->exportTo = $exportTo;
        $this->extension = Str::ucfirst(Str::lower($extension));
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
                $this->collection = new Collection();
                break;
        }

        $this->headers = $headers;
    }

    public function finalize()
    {
        $ext = Str::lower($this->extension);

        return match ($this->exportTo) {
            self::TO_STRING => $this->stringData,
            self::TO_FILE => \Maatwebsite\Excel\Facades\Excel::store($this, "{$this->filename}.{$ext}", 'local', $this->extension),
            default => \Maatwebsite\Excel\Facades\Excel::download($this, "{$this->filename}.{$ext}", $this->extension),
        };
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
