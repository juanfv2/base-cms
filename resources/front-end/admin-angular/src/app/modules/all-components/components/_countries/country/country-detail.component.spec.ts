import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {ReactiveFormsModule} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'
import {RouterTestingModule} from '@angular/router/testing'
import {Location} from '@angular/common'
import {of} from 'rxjs/internal/observable/of'

import {BaseCmsModule, JfCrudService} from 'base-cms' // @juanfv2/base-cms

import {k} from '../../../../../../environments/k'
import {DOMHelper, Helpers} from '../../../../../../testing/helpers'

import {CountryDetailComponent} from './country-detail.component'

describe('CountryDetailComponent', () => {
  let router: Router
  let location: Location
  let component: CountryDetailComponent
  let domHelper: DOMHelper<CountryDetailComponent>
  let fixture: ComponentFixture<CountryDetailComponent>
  const activeRouteStub: jasmine.SpyObj<ActivatedRoute> = jasmine.createSpyObj('route', ['params'])
  const crudServiceStub: jasmine.SpyObj<JfCrudService> = jasmine.createSpyObj('crudService', [
    'updateEntity',
    'getEntity',
  ])

  beforeEach(async () => {
    activeRouteStub.params = of({id: 'new'})
    await TestBed.configureTestingModule({
      declarations: [CountryDetailComponent],
      imports: [HttpClientTestingModule, RouterTestingModule, ReactiveFormsModule, BaseCmsModule],
      providers: [
        {provide: JfCrudService, useValue: crudServiceStub},
        {provide: ActivatedRoute, useValue: activeRouteStub},
      ],
    }).compileComponents()

    fixture = TestBed.createComponent(CountryDetailComponent)
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
    component.country.id = 1
    component.hasPermission2new = true
    fixture.detectChanges()
    expect(domHelper.count('.new-country')).toEqual(1)
    component.addNew()

    expect(router.navigateByUrl).toHaveBeenCalledWith(router.createUrlTree([k.routes.transition]), {
      replaceUrl: true,
    })

    // expect(router.navigateByUrl).toHaveBeenCalledWith(router.createUrlTree([k.routes.countries, 'new']), {
    // replaceUrl: true,
    // })
  })

  it('should render "country-name" validation message when formControl mark as dirty and empty', () => {
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

  it('should render "country-code" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.code-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const code = component.mFormGroup.controls['code']
    code.setValue('')
    code.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Código es requerido')
  })

  it('should render code validation message when formControl is length is between 2 min', () => {
    const elements: HTMLElement = fixture.nativeElement
    expect(domHelper.count('.code-error-valid-length')).toEqual(0)

    // elements.querySelector('button').click();
    const code = component.mFormGroup.controls['code']
    code.setValue('1')
    code.markAsDirty()

    fixture.detectChanges()
    expect(domHelper.count('.code-error-valid-length')).toEqual(1)
    expect(domHelper.singleText('.code-error-valid-length')).toContain('Código debe ser mínimo 2, máximo 2')
  })

  it('should render code validation message when formControl is length is between 2 min, 2 max', () => {
    const elements: HTMLElement = fixture.nativeElement
    expect(elements.querySelector('.code-error-valid-length')).toBeNull()

    // elements.querySelector('button').click();
    const code = component.mFormGroup.controls['code']
    code.setValue('123')
    code.markAsDirty()

    fixture.detectChanges()
    // expect(elements.querySelector('.code-error-valid-length')).toBeTruthy()
    expect(elements.querySelector('.code-error-valid-length')?.textContent).toContain(
      'Código debe ser mínimo 2, máximo 2'
    )
  })

  it('should invoke crud-service updateEntity (create Country) when form is valid', () => {
    const modelTemp = Helpers.generateObjectMock(component.labels.country, 1, ['id'])

    for (const field in component.mFormGroup.controls) {
      component.mFormGroup.controls[field].setValue(modelTemp[field])
      component.mFormGroup.controls[field].markAsDirty()
    }

    component.mFormGroup.controls['code'].setValue('sv')
    modelTemp.code = 'sv'

    expect(domHelper.count('.save-country')).toEqual(0)

    crudServiceStub.updateEntity.and.returnValue(of())
    component.hasPermission2edit = true
    fixture.detectChanges()

    expect(domHelper.count('.save-country')).toEqual(1)

    domHelper.clickButton('Guardar')
    // fixture.nativeElement.querySelector('button.save').click()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledTimes(1)
    // expect(crudServiceStub.updateEntity.calls.any()).toBeTruthy()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledWith(k.routes.countries, modelTemp)
  })
})
