<?php

namespace Juanfv2\BaseCms\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Juanfv2\BaseCms\Models\Auth\XFile;

trait ControllerFiles
{
    public function updateXfile($id, Request $request)
    {
        $input = $request->all();
        $f     = XFile::find($id);

        if (!$f) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Archivo']));
        }

        $f->fill($input);
        $f->save();

        return ['id' => $f->id];
    }

    public function deleteXfile($id)
    {
        $f = XFile::find($id);

        if (!$f) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Archivo']));
        }

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
     *
     * Header: isMulti = true
     * If the file being uploaded is part of several uploads always saved
     * Header: isMulti = false
     * If not, The field is deleted the previous ones and update the name in the "x_file" table
     *
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

        $columns              = 0;
        $isMulti              = request()->header('isMulti', 0);
        $isTemporal           = strpos($fieldName, 'massive') !== false;
        $uploadedFile         = request()->file($fieldName);
        $baseAssets           = '/assets/adm';
        $strLocation          = public_path("$baseAssets/$tableName/$fieldName");
        $originalName         = $uploadedFile->getClientOriginalName();
        $fileExtension        = $uploadedFile->getClientOriginalExtension();
        $fileNamePrefix       = $tableName . '-' . $id;
        $newName              = uniqid($fileNamePrefix . '-');
        $newNameWithExtension = $newName . '.' . $fileExtension;
        $xFile                = new XFile();

        /**
         * Si el nombre del archivo trae la palabra "massive"
         * se guarda en una carpeta temporal
         */
        if ($isTemporal) {

            $strLocation = public_path("$baseAssets/temporals/$newName/$tableName/$fieldName");

            $uploadedFile->move($strLocation, $newNameWithExtension);

            $location    = $strLocation . '/' . $newNameWithExtension;
            $xFile->name = $newNameWithExtension;

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

            if ($isMulti) {
                $xFile->entity_id    = $id;
                $xFile->entity       = $tableName;
                $xFile->field        = $fieldName;
                $xFile->name         = $newNameWithExtension;
                $xFile->nameOriginal = $originalName;
                $xFile->extension    = $fileExtension;

                if ($color && class_exists('\ColorPalette')) {

                    $colors = $this->getColor($strLocation . '/' . $newNameWithExtension);

                    $data['colors'] = $colors;

                    $xFile->data = $data;
                }

                $xFile->save();
            } else {

                $xFile = XFile::firstOrNew([
                    'entity_id' => $id,
                    'entity'    => $tableName,
                    'field'     => $fieldName,
                ]);

                $xFile->name = $newNameWithExtension;
                $xFile->nameOriginal = $originalName;
                $xFile->extension = $fileExtension;

                // logger(__FILE__ . ':' . __LINE__ . ' $xFile 1 ', [$xFile]);

                if ($color && class_exists('\ColorPalette')) {

                    $colors = $this->getColor($strLocation . '/' . $newNameWithExtension);

                    $data = $xFile->data ?? [];
                    $data['colors'] = $colors;

                    $xFile->data = $data; // json_encode($data);

                    // logger(__FILE__ . ':' . __LINE__ . ' $xFile 2 ', [$xFile]);
                }

                $xFile->save();
            }
        }

        return $this->sendResponse(
            __('validation.model.image.added', ['model' => $tableName]),
            [
                $fieldName => $xFile,
                'columns' => $columns,
            ]
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
    public function fileDown($tableName, $fieldName, $id, $w = 0, $h = 0, $imageName = '')
    {
        if (!$imageName) {
            $imageName = '-';
            $f = XFile::where('entity', "{$tableName}")
                ->where('field', $fieldName)
                ->where('entity_id', $id)
                ->first();

            if ($f) {
                $imageName    = $f->name;
            }
        }

        $baseAssets               = '/assets/adm/';
        $w                        = (int) $w;
        $h                        = (int) $h;
        $strLocationImageNotFound = public_path('/assets/images/image-not-found.png');
        $strLocationImageSaved    = public_path($baseAssets . $tableName . '/' . $fieldName . '/' . $imageName);
        $strLocationImage2show    = File::exists($strLocationImageSaved) ? $strLocationImageSaved : $strLocationImageNotFound;
        $lifeTime                 = 60 * 24 * 365;

        ini_set('memory_limit', '-1');

        if ($strLocationImage2show && ($w || $h)) {
            // create a cached image and set a lifetime and return as object instead of string
            return Image::cache(function ($image) use ($strLocationImage2show, $w, $h) {
                $image->make($strLocationImage2show)
                    ->resize(
                        $w > 0 ? $w : null,
                        $h > 0 ? $h : null,
                        function ($constraint) use ($w, $h) {
                            if (!($w > 0 && $h > 0)) {
                                $constraint->aspectRatio();
                            }
                        }
                    );
            }, $lifeTime, true)->response();
        } else {
            return response()->file($strLocationImage2show);
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
}
