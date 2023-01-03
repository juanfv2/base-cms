<?php

namespace Juanfv2\BaseCms\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class AngularListComponentGenerator extends BaseGenerator
{

    public string $path;

    private string $fileName;

    private string $fileNameSpec;

    private string $fileNameScss;

    private string $fileNameHtml;

    public function __construct()
    {
        parent::__construct();

        $mPath = config('laravel_generator.path.angular', 'angular/');
        $this->path = $mPath . $this->config->modelNames->dashed . '/';
        $name = $this->config->modelNames->dashed . '-list.component.';
        $this->fileName = $name . 'ts';
        $this->fileNameSpec = $name . 'spec.ts';
        $this->fileNameScss = $name . 'scss';
        $this->fileNameHtml = $name . 'html';
    }

    public function variables(): array
    {
        return array_merge([], $this->docsVariables());
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
        $viewName = 'list_component';
        $templateData = view('laravel-generator::angular.' . $viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path . $this->fileName, $templateData);

        $this->config->commandComment("Detail.ts Component created: ");
        $this->config->commandInfo($this->fileName);
    }

    public function generateScss()
    {
        $viewName = 'list_component_scss';
        $templateData = view('laravel-generator::angular.' . $viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path . $this->fileNameScss, $templateData);

        $this->config->commandComment("Detail.scss Component created: ");
        $this->config->commandInfo($this->fileNameScss);
    }

    public function generateSpec()
    {
        $viewName = 'list_component_spec';
        $templateData = view('laravel-generator::angular.' . $viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path . $this->fileNameSpec, $templateData);

        $this->config->commandComment("Detail.spec Component created: ");
        $this->config->commandInfo($this->fileNameSpec);
    }

    public function generateHtml()
    {
        $viewName = 'list_component_html';
        $templateData = view('laravel-generator::angular.' . $viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path . $this->fileNameHtml, $templateData);

        $this->config->commandComment("Detail.html Component created: ");
        $this->config->commandInfo($this->fileNameHtml);
    }

    private function getSearchables()
    {
        $searchables = [];

        foreach ($this->config->fields as $field) {
            if ($field->name == 'created_by' || $field->name == 'updated_by') {
                continue;
            }
            if ($field->isSearchable) {
                $searchables[] = "this.labels.{$this->config->modelNames->camel}.{$field->name}";
            }
        }

        return $searchables;
    }

