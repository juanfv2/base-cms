<?php

namespace Juanfv2\BaseCms\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;

class AngularAutoCompleteComponentGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;
    /** @var string */
    private $primaryKey;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        // dd($this->commandData);
        $this->path = base_path('angular/') . $this->commandData->config->mDashed . '/';
        $name = $this->commandData->config->mDashed . '-auto-complete.component.';
        $this->fileName = $name . 'ts';
    }

    public function generate()
    {
        $this->generateTs();
    }

    public function generateTs()
    {
        $templateData = get_template("angular.auto_complete_list_component", 'laravel-generator');
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nAPI ListComponent created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        list($searchables1, $searchables2) = $this->getSearchables();

        $templateData = str_replace('$SEARCHABLE_FIELDS_1$',  implode(infy_nl_tab(1, 2), $searchables1), $templateData);
        $templateData = str_replace('$SEARCHABLE_FIELDS_2$',  implode(',' . infy_nl_tab(1, 2), $searchables2), $templateData);
        $templateData = str_replace('$RELATION_MODEL_NAMES$', implode(',',  $this->generateRelationModelNames()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_FIELDS_1$', implode("\n", $this->generateRelationsFields()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_FIELDS_2$', implode("\n", $this->generateRelationsFields2()), $templateData);

        return $templateData;
    }

    private function getSearchables()
    {
        $searchables1 = [];
        $searchables2 = [];
        $this->primaryKey = 'id';

        foreach ($this->commandData->fields as $field) {
            if ($field->isSearchable) {
                $searchables1[] = "g.push(new JfCondition(`OR \${this.labels.{$this->commandData->config->mCamel}.{$field->name}.field} like`, term));";
                $searchables2[] = "`\${this.labels.{$this->commandData->config->mCamel}.{$field->name}.field}`";
            }
            if ($field->isPrimary) {
                $searchables1[] = "g.push(new JfCondition(`OR \${this.labels.{$this->commandData->config->mCamel}.{$field->name}.field} like`, term));";
                $searchables2[] = "`\${this.labels.{$this->commandData->config->mCamel}.{$field->name}.field}`";
                $this->primaryKey = $field->name;
            }
        }

        return [$searchables1, $searchables2];
    }

    private function generateRelationModelNames()
    {
        $relations = [];

        foreach ($this->commandData->relations as $relation) {
            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;
            if ($type != 'mt1') {
                continue;
            }

            $relationShipText = $field;

            if (!empty($relationsOpts)) {
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
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

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
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;
            $fieldFK = (isset($relation->inputs[1])) ? $relation->inputs[1] : null;

            if ($type != 'mt1') {
                continue;
            }

            $fieldSnape = Str::camel($field);
            $relationText = <<<EOF
            if (this.m$field) {
                conditions.push(new JfCondition(`\${this.labels.{$this->commandData->config->mCamel}.tableName}.$fieldFK`, this.m$field.id));
            }
            EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('API Controller file deleted: ' . $this->fileName);
        }
    }
}
