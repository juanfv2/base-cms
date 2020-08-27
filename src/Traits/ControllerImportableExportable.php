<?php

namespace Juanfv2\BaseCms\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
        $table                      = $request->get('table');
        $massiveQueryFieldName      = $request->get('massiveQueryFieldName');
        $massiveQueryFileName       = $request->get('massiveQueryFileName');

        $fileTemp     = explode('.', $massiveQueryFileName);
        $fileTempName = $fileTemp[0];
        // $fileTempExt  = $fileTemp[1];

        $massiveQueryFileNameDataPath   = 'assets/adm/temporals/' . $fileTempName;
        $massiveQueryFile               = public_path($massiveQueryFileNameDataPath . '/' . $table . '/' . $massiveQueryFieldName . '/' . $massiveQueryFileName);

        $keys           = $request->get('keys');
        $primaryKeyName = $request->get('primaryKeyName');

        $original       = ini_get('auto_detect_line_endings');
        $created        = 0;
        $handle         = null;

        try {

            if (($handle = fopen($massiveQueryFile, 'r')) !== false) {

                ini_set('auto_detect_line_endings', true);
                DB::beginTransaction();

                $xHeaders = [];
                while (($datum = fgetcsv($handle, 10000, ',')) !== false) {
                    $datum = $this->toUtf8($datum);

                    if ($created === 0) {
                        $xHeaders = $this->toUtf8($datum);
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
                        $r = DB::select("select count(*) as `aggregate` from $table where $primaryKeyName = ?", [$obj[$primaryKeyName]]);
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

                return [
                    'updated' => $created - 1,
                ];
            } // end ($handle = fopen($massiveQueryFile, 'r')) !== false
        } catch (Exception $e) {
            // logger(__FILE__ . ':' . __LINE__ . ' $errors // exception.: ' . $created);
            DB::rollBack();
            ini_set('auto_detect_line_endings', $original);
            if ($handle) fclose($handle);

            File::deleteDirectory($massiveQueryFileNameDataPath);

            // return $this->reportBug($e->getMessage(), $created);
            return $this->sendError(
                'Error en la linea ' . $created,
                500,
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'updated' => $created,
                ]
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

    protected function export($table, $headers, $items)
    {
        $exporter = new ExportDataCSV('browser', $table . '.csv');

        // logger(__FILE__ . ':' . __LINE__ . ' $table   ', [$table]);
        // logger(__FILE__ . ':' . __LINE__ . ' $headers ', [$headers]);
        // logger(__FILE__ . ':' . __LINE__ . ' $items   ', [$items]);

        $exporter->initialize(); // starts streaming data to web browser

        // doesn't care how many columns you give it
        //    $exporter->addRow(array(''));

        // pass addRow() an array and it converts it to Excel XML format and sends
        // it to the browser
        $labels = array_values($headers);
        $fnames = array_keys($headers);

        $exporter->addRow($labels);
        foreach ($items as $item) {

            // $item = $itemR;

            // logger(__FILE__ . ':' . __LINE__ . ' $items   ', [$labels, $fnames, $item]);

            $i = array();
            foreach ($fnames as $key) {
                // logger(__FILE__ . ':' . __LINE__ . ' $item->{$key} ', [$key, $item->{$key}]);
                $i[$key] = $item->{$key};
            }
            $exporter->addRow($i);
        }

        $exporter->finalize(); // writes the footer, flushes remaining data to browser.

        exit(); // all done
    }

    public function exportCsv(Request $request)
    {
        $criteria = new RequestGenericCriteria($request);

        $zname = $request->get('zname');

        $repo = new MyBaseRepository(app());
        $repo->table = $zname;
        $repo->primaryKey = $request->get('zid', null);
        $repo->reMakeModel();
        $repo->pushCriteria($criteria);

        $items = $repo->all();

        $headers = json_decode($request->get('fields'), true);
        $results = json_decode(json_encode($items), true);

        return $this->export($zname, $headers, $results);
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
