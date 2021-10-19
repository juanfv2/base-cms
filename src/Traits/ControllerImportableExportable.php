<?php

namespace  Juanfv2\BaseCms\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use Juanfv2\BaseCms\Utils\ExportDataCSV;

trait ControllerImportableExportable
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
        $original                     = ini_get('auto_detect_line_endings');
        $created                      = 0;
        $handle                       = null;
        $xHeaders                     = [];

        try {
            logger(__FILE__ . ':' . __LINE__ . ' $massiveQueryFile ', [$massiveQueryFile]);
            if (($handle = fopen($massiveQueryFile, 'r')) !== false) {

                ini_set('auto_detect_line_endings', true);
                DB::beginTransaction();

                while (($datum = fgetcsv($handle, 10000, ',')) !== false) {
                    $datum = $this->toUtf8($datum);

                    if ($created === 0) {
                        $xHeaders = $datum;
                        $created++;
                        continue;
                    }
                    $cKeys = count($xHeaders);
                    $cDatum = count($datum);

                    if ($cKeys !== $cDatum) {
                        throw new \Exception(__('validation.columns.no.match', ['required' => $cKeys, 'sent' => $cDatum]), $created + 1);
                    }
                    $itemArr = array_combine($xHeaders, $datum);

                    // AVOID columns empties
                    $obj = [];
                    foreach ($xHeaders as $k) {
                        if (isset($keys[$k])) {
                            if ($itemArr[$k] !== '') {
                                $obj[$keys[$k]] = $itemArr[$k];
                            }
                        }
                    }
                    $exist = false;
                    if (isset($obj[$primaryKeyName])) {
                        $r = DB::select("select count(*) as aggregate from $table where $primaryKeyName = ?", [$obj[$primaryKeyName]]);
                        $exist = $r[0]->aggregate > 0;
                    }

                    if ($exist) {
                        DB::table('' . $table)
                            ->where($primaryKeyName, $obj[$primaryKeyName])
                            ->update($obj);
                    } else {
                        DB::table('' . $table)->insert($obj);
                    }

                    $created++;

                    // logger(__FILE__ . ':' . __LINE__ . ' $errors // end while ' . $created);
                } // end while

                if ($created) {
                    DB::commit();
                }

                ini_set('auto_detect_line_endings', $original);
                fclose($handle);

                File::deleteDirectory($massiveQueryFileNameDataPath);

                // return [
                //     'updated' => $created - 1,
                // ];
                return $this->sendResponse(
                    ['updated' => $created - 1],
                    __('validation.model.list', ['model' => $table]),
                );
            } // end ($handle = fopen($massiveQueryFile, 'r')) !== false
        } catch (Exception $e) {
            // logger(__FILE__ . ':' . __LINE__ . ' $errors // exception.: ' . $created);
            DB::rollBack();
            ini_set('auto_detect_line_endings', $original);
            if ($handle) fclose($handle);

            File::deleteDirectory($massiveQueryFileNameDataPath);

            return $this->sendError(
                ['code' => $e->getCode(), 'message' => $e->getMessage(), 'updated' => $created,],
                'Error en la linea ' . $created,
                500
            );
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
        $fieldNames   = array_keys($headers);
        $exporter = new ExportDataCSV('browser', $table . '.csv');

        $exporter->initialize(); // starts streaming data to web browser
        $exporter->addRow($labels);

        $repo->allForChunk()->chunk(10000, function ($items) use ($fieldNames, $exporter) {
            foreach ($items as $listItem) {
                $i = [];
                foreach ($fieldNames as $key) {
                    $i[$key] = $listItem->{$key};
                }
                $exporter->addRow($i);
            }
        });

        $exporter->finalize(); // writes the footer, flushes remaining data to browser.

        exit(); // all done
    }

    public function exportCsv(Request $request)
    {
        // $criteria = new RequestGenericCriteria($request);
        // $zname    = $request->get('zname');
        // $repo     = new MyBaseRepository(app());

        // $repo->table = $zname;
        // $repo->primaryKey = $request->get('zid', null);
        // $repo->resetModel();
        // $repo->pushCriteria($criteria);

        // $items = $repo->all();

        // $headers = json_decode($request->get('fields'), true);
        // $results = json_decode(json_encode($items), true);

        // return $this->export($zname, $headers, $results);
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
}
