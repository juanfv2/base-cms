<?php

namespace Juanfv2\BaseCms\Commands;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Misc\ItemField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CreateFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'base-cms:fields {--f|updateFile} {--t|updateTable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(-f): update labels file, (-t): update table';

    private $responseList;

    private $labelsFile;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $updateFile = $this->option('updateFile');
        $updateTable = $this->option('updateTable');

        if ($updateTable) {
            $this->createFields();
        }

        if ($updateFile) {
            $this->updateLabels();
        }

        return 1;
    }

    public function createFields()
    {
        $q = database_path('data/auth/item_fields.json');
        $qq = File::exists($q);

        if ($qq) {
            $qString = File::get($q);
            $json = json_decode($qString, null, 512, JSON_THROW_ON_ERROR);

            foreach ($json as $pc) {

                $f = ItemField::updateOrCreate([
                    'field' => $pc->field,
                    'name' => $pc->name,
                    'model' => $pc->model,

                ], (array) $pc);

                logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);
            }
            $count = ItemField::count();

            $this->info("Fields : $count");
        } else {
            $this->error("File not found: $q");
        }
    }

    public function updateLabels()
    {

        $index1 = resource_path('front-end/admin-angular/src/environments/l.template.dev.json');
        $index2 = resource_path('front-end/admin-angular/src/environments/l.ts');

        $this->responseList = ItemField::orderBy('model')
            ->orderBy('model')
            ->orderBy('index')
            ->orderBy('id')
            ->get()
            ->setHidden([
                'id', 'table', 'alias',
                'key', 'extra', 'allowNull',
                'index', 'defaultValue',
                'createdBy', 'updatedBy',
                'created_at', 'updated_at', 'deleted_at',
            ]);

        $this->getTemplate($index1);

        $this->responseList->each(fn ($m) => $this->eachModel($m->model));

        $this->save2ts($index2);

        $this->info("File updated");
    }

    public function eachModel($k)
    {
        $fields = $this->responseList->filter(fn ($i) => $i->model === $k);

        if ($fields) {
            $this->eachField($fields, $k);
        }
    }

    public function eachField($fields, $modelName)
    {
        $fields->each(
            function ($f) use ($modelName) {

                if (isset($this->labelsFile->{$modelName})) {
                    $this->labelsFile->{$modelName}->{$f['name']} = $f;
                }
            }
        );
    }

    public function save2ts($path)
    {
        if (File::exists($path)) {
            $lString = json_encode($this->labelsFile, JSON_ERROR_NONE);
            $l_ts = "export const l: any = {$lString}";

            File::put($path, $l_ts);
        }
    }

    public function getTemplate($path)
    {
        $indexStr = File::get($path);

        $this->labelsFile = json_decode($indexStr, false, 512, 0);
    }
}
