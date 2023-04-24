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

import {RegionDetailComponent} from './region-detail.component'
import {CountriesModule} from 'src/app/modules/countries/countries.module'

describe('RegionDetailComponent', () => {
  let router: Router
  let location: Location
  let component: RegionDetailComponent
  let domHelper: DOMHelper<RegionDetailComponent>
  let fixture: ComponentFixture<RegionDetailComponent>
  const activeRouteStub: jasmine.SpyObj<ActivatedRoute> = jasmine.createSpyObj('route', ['params'])
  const crudServiceStub: jasmine.SpyObj<JfCrudService> = jasmine.createSpyObj('crudService', [
    'updateEntity',
    'getEntity',
  ])

  beforeEach(async () => {
    activeRouteStub.params = of({id: 'new'})
    await TestBed.configureTestingModule({
      declarations: [RegionDetailComponent],
      imports: [HttpClientTestingModule, RouterTestingModule, ReactiveFormsModule, BaseCmsModule, CountriesModule],
      providers: [
        {provide: JfCrudService, useValue: crudServiceStub},
        {provide: ActivatedRoute, useValue: activeRouteStub},
      ],
    }).compileComponents()

    fixture = TestBed.createComponent(RegionDetailComponent)
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
    component.region.id = 1
    component.hasPermission2new = true
    fixture.detectChanges()
    expect(domHelper.count('.new-region')).toEqual(1)
    component.addNew()
    expect(router.navigateByUrl).toHaveBeenCalledWith(router.createUrlTree([k.routes.regions, 'new']), {
      skipLocationChange: false,
    })
  })

  it('should render "region-name" validation message when formControl mark as dirty and empty', () => {
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

  it('should render "region-code" validation message when formControl mark as dirty and empty', () => {
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

  it('should invoke crud-service updateEntity (create Region) when form is valid', () => {
    const modelTemp = Helpers.generateObjectMock(component.labels.region, 1, ['id'])
    modelTemp.country = {id: 1}
    modelTemp.country_id = modelTemp.country.id

    for (const field in component.mFormGroup.controls) {
      component.mFormGroup.controls[field].setValue(modelTemp[field])
      component.mFormGroup.controls[field].markAsDirty()
    }
    delete modelTemp.country
    delete modelTemp.countryName

    expect(domHelper.count('.save-region')).toEqual(0)

    crudServiceStub.updateEntity.and.returnValue(of())
    component.hasPermission2edit = true
    fixture.detectChanges()

    expect(domHelper.count('.save-region')).toEqual(1)

    domHelper.clickButton('Guardar')
    // fixture.nativeElement.querySelector('button.save').click()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledTimes(1)
    // expect(crudServiceStub.updateEntity.calls.any()).toBeTruthy()

    expect(crudServiceStub.updateEntity).toHaveBeenCalledWith(k.routes.regions, modelTemp)
  })
})
