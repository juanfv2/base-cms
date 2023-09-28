import {ComponentFixture, TestBed} from '@angular/core/testing'
import {ReactiveFormsModule} from '@angular/forms'
import {RouterTestingModule} from '@angular/router/testing'
import {NgIdleModule} from '@ng-idle/core'
import {BaseCmsModule, JfUtils} from 'base-cms'

import {AdminComponent} from './admin.component'
import {k} from 'src/environments/k'
import {user} from 'src/testing/resources.testing.spec'
import {By} from '@angular/platform-browser'
import {DebugElement} from '@angular/core'

describe('AdminComponent', () => {
  let component: AdminComponent
  let fixture: ComponentFixture<AdminComponent>
  let localStore: any = {}

  beforeEach(() => {
    spyOn(localStorage, 'getItem').and.callFake((key: string) => (key in localStore ? localStore[key] : null))
    spyOn(localStorage, 'setItem').and.callFake((key: string, value: string) => (localStore[key] = value + ''))
    spyOn(localStorage, 'clear').and.callFake(() => (localStore = {}))
    JfUtils.getBaseLocation()

    JfUtils.mStorage.setItem(k._1_user, JSON.stringify(user))
  })

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [AdminComponent],
      imports: [RouterTestingModule, ReactiveFormsModule, NgIdleModule.forRoot(), BaseCmsModule],
    }).compileComponents()

    fixture = TestBed.createComponent(AdminComponent)
    component = fixture.componentInstance
    fixture.detectChanges()
  })

  it('should create', () => {
    expect(component).toBeTruthy()
  })

  it('should have 3 sections', () => {
    const titleElements = fixture.debugElement.queryAll(By.css('.nav .nav-item'))
    expect(titleElements.length).toBe(user.role.menus.length)
  })

  it('should show the section titles', () => {
    const sectionElements = fixture.debugElement.queryAll(By.css('.nav .nav-item'))
    sectionElements.forEach((movieElement: DebugElement, index) => {
      expect(movieElement.nativeElement.innerHTML).toContain(user.role.menus[index].name)
    })
  })
})
