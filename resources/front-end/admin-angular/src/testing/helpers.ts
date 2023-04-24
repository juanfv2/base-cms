import {ComponentFixture} from '@angular/core/testing'
import {FormArray, FormGroup} from '@angular/forms'
import {By} from '@angular/platform-browser'

export class DOMHelper<T> {
  private fixture: ComponentFixture<T>
  constructor(fixture: ComponentFixture<T>) {
    this.fixture = fixture
  }

  singleText(tagName: string): string {
    const h2Ele = this.fixture.debugElement.query(By.css(tagName))
    if (h2Ele) {
      return h2Ele.nativeElement.textContent
    }
    return ''
  }

  count(tagName: string): number {
    const elements = this.fixture.debugElement.queryAll(By.css(tagName))
    return elements.length
  }

  countText(tagName: string, text: string): number {
    const elements = this.fixture.debugElement.queryAll(By.css(tagName))
    return elements.filter((element) => element.nativeElement.textContent === text).length
  }

  clickButton(buttonText: string) {
    this.findAll('button').forEach((button) => {
      const buttonElement: HTMLButtonElement = button.nativeElement
      if (buttonElement.textContent?.trim() === buttonText) {
        buttonElement.click()
      }
    })
  }

  findAll(tagName: string) {
    return this.fixture.debugElement.queryAll(By.css(tagName))
  }
}

export class Helpers {
  static generateObjectMocks(amount: number, properties: string[]) {
    return {}
  }

  static generateObjectsMock(pLabel: any, amount: number, exclude?: string[]): any[] {
    const items = []

    for (let index = 0; index < amount; index++) {
      const nItem: any = Helpers.generateObjectMock(pLabel, index, exclude)
      items.push(nItem)
    }

    return items
  }

  static generateObjectMock(pLabel: any, index: number, exclude?: string[]): any {
    const newObject: any = {}

    for (const key in pLabel) {
      if (pLabel.hasOwnProperty(key)) {
        const element = pLabel[key]
        if (element && typeof element === 'object') {
          if (!exclude?.includes(element.name)) {
            newObject[element.name] = `${pLabel.ownName}-${element.name}-${index}`
          }
        }
      }
    }

    return newObject
  }

  static findInvalidControls(fg: any) {
    const invalid = []
    const controls = fg.controls
    for (const name in controls) {
      if (controls[name].invalid) {
        invalid.push(name)
      }
    }
    return invalid
  }

  static findInvalidControlsRecursive(formToInvestigate: FormGroup | FormArray): string[] {
    var invalidControls: string[] = []
    let recursiveFunc = (form: FormGroup | FormArray) => {
      Object.keys(form.controls).forEach((field) => {
        const control = form.get(field)
        if (control?.invalid) invalidControls.push(field)
        if (control instanceof FormGroup) {
          recursiveFunc(control)
        } else if (control instanceof FormArray) {
          recursiveFunc(control)
        }
      })
    }
    recursiveFunc(formToInvestigate)
    return invalidControls
  }
}
