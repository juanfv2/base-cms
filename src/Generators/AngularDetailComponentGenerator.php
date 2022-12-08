<?php

namespace Juanfv2\BaseCms\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class AngularDetailComponentGenerator extends BaseGenerator
{
    private \InfyOm\Generator\Common\CommandData $commandData;

    private string $path;

    private string $fileName;

    private string $primaryKey;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        // dd($this->commandData);
        $mPath = config('infyom.laravel_generator.path.angular', 'angular/');
        $this->path = $mPath.$this->commandData->config->mDashed.'/';
        $name = $this->commandData->config->mDashed.'-detail.component.';
        $this->fileName = $name.'ts';
        $this->fileNameSpec = $name.'spec.ts';
        $this->fileNameScss = $name.'scss';
        $this->fileNameHtml = $name.'html';
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
        $templateData = get_template('angular.detail_component', 'laravel-generator');
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nAPI DetailComponent created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    public function generateScss()
    {
        $templateData = get_template('angular.detail_component_scss', 'laravel-generator');
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileNameScss, $templateData);

        $this->commandData->commandComment("\nAPI DetailComponent created: ");
        $this->commandData->commandInfo($this->fileNameScss);
    }

    public function generateSpec()
    {
        $templateData = get_template('angular.detail_component_spec', 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileNameSpec, $templateData);

        $this->commandData->commandComment("\nAPI DetailComponent created: ");
        $this->commandData->commandInfo($this->fileNameSpec);
    }

    public function generateHtml()
    {
        $templateData = get_template('angular.detail_component_html', 'laravel-generator');
        $templateData = $this->fillTemplateHtml($templateData);

        FileUtil::createFile($this->path, $this->fileNameHtml, $templateData);

        $this->commandData->commandComment("\nAPI DetailComponent created: ");
        $this->commandData->commandInfo($this->fileNameHtml);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        [$related1, $related2] = $this->tsRelations_mtm();
        [$relatedNames1, $relatedNames2] = $this->generateRelationModelNames();
        $templateData = str_replace('$RELATION_MODEL_CAMEL_NAMES_1$', implode(',', $relatedNames1), $templateData);
        $templateData = str_replace('$RELATION_MODEL_CAMEL_NAMES_2$', implode(',', $relatedNames2), $templateData);
        $templateData = str_replace('$RELATIONS_AS_FIELDS$', implode("\n", $this->generateRelationsFields()), $templateData);
        $templateData = str_replace('$RELATED_1$', implode("\n", $related1), $templateData);
        $templateData = str_replace('$RELATED_2$', implode("\n", $related2), $templateData);
        $templateData = str_replace('$_MODEL_INFO_$', implode("\n", $this->tsModel()), $templateData);

        return $templateData;
    }

    private function generateRelationModelNames()
    {
        $relations1 = [$this->commandData->config->mName];
        $relations2 = [];
        foreach ($this->commandData->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            $fieldCamel = Str::camel($field);
            $relations2[] = "'$fieldCamel'";

            if ($type == 'mtm') {
                $fieldUcFirst = Str::ucfirst($fieldCamel);
                $relations1[] = "$fieldUcFirst";
            }
        }

        return [$relations1, $relations2];
    }

    private function generateRelationsFields()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

            $fieldCamel = Str::camel($field);
            $fieldCamelPlural = Str::plural($fieldCamel);
            $fieldSnake = Str::snake($field);
            if ($type == 'mt1') {
                $relationText = <<<EOF
                modelTemp.{$fieldSnake}_id = null;
                if (modelTemp.$fieldCamel) {
                    modelTemp.{$fieldSnake}_id = modelTemp.$fieldCamel.id;
                    delete modelTemp.$fieldCamel;
                }
                EOF;
                $relations[] = $relationText;
            }

            if ($type == 'mtm') {
                $relationText = " modelTemp.{$fieldCamelPlural} = modelTemp.{$fieldCamelPlural} ? modelTemp.{$fieldCamelPlural}.map((item:any) => item.id) : [];";
                $relations[] = $relationText;
            }
        }

        return $relations;
    }

    private function tsRelations_mtm()
    {
        $relations1 = [];
        $relations2 = [];
        foreach ($this->commandData->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

            if ($type != 'mtm') {
                continue;
            }

            $fieldCamel = Str::camel($field);
            $fieldCamelPlural = Str::plural($fieldCamel);
            $relationText = <<<EOF
            update2$fieldCamelPlural(\$e: any): void {
                this.{$this->commandData->config->mCamel}.$fieldCamelPlural = this.{$this->commandData->config->mCamel}.$fieldCamelPlural || [];
                // do: something like that?
                // this.{$fieldCamelPlural}AreRequired   = this.{$this->commandData->config->mCamel}.$fieldCamelPlural.length > 0 ? '-' : '';
                // this.avoidables{$fieldCamelPlural}  = [...new Set([...k.{$this->commandData->config->mCamel}Clients, ...this.selectables{$fieldCamelPlural}])];
            }

            rm2{$fieldCamel}($fieldCamel: $field): void {
                this.{$this->commandData->config->mCamel}.$fieldCamelPlural = this.{$this->commandData->config->mCamel}.$fieldCamelPlural.filter((r:any) => r.id !== $fieldCamel.id);
                // this.update2$fieldCamelPlural('');
            }

            go2{$fieldCamel}($fieldCamel: $field): void {
                this.router.navigate([k.routes.$fieldCamelPlural, $fieldCamel.id]);
            }
            EOF;
            $relations1[] = $relationText;
            $relations2[] = "this.update2$fieldCamelPlural('');";
        }

        return [$relations1, $relations2];
    }

    private function fillTemplateHtml($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$INPUT_FIELDS$', implode("\n", $this->generateHtmlInputFields()), $templateData);
        $templateData = str_replace('$INPUT_FIELDS_RELATED$', implode("\n", $this->generateHtmlInputFieldsRelated()), $templateData);
        $templateData = str_replace('$LISTS_RELATED$', implode("\n", [$this->generateHtmlRelated()]), $templateData);

        return $templateData;
    }

    private function generateHtmlInputFields()
    {
        $relations = [];
        foreach ($this->commandData->fields as $field) {
            if ($field->name == 'createdBy' || $field->name == 'updatedBy') {
                continue;
            }
            if ($field->inForm) {
                $relationText = "<!-- $field->name . init -->\n";
                $fieldCamel = Str::camel($field->name);
                $requiredTextProperties = '';
                $requiredTextAsterisk = '';
                $requiredText = '';
                $required = strpos($field->validations, 'required') !== false;
                if ($required) {
                    $requiredTextAsterisk = '*';
                    $requiredTextProperties = "required\nplaceholder=\"Requerido\"";

                    $requiredText = <<<EOF
                    <div *ngIf="!{$this->commandData->config->mCamel}_$fieldCamel.valid && {$this->commandData->config->mCamel}_$fieldCamel.dirty && {$this->commandData->config->mCamel}_$fieldCamel.errors?.['required']"
                         class="alert alert-danger form-text"
                         role="alert">
                        {{labels.{$this->commandData->config->mCamel}.$field->name.label}} es requerido
                    </div>
                    EOF;
                }

                $relationText .= "<div class=\"form-group\">\n";
                $relationText .= "<label for=\"{$this->commandData->config->mCamel}-$field->name\">{{labels.{$this->commandData->config->mCamel}.$field->name.label}} $requiredTextAsterisk</label>\n";

                switch ($field->htmlType) {
                    case 'textarea':
                        $relationText .= <<<EOF
                        <textarea id="{$this->commandData->config->mCamel}-$field->name"
                                  name="{$this->commandData->config->mCamel}-$field->name"
                                  [(ngModel)]="{$this->commandData->config->mCamel}.$field->name"
                                  #{$this->commandData->config->mCamel}_$fieldCamel="ngModel"
                                  class="form-control"
                                  rows="3"
                                  $requiredTextProperties></textarea>
                        EOF;
                        break;
                    case 'checkbox':
                        $requiredText = '';
                        $relationText .= <<<EOF
                        <div class="d-flex">
                            <label for="{$this->commandData->config->mCamel}-$field->name"
                                   class="switch">
                                   <input id="{$this->commandData->config->mCamel}-$field->name"
                                          name="{$this->commandData->config->mCamel}-$field->name"
                                          [(ngModel)]="{$this->commandData->config->mCamel}.$field->name"
                                          #{$this->commandData->config->mCamel}_$fieldCamel="ngModel"
                                          type="checkbox">
                                <span class="slider round"></span>
                            </label>
                            <span class="p-1">{{ {$this->commandData->config->mCamel}.$field->name ? 'Activo' : 'Desactivo' }}</span>
                        </div>
                        EOF;
                        break;
                    case 'date-time':
                    case 'datetime':
                    case 'date':
                        $relationText .= <<<EOF
                        <div class="input-group">
                            <input id="{$this->commandData->config->mCamel}-$field->name"
                                   name="{$this->commandData->config->mCamel}-$field->name"
                                   [(ngModel)]="{$this->commandData->config->mCamel}.$field->name"
                                   #{$this->commandData->config->mCamel}_$fieldCamel="ngModel"
                                   #{$this->commandData->config->mCamel}_{$fieldCamel}_date="ngbDatepicker"
                                   class="form-control"
                                   placeholder="yyyy-mm-dd"
                                   ngbDatepicker>
                            <button title="calendario"
                                    class="btn btn-outline-secondary m-0"
                                    (click)="{$this->commandData->config->mCamel}_{$fieldCamel}_date.toggle()"
                                    type="button">
                                    <i class="fa fa-calendar text-info"></i>
                                    </button>
                        </div>
                        <div *ngIf="!{$this->commandData->config->mCamel}_$fieldCamel.valid && {$this->commandData->config->mCamel}_$fieldCamel.dirty && {$this->commandData->config->mCamel}_$fieldCamel.errors?.ngbDate?.invalid"
                            class="alert alert-danger form-text"
                            role="alert">
                            {{labels.{$this->commandData->config->mCamel}.$field->name.label}} fecha invalida
                        </div>
                        EOF;
                        break;

                    default: // text, number, email, password
                        $tType = $field->htmlInput == '' ? 'number' : 'text';

                        $relationText .= <<<EOF
                        <input id="{$this->commandData->config->mCamel}-$field->name"
                               name="{$this->commandData->config->mCamel}-$field->name"
                               [(ngModel)]="{$this->commandData->config->mCamel}.$field->name"
                               #{$this->commandData->config->mCamel}_$fieldCamel="ngModel"
                               class="form-control"
                               type="$tType"
                               $requiredTextProperties />
                        EOF;
                        break;
                }

                $relationText .= $requiredText;
                $relationText .= "\n</div>";
                $relationText .= "\n<!-- $field->name . end -->";

                $relations[] = $relationText;
            }
        }

        return $relations;
    }

    private function generateHtmlInputFieldsRelated()
    {
        $relations = [];
        foreach ($this->commandData->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            if ($type != 'mt1') {
                continue;
            }

            $fieldCamel = Str::camel($field);
            $fieldDash = Str::kebab($field);
            $relationText = <<<EOF
            <!-- $fieldCamel . init -->
            <div class="form-group">
              <app-$fieldDash-auto-complete id="{$this->commandData->config->mCamel}-$fieldCamel"
                                            [name]="labels.$fieldCamel.ownName"
                                            [(ngModel)]="{$this->commandData->config->mCamel}.$fieldCamel"
                                            #{$this->commandData->config->mCamel}_$fieldCamel="ngModel"
                                            [disabled]="isSubComponentFrom === '$fieldCamel'"
                                            [currentPage]="mApi.show()"
                                            required>
              </app-$fieldDash-auto-complete>
              <div *ngIf="!{$this->commandData->config->mCamel}_$fieldCamel.valid && {$this->commandData->config->mCamel}_$fieldCamel.dirty && {$this->commandData->config->mCamel}_$fieldCamel.errors?.['required']"
                   class="alert alert-danger form-text">
                {{labels.$fieldCamel.ownName}} es requerido
              </div>
            </div>
            <!-- $fieldCamel . end -->
        EOF;
            $relations[] = $relationText;
        }

        return $relations;
    }

    private function generateHtmlRelated()
    {
        $relations = "<div *ngIf=\"{$this->commandData->config->mCamel}?.id\">";
        $relations .= '    <nav ngbNav #nav="ngbNav" class="nav-tabs">';

        foreach ($this->commandData->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            /* AngularDetailComponentGenerator::htmlRelations_1tm(); */
            /* AngularDetailComponentGenerator::htmlRelations_mtm(); */
            if (method_exists($this, "htmlRelations_$type")) {
                $relations .= "\n{$this->{'htmlRelations_'.$type}($field)}";
            }
        }

        $relations .= "\n</nav><div [ngbNavOutlet]=\"nav\" class=\"bg-white\"></div></div>";

        return $relations;
    }

    private function htmlRelations_1tm($field)
    {
        $fieldCamel = Str::camel($field);
        $fieldDash = Str::kebab($field);
        $relationText = <<<EOF
        <ng-container ngbNavItem>
            <a ngbNavLink>{{labels.$fieldCamel.ownNamePlural}}</a>
            <ng-template ngbNavContent>
            <app-$fieldDash-list [{$this->commandData->config->mCamel}]="{$this->commandData->config->mCamel}"
                                 [isSubComponent]="true">
            </app-$fieldDash-list>
            </ng-template>
        </ng-container>
        EOF;

        return $relationText;
    }

    private function htmlRelations_mtm($field)
    {
        $fieldCamel = Str::camel($field);
        $fieldCamelPlural = Str::plural($fieldCamel);
        $fieldDash = Str::kebab($field);
        $plural = Str::plural($field);

        $relationText = <<<EOF
        <ng-container ngbNavItem>
            <a ngbNavLink>{{labels.$fieldCamel.ownNamePlural}}</a>
            <ng-template ngbNavContent>
            <div class="card">
                <div class="card-body">
                <div *ngIf="!{$fieldCamelPlural}AreRequired" class="alert alert-danger form-text" role="alert"> Debe seleccionar {{labels.$fieldCamel.ownName}} </div>
                <app-$fieldDash-auto-complete id="{$fieldCamel}-availables"
                                              name="$plural disponibles"
                                              [multiple]="true"
                                              [currentPage]="mApi.show()"
                                              (oSelected)="update2{$fieldCamelPlural}(\$event)"
                                              [avoidable]="{$this->commandData->config->mCamel}.$fieldCamelPlural"
                                              [(ngModel)]="{$this->commandData->config->mCamel}.$fieldCamelPlural">
                </app-$fieldDash-auto-complete>
                <base-cms-many-to-many lField="name" [lModel]="labels.{$fieldCamel}" [gOptions]="{$this->commandData->config->mCamel}.$fieldCamelPlural" (rm)="rm2{$fieldCamel}(\$event)" (go)="go2{$fieldCamel}(\$event)" ></base-cms-many-to-many>
                </div>
                <div class="card-footer"> </div>
            </div>
            </ng-template>
        </ng-container>
        EOF;

        return $relationText;
    }

    private function tsModel()
    {
        $mPrimaryKey = '';
        $fields = [];
        $fields[] = '/**';
        $fields[] = "// admin-angular/src/app/models/_models.ts\n";
        $fields[] = "export interface {$this->commandData->config->mName} {";
        foreach ($this->commandData->fields as $field) {
            $fieldText = '';
            $fieldText .= $field->name;

            if ($field->isPrimary) {
                $mPrimaryKey = $field->name;
                $fieldText .= '?';
            }
            switch ($field->fieldType) {
                case 'integer':
                case 'bigInteger':
                    $fieldText .= ': number;';
                    break;
                case 'boolean':
                    $fieldText .= ': boolean;';
                    break;
                default:
                    $fieldText .= ': string;';
                    break;
            }
            $fields[] = $fieldText;
        }

        foreach ($this->commandData->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            $fieldCamel = Str::camel($field);
            $fields[] = "{$fieldCamel}Name: string;// $type";
            switch ($type) {
                case 'mtm':
                case '1tm':
                    $fieldCamel = Str::pluralStudly($fieldCamel);
                    $fields[] = "$fieldCamel: {$field}[]; ";
                    break;

                default:
                    $fields[] = "$fieldCamel: $field; ";
                    break;
            }
        }

        $fields[] = "}\n";

        $fields[] = "// admin-angular/src/environments/k.ts>routes\n";
        $fields[] = "{$this->commandData->config->mCamelPlural}: '{$this->commandData->config->mSnakePlural}',\n";

        $fields[] = "// admin-angular/src/environments/l.ts\n";
        $fields[] = "{$this->commandData->config->mCamel}: {";
        $fields[] = "tablePK: '{$mPrimaryKey}',";
        $fields[] = "tableName: '{$this->commandData->config->tableName}',";
        $fields[] = "ownName: '{$this->commandData->config->mName}',";
        $fields[] = "ownNamePlural: '{$this->commandData->config->mPlural}',";

        foreach ($this->commandData->fields as $field) {
            $converted = Str::title($field->name);
            $fieldText = "$field->name: new DBType({label: '{$converted}', field: '{$this->commandData->config->tableName}.$field->name', ";

            if ($field->isPrimary) {
                $mPrimaryKey = $field->name;
            }
            switch ($field->fieldType) {
                case 'integer':
                case 'bigInteger':
                    $fieldText .= "type: 'number'}),";
                    break;
                case 'date':
                case 'datetime':
                    $fieldText .= "type: 'date'}),";
                    break;
                case 'boolean':
                    $fieldText .= "type: 'boolean'}),";
                    break;
                default:
                    $fieldText .= "type: 'string'}),";
                    break;
            }
            $fields[] = $fieldText;
        }

        foreach ($this->commandData->relations as $relation) {
            $field = $relation->inputs[0] ?? null;
            $fieldFk = $relation->inputs[1] ?? null;

            $title = Str::title($field);
            $fieldCamel = Str::camel($field);

            $fields[] = '';
            $fields[] = "{$fieldFk}: new DBType({label: `$title #`,field: '$fieldCamel.$fieldFk',type: 'number'}),";
            $fields[] = "{$fieldCamel}Name: new DBType({label: `$title`, '{$fieldCamel}Name', field: 'string', allowExport: true, allowImport:false}),";
        }

        $fields[] = "},\n";

        $fields[] = "// admin-angular/src/app/core/modules/main/main.module.ts\n";
        $fields[] = "{$this->commandData->config->mName}ListComponent,";
        $fields[] = "{$this->commandData->config->mName}DetailComponent,";
        $fields[] = "{$this->commandData->config->mName}AutoCompleteComponent,\n";
        $fields[] = "// admin-angular/src/app/core/modules/main/main-routing.module.ts\n";
        $fields[] = "{ path: `\${k.routes.{$this->commandData->config->mCamelPlural}}/:id`, component: {$this->commandData->config->mName}DetailComponent },";
        $fields[] = "{ path: k.routes.{$this->commandData->config->mCamelPlural}, component: {$this->commandData->config->mName}ListComponent },";

        $fields[] = '*/';

        return $fields;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('API Controller file deleted: '.$this->fileName);
        }
    }
}
