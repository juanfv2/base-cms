import {Component, forwardRef} from '@angular/core'
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms'

import {Observable, of} from 'rxjs'
import {tap, catchError, map} from 'rxjs/operators'

import {
  JfSort,
  JfResponse,
  JfCondition,
  JfLazyLoadEvent,
  jfTemplateAutoComplete,
  BaseCmsAutoCompleteComponent,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'


import { {{ $config->modelNames->name }}, {{ $relation_model_names }} } from 'src/app/models/_models';

// Resource: http://almerosteyn.com/2016/04/linkup-custom-control-to-ngcontrol-ngmodel

export const {{ $config->modelNames->snake }}_auto_complete_control_value_accessor: any = {
provide: NG_VALUE_ACCESSOR,
useExisting: forwardRef(() => {{ $config->modelNames->name }}AutoCompleteComponent),
multi: true
};

const kRoute = k.routes.{{ $config->modelNames->camelPlural }};

{{'@'}}Component({
selector: 'app-{{ $config->modelNames->dashed }}-auto-complete',
template: jfTemplateAutoComplete,
providers: [{{ $config->modelNames->snake }}_auto_complete_control_value_accessor]
})
export class {{ $config->modelNames->name }}AutoCompleteComponent extends BaseCmsAutoCompleteComponent implements ControlValueAccessor {
{{ $relations_1 }}

override labels = l
override kRoute = kRoute

// override formatter = (x: {{ $config->modelNames->name }}) => `${ x.name || ''}`;

override searchTerm(term: string): Observable<any> {
this.previousTerm = term;
const conditions: any[] = [];
if (this.avoidable && this.avoidable.length > 0) {
conditions.push(new JfCondition(`and ${this.labels.{{ $config->modelNames->camel }}.{{ $config->primaryName }}.field} not-in`, this.avoidable.map(r => r.id)));
}
{{ $relations_2 }}
if (term) {
const g: any[] = [];
{{ $searchable_1 }}
conditions.push(g);
}
const mEvent = new JfLazyLoadEvent();
mEvent.select = [
{{ $searchable_2 }}
];
mEvent.sorts = [new JfSort(`${this.labels.{{ $config->modelNames->camel }}.{{ $config->primaryName }}.field}`, JfSort.asc)];
mEvent.additional = [new JfCondition('cp', this.currentPage)];
mEvent.conditions = conditions;
// mEvent.rows = 10;
return this.crudService.getPage(kRoute, mEvent)
.pipe(
tap(() => this.searchFailed = false),
map((resp: JfResponse) => {
this.searchFailed = resp.data.content.length === 0;
return resp.data.content;
}),
catchError( error => {
this.searchFailed = true;
this.messageService.danger(k.project_name, error, this.labels.{{ $config->modelNames->camel }}.ownName);
return of([]);
})
);
}

}
