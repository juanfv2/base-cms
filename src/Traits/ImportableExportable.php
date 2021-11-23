<?php

namespace Juanfv2\BaseCms\Traits;

use App\Models\Misc\BulkError;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ExportDataService;

trait ImportableExportable
{

    /**
     * @param $table
     * @param $primaryKeyName
     * @param $massiveQueryFileName
     * @param $keys
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function importCsv(Request $request)
    {
        $table                        = $request->get('table');
        $massiveQueryFieldName        = $request->get('massiveQueryFieldName');
        $massiveQueryFileName         = $request->get('massiveQueryFileName');
        $fileTemp                     = explode('.', $massiveQueryFileName);
        $fileTempName                 = $fileTemp[0];
        $massiveQueryFileNameDataPath = storage_path('app/public/assets/adm/temporals/' . $fileTempName);
        $massiveQueryFile             = $massiveQueryFileNameDataPath . '/' . $table . '/' . $massiveQueryFieldName . '/' . $massiveQueryFileName;
        $keys                         = $request->get('keys');
        $primaryKeyName               = $request->get('primaryKeyName');

        if (($handle = fopen($massiveQueryFile, 'r')) !== false) {
            try {
                $delimiter    = $this->getFileDelimiter($massiveQueryFile);

                $created = $this->importing($handle, $table, $primaryKeyName, $keys, $delimiter);

                return $this->sendResponse(['updated' => $created - 1], __('validation.model.list', ['model' => $table]),);
            } catch (\Throwable $th) {
                //throw $th;
                return $this->sendError(['code' => $th->getCode(), 'message' => $th->getMessage(), 'updated' => $created,], 'Error en la linea ' . $created, 500);
            }
        } // end ($handle = fopen($massiveQueryFile, 'r')) !== false
    }

    public function importing($handle, $table, $primaryKeys, $keys, $delimiter)
    {
        $created      = 0;
        $line         = 0;
        $data1        = [];
        $xHeadersTemp = fgetcsv($handle, 0, $delimiter);
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            try {
                $data1       = $this->toUtf8($data);
                $dataCombine = array_combine($xHeadersTemp, $data1);

                if ($dataCombine) {
                    $data = $this->getDataToSave($xHeadersTemp, $dataCombine, $keys);

                    $attrKeys = [];

                    if (is_string($primaryKeys)) {
                        if (isset($data[$primaryKeys])) {
                            $attrKeys[$primaryKeys] = $data[$primaryKeys];
                        }
                    }
                    if (is_array($primaryKeys)) {
                        $attrKeys = $this->getDataToSave($primaryKeys, $dataCombine, $keys);
                    }

                    $r = $this->saveData($table, $attrKeys, $data, $created);
                    if ($r)  $created++;
                }
            } catch (\Throwable $th) {
                $d = implode($delimiter, $data1);
                BulkError::create(['queue' => $this->event->data->cQueue, 'payload' => "Línea: {$line} {$d} {$th->getMessage()}",]);
            }
        }

        fclose($handle);

        return $created;
    }

    public function getDataToSave($headers, $data, $keys)
    {
        $dataToSave = [];

        foreach ($headers as $k) {
            if (isset($keys[$k])) {
                if ($data[$k] !== '') {
                    $dataToSave[$keys[$k]] = $data[$k];
                }
            }
        }

        return $dataToSave;
    }

    public function saveData($table, $attrKeys, $data)
    {
        try {
            if (empty($attrKeys)) {
                return DB::table($table)->insert($data);
            }

            return DB::table($table)->updateOrInsert($attrKeys, $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function importJson(Request $request)
    {
        try {
            $updated = 0;

            if ($request->has('tables')) {
                $tables = $request->input('tables');

                DB::beginTransaction();

                foreach ($tables as $table) {
                    $primaryKeyName = 'id';

                    foreach ($table as $tableName => $objects) {
                        if ($tableName == 'primaryKeyName') {
                            $primaryKeyName = $objects;
                            continue;
                        }

                        foreach ($objects as $object) {

                            $exist = false;
                            if (isset($object[$primaryKeyName])) {
                                $r = DB::select("select count(*) as `aggregate` from $tableName where $primaryKeyName = ?", [$object[$primaryKeyName]]);
                                $exist = $r[0]->aggregate > 0;
                            }
                            if ($exist) {
                                DB::table('' . $tableName)
                                    ->where('' . $primaryKeyName, $object[$primaryKeyName])
                                    ->update($object);
                            } else {
                                DB::table('' . $tableName)->insert($object);
                            }

                            $updated++;
                        } // end for objects
                    } // end for table
                } // end for tables

                DB::commit();

                return [
                    'updated' => $updated,
                    'success' => true,
                ];
            }
        } catch (Exception $e) {

            DB::rollBack();

            return $this->sendError(
                'Error en la linea ' . $updated,
                500,
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'updated' => $updated,
                ]
            );
        }
    }

    protected function export($table, $headers, $repo)
    {
        $labels   = array_values($headers);
        $fNames   = array_keys($headers);
        $exporter = (new ExportDataService('csv', 'browser', $table . '.csv'))->getExporter();

        $exporter->initialize(); // starts streaming data to web browser
        $exporter->addRow($labels);

        $repo->allForChunk()->chunk(10000, function ($items) use ($fNames, $exporter) {
            foreach ($items as $listItem) {
                $i = [];
                foreach ($fNames as $key) {
                    $i[$key] = $listItem->{$key};
                }
                $exporter->addRow($i);
            }
        });

        $exporter->finalize(); // writes the footer, flushes remaining data to browser.

        exit(); // all done
    }

    protected function toUtf8($in)
    {
        if (is_array($in)) {
            foreach ($in as $key => $value) {
                $out[$this->toUtf8($key)] = $this->toUtf8($value);
            }
        } elseif (is_string($in)) {
            if (mb_detect_encoding($in) != 'UTF-8') {
                return trim(utf8_encode($in));
            } else {
                return trim($in);
            }
        } else {
            return trim($in);
        }
        return $out;
    }

    function getFileDelimiter($file, $checkLines = 2)
    {
        $file = new \SplFileObject($file);
        $delimiters = [',', '\t', ';', '|', ':'];
        $results = array();
        $i = 0;
        while ($file->valid() && $i <= $checkLines) {
            $line = $file->fgets();
            foreach ($delimiters as $delimiter) {
                $regExp = '/[' . $delimiter . ']/';
                $fields = preg_split($regExp, $line);
                if (count($fields) > 1) {
                    if (!empty($results[$delimiter])) {
                        $results[$delimiter]++;
                    } else {
                        $results[$delimiter] = 1;
                    }
                }
            }
            $i++;
        }
        $results = array_keys($results, max($results));
        return $results[0];
    }
}
