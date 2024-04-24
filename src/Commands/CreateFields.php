<?php

namespace Juanfv2\BaseCms\Commands;

use App\Models\Misc\ItemField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateFields extends Command
{
    public string $separator = '# ---------------------------------------------------------------------------- #';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'base-cms:fields {--country=} {--f|saveFields_file2table} {--t|saveFields_table2file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(-f): add/update fields  from "z_base_cms_fields"
                              (-t): create fields file to   "l.ts"
                              (--country):
                              ';

    private $responseList;

    private $labelsFile;

    protected $rCountry;

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
        $this->rCountry = $this->option('country');

        if ($this->rCountry == 'all') {
            $this->execAll();

            return 1;
        }

        $this->execOne();

        return 1;
    }

    public function execAll()
    {
        $countries = config('countries');
        foreach ($countries as $key => $country) {
            $this->rCountry = $country['code'];
            $this->execOne();
        }
    }

    public function execOne()
    {
        $saveFields_file2table = $this->option('saveFields_file2table');
        $saveFields_table2file = $this->option('saveFields_table2file');

        if ($this->rCountry) {
            config()->set('database.default', config('base-cms.default_prefix').$this->rCountry);
        }

        if ($saveFields_table2file) {
            $this->updateLabelsFile();
        }

        if ($saveFields_file2table) {
            $this->saveFields();
        }

        $this->info("{$this->rCountry} finished.");
    }

    public function saveFields()
    {
        $q = database_path('data/auth/z_base_cms_fields.json');
        $qq = File::exists($q);

        if ($qq) {
            $qString = File::get($q);
            $json = json_decode($qString, null, 512, JSON_THROW_ON_ERROR);
            $result = 0;

            foreach ($json as $pc) {

                $r = ItemField::updateOrCreate([
                    'field' => $pc->field,
                    'name' => $pc->name,
                    'model' => $pc->model,

                ], (array) $pc) ? 1 : 0;

                $result += $r;

                if ($r) {
                    $this->info("{$pc->model} '{$pc->field}' {$pc->name}");
                } else {
                    $this->warn($this->separator);
                    $this->warn("{$pc->field}");
                    $this->warn($this->separator);
                }
            }
            $count = ItemField::count();
            $this->info("Fields modified: $result");
            $this->info("Fields total: $count");
        } else {
            $this->error("File not found: $q");
        }
    }

    public function updateLabelsFile()
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

        $this->info('File updated');
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

                    if ($f->model) {
                        unset($f->model);
                    }

                    $this->labelsFile->{$modelName}->{$f['name']} = $f;
                }
            }
        );
    }

    public function save2ts($path)
    {
        if (File::exists($path)) {
            $lString = json_encode($this->labelsFile, JSON_ERROR_NONE);
            $l_ts = "import {DBType} from 'base-cms' // from '@juanfv2/base-cms';
            \n\nexport const l = {$lString};
            \n\n// console.log('labels', JSON.stringify(l.getLabels(l)))
            ";

            $pattern = '/("fixed":false}|"fixed":true})/';
            $replacement = '$1 as DBType';
            $l_ts = preg_replace($pattern, $replacement, $l_ts);

            $pattern = '/"k":{},/';
            $replacement = "  k: {} as any,

            isObject: (value: any) => typeof value === 'object' && value !== null && !Array.isArray(value),
          
            getDBFields: (values: any): DBType[] => {
              const labels = []
              for (let key1 in values) {
                const model = values[key1]
                if (l.isObject(model)) {
                  labels.push(model)
                }
              }
              return labels
            },
          
            getLabels: (values: any) => {
              const labels = []
              for (let key1 in values) {
                const model = values[key1]
                if (l.isObject(model)) {
                  let _index = 0
                  for (let key2 in model) {
                    const field = model[key2]
                    if (l.isObject(field)) {
                      field.model = key1
                      field.index = _index
                      labels.push(field)
                      _index++
                    }
                  }
                }
              }
              return labels
            },";

            $l_ts = preg_replace($pattern, $replacement, $l_ts);

            File::put($path, $l_ts);
        }
    }

    public function getTemplate($path)
    {
        $indexStr = File::get($path);

        $this->labelsFile = json_decode($indexStr, false, 512, 0);
    }
}