import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core'
import {FormBuilder, FormGroup, Validators} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'

import {JfResponse, JfApiRoute, JfCrudService, JfRequestOption, JfMessageService} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import {Country} from 'src/app/models/_models'

const kRoute = k.routes.countries

@Component({
  selector: 'app-country-detail',
  templateUrl: './country-detail.component.html',
  styleUrls: ['./country-detail.component.scss'],
})
export class CountryDetailComponent implements OnInit, OnDestroy {
  @Output() saveClicked = new EventEmitter<Country>()
  @Output() cancelClicked = new EventEmitter()

  @Input() country: Country
  @Input() isSubComponentFrom = '-'
  @Input() isSubComponent = false

  mFormGroup!: FormGroup
  labels = l
  includes = ['user', 'city', 'region']
  mApi = new JfApiRoute(kRoute)
  private mSubscription: any
  sending = false
  hasPermission2new = false
  hasPermission2edit = false

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private crudService: JfCrudService,
    private messageService: JfMessageService
  ) {
    this.country = {} as Country
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2edit = JfRequestOption.isAuthorized(`/${kRoute}/edit`) || this.hasPermission2new
  }

  ngOnInit(): void {
    this.validateFormGroup()

    this.mSubscription = this.route.params.subscribe((params) => {
      const id = this.isSubComponent ? this.country?.id : params['id']
      // console.log('params', params, `\nthis.country`, this.country);
      this.newCountry(this.country)
      if (id !== 'new') {
        this.getCountry(id)
      }
    })
  }

  ngOnDestroy(): void {
    if (!this.isSubComponent && this.mSubscription) {
      this.mSubscription.unsubscribe()
    }
  }

  newCountry(tempCountry?: Country): void {
    this.country = tempCountry || ({} as Country)
    delete this.country.id

    this.validateFormGroup()
  }

  getCountry(id: any): void {
    const mId = `${id}?includes=${JSON.stringify(this.includes)}`
    this.sending = true
    this.crudService.getEntity(kRoute, mId).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.country = resp.data

        this.validateFormGroup()
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.country.ownName)
      },
    })
  }

  onSave(): void {
    const modelTemp = JSON.parse(JSON.stringify(this.country))

    for (const field in this.mFormGroup.controls) {
      modelTemp[field] = this.mFormGroup.controls[field].value
    }

    // prepare

    // modelTemp.includes = this.includes;
    // prepare
    this.sending = true
    this.crudService.updateEntity(kRoute, modelTemp).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.country.id = resp.data.id
        this.messageService.success(k.project_name, 'Guardado')
        if (this.isSubComponent) {
          // ?? this.saveClicked.emit(this.country);
        } else {
          this.router.navigate([kRoute, this.country.id])
        }
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.country.ownName)
      },
    })
  }

  addNew(): void {
    this.newCountry()
    this.router.navigate([kRoute, 'new'])
  }

  onBack(): void {
    if (this.isSubComponent) {
      this.cancelClicked.emit('cancel')
      return
    }

    this.router.navigate([kRoute])
  }

  private validateFormGroup() {
    this.mFormGroup = this.formBuilder.group({
      name: [this.country.name, Validators.required],
      code: [this.country.code, [Validators.required, Validators.minLength(2), Validators.maxLength(2)]],
    })
  }
  // ? m2m
}
