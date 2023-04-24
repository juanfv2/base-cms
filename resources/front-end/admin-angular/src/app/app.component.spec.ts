import {ComponentFixture, TestBed} from '@angular/core/testing'
import {RouterTestingModule} from '@angular/router/testing'
import {ServiceWorkerModule} from '@angular/service-worker'
import {BaseCmsModule, JfUtils} from 'base-cms'
import {k} from 'src/environments/k'
import {AppComponent} from './app.component'

describe('AppComponent', () => {
  let component: AppComponent
  let fixture: ComponentFixture<AppComponent>
  let localStore: any = {}

  beforeEach(() => {
    spyOn(localStorage, 'getItem').and.callFake((key: string) => (key in localStore ? localStore[key] : null))
    spyOn(localStorage, 'setItem').and.callFake((key: string, value: string) => (localStore[key] = value + ''))
    spyOn(localStorage, 'clear').and.callFake(() => (localStore = {}))
    JfUtils.getBaseLocation()
  })

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RouterTestingModule, ServiceWorkerModule.register('', {enabled: false}), BaseCmsModule],
      declarations: [AppComponent],
    }).compileComponents()
  })

  beforeEach(() => {
    fixture = TestBed.createComponent(AppComponent)
    component = fixture.componentInstance
    component.ngOnInit()
    fixture.detectChanges()
  })

  it('should create the app', () => {
    expect(component).toBeTruthy()
  })

  it(`should be inDevMode equal to ''`, () => {
    JfUtils.mStorage.setItem(k.dev, '')

    fixture.detectChanges()

    expect(component.inDevMode).toEqual('')
  })

  it(`should be inDevMode equal to 'dev'`, () => {
    JfUtils.mStorage.setItem(k.dev, 'dev')

    fixture = TestBed.createComponent(AppComponent)
    component = fixture.componentInstance
    component.ngOnInit()
    fixture.detectChanges()

    expect(component.inDevMode).toEqual('dev')
  })
})
