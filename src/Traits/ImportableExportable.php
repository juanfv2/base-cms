<?php

namespace Juanfv2\BaseCms\Traits;

use App\Models\Misc\VisorLogError;
use App\Models\Misc\XFile;
use ForceUTF8\Encoding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Juanfv2\BaseCms\Events\AnyTableExportEvent;
use Juanfv2\BaseCms\Events\AnyTableImportEvent;
use Juanfv2\BaseCms\Utils\BaseCmsExportCSV;
use Juanfv2\BaseCms\Utils\BaseCmsExportExcel;

trait ImportableExportable
{
    public function import2email(Request $request, XFile $xFile)
    {
        // your-code
        $input = $request->all();

        foreach ($input as $key => $value) {
            if (is_object($input[$key])) {
                unset($input[$key]);
            }
        }

        session(['cQueue' => $this->queueName('import')]);

        $input['user_id'] = auth()->user()->id;
        $input['user_email'] = auth()->user()->email;
        $input['rCountry'] = $request->header('r-country', '.l.');
        $input['massiveQueryFieldName'] = $xFile->fieldName;
        $input['massiveQueryFileName'] = $xFile->name;
        $input['massiveQueryFileNameOriginal'] = $xFile->nameOriginal;
        $input['cQueue'] = session('cQueue');
        $input['cModel'] = Str::replace('-', '\\', $request->get('cModel', ''));

        // dd($input);
        $inputObj = (object) $input;

        $dbDefault = config('base-cms.default_prefix').config('base-cms.default_code');
        config()->set('database.default', $dbDefault);
        event(new AnyTableImportEvent($inputObj));
        $this->trackingPending($inputObj->rCountry, $inputObj->cQueue, $inputObj->user_id);

        $xFile->extension = __('messages.mail.file.alert', ['email' => $inputObj->user_email]);
        $xFile->cQueue = $inputObj->cQueue;

        return $xFile;
    }

    public function export2email(Request $request)
    {
        session(['cQueue' => $this->queueName('export')]);
        $input = $request->all();

        $input['rCountry'] = $request->header('r-country', '.l.');
        $input['user_id'] = auth()->user()->id;
        $input['user_email'] = auth()->user()->email;
        $input['user_name'] = auth()->user()->name;
        $input['cQueue'] = session('cQueue');
        $input['cModel'] = Str::replace('-', '\\', $request->get('cModel', ''));
        $input['extension'] = $request->ext;

        $inputObj = (object) $input;

        $dbDefault = config('base-cms.default_prefix').config('base-cms.default_code');
        config()->set('database.default', $dbDefault);
        event(new AnyTableExportEvent($inputObj));
        $this->trackingPending($inputObj->rCountry, $inputObj->cQueue, $inputObj->user_id);

        return $this->sendResponse(['cQueue' => $inputObj->cQueue], __('messages.mail.file.alert', ['email' => $input['user_email']]));
    }

