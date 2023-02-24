import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {ReactiveFormsModule} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'
import {RouterTestingModule} from '@angular/router/testing'
import {Location} from '@angular/common'
import {BaseCmsModule, JfCrudService} from 'base-cms' // @juanfv2/base-cms
import {of} from 'rxjs'
import {k} from 'src/environments/k'
import {DOMHelper, Helpers} from 'src/testing/helpers'

import { {{ $config->modelNames->name }}DetailComponent } from './{{ $config->modelNames->dashed }}-detail.component';

describe('{{ $config->modelNames->name }}DetailComponent', () => {
let router: Router
let location: Location
let component: {{ $config->modelNames->name }}DetailComponent;
let domHelper: DOMHelper<{{ $config->modelNames->name }}DetailComponent>
let fixture: ComponentFixture<{{ $config->modelNames->name }}DetailComponent>;
const activeRouteStub: jasmine.SpyObj<ActivatedRoute> = jasmine.createSpyObj('route', ['params'])
const crudServiceStub: jasmine.SpyObj<JfCrudService> = jasmine.createSpyObj('crudService', [
'updateEntity',
'getEntity',
])

beforeEach(async () => {
activeRouteStub.params = of({id: 'new'})
await TestBed.configureTestingModule({
declarations: [{{ $config->modelNames->name }}DetailComponent],
imports: [HttpClientTestingModule, RouterTestingModule, ReactiveFormsModule, BaseCmsModule],
providers: [
{provide: JfCrudService, useValue: crudServiceStub},
{provide: ActivatedRoute, useValue: activeRouteStub},
],
})
.compileComponents();

fixture = TestBed.createComponent({{ $config->modelNames->name }}DetailComponent);
component = fixture.componentInstance;
// component.ngOnInit()
domHelper = new DOMHelper(fixture)
location = TestBed.inject(Location)
router = TestBed.inject(Router)
fixture.detectChanges();
});

it('should create', () => {
expect(component).toBeTruthy();
});

it('Should navigate to / click', () => {
expect(location.path()).toBe('')
})

it('Should navigate to /new on + button click', () => {
spyOn(router, 'navigateByUrl')
component.{{ $config->modelNames->camel }}.{{ $config->primaryName }} = 1
component.hasPermission2new = true
fixture.detectChanges()
expect(domHelper.count('.new-{{ $config->modelNames->camel }}')).toEqual(1)
component.addNew()
expect(router.navigateByUrl).toHaveBeenCalledWith(
router.createUrlTree([k.routes.{{ $config->modelNames->camelPlural }}, 'new']),
{skipLocationChange: false}
)
})
{!! $spec_validate_fields !!}

it('should invoke crud-service updateEntity (create {{ $config->modelNames->name }}) when form is valid', () => {
const modelTemp = Helpers.generateObjectMock(component.labels.{{ $config->modelNames->camel }}, 1, ['id'])
{{$spec_relations_1}}

for (const field in component.mFormGroup.controls) {
component.mFormGroup.controls[field].setValue(modelTemp[field])
component.mFormGroup.controls[field].markAsDirty()
}
{{$spec_relations_2}}

expect(domHelper.count('.save-{{ $config->modelNames->camel }}')).toEqual(0)

crudServiceStub.updateEntity.and.returnValue(of())
component.hasPermission2edit = true
fixture.detectChanges()

expect(domHelper.count('.save-{{ $config->modelNames->camel }}')).toEqual(1)

domHelper.clickButton('Guardar')
// fixture.nativeElement.querySelector('button.save').click()

expect(crudServiceStub.updateEntity).toHaveBeenCalledTimes(1)
// expect(crudServiceStub.updateEntity.calls.any()).toBeTruthy()

expect(crudServiceStub.updateEntity).toHaveBeenCalledWith(k.routes.{{ $config->modelNames->camelPlural }}, modelTemp)
})

});
