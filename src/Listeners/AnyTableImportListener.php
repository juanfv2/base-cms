<?php

namespace Juanfv2\BaseCms\Listeners;

use App\Models\Auth\User;
use App\Models\Misc\VisorLogError;
use App\Traits\ListenerQueueNotification;
use App\Traits\ListenerTracking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Events\AnyTableImportEvent;
use Juanfv2\BaseCms\Traits\ImportableExportable;

class AnyTableImportListener implements ShouldQueue
{
    use ImportableExportable, ListenerQueueNotification, ListenerTracking;

    /** @var AnyTableImportEvent */
    public $event;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    // public $queue = 'seeder';
    public function __get($name)
    {
        $q = session('cQueue');
        if ($name == 'queue') {
            return $q;
        }
    }

    public function __construct()
    {
        //
    }

    public function handle(AnyTableImportEvent $event)
    {
        $this->event = $event;
        $this->event->data->created = 0;

        session(['r-country' => $this->event->data->rCountry]);
        config()->set('database.default', config('base-cms.default_prefix').$this->event->data->rCountry);

        $user = User::find($this->event->data->user_id);
        Auth::setUser($user);

        $this->trackingInit();

        $this->processing();

        $success = __('messages.mail.success');
        $subject = __('messages.mail.file.subject', ['app_name' => config('app.name'), 'file_name' => $this->event->data->massiveQueryFileNameOriginal]);
        $failedMsg = __('messages.mail.file.successWithErrors');
        $failed = VisorLogError::where(['queue' => $this->event->data->cQueue])->count();

        if ($failed) {
            $this->trackingError();
            $success = $failedMsg;
        } else {
            $this->trackingSuccess();
        }

        $this->event->data->outroLines = [
            'Nombre del proceso' => $this->event->data->cQueue,
            'Procesados' => $this->event->data->created,
            'Inconsistencias' => $failed,
        ];
        $this->event->data->subject = "$subject $success";
        $this->event->data->greeting = $subject;

        $this->sendEmailNotification();
    }

    public function failed(AnyTableImportEvent $event, $exception)
    {
        $this->event = $event;
        if (! isset($this->event->data->created)) {
            $this->event->data->created = 0;
        }

        $basename = basename((string) $this->event->data->massiveQueryFileName);
        $fileTempName = pathinfo($basename, PATHINFO_FILENAME);

        Storage::disk('public')->deleteDirectory("assets/adm/{$this->event->data->rCountry}/temporals/$fileTempName");

        $failed = __('messages.mail.failed');
        $subject = __('messages.mail.file.subject', ['app_name' => config('app.name'), 'file_name' => $this->event->data->massiveQueryFileNameOriginal]);

        $this->event->data->outroLines = [
            'Nombre del proceso' => $this->event->data->cQueue,
            'Procesados' => $this->event->data->created,
            'Inconsistencias' => $failed,
        ];
        $this->event->data->subject = "$subject $failed";
        $this->event->data->greeting = $subject;

        $this->trackingError();

        VisorLogError::create(['queue' => $this->event->data->cQueue, 'payload' => $exception->getMessage()]);

        $this->sendEmailNotificationError($exception);
    }

    private function processing()
    {
        $basename = basename((string) $this->event->data->massiveQueryFileName);
        $fileTempName = pathinfo($basename, PATHINFO_FILENAME);
        $baseAssets = 'assets/adm/'.$this->event->data->rCountry;
        $basePath = "$baseAssets/temporals/$fileTempName";
        $path = "$basePath/{$this->event->data->table}/{$this->event->data->massiveQueryFieldName}/{$this->event->data->massiveQueryFileName}";
        $_versionsCsv_File = Storage::disk('public')->path($path);

        if (! Storage::disk('public')->exists($path)) {
            throw new \Juanfv2\BaseCms\Exceptions\NoReportException("Archivo no encontrado: '{$this->event->data->massiveQueryFileNameOriginal}'");
        }

        $handle = null;

        if (($handle = fopen($_versionsCsv_File, 'r')) !== false) {
            $delimiter = _file_delimiter($_versionsCsv_File);

            $this->event->data->created = $this->importing(
                $handle,
                $this->event->data->table,
                $this->event->data->primaryKeyName,
                $this->event->data->keys,
                $delimiter,
                $this->event->data->cModel,
                $this->event->data->extra_data
            );
        }

        Storage::disk('public')->deleteDirectory($basePath);
    }
}
