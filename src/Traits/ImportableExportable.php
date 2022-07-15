<?php

namespace Juanfv2\BaseCms\Traits;

use Illuminate\Http\Request;

use App\Models\Misc\BulkError;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Utils\ExportDataService;
use Juanfv2\BaseCms\Repositories\BaseRepository;

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
        $rCountry     = $request->header('r-country', '');
        $tableName    = $request->get('table');
        $fieldName    = $request->get('massiveQueryFieldName');
        $fileName     = $request->get('massiveQueryFileName');
        $fileTemp     = explode('.', $fileName);
        $fileTempName = $fileTemp[0];
        $baseAssets   = 'public/assets/adm/';
        if ($rCountry) {
            $baseAssets = $baseAssets . $rCountry . '/';
        }

        $strLocationFileSaved = "{$baseAssets}temporals/$fileTempName/$tableName/$fieldName/$fileName";
        $exists               = Storage::exists($strLocationFileSaved);
        $massiveQueryFile     = Storage::path($strLocationFileSaved);
        $keys                 = $request->get('keys');
        $primaryKeyName       = $request->get('primaryKeyName');
        $created              = 0;

        // logger(__FILE__ . ':' . __LINE__ . ' $exists ', [$exists, $strLocationFileSaved, $massiveQueryFile]);

        try {

            if (($handle = fopen($massiveQueryFile, 'r')) !== false) {

                $delimiter    = _file_delimiter($massiveQueryFile);

                $created = $this->importing($handle, $tableName, $primaryKeyName, $keys, $delimiter);

                return $this->sendResponse(['updated' => $created - 1], __('validation.model.list', ['model' => $tableName]),);
            } // end ($handle = fopen($massiveQueryFile, 'r')) !== false
        } catch (\Throwable $th) {
            // throw $th;
            return $this->sendError(['code' => $th->getCode(), 'message' => $th->getMessage(), 'updated' => $created,], 'Error en la linea ' . $created, 500);
        }
    }

    public function importing($handle, $table, $primaryKeys, $keys, $delimiter, $model_name = '', $extra_data = null, $callback = null)
    {
        $created      = 0;
        $line         = 0;
        $data1        = [];
        $xHeadersTemp = fgetcsv($handle, 0, $delimiter);
        $xHeadersTemp = \ForceUTF8\Encoding::fixUTF8($xHeadersTemp);
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            try {
                $data1 = \ForceUTF8\Encoding::fixUTF8($data);
                $dataCombine = _array_combine($xHeadersTemp, $data1);

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

                    if ($extra_data) {
                        $data = array_merge($data, $extra_data);
                    }

                    if ($model_name) {
                        $r = $this->saveModel($model_name, $attrKeys, $data, $primaryKeys);
                        // logger(__FILE__ . ':' . __LINE__ . ' $r ', [$r]);
                    } else {
                        $r = $this->saveArray($table, $attrKeys, $data);
                    }

                    if ($callback && is_int($r) && $r > 0) {
                        $row = $data;
                        $row[$primaryKeys] = $r;
                        call_user_func($callback, $row);
                    }

                    $created++;
                }
            } catch (\Throwable $th) {
                // throw $th;
                $d = implode($delimiter, $data1);
                $queue = property_exists($this, 'event') ? $this->event->data->cQueue : "__u___";
                BulkError::create(['queue' => $queue, 'payload' => "{$d} $delimiter Línea: {$line} $delimiter {$th->getMessage()}",]);
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

    public function saveArray($table, $attrKeys, $data)
    {
        try {
            if (empty($attrKeys)) {
                return DB::table($table)->insertGetId($data);
            }

            return DB::table($table)->updateOrInsert($attrKeys, $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function saveModel($model_name, $attrKeys, $data, $primaryKeys)
    {
        try {
            $res = $attrKeys + $data;

            $model = new $model_name();

            if (!empty($attrKeys)) {
                $model = $model_name::where($attrKeys)->firstOrNew();
            }

            if (isset($data[$primaryKeys])) {
                $model->$primaryKeys = $data[$primaryKeys];
            }
            $model->fill($res);
            $model->save();

            return $model->$primaryKeys;
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
        } catch (\Exception $e) {

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

    protected function export($table, $headers, $repo, $type = 'browser')
    {
        $labels   = \ForceUTF8\Encoding::fixUTF8($headers);
        $fNames   = array_keys($headers);
        $exporter = ExportDataService::csv($type, $table . '.csv');

        $exporter->initialize(); // starts streaming data to web browser
        $exporter->addRow($labels);


        if ($repo instanceof BaseRepository) {
            $repo->allForChunk()->chunk(10000, function ($items) use ($fNames, $exporter) {
                foreach ($items as $listItem) {
                    $i = [];
                    foreach ($fNames as $key) {
                        $i[$key] = $listItem->{$key};
                    }
                    $exporter->addRow($i);
                }
            });
        } else {
            foreach ($repo as $listItem) {
                $i = [];
                foreach ($fNames as $key) {
                    $i[$key] = $listItem->{$key};
                }
                $exporter->addRow($i);
            }
        }

        return $exporter->finalize(); // writes the footer, flushes remaining data to browser.

        // exit(); // all done
        // return '';
    }
}
