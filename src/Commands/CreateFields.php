<?php

namespace Juanfv2\BaseCms\Commands;

use App\Models\Misc\ItemField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateFields extends Command
{
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
    protected $description = '(-f): add/update fields  from "z_base_cms_menus_permissions"
                              (-t): create fields file to   "z_base_cms_menus_permissions"
                              (--country):
                              ';

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
        $country = $this->option('country');
        $saveFields_file2table = $this->option('saveFields_file2table');
        $saveFields_table2file = $this->option('saveFields_table2file');

        if ($country) {
            config()->set('database.default', config('base-cms.default_prefix').$country);
        }

        if ($saveFields_table2file) {
            $this->updateLabelsFile();
        }

        if ($saveFields_file2table) {
            $this->saveFields();
        }

        return 1;
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
                    $this->info("{$pc->field}");
                }
            }

            $this->info("Fields : $result");
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
                    $this->labelsFile->{$modelName}->{$f['name']} = $f;
                }
            }
        );
    }

    public function save2ts($path)
    {
        if (File::exists($path)) {
            $lString = json_encode($this->labelsFile, JSON_ERROR_NONE);
            $l_ts = "export const l: any = {$lString}

            function getLabels(values: any) {
                function isObject(value: any) {
                  return typeof value === 'object' && value !== null && !Array.isArray(value)
                }

                const labels = []

                for (let key1 in values) {
                  const l1 = values[key1]

                  if (isObject(l1)) {
                    for (let key2 in l1) {
                      const l2 = l1[key2]

                      if (isObject(l2)) {
                        labels.push(l2)
                      }
                    }
                  }
                }

                return labels
              }

              // console.log('labels', JSON.stringify(getLabels(l)))
";

            File::put($path, $l_ts);
        }
    }

    public function getTemplate($path)
    {
        $indexStr = File::get($path);

        $this->labelsFile = json_decode($indexStr, false, 512, 0);
    }
}
