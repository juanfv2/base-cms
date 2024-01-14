import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {ReactiveFormsModule} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'
import {RouterTestingModule} from '@angular/router/testing'
import {Location} from '@angular/common'
import {of} from 'rxjs/internal/observable/of'
import {NgbNavModule} from '@ng-bootstrap/ng-bootstrap'

import {BaseCmsModule, JfCrudService} from 'base-cms' // @juanfv2/base-cms

import {k} from '../../../../../../environments/k'
import {DOMHelper, Helpers} from '../../../../../../testing/helpers'

import {RoleDetailComponent} from './role-detail.component'

describe('RoleDetailComponent', () => {
  let router: Router
  let location: Location
  let component: RoleDetailComponent
  let domHelper: DOMHelper<RoleDetailComponent>
  let fixture: ComponentFixture<RoleDetailComponent>
  const activeRouteStub: jasmine.SpyObj<ActivatedRoute> = jasmine.createSpyObj('route', ['params'])
  const crudServiceStub: jasmine.SpyObj<JfCrudService> = jasmine.createSpyObj('crudService', [
    'updateEntity',
    'getEntity',
  ])

  beforeEach(async () => {
    activeRouteStub.params = of({id: 'new'})
    await TestBed.configureTestingModule({
      declarations: [RoleDetailComponent],
      imports: [HttpClientTestingModule, RouterTestingModule, ReactiveFormsModule, BaseCmsModule, NgbNavModule],
      providers: [
        {provide: JfCrudService, useValue: crudServiceStub},
        {provide: ActivatedRoute, useValue: activeRouteStub},
      ],
    }).compileComponents()

    fixture = TestBed.createComponent(RoleDetailComponent)
    component = fixture.componentInstance
    // component.ngOnInit()
    domHelper = new DOMHelper(fixture)
    location = TestBed.inject(Location)
    router = TestBed.inject(Router)
    fixture.detectChanges()
  })

  it('should create', () => {
    expect(component).toBeTruthy()
  })

  it('Should navigate to / click', () => {
    expect(location.path()).toBe('')
  })

  it('Should navigate to /new on + button click', () => {
    spyOn(router, 'navigateByUrl')
    component.role.id = 1
    component.hasPermission2new = true
    fixture.detectChanges()
    expect(domHelper.count('.new-role')).toEqual(1)
    component.addNew()

    expect(router.navigateByUrl).toHaveBeenCalledWith(router.createUrlTree([k.routes.transition]), {
      replaceUrl: true,
    })

    // expect(router.navigateByUrl).toHaveBeenCalledWith(router.createUrlTree([k.routes.roles, 'new']), {
    // replaceUrl: true,
    // })
  })

  it('should render "role-name" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.name-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const name = component.mFormGroup.controls['name']
    name.setValue('')
    name.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Nombre es requerido')
  })

  it('should render "role-description" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.description-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const description = component.mFormGroup.controls['description']
    description.setValue('')
    description.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Descripción es requerido')
  })

  it('should invoke crud-service updateEntity (create Role) when form is valid', () => {
    const modelTemp = Helpers.generateObjectMock(component.labels.role, 1, ['id', 'permissions', 'permissionName'])
    modelTemp.roleIdsPermissionStr = '1'

    for (const field in component.mFormGroup.controls) {
      component.mFormGroup.controls[field].setValue(modelTemp[field])
      component.mFormGroup.controls[field].markAsDirty()
    }
    modelTemp.permissions = [1]
    modelTemp.idsPermissions = [1]
    component.role.idsPermissions = [1]

    expect(domHelper.count('.save-role')).toEqual(0)

    crudServiceStub.updateEntity.and.returnValue(of())
    component.hasPermission2edit = true
    fixture.detectChanges()

    expect(domHelper.count('.save-role')).toEqual(1)

    domHelper.clickButton('Guardar')
    // fixture.nativeElement.querySelector('button.save').click()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledTimes(1)
    // expect(crudServiceStub.updateEntity.calls.any()).toBeTruthy()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledWith(k.routes.roles, modelTemp)
  })
})
