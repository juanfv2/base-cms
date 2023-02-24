import {Component, Input, OnInit, OnChanges, SimpleChanges} from '@angular/core'
import {ActivatedRoute, Router} from '@angular/router'
import {NgbModal} from '@ng-bootstrap/ng-bootstrap'

import {
JfUtils,
JfApiRoute,
JfResponse,
JfCondition,
JfCrudService,
JfResponseList,
JfLazyLoadEvent,
JfRequestOption,
JfMessageService,
JfSearchCondition,
JfStorageManagement,
BaseCmsListComponent,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import { {{ $relation_model_names }} } from 'src/app/models/_models';

const kRoute = k.routes.{{ $config->modelNames->camelPlural }};
const kConditions = `${k.suggestions}${kRoute}`;

{{'@'}}Component({
selector: 'app-{{ $config->modelNames->dashed }}-list',
templateUrl: './{{ $config->modelNames->dashed }}-list.component.html',
styleUrls: ['./{{ $config->modelNames->dashed }}-list.component.scss']
})
export class {{ $config->modelNames->name }}ListComponent extends BaseCmsListComponent implements OnInit, OnChanges {
{!! $relations_fields !!}
override itemCurrent?: {{ $config->modelNames->name }};
override itemLabels = l.{{ $config->modelNames->camel }}
override labels = l
override kRoute = kRoute
override kConditions = kConditions
override mApi = new JfApiRoute(kRoute)
override responseList: JfResponseList<{{ $config->modelNames->name }}| any> = new JfResponseList<{{ $config->modelNames->name }} | any>(0, 0, []);

constructor(
public override router: Router,
public override modalService: NgbModal,
public override crudService: JfCrudService,
public override messageService: JfMessageService,
private route: ActivatedRoute,) {
super()
this.fieldsSearchable = [ {!! $searchable_1 !!} ];
this.fieldsInList = [ {!! $searchable_1 !!} ,  {!! $searchable_2 !!}  ];
this.hasPermission2show = JfRequestOption.isAuthorized(`/${kRoute}/show`)
this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
this.hasPermission2delete = JfRequestOption.isAuthorized(`/${kRoute}/delete`)
}

ngOnInit(): void {
this.initSearch();
this.onLazyLoad();
}

ngOnChanges(changes: SimpleChanges): void {
if (this.isSubComponent) {
this.initSearch();
this.onLazyLoad();
}
}

initSearchModel(): any {
const search = !this.isSubComponent ? JfStorageManagement.getItem(this.kConditions) : null;
const mSearch = {
lazyLoadEvent: new JfLazyLoadEvent(),
{!! $relations_fields_init_search_model !!}
};
const r = search ? (JSON.parse(search) || mSearch) : mSearch;
// console.log('r', r);
return r;
}

override initSearch(): void {
this.modelSearch = this.initSearchModel();
if (this.isSubComponent) {
{{ $relations_fields_init_search }}
} else {
if (this.modelSearch) {
if (this.modelSearch.conditions) {
Promise.resolve(this.searchField).then(() => {
for (const condition of this.modelSearch.conditions) {
this.addFilter(condition);
}
});
}
}
}
}

override onLazyLoad(strAction = ''): void {
if (this.loading) {
return;
}
// console.log('onLazyLoad this.loading', this.loading);
// console.log('onLazyLoad this.loading', this.modelSearch);
this.loading = true;
// prepare
let nextOperator = 'AND';
const conditions: any[] = [];
{!! $relations_fields_on_lazy_load_1 !!}
if (this.modelSearch.conditions) {
for (const c of this.modelSearch.conditions) {
nextOperator = JfUtils.addCondition(c, nextOperator, conditions)
}
}
// joinType === '<' leftJoin, '>' rightJoin
// 'joinTable.joinTablePK.ownTableFK'
// 'joinTable.joinTablePK.ownTableFK.joinType'
// 'joinTable.joinTablePK.ownTable.ownTableFK'
// 'joinTable.joinTablePK.ownTable.ownTableFK.joinType'
this.modelSearch.lazyLoadEvent.joins = [
{{ $relations_fields_on_lazy_load_2 }}
];
this.modelSearch.lazyLoadEvent.conditions = conditions;
this.modelSearch.lazyLoadEvent.additional = [];
// this.modelSearch.lazyLoadEvent.includes = ['relation-1tm', 'relation-mt1', 'relation-1t1', ...];
const mSearch = JSON.stringify(this.modelSearch);
switch (strAction) {
case 'export':
this.onLazyLoadExport(strAction)
break;
default:
this.onLazyLoadList(mSearch)
break;
}
}

override addNew(): void {
this.itemCurrent = { {{ $relations_fields_add_new }} } as unknown as {{ $config->modelNames->name }};
super.addNew()
}
}
