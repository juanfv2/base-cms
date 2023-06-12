<?php

namespace App\Models\Misc;

use App\Services\FirebaseCloudMessaging;
use App\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;

class TrackingJob extends Model
{
    use BaseCmsModel;
    use HasFactory;
    use ConvertTZ;

    public $table = 'tracking_jobs';

    public $fillable = [
        'user_id',
        'queue',
        'status',
        'link',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'queue' => 'string',
        'status' => 'integer',
        'link' => 'json',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required',
        'queue' => 'required|string|max:191',
        'status' => 'required|integer',
        'link' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
    ];

    // public $hidden = ['createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at',];

    final public const STATUS_PENDING = 0;

    final public const STATUS_INITIATED = 1;

    final public const STATUS_FINISHED_SUCCESS = 2;

    final public const STATUS_FINISHED_ERROR = 3;

    public function saveStatus($st, $notify = true)
    {
        $this->status = $st;
        $this->save();

        if ($notify) {
            $queueName = $this->queue;
            $country = session('r-country', request()->header('r-country', '.l.'));
            $body = __('messages.notification.title');
            $title = __("messages.notification.tracking.status.{$this->status}", ['job' => $queueName]);
            $to = "{$country}_tracking_job_{$this->user_id}";
            $type = 'topics';
            $firebase = new FirebaseCloudMessaging();
            $firebase->to([$to], $this->toArray(), $title, $body, $type);
        }
    }

    public function sPending()
    {
        $this->saveStatus(TrackingJob::STATUS_PENDING);
    }

    public function sInitiated($notify = true)
    {
        $this->saveStatus(TrackingJob::STATUS_INITIATED, $notify);
    }

    public function sFinishedSuccess($notify = true)
    {
        $this->saveStatus(TrackingJob::STATUS_FINISHED_SUCCESS, $notify);
    }

    public function sFinishedError()
    {
        $this->saveStatus(TrackingJob::STATUS_FINISHED_ERROR);
    }
}
