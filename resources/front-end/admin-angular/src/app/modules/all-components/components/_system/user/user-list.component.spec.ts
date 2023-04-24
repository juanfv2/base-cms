import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ComponentFixture, TestBed} from '@angular/core/testing'
import {RouterTestingModule} from '@angular/router/testing'
import {ActivatedRoute} from '@angular/router'
import {FormsModule} from '@angular/forms'

import {BaseCmsModule, JfResponseList} from 'base-cms' // @juanfv2/base-cms

import {User} from 'src/app/models/_models'
import {DOMHelper, Helpers} from 'src/testing/helpers'

import {UserListComponent} from './user-list.component'
import {UserDetailComponent} from './user-detail.component'
import {AllComponentsModule} from '../../../all-components.module'

describe('UserListComponent', () => {
  let component: UserListComponent
  let domHelper: DOMHelper<UserListComponent>
  let fixture: ComponentFixture<UserListComponent>
  const activeRouteStub: jasmine.SpyObj<ActivatedRoute> = jasmine.createSpyObj('route', [{snapshot: [{url: 'users'}]}])

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [UserListComponent],
      imports: [
        HttpClientTestingModule,
        RouterTestingModule.withRoutes([
          {path: 'users', component: UserListComponent},
          {path: 'users/new', component: UserDetailComponent},
        ]),
        FormsModule,
        BaseCmsModule,
        AllComponentsModule,
      ],
      providers: [{provide: ActivatedRoute, useValue: activeRouteStub}],
    }).compileComponents()

    fixture = TestBed.createComponent(UserListComponent)
    component = fixture.componentInstance
    const items = Helpers.generateObjectsMock(component.itemLabels, 3, ['photo', 'roles'])
    component.responseList = {content: items} as JfResponseList<User>
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
