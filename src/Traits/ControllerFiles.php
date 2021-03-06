<?php

namespace  Juanfv2\BaseCms\Traits;

use App\Models\Auth\XFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

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

    public static function getPathFileName($location, $name)
    {
        $names          = explode('.', $name);
        $nameWithoutExt = Arr::first($names);
        $temp           = storage_path("app/{$location}/{$nameWithoutExt}");

        return $temp;
    }

    /**
     * param $strLocationAndFileNamePrefix:
     *   like this "{$strLocation}/{$fileNamePrefix}*"
     */
    public static function deleteFileWithGlob($strLocationAndFileNamePrefix)
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
    public function fileUpload($tableName, $fieldName, $id = 0, $color = false, Request $request)
    {
        ini_set('upload_max_filesize', '-1');
        ini_set('memory_limit', '-1');

        $xFile                = new XFile();
        $columns              = 0;
        $isMulti              = $request->header('isMulti', 0);
        $isTemporal           = strpos($fieldName, 'massive') !== false;
        $baseAssets           = 'public/assets/adm';
        $strLocation          = "$baseAssets/$tableName/$fieldName";
        $originalName         = $request->$fieldName->getClientOriginalName();
        $fileExtension        = $request->$fieldName->extension();
        $fileNamePrefix       = $tableName . '-' . $id;
        $newName              = uniqid($fileNamePrefix . '-');
        $newNameWithExtension = $newName . '.' . $fileExtension;

        /**
         * Si el nombre del archivo trae la palabra "massive"
         * se guarda en una carpeta temporal
         */
        if ($isTemporal) {

            $strLocation = "$baseAssets/temporals/$newName/$tableName/$fieldName";
            $path        = $request->$fieldName->storeAs($strLocation, $newNameWithExtension);
            $parts       = explode('/', $path);
            $name        = Arr::last($parts);
            $location    = storage_path("app/$path");
            $xFile->name = $name;
            $xFile->nameOriginal = $originalName;

            if (($fileExtension == 'csv' || $fileExtension == 'txt') && ($handle = fopen($location, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $columns = count($data);
                    break;
                }
                fclose($handle);
            }
        } else {

            $path = $request->$fieldName->store($strLocation);
        }

        // logger(__FILE__ . ':' . __LINE__ . ' $file ', [$file]);

        if ($id) {

            if ($isMulti) {

                $parts               = explode('/', $path);
                $name                = Arr::last($parts);

                $xFile->entity_id    = $id;
                $xFile->entity       = $tableName;
                $xFile->field        = $fieldName;
                $xFile->name         = $name;
                $xFile->nameOriginal = $originalName;
                $xFile->extension    = $fileExtension;
                $xFile->publicPath   = Storage::url($path);

                if ($color && class_exists('\ColorThief\ColorThief')) {

                    $colors            = $this->getColor($path);
                    $data['colors']    = $colors;
                    $xFile->data       = $data;
                }

                $xFile->save();
            } else {

                $xFile = XFile::firstOrNew([
                    'entity_id' => $id,
                    'entity'    => $tableName,
                    'field'     => $fieldName,
                ]);

                if ($xFile->id) {

                    $temp = $this->getPathFileName($strLocation, $xFile->name);

                    $this->deleteFileWithGlob("{$temp}*");
                }

                $parts               = explode('/', $path);
                $name                = Arr::last($parts);
                $xFile->name         = $name;
                $xFile->nameOriginal = $originalName;
                $xFile->extension    = $fileExtension;
                $xFile->publicPath   = Storage::url($path);

                if ($color && class_exists('\ColorThief\ColorThief')) {

                    $colors = $this->getColor($path);

                    $data = $xFile->data ?? [];
                    $data['colors'] = $colors;

                    $xFile->data = $data; // json_encode($data);

                    // logger(__FILE__ . ':' . __LINE__ . ' $xFile 2 ', [$xFile]);
                }

                $xFile->save();
            }
        }

        return $this->sendResponse(
            [$fieldName => $xFile, 'columns' => $columns,],
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

        $baseAssets               = 'public/assets/adm/';
        $w                        = (int) $w;
        $h                        = (int) $h;
        $strLocationImageNotFound = $baseAssets . '../images/image-not-found.png';
        $strLocationImageSaved    = $baseAssets . $tableName . '/' . $fieldName . '/' . $imageName;
        $exists                   = Storage::exists($strLocationImageSaved);
        $strLocationImage2show    = $exists ? $strLocationImageSaved : $strLocationImageNotFound;

        ini_set('memory_limit', '-1');

        if ($strLocationImage2show && ($w || $h)) {

            $parts                    = explode('.', $imageName);
            $ext                      = end($parts);
            $strLocationImage2showNew = Str::replaceLast(".{$ext}", "-{$w}x{$h}.{$ext}", $strLocationImage2show);
            $exists                   = Storage::exists($strLocationImage2showNew);
            $temp                     = storage_path("app/$strLocationImage2show");

            if (!$exists) {
                // use jpg format and quality of 100
                $resized_image = Image::make($temp)->resize(
                    $w > 0 ? $w : null,
                    $h > 0 ? $h : null,
                    function ($constraint) use ($w, $h) {
                        if (!($w > 0 && $h > 0)) {
                            $constraint->aspectRatio();
                        }
                    }
                )->stream($ext, 100);
                // then use Illuminate\Support\Facades\Storage
                Storage::put($strLocationImage2showNew, $resized_image);
            }

            $temp = storage_path("app/$strLocationImage2showNew");

            return response()->file($temp);
        } else {
            $temp = storage_path("app/$strLocationImage2show");

            return response()->file($temp);
        }
    }

    /**
     * https://github.com/ksubileau/color-thief-php
     *
     */
    public function getColor($sourceImage)
    {

        ini_set('memory_limit', '-1');
        // $colors = \ColorPalette::getPalette($sourceImage);
        $colors = \ColorThief\ColorThief::getPalette(storage_path("app/$sourceImage"));

        return $colors;
    }
}
