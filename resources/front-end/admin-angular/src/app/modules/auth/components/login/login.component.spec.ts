import {ComponentFixture, TestBed} from '@angular/core/testing'
import {HttpClientTestingModule} from '@angular/common/http/testing'
import {ReactiveFormsModule} from '@angular/forms'
import {RouterTestingModule} from '@angular/router/testing'
import {BaseCmsModule, JfAuthService} from 'base-cms' // from '@juanfv2/base-cms'
import {of} from 'rxjs'

import {LoginComponent} from './login.component'

describe('LoginComponent', () => {
  let component: LoginComponent
  let fixture: ComponentFixture<LoginComponent>
  const authServiceStub: jasmine.SpyObj<JfAuthService> = jasmine.createSpyObj('authService', ['login'])

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [LoginComponent],
      imports: [RouterTestingModule, HttpClientTestingModule, ReactiveFormsModule, BaseCmsModule],
      providers: [{provide: JfAuthService, useValue: authServiceStub}],
    }).compileComponents()

    fixture = TestBed.createComponent(LoginComponent)
    component = fixture.componentInstance
    component.ngOnInit()
    fixture.detectChanges()
  })

  it('should create', () => {
    expect(component).toBeTruthy()
  })

  // it('should render form with email and password inputs', () => {
  //   const element = fixture.nativeElement
  //   expect(element.querySelector('form')).toBeTruthy()
  //   expect(element.querySelector('#email')).toBeTruthy()
  //   expect(element.querySelector('#password')).toBeTruthy()
  //   expect(element.querySelector('button')).toBeTruthy()
  // })

  it('should return model invalid when form is empty', () => {
    const email = component.mFormGroup.controls['email']
    email.setValue('')
    email.markAsDirty()
    const password = component.mFormGroup.controls['password']
    password.setValue('')
    password.markAsDirty()

    fixture.detectChanges()
    expect(component.mFormGroup.valid).toBeFalsy()
  })

  it('should validate email input as required', () => {
    const email = component.mFormGroup.controls['email']
    email.setValue('test')
    const errors = email.errors || {}

    expect(errors['required']).toBeFalsy()
    expect(errors['pattern']).toBeTruthy()
    expect(email.valid).toBeFalsy()
  })

  it('should render email validation message when formControl is touched and invalid', () => {
    const elements: HTMLElement = fixture.nativeElement
    expect(elements.querySelector('.email-error-invalid')).toBeNull()

    // elements.querySelector('button').click();
    const email = component.mFormGroup.controls['email']
    email.setValue('test')
    email.markAsTouched()

    fixture.detectChanges()
    // expect(elements.querySelector('.email-error-invalid')).toBeTruthy()
    expect(elements.querySelector('.email-error-invalid')?.textContent).toContain('Correo electrónico es invalido')
  })

  it('should render email validation message when formControl mark as dirty and empty', () => {
    const elements: HTMLElement = fixture.nativeElement
    expect(elements.querySelector('.email-error-required')).toBeNull()

    // elements.querySelector('button').click();
    const email = component.mFormGroup.controls['email']
    email.setValue('')
    email.markAsDirty()

    fixture.detectChanges()
    // expect(elements.querySelector('.email-error-required')).toBeTruthy()
    expect(elements.querySelector('.email-error-required')?.textContent).toContain('Correo electrónico es requerido')
  })

  it('should render password validation message when formControl mark as dirty and empty', () => {
    const elements: HTMLElement = fixture.nativeElement
    expect(elements.querySelector('.password-error-required')).toBeNull()

    // elements.querySelector('button').click();
    const password = component.mFormGroup.controls['password']
    password.setValue('')
    password.markAsDirty()

    fixture.detectChanges()
    // expect(elements.querySelector('.password-error-required')).toBeTruthy()
    expect(elements.querySelector('.password-error-required')?.textContent).toContain('Contraseña es requerida')
  })

  it('should invoke auth service when form is valid', () => {
    const includes = ['token', 'person', 'photo', {roles: [{menus: ['subMenus']}, 'urlPermissions']}]
    const email = component.mFormGroup.controls['email']
    const password = component.mFormGroup.controls['password']

    email.setValue('test@test.com')
    email.markAsDirty()

    password.setValue('123456')
    password.markAsDirty()

    authServiceStub.login.and.returnValue(of())

    fixture.detectChanges()

    fixture.nativeElement.querySelector('button').click()
    expect(authServiceStub.login.calls.any()).toBeTruthy()
    expect(authServiceStub.login).toHaveBeenCalledWith(email.value, password.value, includes)
  })
})
// https://dev.to/jpblancodb/angular-series-creating-a-login-with-tdd-3jkl
// https://codecraft.tv/courses/angular/unit-testing/model-driven-forms/
