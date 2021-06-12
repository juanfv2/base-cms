<?php

namespace Juanfv2\BaseCms\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;

class AngularListComponentGenerator extends BaseGenerator
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
        $this->path = base_path('angular/') . $this->commandData->config->mCamel . '/';
        $name = $this->commandData->config->mCamel . '-list.component.';
        $this->fileName = $name . 'ts';
        $this->fileNameSpec = $name . 'spec.ts';
        $this->fileNameScss = $name . 'scss';
        $this->fileNameHtml = $name . 'html';
    }

    public function generate()
    {

        $this->generateScss();
        $this->generateSpec();
        $this->generateHtml();
        $this->generateTs();
    }

    public function generateTs()
    {
        $templateData = get_template("angular.list_component", 'laravel-generator');
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nAPI ListComponent created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    public function generateScss()
    {
        $templateData = get_template("angular.list_component_scss", 'laravel-generator');
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileNameScss, $templateData);

        $this->commandData->commandComment("\nAPI ListComponent created: ");
        $this->commandData->commandInfo($this->fileNameScss);
    }

    public function generateSpec()
    {
        $templateData = get_template("angular.list_component_spec", 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileNameSpec, $templateData);

        $this->commandData->commandComment("\nAPI ListComponent created: ");
        $this->commandData->commandInfo($this->fileNameSpec);
    }

    public function generateHtml()
    {
        $templateData = get_template("angular.list_component_html", 'laravel-generator');
        $templateData = $this->fillTemplateHtml($templateData);

        FileUtil::createFile($this->path, $this->fileNameHtml, $templateData);

        $this->commandData->commandComment("\nAPI ListComponent created: ");
        $this->commandData->commandInfo($this->fileNameHtml);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $searchables = $this->getSearchables();

        if ($this->commandData->getOption('primary')) {
            $primary = $this->commandData->getOption('primary');
        } else {
            $primary = '';
            if ($this->commandData->getOption('fieldsFile') && $this->primaryKey != 'id') {
                $primary = $this->primaryKey;
            }
        }

        $templateData = str_replace('$NAME_PK$',                        $primary, $templateData);
        $templateData = str_replace('$SEARCHABLE_FIELDS$',              implode(',' . infy_nl_tab(1, 2), $searchables), $templateData);
        $templateData = str_replace('$RELATION_MODEL_NAMES$',           implode(',', $this->generateRelationModelNames()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_FIELDS$',            implode("\n", $this->generateRelationsFields()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_INIT_SEARCH_MODEL$', implode(',' . infy_nl_tab(1, 2), $this->generateRelationsInitSearchModel()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_INIT_SEARCH$',       implode(infy_nl_tab(1, 2), $this->generateRelationsInitSearch()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_ON_LAZY_LOAD$',      implode("\n", $this->generateRelationsOnLazyLoad()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_ON_LAZY_LOAD2$',     implode("\n", $this->generateRelationsOnLazyLoad2()), $templateData);
        $templateData = str_replace('$RELATIONS_AS_ADD_NEW$',           implode(',' . infy_nl_tab(1, 2), $this->generateRelationsAddNew()), $templateData);

        return $templateData;
    }

    private function getSearchables()
    {
        $searchables = [];
        $this->primaryKey = 'id';

        foreach ($this->commandData->fields as $field) {
            if ($field->isSearchable) {
                $searchables[] = "this.labels.{$this->commandData->config->mCamel}.{$field->name}";
            }
            if ($field->isPrimary) {
                $this->primaryKey = $field->name;
            }
        }

        return $searchables;
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
            $relationText = "
            m$field?: $field;
            @Input()
            set $fieldSnape($fieldSnape: $field) {
              if ($fieldSnape) {
                this.m$field = $fieldSnape;
                this.isSubComponentFrom = '$fieldSnape';
              }
            }";
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateRelationsInitSearchModel()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            if ($type != 'mt1') {
                continue;
            }
            $relations[] = "condition$field: new JfSearchCondition()";
        }

        return $relations;
    }

    private function generateRelationsInitSearch()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            if ($type != 'mt1') {
                continue;
            }
            $relations[] = " this.modelSearch.condition$field.value = this.m$field;";
        }

        return $relations;
    }

    private function generateRelationsOnLazyLoad()
    {
        $relations = [];

        $count = 1;
        $fieldsArr = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;
            $fieldFK = (isset($relation->inputs[1])) ? $relation->inputs[1] : null;

            if ($type != 'mt1') {
                continue;
            }

            $relationText = "
            nextOperator = MyUtils.x2one({
                conditions,
                conditionModel: this.modelSearch.condition$field,
                foreignKName: `\${this.labels.{$this->commandData->config->mCamel}.tableName}.$fieldFK`,
                primaryKName: 'id',
                nextOperator
              });";
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateRelationsOnLazyLoad2()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;
            $fieldFK = (isset($relation->inputs[1])) ? $relation->inputs[1] : null;

            if ($type != 'mt1') {
                continue;
            }

            $fieldCamel = Str::camel($field);
            $relationText = "new JfCondition(
                `\${this.labels.$fieldCamel.tableName}.id.$fieldFK`,
                [
                  // `\${this.labels.$fieldCamel.tableName}.id as $fieldFK`,
                  `\${this.labels.$fieldCamel.tableName}.name as {$fieldCamel}Name`
                ]
              ),";
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateRelationsAddNew()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            if ($type != 'mt1') {
                continue;
            }
            $fieldCamel = Str::camel($field);
            $relations[] = "$fieldCamel: this.m$field";
        }

        return $relations;
    }

    private function fillTemplateHtml($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$RELATIONS_AS_SEARCH_FIELDS$',           implode("\n", $this->generateRelationsHtmlSearchFields()), $templateData);
        $templateData = str_replace('$COLUMN_FIELDS$',                        implode("\n", $this->generateHtmlColumnFields()), $templateData);
        $templateData = str_replace('$COLUMN_FIELDS_RELATIONS$',              implode("\n", $this->generateHtmlColumnFieldsRelations()), $templateData);
        $templateData = str_replace('$COLUMN_VALUES$',                        implode("\n", $this->generateHtmlColumnValues()), $templateData);
        $templateData = str_replace('$COLUMN_VALUES_RELATIONS$',              implode("\n", $this->generateHtmlColumnValuesRelations()), $templateData);

        return $templateData;
    }

    private function generateRelationsHtmlSearchFields()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            if ($type != 'mt1') {
                continue;
            }

            $fieldSnape = Str::camel($field);
            $fieldDash = Str::slug($field);
            $relationText = <<<EOF
            <!-- $fieldSnape -->
            <div class="row">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label> Cond. </label>
                        <select id="{$fieldSnape}Cond"
                                name="{$fieldSnape}Cond"
                                class="form-control"
                                [(ngModel)]="modelSearch.condition$field.cond">
                            <option *ngFor="let o of conditionalOptions"
                                    [value]="o.value">
                                {{ o.label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <div class="form-group">
                        <app-$fieldDash-auto-complete id="$fieldSnape"
                                                      [name]="labels.$fieldSnape.ownName"
                                                      [currentPage]="mApi.index()"
                                                      [(ngModel)]="modelSearch.condition$field.value">
                        </app-$fieldDash-auto-complete>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label> Oper. </label>
                        <select id="{$fieldSnape}Oper"
                                name="{$fieldSnape}Oper"
                                class="form-control"
                                [(ngModel)]="modelSearch.condition$field.oper">
                            <option *ngFor="let o of operatorOptions"
                                    [value]="o.value">
                                {{ o.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- $fieldSnape . end -->
            EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateHtmlColumnFields()
    {
        $relations = [];
        foreach ($this->commandData->fields as $field) {
            $relationText = <<<EOF
            <th appMultiSortMeta
                [host]="this"
                [colName]="labels.{$this->commandData->config->mCamel}.$field->name.field!">
                {{ labels.{$this->commandData->config->mCamel}.$field->name.label }}
            </th>
            EOF;
            if ($field->isPrimary) {
                $relationText = <<<EOF
                <th appMultiSortMeta
                    [host]="this"
                    [colName]="labels.{$this->commandData->config->mCamel}.$field->name.field!">
                    #
                </th>
                EOF;
            }
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateHtmlColumnFieldsRelations()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            if ($type != 'mt1') {
                continue;
            }
            $fieldCamel = Str::camel($field);
            $relationText = <<<EOF
            <th appMultiSortMeta
                [host]="this"
                colName="{$fieldCamel}Name">
                {{ labels.{$fieldCamel}.ownName }}
            </th>
            EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateHtmlColumnValues()
    {
        $relations = [];
        foreach ($this->commandData->fields as $field) {

            $relationText = <<<EOF
                <td>
                <strong class="d-block d-md-none">{{ labels.{$this->commandData->config->mCamel}.$field->name.label }}</strong>
                {{ model.$field->name }}
                </td>
            EOF;

            if ($field->isPrimary) {
                $relationText = <<<EOF
                    <td class="td-actions">
                        <button *ngIf="hasPermission2show"
                                (click)="onRowSelect(model)"
                                type="button"
                                class="btn btn-sm btn-info m-1">
                            {{ model.$field->name }}
                        </button>
                    </td>
                EOF;
            }
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateHtmlColumnValuesRelations()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {

            $type  = (isset($relation->type))      ? $relation->type      : null;
            $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

            if ($type != 'mt1') {
                continue;
            }
            $fieldCamel = Str::camel($field);
            $fieldDash = Str::slug($field);
            $relationText = <<<EOF
            <td>
                <strong class="d-block d-md-none">{{ labels.$fieldCamel.ownName }}</strong>
                {{ model.{$fieldCamel}Name }}
                <!-- <app-$fieldDash [$fieldCamel]="model.$fieldCamel"></app-$fieldDash> -->
            </td>
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
