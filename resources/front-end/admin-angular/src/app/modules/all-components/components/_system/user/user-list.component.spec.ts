import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {RouterTestingModule} from '@angular/router/testing'
import {FormsModule} from '@angular/forms'
import {of} from 'rxjs/internal/observable/of'

import {BaseCmsModule, JfCrudService} from 'base-cms' // @juanfv2/base-cms

import {DOMHelper, Helpers} from '../../../../../../testing/helpers'

import {UserListComponent} from './user-list.component'
import {AllComponentsModule} from '../../../all-components.module'

describe('UserListComponent', () => {
  let component: UserListComponent
  let domHelper: DOMHelper<UserListComponent>
  let fixture: ComponentFixture<UserListComponent>
  const crudServiceStub: jasmine.SpyObj<JfCrudService> = jasmine.createSpyObj('crudService', ['getPage'])

  beforeEach(async () => {
    crudServiceStub.getPage.calls.reset()

    await TestBed.configureTestingModule({
      declarations: [UserListComponent],
      imports: [HttpClientTestingModule, RouterTestingModule, FormsModule, BaseCmsModule, AllComponentsModule],
      providers: [{provide: JfCrudService, useValue: crudServiceStub}],
    }).compileComponents()

    fixture = TestBed.createComponent(UserListComponent)
    component = fixture.componentInstance
    component.hasPermission2delete = true

    const resp = {data: {content: Helpers.generateModelsMock(component.fieldsInList, 3, ['photo', 'roles'])}}
    crudServiceStub.getPage.and.returnValue(of(resp))

    domHelper = new DOMHelper(fixture)
    fixture.detectChanges()
  })

  it('should create', () => {
    expect(component).toBeTruthy()
  })

  it('should have [3] headers', () => {
    expect(domHelper.count('table.table thead th')).toBe(component.fieldsInList.filter((f) => f.allowInList).length + 1)
  })

  it('should render [3] items', () => {
    expect(domHelper.count('table.table tbody tr')).toBe(3)
  })

  it('should render [3] items, invoke crud-service getPage', () => {
    component.onLazyLoad()

    expect(crudServiceStub.getPage).toHaveBeenCalledTimes(2)
  })
})
