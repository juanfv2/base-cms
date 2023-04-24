import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core'
import {FormBuilder, FormGroup, Validators} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'

import {JfResponse, JfApiRoute, JfCrudService, JfRequestOption, JfMessageService} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'
import {isValidLatitude, isValidLongitude} from 'src/app/shared/validators'

import {City} from 'src/app/models/_models'

const kRoute = k.routes.cities

@Component({
  selector: 'app-city-detail',
  templateUrl: './city-detail.component.html',
  styleUrls: ['./city-detail.component.scss'],
})
export class CityDetailComponent implements OnInit, OnDestroy {
  @Output() saveClicked = new EventEmitter<City>()
  @Output() cancelClicked = new EventEmitter()

  @Input() city: City
  @Input() isSubComponentFrom = '-'
  @Input() isSubComponent = false

  mFormGroup!: FormGroup
  labels = l
  includes = ['user', 'country', 'region']
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
    this.city = {} as City
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2edit = JfRequestOption.isAuthorized(`/${kRoute}/edit`) || this.hasPermission2new
  }

  ngOnInit(): void {
    this.validateFormGroup()

    this.mSubscription = this.route.params.subscribe((params) => {
      const id = this.isSubComponent ? this.city?.id : params['id']
      // console.log('params', params, `\nthis.city`, this.city);
      this.newCity(this.city)
      if (id !== 'new') {
        this.getCity(id)
      }
    })
  }

  ngOnDestroy(): void {
    if (!this.isSubComponent && this.mSubscription) {
      this.mSubscription.unsubscribe()
    }
  }

  newCity(tempCity?: City): void {
    this.city = tempCity || ({} as City)
    delete this.city.id

    this.validateFormGroup()
  }

  getCity(id: any): void {
    const mId = `${id}?includes=${JSON.stringify(this.includes)}`
    this.sending = true
    this.crudService.getEntity(kRoute, mId).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.city = resp.data

        this.validateFormGroup()
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.city.ownName)
      },
    })
  }

  onSave(): void {
    const modelTemp = JSON.parse(JSON.stringify(this.city))

    for (const field in this.mFormGroup.controls) {
      modelTemp[field] = this.mFormGroup.controls[field].value
    }

    // prepare
    modelTemp.country_id = null
    if (modelTemp.country) {
      modelTemp.country_id = modelTemp.country.id
      delete modelTemp.country
    }
    modelTemp.region_id = null
    if (modelTemp.region) {
      modelTemp.region_id = modelTemp.region.id
      delete modelTemp.region
    }
    // modelTemp.includes = this.includes;
    // prepare
    this.sending = true
    this.crudService.updateEntity(kRoute, modelTemp).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.city.id = resp.data.id
        this.messageService.success(k.project_name, 'Guardado')
        if (this.isSubComponent) {
          // ?? this.saveClicked.emit(this.city);
        } else {
          this.router.navigate([kRoute, this.city.id])
        }
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.city.ownName)
      },
    })
  }

  addNew(): void {
    this.newCity()
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
      name: [this.city.name, Validators.required],
      latitude: [this.city.latitude, [Validators.required, isValidLatitude()]],
      longitude: [this.city.longitude, [Validators.required, isValidLongitude()]],
      country: [this.city.country, Validators.required],
      region: [this.city.region, Validators.required],
    })

    this.mFormGroup.controls['country'].valueChanges.subscribe((val) => {
      this.mFormGroup.controls['region'].setValue(null)
    })
  }
  // ? m2m
}