    /* -------------------------------------------------------------------------- */
    /* save                                                                       */
    /* -------------------------------------------------------------------------- */
    public function importing($handle, $table, $primaryKeys, $keys, $delimiter, $model_name = '', $extra_data = null, $callback = null)
    {
        $primaryKeys = is_array($primaryKeys) ? $primaryKeys : (json_decode((string) $primaryKeys, true, 512, JSON_ERROR_NONE) ?? $primaryKeys);
        $keys = is_array($keys) ? $keys : (json_decode((string) $keys, true, 512, JSON_ERROR_NONE) ?? $keys);
        $created = 0;
        $line = 0;
        $data1 = [];
        $keys = self::assoc2lower($keys);
        $xHeadersTemp = self::array2lower(fgetcsv($handle, 0, $delimiter));

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            try {
                $data1 = Encoding::fixUTF8($data);
                $dataCombine = _array_combine($xHeadersTemp, $data1);

                if ($dataCombine) {
                    $data = $this->getDataToSave($xHeadersTemp, $dataCombine, $keys);

                    if ($extra_data) {
                        $data = array_merge($data, $extra_data);
                    }

                    $attrKeys = [];
                    $kName = '';

                    if (is_string($primaryKeys)) {
                        $kName = $primaryKeys;
                        $attrKeys = $this->getDataToKeys([$primaryKeys], $data);
                    }

                    if (is_array($primaryKeys)) {
                        $attrKeys = $this->getDataToKeys($primaryKeys, $data);
                    }

                    if ($model_name) {
                        $recover = config('base-cms.recover');
                        if (isset($data[$recover]) && $data[$recover]) {
                            $r = $this->restoreModel($model_name, $attrKeys, $primaryKeys, $table);
                            unset($data[$recover]);
                        }

                        $r = $this->saveModel($model_name, $attrKeys, $data, $primaryKeys, $table);
                    // logger(__FILE__ . ':' . __LINE__ . ' $r ', [$r]);
                    } else {
                        $r = $this->saveArray($table, $attrKeys, $data, $kName);
                    }

                    if ($callback && is_int($r) && $r > 0) {
                        $row = $data;
                        $row[$primaryKeys] = $r;
                        call_user_func($callback, $row);
                    }

                    $created++;
                }
            } catch (\Throwable $th) {
                $d = implode($delimiter, $data1);
                $queue = property_exists($this, 'event') ? $this->event->data->cQueue : '__u___';
                VisorLogError::create(['queue' => $queue, 'payload' => "{$d} $delimiter Línea: {$line} $delimiter {$th->getMessage()}"]);
                // throw $th;
            }
        }

        fclose($handle);

