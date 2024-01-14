<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    public function admin()
    {
        debugbar()->disable();

        $index = public_path('_.admin/browser/index.html');

        if (File::exists($index)) {
            return File::get($index);
        }

        return view('under-construction');
    }

    public function adminDevelopment()
    {
        debugbar()->enable();

        $index = public_path('_.admin/browser/index.html');

        if (File::exists($index)) {
            return File::get($index);
        }

        return $this->admin();
    }

    public function artisan($key1, $key2 = null)
    {
        // ? artisan:
        $k = $key1 . ($key2 ? ':' . $key2 : '');

        try {
            // echo '<br>php artisan view:clear...';
            $parameters = request('params', '[]');

            $params = json_decode((string) $parameters, true, 512, JSON_THROW_ON_ERROR);

            \Illuminate\Support\Facades\Artisan::call($k, $params);

            $params['cmd'] = '<br>php artisan ' . $k . ' ' . $parameters;

            // $params['msg'] = '<br>php artisan ' . $k . ' completed';
            return $params;
        } catch (\Exception $e) {
            // echo '<br>' . $e->getMessage();
            return $e;
        }
    }
}
