import {AbstractControl, ValidationErrors, ValidatorFn, Validators} from '@angular/forms'

export function fieldConditionallyRequiredValidator(formControl: AbstractControl) {
  console.log('formControl?.parent?.get(id)?.value', !!formControl?.parent?.get('id')?.value)
  if (!formControl?.parent?.get('id')?.value) {
    return Validators.required(formControl)
  }
  return null
}

export function createPasswordStrengthValidator(formControl: AbstractControl) {
  const value = formControl.value

  if (!value) {
    return null
  }

  const hasUpperCase = /[A-Z]+/.test(value)

  const hasLowerCase = /[a-z]+/.test(value)

  const hasNumeric = /[0-9]+/.test(value)

  const passwordValid = hasUpperCase && hasLowerCase && hasNumeric

  return !passwordValid ? {passwordStrength: true} : null
}

export function validateRequiredIf(required: boolean): ValidatorFn {
  return (formControl: AbstractControl): ValidationErrors | null => {
    if (required) {
      return Validators.required(formControl)
    }
    return null
  }
}

export function isValidLatitude(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const lat = control.value
    if (!lat) return null
    const isValid = isFinite(lat) && Math.abs(lat) <= 90
    return !isValid ? {validLatitude: true} : null
  }
}

export function isValidLongitude(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const lng = control.value
    if (!lng) return null
    const isValid = isFinite(lng) && Math.abs(lng) <= 180
    return !isValid ? {validLongitude: true} : null
  }
}
