<?php

namespace App\Http\Controllers\API\Misc;

use App\Models\Misc\TrackingJob;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Controllers\AppBaseController;

/**
 * Class TrackingJobController
 */
class TrackingJobAPIController extends AppBaseController
{
    /** @var \App\Models\Misc\TrackingJob */
    public $model;

    public $modelNameCamel = 'TrackingJob';

    public function __construct(TrackingJob $model)
    {
        $this->model = $model;
    }

    public function downloadable($id)
    {
        $f = request()->get('f', '');
        $fName = request()->get('fName', '');

        $disk = app()->environment('local') || app()->environment('testing') ? 's3-01' : 's3-00';

        if (empty($f)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.version')]));
        }

        if (! Storage::disk($disk)->exists($f)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.version')]));
        }

        ini_set('memory_limit', '-1');

        set_time_limit(60 * 60 * 3);

        return Storage::disk($disk)->download($f, $fName);
    }
}
