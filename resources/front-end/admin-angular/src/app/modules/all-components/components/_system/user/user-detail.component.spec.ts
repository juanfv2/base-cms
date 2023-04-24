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

import {UserDetailComponent} from './user-detail.component'
import {NgbDatepickerModule} from '@ng-bootstrap/ng-bootstrap'
import {Account, Person} from 'src/app/models/_models'
import {AllComponentsModule} from '../../../all-components.module'

describe('UserDetailComponent', () => {
  let router: Router
  let location: Location
  let component: UserDetailComponent
  let domHelper: DOMHelper<UserDetailComponent>
  let fixture: ComponentFixture<UserDetailComponent>
  const activeRouteStub: jasmine.SpyObj<ActivatedRoute> = jasmine.createSpyObj('route', ['params'])
  const crudServiceStub: jasmine.SpyObj<JfCrudService> = jasmine.createSpyObj('crudService', [
    'updateEntity',
    'getEntity',
  ])

  beforeEach(async () => {
    activeRouteStub.params = of({id: 'new'})
    await TestBed.configureTestingModule({
      declarations: [UserDetailComponent],
      imports: [
        HttpClientTestingModule,
        RouterTestingModule.withRoutes([
          {path: 'users/new', component: UserDetailComponent},
          {path: 'users/1', component: UserDetailComponent},
        ]),
        ReactiveFormsModule,
        NgbDatepickerModule,
        BaseCmsModule,
        AllComponentsModule,
      ],
      providers: [
        {provide: JfCrudService, useValue: crudServiceStub},
        {provide: ActivatedRoute, useValue: activeRouteStub},
      ],
    }).compileComponents()

    fixture = TestBed.createComponent(UserDetailComponent)
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
    component.user.id = 1
    component.currentPath = k.routes.accounts
    component.hasPermission2new = true
    fixture.detectChanges()
    expect(domHelper.count('.new-user')).toEqual(1)
    component.addNew()
    expect(router.navigateByUrl).toHaveBeenCalledWith(router.createUrlTree([k.routes.accounts, 'new']), {
      skipLocationChange: false,
    })
  })

  it('should render "user-name" validation message when formControl mark as dirty and empty', () => {
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

  it('should render "user-email" validation message when formControl is touched and invalid', () => {
    const _tag = '.email-error-invalid'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const email = component.mFormGroup.controls['email']
    email.setValue('test')
    email.markAsTouched()

    fixture.detectChanges()
    expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Correo electrónico es invalido')
  })

  it('should render "user-email" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.email-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const email = component.mFormGroup.controls['email']
    email.setValue('')
    email.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Correo electrónico es requerido')
  })

  it('should render "user-password" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.password-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const password = component.mFormGroup.controls['password']
    password.setValue('')
    password.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Contraseña es requerido')
  })

  it('should render "user-password" validation message when formControl is touched and invalid', () => {
    const _tag = '.password-error-invalid'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const password = component.mFormGroup.controls['password']
    password.setValue('asd')
    password.markAsTouched()

    fixture.detectChanges()
    expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain(
      'Contraseña debe ser mayor a 8 caracteres y debe incluir mayúsculas, minúsculas, números.'
    )
  })

  it('should render "user-disabled" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.disabled-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const disabled = component.mFormGroup.controls['disabled']
    disabled.setValue('')
    disabled.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Deshabilitado es requerido')
  })

  describe('Account', () => {
    beforeEach(async () => {
      // location.go('accounts/new')
      router.createUrlTree([k.routes.accounts, 'new'])

      component.currentPath = k.routes.accounts
      component.user.account = {id: 1} as Account
      crudServiceStub.updateEntity.calls.reset()
      fixture.detectChanges()
    })

    it('should render "account-firstName" validation message when formControl mark as dirty and empty', () => {
      const _tag = '.firstName-error-required'
      expect(domHelper.count(_tag)).toEqual(0)

      // elements.querySelector('button').click();
      const firstName = component.mFormGroup.get('account')?.get('firstName')
      firstName?.setValue('')
      firstName?.markAsDirty()

      fixture.detectChanges()
      expect(firstName).toBeTruthy()
      expect(domHelper.count('.card-account')).toEqual(1)
      expect(domHelper.count(_tag)).toEqual(1)
      expect(domHelper.singleText(_tag)).toContain('Nombre es requerido')
    })

    it('should invoke crud-service updateEntity (create User) when form is valid', () => {
      const modelTemp = Helpers.generateObjectMock(component.labels.user, 1, [
        'id',
        'accountName',
        'personName',
        'remember_token',
        'email_verified_at',
        'uid',
      ])
      modelTemp.account = Helpers.generateObjectMock(component.labels.account, 1, ['id', 'userName', 'birthDate'])
      modelTemp.country = {id: 1}
      modelTemp.country_id = modelTemp.country.id
      modelTemp.region = {id: 1}
      modelTemp.region_id = modelTemp.region.id
      modelTemp.city = {id: 1}
      modelTemp.city_id = modelTemp.city.id
      modelTemp.role = {id: 1}
      modelTemp.role_id = modelTemp.role.id
      modelTemp.roles = [{id: 1, name: 'names'}]
      modelTemp.account.birthDate = {year: 2000, month: 2, day: 1}
      modelTemp.email = 'email@valid.com'
      modelTemp.password = 'Pa55w0rd3'
      modelTemp.photo_id = 1
      // console.log('modelTemp.1', modelTemp)

      for (const field in component.mFormGroup.controls) {
        component.mFormGroup.controls[field].setValue(modelTemp[field])
        component.mFormGroup.controls[field].markAsDirty()
      }

      const properties = Object.assign(modelTemp, modelTemp.account)
      delete modelTemp.country
      delete modelTemp.countryName
      delete modelTemp.region
      delete modelTemp.regionName
      delete modelTemp.city
      delete modelTemp.cityName
      delete modelTemp.role
      delete modelTemp.roleName
      delete modelTemp.account
      delete modelTemp.person
      delete modelTemp.photo
      modelTemp.roles = [1]
      modelTemp.birthDate = '2000-2-1'
      modelTemp.password_confirmation = modelTemp.password
      modelTemp.withEntity = 'auth_accounts'

      expect(domHelper.count('.save-user')).toEqual(0)

      // reset counter if,
      crudServiceStub.updateEntity.and.returnValue(of())
      component.hasPermission2edit = true
      fixture.detectChanges()

      // console.log('invalid', Helpers.findInvalidControls(component.mFormGroup))

      expect(domHelper.count('.save-user')).toEqual(1)

      domHelper.clickButton('Guardar')
      // fixture.nativeElement.querySelector('button.save').click()

      expect(crudServiceStub.updateEntity).toHaveBeenCalledTimes(1)
      // expect(crudServiceStub.updateEntity.calls.any()).toBeTruthy()

      expect(crudServiceStub.updateEntity).toHaveBeenCalledWith(k.routes.users, modelTemp)
    })
  })

  describe('Person', () => {
    beforeEach(async () => {
      // location.go('accounts/new')
      router.createUrlTree([k.routes.users, 'new'])
      component.currentPath = k.routes.users
      component.user.person = {id: 1} as Person
      crudServiceStub.updateEntity.calls.reset()
      fixture.detectChanges()
    })

    it('should render "person-firstName" validation message when formControl mark as dirty and empty', () => {
      const _tag = '.firstName-error-required'
      expect(domHelper.count(_tag)).toEqual(0)
      // elements.querySelector('button').click();
      const firstName = component.mFormGroup.get('person')?.get('firstName')
      firstName?.setValue('')
      firstName?.markAsDirty()
      fixture.detectChanges()
      expect(firstName).toBeTruthy()
      expect(domHelper.count('.card-account')).toEqual(1)
      expect(domHelper.count(_tag)).toEqual(1)
      expect(domHelper.singleText(_tag)).toContain('Nombre es requerido')
    })

    it('should invoke crud-service updateEntity (create User) when form is valid', () => {
      const modelTemp = Helpers.generateObjectMock(component.labels.user, 1, [
        'id',
        'accountName',
        'personName',
        'remember_token',
        'email_verified_at',
        'uid',
      ])
      modelTemp.person = Helpers.generateObjectMock(component.labels.person, 1, ['id', 'userName', 'birthDate'])
      modelTemp.country = {id: 1}
      modelTemp.country_id = modelTemp.country.id
      modelTemp.region = {id: 1}
      modelTemp.region_id = modelTemp.region.id
      modelTemp.city = {id: 1}
      modelTemp.city_id = modelTemp.city.id
      modelTemp.role = {id: 1}
      modelTemp.role_id = modelTemp.role.id
      modelTemp.roles = [{id: 1, name: 'names'}]
      modelTemp.person.birthDate = {year: 2000, month: 2, day: 1}
      modelTemp.email = 'email@valid.com'
      modelTemp.password = 'Pa55w0rd3'
      modelTemp.photo_id = 1
      // console.log('modelTemp.1', modelTemp)
      for (const field in component.mFormGroup.controls) {
        component.mFormGroup.controls[field].setValue(modelTemp[field])
        component.mFormGroup.controls[field].markAsDirty()
      }
      const properties = Object.assign(modelTemp, modelTemp.person)

      delete modelTemp.country
      delete modelTemp.countryName
      delete modelTemp.region
      delete modelTemp.regionName
      delete modelTemp.city
      delete modelTemp.cityName
      delete modelTemp.role
      delete modelTemp.roleName
      delete modelTemp.account
      delete modelTemp.person
      delete modelTemp.photo
      modelTemp.roles = [1]
      modelTemp.birthDate = '2000-2-1'
      modelTemp.password_confirmation = modelTemp.password
      modelTemp.withEntity = 'auth_people'

      expect(domHelper.count('.save-user')).toEqual(0)
      crudServiceStub.updateEntity.and.returnValue(of())
      component.hasPermission2edit = true
      fixture.detectChanges()

      expect(domHelper.count('.save-user')).toEqual(1)
      domHelper.clickButton('Guardar')
      // fixture.nativeElement.querySelector('button.save').click()
      expect(crudServiceStub.updateEntity).toHaveBeenCalledTimes(1)
      // expect(crudServiceStub.updateEntity.calls.any()).toBeTruthy()
      expect(crudServiceStub.updateEntity).toHaveBeenCalledWith(k.routes.users, modelTemp)
    })
  })
})
