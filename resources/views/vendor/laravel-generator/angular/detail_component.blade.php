import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core'
import {FormBuilder, FormGroup, Validators} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'

import {
JfResponse,
JfApiRoute,
JfCrudService,
JfRequestOption,
JfMessageService,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from '../../../../../../environments/k'
import {l} from '../../../../../../environments/l'

import { {{ $relation_model_names_1 }} } from '../../../../../models/_models';

const kRoute = k.routes.{{ $config->modelNames->camelPlural }};

{{'@'}}Component({
selector: 'app-{{ $config->modelNames->dashed }}-detail',
templateUrl: './{{ $config->modelNames->dashed }}-detail.component.html',
styleUrls: ['./{{ $config->modelNames->dashed }}-detail.component.scss']
})
export class {{ $config->modelNames->name }}DetailComponent implements OnInit, OnDestroy {

@Output() saveClicked = new EventEmitter<{{ $config->modelNames->name }}>();
@Output() cancelClicked = new EventEmitter();

@Input() {{ $config->modelNames->camel }}!: {{ $config->modelNames->name }};
@Input() isSubComponentFrom = '-';
@Input() isSubComponent = false;

mFormGroup!: FormGroup
labels = l;
itemLabels: any = l.{{ $config->modelNames->camel }}
includes = [{!! $relation_model_names_2 !!}];
mApi = new JfApiRoute(kRoute);
private mSubscription: any;
sending = false;
hasPermission2new = false;
hasPermission2edit = false;

constructor(
private router: Router,
private route: ActivatedRoute,
private formBuilder: FormBuilder,
private crudService: JfCrudService,
private messageService: JfMessageService
) {
this.{{ $config->modelNames->camel }} = {} as {{ $config->modelNames->name }};
this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
this.hasPermission2edit = JfRequestOption.isAuthorized(`/${kRoute}/edit`) || this.hasPermission2new
}

ngOnInit(): void {
this.validateFormGroup()

this.mSubscription = this.route.params.subscribe(params => {
const id = this.isSubComponent ? this.{{ $config->modelNames->camel }}?.id : params['id'];
// console.log('params', params, `\nthis.{{ $config->modelNames->camel }}`, this.{{ $config->modelNames->camel }});
this.new{{ $config->modelNames->name }}(this.{{ $config->modelNames->camel }});
this.get{{ $config->modelNames->name }}(id);
});
}

ngOnDestroy(): void {
this.mSubscription?.unsubscribe();
}

new{{ $config->modelNames->name }}(temp{{ $config->modelNames->name }}?: {{ $config->modelNames->name }}):void {
this.{{ $config->modelNames->camel }} = temp{{ $config->modelNames->name }} || {} as {{ $config->modelNames->name }};
delete this.{{ $config->modelNames->camel }}.id;

this.validateFormGroup()
{!! $relations_2 !!}
}

get{{ $config->modelNames->name }}(id: any): void {
if (id === 'new') return

const mId = `${id}?includes=${JSON.stringify(this.includes)}`
this.sending = true;

this.crudService.getEntity(kRoute, mId)
.subscribe(
{next: (resp: JfResponse) => {
this.sending = false;
this.{{ $config->modelNames->camel }} = resp.data;

this.validateFormGroup()
{!! $relations_2 !!}
},
error: (error: any) => {
this.sending = false;
this.messageService.danger(k.project_name, error, this.itemLabels.ownName)
}
});
}

onSave(): void {
const modelTemp = JSON.parse(JSON.stringify(this.{{ $config->modelNames->camel }}));

for (const field in this.mFormGroup.controls) {
modelTemp[field] = this.mFormGroup.controls[field].value
}

// prepare
{!! $relations_3 !!}
// modelTemp.includes = this.includes;
// prepare
this.sending = true;
this.crudService.updateEntity(kRoute, modelTemp)
.subscribe(
{next: (resp: JfResponse) => {
this.sending = false;
this.{{ $config->modelNames->camel }}.id = resp.data.id;
this.messageService.success(k.project_name, 'Guardado');
if (this.isSubComponent) {
// ?? this.saveClicked.emit(this.{{ $config->modelNames->camel }});
} else {
this.router.navigate([kRoute, this.{{ $config->modelNames->camel }}.id]);
}
},
error: (error: any) => {
this.sending = false;
this.messageService.danger(k.project_name, error, this.itemLabels.ownName)
}
}
);
}

addNew(): void {
this.router.navigate([k.routes.transition], {replaceUrl: true})

setTimeout(() => {
this.router.navigate([kRoute, 'new'], {replaceUrl: true})
}, 5)
}

onBack(): void {
if (this.isSubComponent) {
this.cancelClicked.emit('cancel');
return;
}

this.router.navigate([kRoute]);
}

private validateFormGroup() {
this.mFormGroup = this.formBuilder.group({
{{ $validate_form_group }}
})
}
// ? m2m
{!! $relations_1 !!}

}
{!! $model_info !!}
