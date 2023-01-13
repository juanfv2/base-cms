<?php

namespace Juanfv2\BaseCms\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;

class AngularDetailComponentGenerator extends BaseGenerator
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
        $this->path = $mPath.$this->config->modelNames->dashed.'/';
        $name = $this->config->modelNames->dashed.'-detail.component.';

        $this->fileName = $name.'ts';
        $this->fileNameSpec = $name.'spec.ts';
        $this->fileNameScss = $name.'scss';
        $this->fileNameHtml = $name.'html';
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
        $viewName = 'detail_component';
        $templateData = view('laravel-generator::angular.'.$viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment('Detail.ts Component created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function generateScss()
    {
        $viewName = 'detail_component_scss';
        $templateData = view('laravel-generator::angular.'.$viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileNameScss, $templateData);

        $this->config->commandComment('Detail.scss Component created: ');
        $this->config->commandInfo($this->fileNameScss);
    }

    public function generateSpec()
    {
        $viewName = 'detail_component_spec';
        $templateData = view('laravel-generator::angular.'.$viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileNameSpec, $templateData);

        $this->config->commandComment('Detail.spec Component created: ');
        $this->config->commandInfo($this->fileNameSpec);
    }

    public function generateHtml()
    {
        $viewName = 'detail_component_html';
        $templateData = view('laravel-generator::angular.'.$viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileNameHtml, $templateData);

        $this->config->commandComment('Detail.html Component created: ');
        $this->config->commandInfo($this->fileNameHtml);
    }

    private function generateRelationModelNames()
    {
        $relations1 = [$this->config->modelNames->name];
        $relations2 = [];
        foreach ($this->config->relations as $relation) {
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

    private function htmlInputFields()
    {
        $relations = [];
        foreach ($this->config->fields as $field) {
            if ($field->name == '' || $field->name == 'created_by' || $field->name == 'updated_by') {
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
                    <div *ngIf="!mFormGroup.controls['$field->name'].valid && mFormGroup.controls['$field->name'].dirty && mFormGroup.controls['$field->name'].errors?.['required']"
                         class="alert alert-danger form-text {$field->name}-error-required"
                         role="alert">
                        {{labels.{$this->config->modelNames->camel}.$field->name.label}} es requerido
                    </div>
                    EOF;
                }

                $relationText .= "<div class=\"form-group\">\n";
                $relationText .= "<label for=\"{$this->config->modelNames->camel}-$field->name\">{{labels.{$this->config->modelNames->camel}.$field->name.label}} $requiredTextAsterisk</label>\n";

                switch ($field->htmlType) {
                    case 'textarea':
                        $relationText .= <<<EOF
                        <textarea
                        id="{$this->config->modelNames->camel}-$field->name"
                        class="form-control"
                        rows="3"
                        $requiredTextProperties
                        formControlName="$field->name"
                         ></textarea>
                        EOF;
                        break;
                    case 'checkbox':
                        $requiredText = '';
                        $relationText .= <<<EOF
                        <div class="d-flex">
                            <label for="{$this->config->modelNames->camel}-$field->name"
                                   class="switch">
                                   <input id="{$this->config->modelNames->camel}-$field->name"
                                          type="checkbox"
                                          formControlName="$field->name"
                                        />
                                <span class="slider round"></span>
                            </label>
                            <span class="p-1">{{ {$this->config->modelNames->camel}.$field->name ? 'Activo' : 'Desactivo' }}</span>
                        </div>
                        EOF;
                        break;
                    case 'date-time':
                    case 'datetime':
                    case 'date':
                        $relationText .= <<<EOF
                        <div class="input-group">
                            <input
                            id="{$this->config->modelNames->camel}-$field->name"
                            #{$this->config->modelNames->camel}_{$fieldCamel}_date="ngbDatepicker"
                            class="form-control"
                            placeholder="yyyy-mm-dd"
                            ngbDatepicker
                            formControlName="$field->name"
                            >
                            <button title="calendario"
                                    class="btn btn-outline-secondary m-0"
                                    (click)="{$this->config->modelNames->camel}_{$fieldCamel}_date.toggle()"
                                    type="button">
                                    <i class="fa fa-calendar text-info"></i>
                                    </button>
                        </div>
                        <div *ngIf="!mFormGroup.controls['$field->name'].valid && mFormGroup.controls['$field->name'].dirty && mFormGroup.controls['$field->name'].errors?.[ 'ngbDate' ]?.invalid"
                            class="alert alert-danger form-text"
                            role="alert">
                            {{labels.{$this->config->modelNames->camel}.$field->name.label}} fecha invalida
                        </div>
                        EOF;
                        break;

                    default: // text, number, email, password
                        $tType = $field->htmlType == '' ? 'number' : 'text';

                        $relationText .= <<<EOF
                        <input id="{$this->config->modelNames->camel}-$field->name"
                               class="form-control"
                               type="$tType"
                               $requiredTextProperties
                               formControlName="$field->name"
                               />
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

    private function htmlInputFieldsRelated()
    {
        $relations = [];
        foreach ($this->config->relations as $relation) {
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
              <app-$fieldDash-auto-complete id="{$this->config->modelNames->camel}-$fieldCamel"
                                            [name]="labels.$fieldCamel.ownName"
                                            [(ngModel)]="{$this->config->modelNames->camel}.$fieldCamel"
                                            #{$this->config->modelNames->camel}_$fieldCamel="ngModel"
                                            [disabled]="isSubComponentFrom === '$fieldCamel'"
                                            [currentPage]="mApi.show()"
                                            required>
              </app-$fieldDash-auto-complete>
              <div *ngIf="!{$this->config->modelNames->camel}_$fieldCamel.valid && {$this->config->modelNames->camel}_$fieldCamel.dirty && {$this->config->modelNames->camel}_$fieldCamel.errors?.['required']"
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

    private function htmlRelated()
    {
        $relations = "<div *ngIf=\"{$this->config->modelNames->camel}?.id\">";
        $relations .= '    <nav ngbNav #nav="ngbNav" class="nav-tabs">';

        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;
            /* AngularDetailComponentGenerator::htmlRelations_1tm(); */
            /* AngularDetailComponentGenerator::htmlRelations_mtm(); */
            if (method_exists($this, "htmlRelations_$type")) {
                $relations .= "\n{$this->{'htmlRelations_'.$type}($field)}";
            }
        }

        $relations .= "\n</nav><div [ngbNavOutlet]=\"nav\" class=\"bg-white\"></div></div>";

        return count($this->config->relations) > 0 ? $relations : '';
    }

    private function htmlRelations_1tm($field)
    {
        $fieldCamel = Str::camel($field);
        $fieldDash = Str::kebab($field);
        $relationText = <<<EOF
        <ng-container ngbNavItem>
            <a ngbNavLink>{{labels.$fieldCamel.ownNamePlural}}</a>
            <ng-template ngbNavContent>
            <app-$fieldDash-list [{$this->config->modelNames->camel}]="{$this->config->modelNames->camel}" [isSubComponent]="true">
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
                                              (oSelected)="{$fieldCamelPlural}2update(\$event)"
                                              [avoidable]="{$this->config->modelNames->camel}.$fieldCamelPlural"
                                              [(ngModel)]="{$this->config->modelNames->camel}.$fieldCamelPlural">
                </app-$fieldDash-auto-complete>
                <base-cms-many-to-many lField="name" [lModel]="labels.{$fieldCamel}" [gOptions]="{$this->config->modelNames->camel}.$fieldCamelPlural" (rm)="{$fieldCamel}2rm(\$event)" (go)="{$fieldCamel}2go(\$event)" ></base-cms-many-to-many>
                </div>
                <div class="card-footer"> </div>
            </div>
            </ng-template>
        </ng-container>
        EOF;

        return $relationText;
    }

    private function tsValidateFormGroup()
    {
        $validations = [];
        foreach ($this->config->fields as $field) {
            if ($field->name == '' || $field->name == 'created_by' || $field->name == 'updated_by') {
                continue;
            }
            $required = strpos($field->validations, 'required') !== false;
            if ($field->inForm && $required) {
                $validations[] = " {$field->name}: [this.{$this->config->modelNames->camel}.{$field->name}, Validators.required], ";
            }
        }

        return $validations;
    }

    private function tsRelations_mtm()
    {
        $relations1 = [];
        $relations2 = [];
        foreach ($this->config->relations as $relation) {
            $type = $relation->type ?? null;
            $field = $relation->inputs[0] ?? null;

            if ($type != 'mtm') {
                continue;
            }

            $fieldCamel = Str::camel($field);
            $fieldCamelPlural = Str::plural($fieldCamel);
            $relationText = <<<EOF
            {$fieldCamelPlural}2update(\$e?: any): void {
                this.{$this->config->modelNames->camel}.$fieldCamelPlural = this.{$this->config->modelNames->camel}.$fieldCamelPlural || [];
                // are required? do you need something like that?
                // this.{$fieldCamelPlural}AreRequired   = this.{$this->config->modelNames->camel}.$fieldCamelPlural.length > 0 ? '-' : '';
                // this.{$fieldCamelPlural}Avoidable  = [...new Set([...k.{$this->config->modelNames->camel}Clients, ...this.{$fieldCamelPlural}Selectable])];
            }

            {$fieldCamel}2rm($fieldCamel: $field): void {
                this.{$this->config->modelNames->camel}.$fieldCamelPlural = this.{$this->config->modelNames->camel}.$fieldCamelPlural.filter((r:any) => r.id !== $fieldCamel.id);
                // this.{$fieldCamelPlural}2update();
            }

            {$fieldCamel}2gp($fieldCamel: $field): void {
                this.router.navigate([k.routes.$fieldCamelPlural, $fieldCamel.id]);
            }
            EOF;
            $relations1[] = $relationText;
            $relations2[] = "this.{$fieldCamelPlural}2update();";
        }

        return [$relations1, $relations2];
    }

    private function tsSaveRelations()
    {
        $relations = [];
        foreach ($this->config->relations as $relation) {
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

    private function tsModel()
    {
        $fields = [];
        $fields[] = '/**';
        $fields[] = '// admin-angular/src/app/models/_models.ts';
        $fields[] = '';
        $fields[] = "export interface {$this->config->modelNames->name} {";
        foreach ($this->config->fields as $field) {
            if ($field->name == '' || $field->name == 'created_by' || $field->name == 'updated_by') {
                continue;
            }
            $fieldText = '';
            $fieldText .= $field->name;

            if ($field->isPrimary) {
                $fieldText .= '?: number;';
                $fields[] = $fieldText;

                continue;
            }
            switch ($field->dbType) {
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

        foreach ($this->config->relations as $relation) {
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
        $fields[] = "{$this->config->modelNames->camelPlural}: '{$this->config->modelNames->snakePlural}',\n";

        $fields[] = '// admin-angular/src/environments/l.ts';
        $fields[] = '';
        $fields[] = "{$this->config->modelNames->camel}: {";
        $fields[] = "tablePK: '{$this->config->primaryName}',";
        $fields[] = "tableName: '{$this->config->tableName}',";
        $fields[] = "ownName: '{$this->config->modelNames->name}',";
        $fields[] = "ownNamePlural: '{$this->config->modelNames->plural}',";

        foreach ($this->config->fields as $field) {
            if (! $field->name) {
                continue;
            }
            $converted = Str::title($field->name);
            $fieldText = "$field->name: new DBType({label: '{$converted}', name: '$field->name', field: '{$this->config->tableName}.$field->name', ";

            if ($field->isPrimary) {
                $mPrimaryKey = $field->name;
            }
            switch ($field->dbType) {
                case 'integer':
                case 'bigInteger':
                    $fieldText .= "type: 'number'} as DBType),";
                    break;
                case 'date':
                case 'datetime':
                    $fieldText .= "type: 'date'} as DBType),";
                    break;
                case 'boolean':
                    $fieldText .= "type: 'boolean'} as DBType),";
                    break;
                default:
                    $fieldText .= "type: 'string'} as DBType),";
                    break;
            }
            $fields[] = $fieldText;
        }

        foreach ($this->config->relations as $relation) {
            $field = $relation->inputs[0] ?? null;
            $fieldFk = $relation->inputs[1] ?? null;

            $title = Str::title($field);
            $fieldCamel = Str::camel($field);

            $fields[] = "{$fieldFk}:        new DBType({name: '$fieldFk', label: '$title #',field: '$fieldCamel.id',type: 'number'} as DBType),";
            $fields[] = "{$fieldCamel}Name: new DBType({name: '{$fieldCamel}Name', label: '$title', field: '{$fieldCamel}Name',   type: 'string', allowExport: true, allowImport:false} as DBType),";
        }

        $fields[] = '},';
        $fields[] = '';
        $fields[] = '// admin-angular/src/app/core/modules/main/main.module.ts';
        $fields[] = '';

        $fields[] = "{$this->config->modelNames->name}ListComponent,";
        $fields[] = "{$this->config->modelNames->name}DetailComponent,";
        $fields[] = "{$this->config->modelNames->name}AutoCompleteComponent,";
        $fields[] = '';
        $fields[] = '// admin-angular/src/app/core/modules/main/main-routing.module.ts';
        $fields[] = '';
        $fields[] = "{ path: `\${k.routes.{$this->config->modelNames->camelPlural}}/:id`, component: {$this->config->modelNames->name}DetailComponent },";
        $fields[] = "{ path: k.routes.{$this->config->modelNames->camelPlural}, component: {$this->config->modelNames->name}ListComponent },";

        $fields[] = '*/';

        return $fields;
    }

    private function specValidateFields()
    {
        $validations = [];
        foreach ($this->config->fields as $field) {
            if ($field->name == '' || $field->name == 'created_by' || $field->name == 'updated_by') {
                continue;
            }
            $required = strpos($field->validations, 'required') !== false;
            if ($field->inForm && $required) {
                $converted = Str::title($field->name);
                $relationyyText = <<<EOF

                it('should render "{$this->config->modelNames->camel}-{$field->name}" validation message when formControl mark as dirty and empty', () => {
                const elements: HTMLElement = fixture.nativeElement
                expect(elements.querySelector('.{$field->name}-error-required')).toBeNull()

                // elements.querySelector('button').click();
                const {$field->name} = component.mFormGroup.controls['{$field->name}']
                {$field->name}.setValue('')
                {$field->name}.markAsDirty()

                fixture.detectChanges()
                // expect(elements.querySelector('.{$field->name}-error-required')).toBeTruthy()
                expect(elements.querySelector('.{$field->name}-error-required')?.textContent).toContain('{$converted} es requerido')
                })
                EOF;
                $validations[] = $relationyyText;
            }
        }

        return $validations;
    }

    protected function docsVariables(): array
    {
        $variables = [];

        [$related1, $related2] = $this->tsRelations_mtm();
        [$relatedNames1, $relatedNames2] = $this->generateRelationModelNames();
        $variables['relations_1'] = implode(infy_nl_tab(), $related1);
        $variables['relations_2'] = implode(infy_nl_tab(), $related2);
        $variables['relations_3'] = implode(infy_nl_tab(), $this->tsSaveRelations());
        $variables['relation_model_names_1'] = implode(',', $relatedNames1);
        $variables['relation_model_names_2'] = implode(',', $relatedNames2);
        $variables['model_info'] = implode(infy_nl(), $this->tsModel());
        $variables['validate_form_group'] = implode(infy_nl(), $this->tsValidateFormGroup());
        $variables['spec_validate_fields'] = implode(infy_nl(), $this->specValidateFields());

        $variables['input_fields'] = implode(infy_nl(), $this->htmlInputFields());
        $variables['input_fields_related'] = implode(infy_nl(), $this->htmlInputFieldsRelated());
        $variables['lists_related'] = implode(infy_nl(), [$this->htmlRelated()]);

        return $variables;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Controller file deleted: '.$this->fileName);
        }
    }
}