    private function generateRelationModelNames()
    {
        $relations = [$this->config->modelNames->name];

        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
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
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

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
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

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
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

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
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            $fieldFK = $relation->inputs[1] ?? null;

            if ($type != 'mt1') {
                continue;
            }

            $relationText = "
            nextOperator = JfUtils.x2one({
                conditions,
                conditionModel: this.modelSearch.condition$field,
                foreignKName: `\${this.labels.{$this->config->modelNames->camel}.tableName}.$fieldFK`,
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
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            $fieldFK = $relation->inputs[1] ?? null;

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
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

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
        // $templateData = fill_template($this->dynamicVars, $templateData);

        $templateData = str_replace('$RELATIONS_AS_SEARCH_FIELDS$', implode("\n", $this->generateRelationsHtmlSearchFields()), $templateData);
        $templateData = str_replace('$COLUMN_FIELDS$', implode("\n", $this->generateHtmlColumnFields()), $templateData);
        $templateData = str_replace('$COLUMN_FIELDS_RELATIONS$', implode("\n", $this->generateHtmlColumnRelationsFields()), $templateData);
        $templateData = str_replace('$COLUMN_VALUES$', implode("\n", $this->generateHtmlColumnValues()), $templateData);
        $templateData = str_replace('$COLUMN_VALUES_RELATIONS$', implode("\n", $this->generateHtmlColumnRelationsValues()), $templateData);

        return $templateData;
    }

    private function generateRelationsHtmlSearchFields()
    {
        $relations = [];
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

            if ($type != 'mt1') {
                continue;
            }

            $fieldSnape = Str::camel($field);
            $fieldDash = Str::kebab($field);
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
        foreach ($this->config->fields as $field) {
            if (
                $field->name == 'createdBy' ||
                $field->name == 'updatedBy'
            ) {
                continue;
            }
            if ($field->inIndex) {
                $relationText = <<<EOF
                <th [jfMultiSortMeta]="labels.{$this->config->modelNames->camel}.$field->name.field!"
                    [sorts]="modelSearch.lazyLoadEvent.sorts"
                    (sort)="onSort(\$event)"
                    scope="col">
                    {{ labels.{$this->config->modelNames->camel}.$field->name.label }}
                </th>
                EOF;
                $relations[] = $relationText;
            }
        }

        return $relations;
    }

    private function generateHtmlColumnValues()
    {
        $relations = [];
        foreach ($this->config->fields as $field) {
            if (
                $field->name == 'createdBy' ||
                $field->name == 'updatedBy'
            ) {
                continue;
            }
            if ($field->inIndex) {
                $relationText = <<<EOF
                <td>
                    <strong class="d-block d-md-none">{{ labels.{$this->config->modelNames->camel}.$field->name.label }}</strong>
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
        }

        return $relations;
    }

    private function generateHtmlColumnRelationsFields()
    {
        $relations = [];
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

            if ($type != 'mt1') {
                continue;
            }
            $fieldCamel = Str::camel($field);
            $relationText = <<<EOF
            <th [jfMultiSortMeta]="labels.{$this->config->modelNames->camel}.{$fieldCamel}Name.field!"
                [sorts]="modelSearch.lazyLoadEvent.sorts"
                (sort)="onSort(\$event)"
                scope="col">
                {{ labels.{$this->config->modelNames->camel}.{$fieldCamel}Name.label }}
            </th>
            EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateHtmlColumnRelationsValues()
    {
        $relations = [];
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

            if ($type != 'mt1') {
                continue;
            }
            $fieldCamel = Str::camel($field);
            $relationText = <<<EOF
            <td>
                <strong class="d-block d-md-none">{{ labels.$fieldCamel.ownName }}</strong>
                {{ model.{$fieldCamel}Name }}
            </td>
            EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    protected function docsVariables(): array
    {
        $variables = [];
        $searchables1 = $this->getSearchables();

        $variables['searchable_1']                       = implode(infy_nl_tab().',', $searchables1);
        $variables['relation_model_names']               = implode(',', $this->generateRelationModelNames());
        $variables['relations_fields']                   = implode(infy_nl_tab(), $this->generateRelationsFields());
        $variables['relations_fields_init_search_model'] = implode(infy_nl_tab().',', $this->generateRelationsInitSearchModel());
        $variables['relations_fields_init_search']       = implode(infy_nl_tab().',', $this->generateRelationsInitSearch());
        $variables['relations_fields_on_lazy_load_1']    = implode(infy_nl_tab(), $this->generateRelationsOnLazyLoad());
        $variables['relations_fields_on_lazy_load_2']    = implode(infy_nl_tab(), $this->generateRelationsOnLazyLoad2());
        $variables['relations_fields_add_new']           = implode(infy_nl_tab(), $this->generateRelationsAddNew());

        $variables['relations_search_fields'] = implode(infy_nl_tab(), $this->generateRelationsHtmlSearchFields());
        $variables['column_fields']           = implode(infy_nl_tab(), $this->generateHtmlColumnFields());
        $variables['column_fields_relations'] = implode(infy_nl_tab(), $this->generateHtmlColumnRelationsFields());
        $variables['column_values'] = implode(infy_nl_tab(), $this->generateHtmlColumnValues());
        $variables['column_values_relations'] = implode(infy_nl_tab(), $this->generateHtmlColumnRelationsValues());

        // $templateData = str_replace('$RELATIONS_AS_SEARCH_FIELDS$', implode("\n", $this->generateRelationsHtmlSearchFields()), $templateData);
        // $templateData = str_replace('$COLUMN_FIELDS$', implode("\n", $this->generateHtmlColumnFields()), $templateData);
        // $templateData = str_replace('$COLUMN_FIELDS_RELATIONS$', implode("\n", $this->generateHtmlColumnRelationsFields()), $templateData);
        // $templateData = str_replace('$COLUMN_VALUES$', implode("\n", $this->generateHtmlColumnValues()), $templateData);
        // $templateData = str_replace('$COLUMN_VALUES_RELATIONS$', implode("\n", $this->generateHtmlColumnRelationsValues()), $templateData);

        return $variables;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Controller file deleted: ' . $this->fileName);
        }
    }
}
