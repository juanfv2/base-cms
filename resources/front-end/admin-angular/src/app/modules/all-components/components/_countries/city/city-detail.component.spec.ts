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

import {CityDetailComponent} from './city-detail.component'
import {AllComponentsModule} from '../../../all-components.module'

describe('CityDetailComponent', () => {
  let router: Router
  let location: Location
  let component: CityDetailComponent
  let domHelper: DOMHelper<CityDetailComponent>
  let fixture: ComponentFixture<CityDetailComponent>
  const activeRouteStub: jasmine.SpyObj<ActivatedRoute> = jasmine.createSpyObj('route', ['params'])
  const crudServiceStub: jasmine.SpyObj<JfCrudService> = jasmine.createSpyObj('crudService', [
    'updateEntity',
    'getEntity',
  ])

  beforeEach(async () => {
    activeRouteStub.params = of({id: 'new'})
    await TestBed.configureTestingModule({
      declarations: [CityDetailComponent],
      imports: [HttpClientTestingModule, RouterTestingModule, ReactiveFormsModule, BaseCmsModule, AllComponentsModule],
      providers: [
        {provide: JfCrudService, useValue: crudServiceStub},
        {provide: ActivatedRoute, useValue: activeRouteStub},
      ],
    }).compileComponents()

    fixture = TestBed.createComponent(CityDetailComponent)
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
    component.city.id = 1
    component.hasPermission2new = true
    fixture.detectChanges()
    expect(domHelper.count('.new-city')).toEqual(1)
    component.addNew()
    expect(router.navigateByUrl).toHaveBeenCalledWith(router.createUrlTree([k.routes.cities, 'new']), {
      skipLocationChange: false,
    })
  })

  it('should render "city-name" validation message when formControl mark as dirty and empty', () => {
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

  it('should render "city-latitude" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.latitude-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const latitude = component.mFormGroup.controls['latitude']
    latitude.setValue('')
    latitude.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Latitud es requerido')
  })

  it('should render "city-latitude" validation message when formControl is invalid latitude', () => {
    const _tag = '.latitude-error-invalid'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const latitude = component.mFormGroup.controls['latitude']
    latitude.setValue('2000')
    latitude.markAsDirty()

    fixture.detectChanges()
    expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Latitud no es valida')
  })

  it('should render "city-longitude" validation message when formControl mark as dirty and empty', () => {
    const _tag = '.longitude-error-required'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const longitude = component.mFormGroup.controls['longitude']
    longitude.setValue('')
    longitude.markAsDirty()

    fixture.detectChanges()
    // expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Longitud es requerido')
  })

  it('should render "city-longitude" validation message when formControl is invalid longitude', () => {
    const _tag = '.longitude-error-invalid'
    expect(domHelper.count(_tag)).toEqual(0)

    // elements.querySelector('button').click();
    const longitude = component.mFormGroup.controls['longitude']
    longitude.setValue('2000')
    longitude.markAsDirty()

    fixture.detectChanges()
    expect(domHelper.count(_tag)).toEqual(1)
    expect(domHelper.singleText(_tag)).toContain('Longitud no es valida')
  })

  it('should invoke crud-service updateEntity (create City) when form is valid', () => {
    const modelTemp = Helpers.generateObjectMock(component.labels.city, 1, ['id'])
    modelTemp.country = {id: 1}
    modelTemp.country_id = modelTemp.country.id
    modelTemp.region = {id: 1}
    modelTemp.region_id = modelTemp.region.id
    modelTemp.latitude = 1
    modelTemp.longitude = 1

    for (const field in component.mFormGroup.controls) {
      component.mFormGroup.controls[field].setValue(modelTemp[field])
      component.mFormGroup.controls[field].markAsDirty()
    }
    delete modelTemp.country
    delete modelTemp.countryName
    delete modelTemp.region
    delete modelTemp.regionName

    expect(domHelper.count('.save-city')).toEqual(0)

    crudServiceStub.updateEntity.and.returnValue(of())
    component.hasPermission2edit = true
    fixture.detectChanges()

    expect(domHelper.count('.save-city')).toEqual(1)

    domHelper.clickButton('Guardar')
    // fixture.nativeElement.querySelector('button.save').click()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledTimes(1)
    // expect(crudServiceStub.updateEntity.calls.any()).toBeTruthy()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledWith(k.routes.cities, modelTemp)
  })
})
