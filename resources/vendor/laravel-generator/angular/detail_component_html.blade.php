<div class="panel-header panel-header-sm"
*ngIf="!isSubComponent">
</div>
<div *ngIf="{{ $config->modelNames->camel }}"
[class.main-content]="!isSubComponent">
<!-- form -->
<form class="card" [formGroup]="mFormGroup">
<div class="card-header">
<h5 class="title d-inline-block">
    {{ "{{" }}
    labels.{{ $config->modelNames->camel }}.ownName
    {!! "}}" !!}
</h5>
<h6 class="ps-1 d-inline-block ms-2">
    {{ "{{" }}
    labels.{{ $config->modelNames->camel }}.{{ $config->primaryName }}.label
    {!! '}}' !!}
    {{ "{{" }}
    {{ $config->modelNames->camel }}.{{ $config->primaryName }}
    {!! '}}' !!}</h6>
</div>
<div class="card-body">
    {!! $input_fields !!}
    {!! $input_fields_related !!}
</div>
<div class="card-footer">
<button (click)="onBack()"
type="button"
class="btn btn-secondary m-1">Regresar</button>
<button *ngIf="hasPermission2edit"
(click)="onSave()"
type="submit"
class="btn btn-primary m-1 save-{{ $config->modelNames->camel }}"
[disabled]="!mFormGroup.valid || sending">
Guardar
<div *ngIf="sending"
class="fa-3x i-sending mx-1 float-end">
<i class="fas fa-spinner fa-spin"></i>
</div>
</button>
<button *ngIf="hasPermission2new && !isSubComponent && {{ $config->modelNames->camel }}.{{ $config->primaryName }}"
(click)="addNew()"
type="button"
class="btn btn-warning m-1 new-{{ $config->modelNames->camel }}">Agregar otro</button>
</div>
</form>
<!-- form . end -->
<!-- related . init -- >
    {!! $lists_related !!}
<!-- related . end -->
</div>