        return $created;
    }

    public function saveArray($table, $attrKeys, $data, $kName)
    {
        try {
            if (empty($attrKeys)) {
                return DB::table($table)->insertGetId($data, $kName);
            }

            return DB::table($table)->updateOrInsert($attrKeys, $data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function saveModel($model_name, $attrKeys, $data, $primaryKeys, $tableName = '')
    {
        // logger(__FILE__.':'.__LINE__.' saveModel:: $model_name, $attrKeys, $data, $primaryKeys, $tableName ', [$model_name, $attrKeys, $data, $primaryKeys, $tableName]);

        try {
            $res = array_merge($attrKeys, $data);
            $nId = 0;

            session(['z-table' => $tableName]);
            $model = new $model_name();

            if ($tableName) {
                $model->setTable($tableName);
            }

            if (is_array($primaryKeys) && ! empty($attrKeys)) {
                $saved = $model->updateOrInsert($attrKeys, $res);
                if ($saved) {
                    return $model->where($attrKeys)->first();
                }
            }

            if (! empty($attrKeys)) {
                $model = $model->where($attrKeys)->firstOrNew();
            }

            if (is_string($primaryKeys)) {
                $model->setKeyName($primaryKeys);
                $model->fill($res);
                $model->save();

                return $model->$primaryKeys;
            }

            return $nId;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /* -------------------------------------------------------------------------- */
    /* delete                                                                     */
    /* -------------------------------------------------------------------------- */
    public function deleting($handle, $table, $primaryKeys, $keys, $delimiter, $model_name = '')
    {
        $primaryKeys = is_array($primaryKeys) ? $primaryKeys : (json_decode((string) $primaryKeys, true, 512, JSON_ERROR_NONE) ?? $primaryKeys);
        $keys = is_array($keys) ? $keys : (json_decode((string) $keys, true, 512, JSON_ERROR_NONE) ?? $keys);
        $created = 0;
        $line = 0;
        $data1 = [];
        $keys = self::assoc2lower($keys);
        $xHeadersTemp = self::array2lower(fgetcsv($handle, 0, $delimiter));

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            try {
                $data1 = Encoding::fixUTF8($data);
                $dataCombine = _array_combine($xHeadersTemp, $data1);

                if ($dataCombine) {
                    $data = $this->getDataToSave($xHeadersTemp, $dataCombine, $keys);

                    $attrKeys = [];

                    if (is_string($primaryKeys)) {
                        $attrKeys = $this->getDataToKeys([$primaryKeys], $data);
                    }

                    if (is_array($primaryKeys)) {
                        $attrKeys = $this->getDataToKeys($primaryKeys, $data);
                    }

                    if ($model_name) {
                        $r = $this->deleteModel($model_name, $attrKeys, $primaryKeys, $table);
                    } else {
                        $r = $this->deleteArray($table, $attrKeys);
                    }

                    $created++;
                }
            } catch (\Throwable $th) {
                $d = implode($delimiter, $data1);
                $queue = property_exists($this, 'event') ? $this->event->data->cQueue : '__u___';
                VisorLogError::create(['queue' => $queue, 'payload' => "{$d} $delimiter Línea: {$line} $delimiter {$th->getMessage()}"]);
                // throw $th;
            }
        }

        fclose($handle);

        return $created;
    }

    public function deleteArray($table, $attrKeys)
    {
        try {
            if (empty($attrKeys)) {
                throw new \Juanfv2\BaseCms\Exceptions\NoReportException('No tiene [PK]');
            }

            return DB::table($table)->where($attrKeys)->delete();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteModel($model_name, $attrKeys, $primaryKeys, $tableName)
    {
        // logger(__FILE__.':'.__LINE__.' deleteModel:: $model_name, $attrKeys, $primaryKeys, $tableName ', [$model_name, $attrKeys, $primaryKeys, $tableName]);

        try {
            if (empty($attrKeys)) {
                throw new \Juanfv2\BaseCms\Exceptions\NoReportException('No tiene [PK]');
            }

            session(['z-table' => $tableName]);
            $model = new $model_name();

            if ($tableName) {
                $model->setTable($tableName);
            }

            if (is_string($primaryKeys)) {
                $model->setKeyName($primaryKeys);
                $model = $model->where($attrKeys)->first();

                return $model?->delete();
            }

            $r = $model->where($attrKeys)->delete();

            return $r;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function restoreModel($model_name, $attrKeys, $primaryKeys, $tableName)
    {
        // logger(__FILE__.':'.__LINE__.' restoreModel:: $model_name, $attrKeys, $primaryKeys, $tableName ', [$model_name, $attrKeys, $primaryKeys, $tableName]);

        try {
            session(['z-table' => $tableName]);
            $model = new $model_name();

            if ($tableName) {
                $model->setTable($tableName);
            }

            if (is_string($primaryKeys)) {
                $model->setKeyName($primaryKeys);
            }

            $model = $model->withTrashed()->where($attrKeys)->first();

            return $model?->restore();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /* -------------------------------------------------------------------------- */
    /* utils                                                                      */
    /* -------------------------------------------------------------------------- */
    /**
     * @param $table
     * @param $primaryKeyName
     * @param $massiveQueryFileName
     * @param $keys
     */
    public function importCsv(Request $request): array|\Illuminate\Http\JsonResponse
    {
        $rCountry = $request->header('r-country', '');
        $tableName = $request->get('table');
        $fieldName = $request->get('massiveQueryFieldName');
        $fileName = $request->get('massiveQueryFileName');
        $fileTemp = explode('.', (string) $fileName);
        $fileTempName = $fileTemp[0];
        $baseAssets = 'public/assets/adm/';
        if ($rCountry) {
            $baseAssets = $baseAssets.$rCountry.'/';
        }

        $strLocationFileSaved = "{$baseAssets}temporals/$fileTempName/$tableName/$fieldName/$fileName";
        $exists = Storage::exists($strLocationFileSaved);
        $massiveQueryFile = Storage::path($strLocationFileSaved);
        $keys = $request->get('keys');
        $primaryKeyName = $request->get('primaryKeyName');
        $cModel = \Illuminate\Support\Str::replace('-', '\\', $request->get('cModel', ''));
        $created = 0;

        // logger(__FILE__ . ':' . __LINE__ . ' $exists ', [$exists, $strLocationFileSaved, $massiveQueryFile]);

        try {
            if (($handle = fopen($massiveQueryFile, 'r')) !== false) {
                $delimiter = _file_delimiter($massiveQueryFile);

                $created = $this->importing($handle, $tableName, $primaryKeyName, $keys, $delimiter, $cModel);

                return $this->sendResponse(['updated' => $created - 1], __('validation.model.list', ['model' => $tableName]));
            } // end ($handle = fopen($massiveQueryFile, 'r')) !== false
        } catch (\Throwable $th) {
            // throw $th;
            return $this->sendError(['code' => $th->getCode(), 'message' => $th->getMessage(), 'updated' => $created], 'Error en la linea '.$created, 500);
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
                                DB::table(''.$tableName)
                                    ->where(''.$primaryKeyName, $object[$primaryKeyName])
                                    ->update($object);
                            } else {
                                DB::table(''.$tableName)->insert($object);
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
                'Error en la linea '.$updated,
                500,
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'updated' => $updated,
                ]
            );
        }
    }

    protected function export($title, $extension, $headers, $model)
    {
        $labels = Encoding::fixUTF8($headers);
        $fNames = array_keys($headers);
        $exporter = new BaseCmsExportCSV($title, $extension);

        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $exporter = new BaseCmsExportExcel($title, $extension);
        }

        $exporter->initialize($labels);

        if ($model instanceof \Juanfv2\BaseCms\Repositories\BaseRepository) {
            $model->allForChunk()->chunk(10000, function ($items) use ($fNames, $exporter) {
                foreach ($items as $listItem) {
                    $i = [];
                    foreach ($fNames as $key) {
                        $i[$key] = $listItem->{$key};
                    }
                    $exporter->addRow($i);
                }
            });
        } elseif ($model instanceof \Illuminate\Database\Eloquent\Model) {
            $model->mQueryWithCriteria()->chunk(10000, function ($items) use ($fNames, $exporter) {
                foreach ($items as $listItem) {
                    $i = [];
                    foreach ($fNames as $key) {
                        $i[$key] = $listItem->{$key};
                    }

                    if (isset($listItem->customLabels)) {
                        foreach ($listItem->customLabels as $key0 => $value0) {
                            foreach ($fNames as $key) {
                                if (isset($i[$key]) && "{$key}_label" == $key0) {
                                    $i[$key] = "$value0 ($i[$key])";
                                }
                            }
                        }
                    }

                    $exporter->addRow($i);
                }
            });
        } else {
            foreach ($model as $listItem) {
                $i = [];
                foreach ($fNames as $key) {
                    $i[$key] = $listItem->{$key};
                }
                $exporter->addRow($i);
            }
        }

        return $exporter->finalize();
    }

    public function getDataToSave($headersInCsv, $data, $headersValid)
    {
        $dataToSave = [];

        foreach ($headersInCsv as $header) {
            if (isset($headersValid[$header])) {
                $d = $data[$header] ?? '';
                $value = trim("$d");
                $dataToSave[$headersValid[$header]] = $value;

                if ($value === '') {
                    unset($dataToSave[$headersValid[$header]]);
                }
            }
        }

        return $dataToSave;
    }

    public function getDataToKeys($headers, $data)
    {
        $dataToSave = [];

        foreach ($headers as $header) {
            if (isset($data[$header])) {
                $d = $data[$header] ?? '';
                $value = trim("$d");
                $dataToSave[$header] = $value;

                if ($value === '') {
                    unset($dataToSave[$header]);
                }
            }
        }

        return $dataToSave;
    }

    public static function array2lower(array $array)
    {
        return Encoding::fixUTF8(array_map('Str::lower', $array));
    }

    public static function assoc2lower(array $array, $lowerValues = false)
    {
        // get keys $arrays
        $keys = array_keys($array);

        // get values $arrays
        $values = array_values($array);

        // lower keys
        $keys = Encoding::fixUTF8(array_map('Str::lower', $keys));

        // lower values
        if ($lowerValues) {
            $values = array_map('Str::lower', $values);
        }

        // return array
        return array_combine($keys, $values);
    }

    public static function array2upper(array $array)
    {
        return array_map('Str::upper', $array);
    }
}
