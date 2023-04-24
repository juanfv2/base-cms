import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core'
import {FormBuilder, FormGroup, Validators} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'

import {JfResponse, JfApiRoute, JfCrudService, JfRequestOption, JfMessageService} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import {Region} from 'src/app/models/_models'

const kRoute = k.routes.regions

@Component({
  selector: 'app-region-detail',
  templateUrl: './region-detail.component.html',
  styleUrls: ['./region-detail.component.scss'],
})
export class RegionDetailComponent implements OnInit, OnDestroy {
  @Output() saveClicked = new EventEmitter<Region>()
  @Output() cancelClicked = new EventEmitter()

  @Input() region: Region
  @Input() isSubComponentFrom = '-'
  @Input() isSubComponent = false

  mFormGroup!: FormGroup
  labels = l
  includes = ['user', 'country', 'city']
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
    this.region = {} as Region
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2edit = JfRequestOption.isAuthorized(`/${kRoute}/edit`) || this.hasPermission2new
  }

  ngOnInit(): void {
    this.validateFormGroup()

    this.mSubscription = this.route.params.subscribe((params) => {
      const id = this.isSubComponent ? this.region?.id : params['id']
      // console.log('params', params, `\nthis.region`, this.region);
      this.newRegion(this.region)
      if (id !== 'new') {
        this.getRegion(id)
      }
    })
  }

  ngOnDestroy(): void {
    if (!this.isSubComponent && this.mSubscription) {
      this.mSubscription.unsubscribe()
    }
  }

  newRegion(tempRegion?: Region): void {
    this.region = tempRegion || ({} as Region)
    delete this.region.id

    this.validateFormGroup()
  }

  getRegion(id: any): void {
    const mId = `${id}?includes=${JSON.stringify(this.includes)}`
    this.sending = true
    this.crudService.getEntity(kRoute, mId).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.region = resp.data

        this.validateFormGroup()
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.region.ownName)
      },
    })
  }

  onSave(): void {
    const modelTemp = JSON.parse(JSON.stringify(this.region))

    for (const field in this.mFormGroup.controls) {
      modelTemp[field] = this.mFormGroup.controls[field].value
    }

    // prepare
    modelTemp.country_id = null
    if (modelTemp.country) {
      modelTemp.country_id = modelTemp.country.id
      delete modelTemp.country
    }
    // modelTemp.includes = this.includes;
    // prepare
    this.sending = true
    this.crudService.updateEntity(kRoute, modelTemp).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.region.id = resp.data.id
        this.messageService.success(k.project_name, 'Guardado')
        if (this.isSubComponent) {
          // ?? this.saveClicked.emit(this.region);
        } else {
          this.router.navigate([kRoute, this.region.id])
        }
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.region.ownName)
      },
    })
  }

  addNew(): void {
    this.newRegion()
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
      name: [this.region.name, Validators.required],
      code: [this.region.code, Validators.required],
      country: [this.region.country, Validators.required],
    })
  }
  // ? m2m
}
