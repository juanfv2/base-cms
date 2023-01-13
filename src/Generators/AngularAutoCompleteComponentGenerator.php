<?php

namespace Juanfv2\BaseCms\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;

class AngularAutoCompleteComponentGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $mPath = config('laravel_generator.path.angular', 'angular/');
        $this->path = $mPath.$this->config->modelNames->dashed.'/';

        $name = $this->config->modelNames->dashed.'-auto-complete.component.';
        $this->fileName = $name.'ts';
    }

    public function variables(): array
    {
        return array_merge([], $this->docsVariables());
    }

    public function generate()
    {
        $this->generateTs();
    }

    public function generateTs()
    {
        $viewName = 'auto_complete_component';
        $templateData = view('laravel-generator::angular.'.$viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment('AutoComplete Component created: ');
        $this->config->commandInfo($this->fileName);
    }

    private function getSearchables()
    {
        $searchables1 = [];
        $searchables2 = [];

        foreach ($this->config->fields as $field) {
            if ($field->name == 'created_by' || $field->name == 'updated_by') {
                continue;
            }
            if ($field->isSearchable) {
                $searchables1[] = "g.push(new JfCondition(`OR \${this.labels.{$this->config->modelNames->camel}.{$field->name}.field} like`, term));";
                $searchables2[] = "`\${this.labels.{$this->config->modelNames->camel}.{$field->name}.field}`,";
            }
            if ($field->isPrimary) {
                $searchables1[] = "g.push(new JfCondition(`OR \${this.labels.{$this->config->modelNames->camel}.{$field->name}.field} like`, term));";
                $searchables2[] = "`\${this.labels.{$this->config->modelNames->camel}.{$field->name}.field}`,";
            }
        }

        return [$searchables1, $searchables2];
    }

    private function generateRelationModelNames()
    {
        $relations = [];

        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            if ($type != 'mt1') {
                continue;
            }

            $relationShipText = $field;

            if (! empty($relationsOpts)) {
                if (in_array($field, $relationsOpts)) {
                    $relations[] = $relationShipText;
                }
            } else {
                $relations[] = $relationShipText;
            }
        }

        return $relations;
    }

    private function generateRelationsFields()
    {
        $relations = [];
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

            if ($type != 'mt1') {
                continue;
            }

            $fieldSnape = Str::camel($field);
            $relationText = <<<EOF
            m$field?: $field;
            @Input()
            set $fieldSnape($fieldSnape: $field) {
                this.value = undefined;
                this.m$field = $fieldSnape;
            }
            EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateRelationsFields2()
    {
        $relations = [];
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            $fieldFK = $relation->inputs[1] ?? null;

            if ($type != 'mt1') {
                continue;
            }

            $fieldSnape = Str::camel($field);
            $relationText = <<<EOF
            if (this.m$field) {
                conditions.push(new JfCondition(`\${this.labels.{$this->config->modelNames->camel}.tableName}.$fieldFK`, this.m$field.id));
            }
            EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    protected function docsVariables(): array
    {
        $variables = [];
        [$searchables1, $searchables2] = $this->getSearchables();

        $variables['searchable_1'] = implode(infy_nl_tab(), $searchables1);
        $variables['searchable_2'] = implode(infy_nl_tab(), $searchables2);
        $variables['relation_model_names'] = implode(',', $this->generateRelationModelNames());
        $variables['relations_1'] = implode(infy_nl_tab(), $this->generateRelationsFields());
        $variables['relations_2'] = implode(infy_nl_tab(), $this->generateRelationsFields2());

        return $variables;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Controller file deleted: '.$this->fileName);
        }
    }
}
