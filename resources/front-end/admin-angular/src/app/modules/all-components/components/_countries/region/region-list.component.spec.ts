import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {RouterTestingModule} from '@angular/router/testing'
import {FormsModule} from '@angular/forms'
import {BaseCmsModule, JfResponseList} from 'base-cms' // @juanfv2/base-cms
import {Region} from 'src/app/models/_models'
import {DOMHelper, Helpers} from 'src/testing/helpers'

import {RegionListComponent} from './region-list.component'
import {AllComponentsModule} from '../../../all-components.module'

describe('RegionListComponent', () => {
  let component: RegionListComponent
  let domHelper: DOMHelper<RegionListComponent>
  let fixture: ComponentFixture<RegionListComponent>

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [RegionListComponent],
      imports: [HttpClientTestingModule, RouterTestingModule, FormsModule, BaseCmsModule, AllComponentsModule],
    }).compileComponents()

    fixture = TestBed.createComponent(RegionListComponent)
    component = fixture.componentInstance
    const items = Helpers.generateObjectsMock(component.itemLabels, 3)
    component.responseList = {content: items} as JfResponseList<Region>
    component.hasPermission2delete = true

    domHelper = new DOMHelper(fixture)
    fixture.detectChanges()
  })

  it('should create', () => {
    expect(component).toBeTruthy()
  })

  it('should have [3] headers', () => {
    expect(domHelper.count('table.table thead th')).toBe(component.fieldsInList.length + 1)
  })

  it('should render [3] items', () => {
    expect(domHelper.count('table.table tbody tr')).toBe(3)
  })
})