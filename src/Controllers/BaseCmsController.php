<?php

namespace Juanfv2\BaseCms\Controllers;

use Exception;
use Juanfv2\BaseCms\Models\Auth\XFile;
use Illuminate\Http\Request;
use App\Helpers\ExportDataCSV;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use App\Criteria\RequestGenericCriteria;
use InfyOm\Generator\Utils\ResponseUtil;
use Juanfv2\BaseCms\Repositories\Auth\MyBaseRepository;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Laravel Generator APIs",
 *     version="1.0.0",
 *   )
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class BaseCmsController extends Controller
{

    public function sendResponse($result, $message)
    {
        return response()->json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return response()->json(ResponseUtil::makeError($error), $code);
    }

    /**
     * @param $elements
     * @param int $totalElements
     * @param int $limit
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response2Api($elements, $totalElements = 0, $limit = 0)
    {
        $totalPages = abs(ceil($totalElements / $limit));
        return response()->json([
            'totalPages' => $totalPages,
            'totalElements' => $totalElements,
            'content' => $elements,
        ]);
    }

    // <editor-fold desc='import export'>

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
            $original = ini_get('auto_detect_line_endings');
            ini_set('auto_detect_line_endings', true);

            if (($handle = fopen($massiveQueryFile, 'r')) !== false) {
                DB::beginTransaction();

                $xHeaders = [];
                while (($datum = fgetcsv($handle, 10000, ',')) !== false) {
                    $datum = $this->toUtf8($datum);

                    if ($created === 0) {
                        $xHeaders = $this->toUtf8($datum);
                        $created++;
                        continue;
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
        } catch (\PDOException $e) {
            // logger(__FILE__ . ':' . __LINE__ . ' $errors // exception.: ' . $created);
            DB::rollBack();
            ini_set('auto_detect_line_endings', $original);
            if ($handle) fclose($handle);

            File::deleteDirectory($massiveQueryFileNameDataPath);

            return $this->reportBug($e->getMessage(), $created);
        } catch (Exception $e) {
            // logger(__FILE__ . ':' . __LINE__ . ' $errors // exception.: ' . $created);
            DB::rollBack();
            ini_set('auto_detect_line_endings', $original);
            if ($handle) fclose($handle);

            File::deleteDirectory($massiveQueryFileNameDataPath);

            return $this->reportBug($e->getMessage(), $created);
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

            $errors = [
                'code' => $e->getCode(),
                'message' => 'Error en la linea ' . $updated++ . ' **********************\n',
                'labelError' => 'Error en la linea ' . $updated++,
                'labelDescr' => $e->getMessage(),
                'updated' => $updated,
            ];
            Log::error(__METHOD__ . '.' . __LINE__, $errors);
            return response()->json($errors, 500);
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
        foreach ($items as $itemR) {

            $item = $itemR->resource->toArray();

            // logger(__FILE__ . ':' . __LINE__ . ' $items   ', [$labels, $fnames, $item]);

            $i = array();
            foreach ($fnames as $key) {
                $i[$key] = isset($item[$key]) ? $item[$key] : '';
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

    /**
     * Delete items from any table
     */
    protected function deleteItems($model, $items)
    {
        try {
            $deleted = 0;
            DB::beginTransaction();
            foreach ($items as $item) {
                $deleted++;
                $item->table = $model;
                $item->delete();
            }

            logger(__FILE__ . ':' . __LINE__ . ' $deleted ', [$deleted]);
            if ($deleted) {
                DB::commit();
            }

            return [
                'message' => $deleted . ' ' . $model . ' eliminados',
            ];
        } catch (Exception $e) {
            logger(__FILE__ . ':' . __LINE__ . ' $errors // exception.: ' . $deleted);
            DB::rollBack();
            return $this->reportBug($e->getMessage(), $deleted);
        }
    }

    protected function reportBug($msg, $line = '')
    {
        $errors = [
            'code' => 0,
            'message' => 'Error en la linea ' . $line,
            'labelDescr' => $msg,
            'updated' => 0,
        ];
        // logger(__FILE__ . ':' . __LINE__ . ' $errors ', [$errors]);
        // return $this->sendError($errors, 500);
        return response()->json($errors, 500);
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

    // </editor-fold> import export end

    // <editor-fold desc='upload files'>

    public function updateXfile($id, Request $request)
    {
        $input = $request->all();

        logger(__FILE__ . ':' . __LINE__ . ' $input ', [$input]);

        $f = XFile::find($id);

        if (!$f) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Archivo']));
        }

        $f->fill($input);
        $f->save();

        // $f = new GenericResource(banner);

        return ['id' => $f->id];
    }

    public function deleteXfile($id)
    {
        $f = XFile::find($id);

        if (!$f) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Archivo']));
        }

        $baseAssets = '/assets/adm/';
        $strLocation = public_path($baseAssets . $f->entity . '/' . $f->field);

        File::delete("{$strLocation}/{$f->name}");

        $f->delete();
    }

    /**
     * param $strLocationAndFileNamePrefix: 
     *   like this "{$strLocation}/{$fileNamePrefix}*"
     */
    private function deleteFileWithGlob($strLocationAndFileNamePrefix)
    {
        if ($strLocationAndFileNamePrefix) {
            // Will find 2.txt, 2.php, 2.gif
            $prevFiles = glob($strLocationAndFileNamePrefix);

            // logger(__FILE__ . ':' . __LINE__ . ' $files ', [$strLocationAndFileNamePrefix]);
            // logger(__FILE__ . ':' . __LINE__ . ' $prevFiles ', [$prevFiles]);

            // Process through each file in the list
            // and output its extension
            if (count($prevFiles) > 0) {
                foreach ($prevFiles as $file) {
                    File::delete($file);
                }
            }
        }
    }

    /**
     * /api/file/{tableName}/{fieldName}/{id?}/{color?}
     *
     * @param $tableName
     * @param $fieldName
     * @param int $id
     * @param boolean $color
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileUpload($tableName, $fieldName, $id = 0, $color = false)
    {
        ini_set('upload_max_filesize', '-1');
        ini_set('memory_limit', '-1');

        // logger(__FILE__ . ':' . __LINE__ . '- ', [$tableName, $fieldName, $id, $color]);

        $affected = true;
        $columns = 0;

        /**
         * If the file being uploaded is part of several uploads
         * always saved
         * If not, I delete the previous ones and update the name in the "x_file" table
         */
        $isMulti      = request()->header('isMulti', 0);
        $isTemporal    = strpos($fieldName, 'massive') !== false;

        $uploadedFile = request()->file($fieldName);

        $baseAssets   = '/assets/adm';
        $strLocation = public_path("$baseAssets/$tableName/$fieldName");

        $originalName  = $uploadedFile->getClientOriginalName();
        $fileExtension = $uploadedFile->getClientOriginalExtension();

        $fileNamePrefix = $tableName . '-' . $id;
        $newName        = uniqid($fileNamePrefix . '-');
        $newNameWithExtension = $newName . '.' . $fileExtension;

        $f = new XFile();

        /**
         * Si el nombre del archivo trae la palabra "massive"
         * se guarda en una carpeta temporal
         */
        if ($isTemporal) {

            $strLocation = public_path("$baseAssets/temporals/$newName/$tableName/$fieldName");

            $uploadedFile->move($strLocation, $newNameWithExtension);

            $location = $strLocation . '/' . $newNameWithExtension;

            $f->name         = $newNameWithExtension;

            if ($fileExtension == 'csv' && ($handle = fopen($location, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $columns = count($data);
                    break;
                }
                fclose($handle);
            }
        } else {

            // 1º - delete previous if not part of multiple upload

            if (!$isMulti) {
                $this->deleteFileWithGlob("{$strLocation}/{$fileNamePrefix}*");
            }

            // 2º - add new

            $file = $uploadedFile->move($strLocation, $newNameWithExtension);
        }

        // logger(__FILE__ . ':' . __LINE__ . ' $file ', [$file]);

        if ($id) {
            // $affected = DB::update("update {$tableName} set {$fieldName} = ? where id = ?", [$fileName, $id]);

            if ($isMulti) {
                $f->entity_id    = $id;
                $f->entity       = $tableName;
                $f->field        = $fieldName;
                $f->name         = $newNameWithExtension;
                $f->nameOriginal = $originalName;
                $f->extension    = $fileExtension;
                $f->save();
            } else {
                $f = XFile::updateOrCreate(
                    [
                        'entity_id' => $id,
                        'entity'    => $tableName,
                        'field'     => $fieldName,
                    ],
                    [
                        'name'         => $newNameWithExtension,
                        'nameOriginal' => $originalName,
                        'extension'    => $fileExtension
                    ]
                );
            }

            if ($color) {
                $colors = $this->getColor($strLocation . '/' . $newNameWithExtension);
                $_colors = json_encode($colors);
                logger(__FILE__ . ':' . __LINE__ . ' $colors ', [$colors]);
                logger(__FILE__ . ':' . __LINE__ . ' $colors ', [$_colors]);
                $affectedColors = DB::update("update {$tableName} set {$fieldName}Colors = ? where id = ?", [$_colors, $id]);
            }
        }

        return $this->sendResponse(
            [
                'success' => $affected,
                $fieldName => $f,
                'columns' => $columns,
            ],
            __('validation.model.image.added', ['model' => $tableName])
        );
    }

    /**
     * intervention/imagecache
     *
     * /api/file/{tableName}/{fieldName}/{id}/{width?}/{height?}/{imageNameOriginal?}
     *
     * examples:
     * /api/file/banner_images/strPathImage/1
     * /api/file/banner_images/strPathImage/1/500/0
     * /api/file/banner_images/strPathImage/1/0/500
     */
    public function fileDown($tableName, $fieldName, $id, $w = 0, $h = 0, $imageNameSaved = '')
    {
        //$imageNameSaved     = null;
        // logger(__FILE__ . ':' . __LINE__ . ' $imageNameSaved "' . $imageNameSaved  . '"');
        $imageNameOriginal = '';
        if (!$imageNameSaved) {
            $imageNameSaved = '-';
            // $imageNameOriginal = DB::table($tableName)->where('id', $id)->value($fieldName);
            $f = XFile::where('entity', "{$tableName}")
                ->where('field', $fieldName)
                ->where('entity_id', $id)
                ->first();
            logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);

            if ($f) {
                $imageNameSaved    = $f->name;
                $imageNameOriginal = $f->nameOriginal;
            }
        }

        $baseAssets = '/assets/adm/';
        $w = (int) $w;
        $h = (int) $h;

        $strLocationImageNotFound = public_path('/assets/images/image-not-found.png');
        $strLocationImageSaved    = public_path($baseAssets . $tableName . '/' . $fieldName . '/' . $imageNameSaved);
        $strLocationImage2show    = File::exists($strLocationImageSaved) ? $strLocationImageSaved : $strLocationImageNotFound;

        // logger(__FILE__ . ':' . __LINE__ . ' $strLocationImageOriginal ', [$tableName, $fieldName, $id, $w, $h, $imageNameOriginal]);
        // logger(__FILE__ . ':' . __LINE__ . ' $strLocationImageOriginal "' . $strLocationImageSaved . '"');
        // logger(__FILE__ . ':' . __LINE__ . ' $strLocationImage2show    "' . $strLocationImage2show . '"');

        $lifeTime = 60 * 24 * 365;

        ini_set('memory_limit', '-1');

        if ($strLocationImage2show && ($w || $h)) {
            // create a cached image and set a lifetime and return as object instead of string
            return Image::cache(function ($image) use ($strLocationImage2show, $w, $h) {
                $image->make($strLocationImage2show)->resize($w > 0 ? $w : null, $h > 0 ? $h : null, function ($constraint) use ($w, $h) {
                    if (!($w > 0 && $h > 0)) {
                        $constraint->aspectRatio();
                    }
                });
            }, $lifeTime, true)->response();
        } else {
            // open an image file
            // $img = Image::make($strLocationImage2show);
            return response()->file($strLocationImage2show);
            //return Response::download($strLocationImage2show, $imageNameOriginal);
            // return File::get($strLocationImage2show, $imageNameOriginal);
        }
    }

    /**
     * https://github.com/nikkanetiya/laravel-color-palette
     *
     */
    public function getColor($sourceImage)
    {

        ini_set('memory_limit', '-1');
        $colors = \ColorPalette::getPalette($sourceImage);

        return $colors;
    }

    // </editor-fold> end upload files

}
