<div class="panel-header panel-header-sm"
*ngIf="!isSubComponent"></div>
<div [class.main-content]="!isSubComponent">
<form class="card" #modelForm="ngForm">
<div *ngIf="!isSubComponent" class="card-header">
<h5 class="title"> {{ '{{' }} labels.{{ $config->modelNames->camel }}.ownNamePlural {!! '}}' !!}</h5>
</div>
<div *ngIf="!isSubComponent" class="card-body">
{!! $relations_search_fields !!}
</div>
<div *ngIf="!isSubComponent" class="card-body">
<ng-template baseCmsJfAddComponent></ng-template>
<div class="row">
<div class="col-12">
<div class="form-group">
<button (click)="addFilter()" type="button" class="btn btn-info m-1 float-end">
Agregar criterio de búsqueda
</button>
</div>
</div>
</div>
</div>

<base-cms-generic-table
*ngIf="!itemCurrent"
[allowImport]="true"
[csv]="csv"
[labels]="labels"
[itemLabels]="itemLabels"
[modelSearch]="modelSearch"
[responseList]="responseList"
[loading]="loading"
[isSubComponent]="isSubComponent"
[hasPermission2delete]="hasPermission2delete"
[hasPermission2new]="hasPermission2new"
[hasPermission2show]="hasPermission2show"
(_onLazyLoad)="onLazyLoad($event)"
(_onRowSelect)="onRowSelect($event)"
(_onAddNew)="onAddNew($event)"
(_onDelete)="onDelete($event)"
(_onClearFilters)="clearFilters($event)"
(_onMassiveInsert)="massiveInsert($event)"
></base-cms-generic-table>
</form>

<app-{{ $config->modelNames->dashed }}-detail *ngIf="itemCurrent"
[{{ $config->modelNames->camel }}]="itemCurrent"
[isSubComponent]="isSubComponent"
[isSubComponentFrom]="isSubComponentFrom"
(saveClicked)="saveFormClicked($event)"
(cancelClicked)="itemCurrent = undefined">
</app-{{ $config->modelNames->dashed }}-detail>
</div>
