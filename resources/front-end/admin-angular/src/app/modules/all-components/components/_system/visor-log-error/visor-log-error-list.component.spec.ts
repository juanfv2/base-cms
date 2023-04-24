import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {RouterTestingModule} from '@angular/router/testing'
import {FormsModule} from '@angular/forms'
import {BaseCmsModule, JfResponseList} from 'base-cms' // @juanfv2/base-cms
import {VisorLogError} from 'src/app/models/_models'
import {DOMHelper, Helpers} from 'src/testing/helpers'

import {VisorLogErrorListComponent} from './visor-log-error-list.component'

describe('VisorLogErrorListComponent', () => {
  let component: VisorLogErrorListComponent
  let domHelper: DOMHelper<VisorLogErrorListComponent>
  let fixture: ComponentFixture<VisorLogErrorListComponent>

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [VisorLogErrorListComponent],
      imports: [HttpClientTestingModule, RouterTestingModule, FormsModule, BaseCmsModule],
    }).compileComponents()

    fixture = TestBed.createComponent(VisorLogErrorListComponent)
    component = fixture.componentInstance
    const items = Helpers.generateObjectsMock(component.itemLabels, 3)
    component.responseList = {content: items} as JfResponseList<VisorLogError>
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
