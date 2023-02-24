<div class="panel-header panel-header-sm"
*ngIf="!isSubComponent"></div>
<div [class.main-content]="!isSubComponent">
<div class="row"
*ngIf="!isSubComponent">
<div class="col-md-12">
<form class="card"
#modelForm="ngForm">
<div class="card-header">
<h5 class="title"> {{ '{{' }} labels.{{ $config->modelNames->camel }}.ownNamePlural {!! '}}' !!}</h5>
</div>
<div class="card-body">
{!! $relations_search_fields !!}
</div>
<div class="card-body">
<ng-template baseCmsJfAddComponent></ng-template>
<div class="row">
<div class="col-12">
<div class="form-group">
<button (click)="addFilter()"
type="button"
class="btn btn-info m-1 float-end">
Agregar criterio de búsqueda
</button>
</div>
</div>
</div>
</div>
<div class="card-footer">
<button (click)="onLazyLoad()"
type="submit"
class="btn btn-primary m-1">
Buscar
</button>
<button (click)="addNew()"
type="button"
class="btn btn-warning m-1"
*ngIf="hasPermission2new">
Crear {{ '{{' }} labels.{{ $config->modelNames->camel }}.ownName {!! '}}' !!}
</button>
</div>
</form>
</div>
</div>
<app-{{ $config->modelNames->dashed }}-detail *ngIf="itemCurrent"
[{{ $config->modelNames->camel }}]="itemCurrent"
[isSubComponent]="isSubComponent"
[isSubComponentFrom]="isSubComponentFrom"
(saveClicked)="saveFormClicked($event)"
(cancelClicked)="itemCurrent = undefined">
</app-{{ $config->modelNames->dashed }}-detail>
<div class="row"
*ngIf="!itemCurrent">
<div class="col-md-12">
<div class="card">
<div class="card-header d-flex justify-content-between">
<button *ngIf="isSubComponent && hasPermission2new" (click)="addNew()" type="button" class="btn btn-warning m-1"> Crear {{ '{{ labels.' }}{{ $config->modelNames->camel }}{!!'.ownName }}'!!} </button>
<button *ngIf="!isSubComponent" (click)="clearFilters()" type="button" class="btn btn-link m-1"> Limpiar filtros de búsqueda </button>
<small class="m-1 fw-light p-1">@{{ responseList.totalElements | number }}</small>
</div>
<div class="card-body">
<div class="table-full-width t-responsive table-responsive">
<table class="table table-sm table-striped table-hover">
<thead class="text-nowrap text-primary">
<tr>
<th *ngFor="let _field of fieldsInList" [jfMultiSortMeta]="_field" [host]="this" scope="col">
@{{ _field.label }}
</th>
<th></th>
</tr>
</thead>
<tbody class="text-nowrap">
<tr *ngFor="let model of responseList.content">
<td *ngFor="let _field of fieldsInList" [ngSwitch]="_field.name">
<div class="d-none d-sm-block d-md-none fw-bold">
@{{ _field.label }}
</div>
<div *ngSwitchCase="labels.{{ $config->modelNames->camel }}.id.name">
<button
[disabled]="!hasPermission2show"
(click)="onRowSelect(model)"
type="button"
class="btn btn-sm btn-info m-1 row-select-{{ $config->modelNames->camel }}-@{{ model.id }}"
title="Ver {{ '{{ labels.' }}{{ $config->modelNames->camel }}{!! '.ownName }}' !!}: @{{ model.id }}"
>
@{{ model.id }}
</button>
</div>
<div *ngSwitchDefault>
@{{ model[_field.name] }}
</div>
</td>
<td class="td-actions"> <button *ngIf="hasPermission2delete" (click)="showDeleteDialog(model)" type="button"
class="btn btn-sm btn-danger btn-round btn-icon btn-icon-mini btn-neutral row-delete-{{ $config->modelNames->camel }}-@{{model.id}}"
title="Borrar {{ '{{ labels.' }}{{ $config->modelNames->camel }}{!! '.ownName }}' !!}: @{{model.id}}" > <i class="fas fa-trash-alt"></i> </button> </td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="card-footer">
<div class="row">
<div *ngIf="responseList.totalElements"
class="col-12 col-md-6">
<ngb-pagination [collectionSize]="responseList.totalElements"
[page]="modelSearch.lazyLoadEvent.first"
[pageSize]="modelSearch.lazyLoadEvent.rows"
size="sm"
[maxSize]="5"
[boundaryLinks]="true"
(pageChange)="changePage($event)"
class="d-inline-block"></ngb-pagination>
<div class="ui-paginator ui-dropdown d-inline-block">
<select id="select-qty-table"
name="select-qty-table"
class="form-control m-1"
[(ngModel)]="modelSearch.lazyLoadEvent.rows"
(change)="changePageLimit($event)">
<option *ngFor="let q of labels.misc.pageLimit" [value]="q.v">@{{ q.c }}</option>
</select>
</div>
</div>
<div class="col-12 col-md-6">
<button (click)="onLazyLoad('export')"
type="button"
class="btn btn-primary m-1 w-100">
Exportar resultados
</button>
<base-cms-file-upload
[labels]="labels"
id="{{ $config->modelNames->camel }}-importer"
class="h-100"
minHeight="100px"
label="Importar CSV"
name="massive-inserts"
[showPublicPath]="false"
[error]="csv?.error?.error"
(finish)="massiveInsert($event)"
[url2showStaticImage]="labels.misc.csv"
[value]="{ id: -1, entity: labels.{{ $config->modelNames->camel }}.tableName, field: 'massive-inserts' }"
[allowedTypes]="['text/csv', 'application/vnd.ms-excel']"
></base-cms-file-upload>
</div>
</div>
<base-cms-spinner-loading *ngIf="loading"
class="m-4"></base-cms-spinner-loading>
</div>
</div>
</div>
</div>
</div>
