<?php

namespace Juanfv2\BaseCms\Listeners;

use App\Models\Auth\User;
use App\Models\Misc\VisorLogError;
use App\Traits\ListenerQueueNotification;
use App\Traits\ListenerTracking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Juanfv2\BaseCms\Criteria\RequestCriteriaModel;
use Juanfv2\BaseCms\Events\AnyTableExportEvent;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Utils\BaseCmsExportCSV;
use Juanfv2\BaseCms\Utils\BaseCmsExportExcel;
use ZipArchive;

class AnyTableExportListener implements ShouldQueue
{
    use ListenerQueueNotification, ListenerTracking;

    final public const CHUNK_CSV = 1_000_000;

    final public const CHUNK_QUERY = 10000;

    final public const R_PATH = 'backup-companies';

    /** $model \Illuminate\Database\Eloquent\Model */
    public $model;

    public $exporter;

    private string $attachCsv;

    private string $attachZip;

    private string $csvName;

    private array $labels;

    private array $fNames;

    private array $urlZip;

    private int $counted;

    private int $sent;

    private int $hasResidue;

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
    // public $queue = 'export';
    public function __get($name)
    {
        $q = session('cQueue');
        if ($name == 'queue') {
            return $q;
        }
    }

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(AnyTableExportEvent $event)
    {
        $this->event = $event;

        session(['r-country' => $this->event->data->rCountry]);
        session(['cQueue' => $this->event->data->cQueue]);
        config()->set('database.default', config('base-cms.default_prefix').$this->event->data->rCountry);
        config()->set('tz.timezone', config("tz.{$this->event->data->rCountry}"));

        $user = User::find($this->event->data->user_id);
        Auth::setUser($user);

        $this->trackingInit();

        // sleep(60);
        $this->event->data->attachZip = '';

        $this->model = new $this->event->data->cModel;
        // *: step 1: get data from db
        $this->getHeaders();

        // **: step 2: generate excel
        $this->generateExcel();
    }

    public function failed(AnyTableExportEvent $event, $exception)
    {
        $attachZip = session('attachZip', '');
        $attachCsv = session('attachCsv', '');

        if ($attachZip) {
            // *** step 4: delete file to avoid junks
            Storage::disk('public')->delete($attachZip);
            Storage::disk('public')->delete($attachCsv);
        }

        $this->event = $event;
        $this->event->data->greeting = __('messages.mail.file.subject', ['app_name' => config('app.name')]);
        $this->event->data->subject = __('messages.mail.failed').' '.__('messages.mail.file.subject', ['app_name' => config('app.name'), 'file_name' => 'Excel Report']);

        $this->trackingError();

        VisorLogError::create(['queue' => $this->event->data->cQueue, 'payload' => $exception->getMessage()]);

        // *** step 3: send notification
        $this->sendEmailNotificationError($exception);
    }

    private function getHeaders()
    {
        $request = new Request((array) $this->event->data);
        // $request->headers('r-country', $this->event->data->rCountry);
        $this->model->pushCriteria(new RequestCriteriaModel($request));

        // $items   = $this->model->all();
        // $this->event->data->items   = GenericResource::collection($items);
        $this->event->data->headers = json_decode((string) $request->get('fields'), true, 512, JSON_THROW_ON_ERROR);
    }

    private function generateExcel()
    {
        $this->csvName = ($this->event->data->title ?: 'info').'-'.date('Y-m-d-H-i-s');
        $this->labels = array_values($this->event->data->headers);
        $this->fNames = array_keys($this->event->data->headers);
        $this->counted = 0;
        $this->sent = 1;
        $this->urlZip = [];

        $this->initiateNext();

        $this->model->mQueryWithCriteria()->chunk(self::CHUNK_QUERY, function ($items) {
            foreach ($items as $item) {
                $this->hasResidue = 1;

                $currentItem = [];
                foreach ($this->fNames as $key) {
                    $currentItem[$key] = $item->{$key};
                }
                $this->exporter->addRow($currentItem);

                $this->counted++;

                $init = $this->counted % self::CHUNK_CSV == 0;
                if ($init) {
                    $this->sendEmail();

                    $this->hasResidue = 0;
                }
            } // end foreach ($this->event->data->items ...
        });

        if ($this->hasResidue) {
            $this->sendEmail();
        }

        $this->trackingSuccess('no-info', false);
    }

    public function sendEmail()
    {
        $this->attachCsv = $this->csvName."-parte-{$this->sent}.{$this->event->data->extension}";
        $this->attachZip = "{$this->attachCsv}.zip";

        $this->exporter->finalize(); // writes the footer, flushes remaining data to browser.

        $pathCurrentCsv = Storage::disk('local')->path(self::R_PATH.'/'.$this->attachCsv);
        $pathCurrentZip = Storage::disk('local')->path(self::R_PATH.'/'.$this->attachZip);

        // logger(__FILE__ . ':' . __LINE__ . '  $this->attachZip ', [$pathCurrentCsv, $pathCurrentZip]);
        $zip = new ZipArchive;
        $zip->open($pathCurrentZip, ZipArchive::CREATE);
        $zip->addFile($pathCurrentCsv, $this->attachCsv);
        $zip->close();

        $s3path = 'zips/'.$this->attachZip;

        $disk = app()->environment('local') || app()->environment('testing') ? 's3-01' : 's3-00';

        Storage::disk($disk)->put($s3path, File::get($pathCurrentZip));

        $this->urlZip[] = $s3path;

        // success:::

        $company = auth()->user()->role_id == 8 ? 'ransanet' : 'admin';

        $this->event->data->actionUrl = url("{$company}/{$this->event->data->rCountry}/reports/tracking-jobs");
        $this->event->data->greeting = __('messages.mail.file.subject', ['app_name' => config('app.name'), 'file_name' => 'Excel Report']);
        $this->event->data->subject = __('messages.mail.file.subject', ['app_name' => config('app.name'), 'file_name' => 'Excel Report']);

        $this->trackingSuccess($this->urlZip);

        $this->event->data->outroLines = [
            'Nombre del proceso' => $this->event->data->cQueue,
            'URL' => url("{$company}/{$this->event->data->rCountry}/reports/tracking-jobs"),
            'Cantidad' => "{$this->counted}/{$this->event->data->qq} . PARTE: {$this->sent}",
        ];
        // *** step 3: send notification
        $this->sendEmailNotification();

        // **** step 4: delete file to avoid junks
        Storage::disk('local')->delete(self::R_PATH.'/'.$this->attachCsv);
        Storage::disk('local')->delete(self::R_PATH.'/'.$this->attachZip);

        $this->sent++;
        $this->initiateNext();
    }

    public function initiateNext()
    {
        // initialize next
        // logger(__FILE__ . ':' . __LINE__ . ' // initialize next ', [0]);
        $this->attachCsv = $this->csvName."-parte-{$this->sent}";

        $this->exporter = new BaseCmsExportCSV(self::R_PATH.'/'.$this->attachCsv, $this->event->data->extension, BaseCmsExportCSV::TO_FILE);

        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $this->exporter = new BaseCmsExportExcel(self::R_PATH.'/'.$this->attachCsv, $this->event->data->extension, BaseCmsExportCSV::TO_FILE);
        }

        $this->exporter->initialize($this->labels);

        $this->trackingInit(false);
        // sleep(60);
    }
}
