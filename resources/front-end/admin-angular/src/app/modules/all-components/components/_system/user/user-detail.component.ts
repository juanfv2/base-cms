import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core'
import {FormBuilder, FormGroup, Validators} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'
import {NgbDateParserFormatter} from '@ng-bootstrap/ng-bootstrap'

import {
  JfResponse,
  JfApiRoute,
  JfCrudService,
  JfRequestOption,
  JfMessageService,
  JfCondition,
  JfAuthService,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from '../../../../../../environments/k'
import {l} from '../../../../../../environments/l'

import {User, Account, Person, Role, XFile} from '../../../../../models/_models'

import {validateRequiredIf, createPasswordStrengthValidator} from '../../../../../shared/validators'

const kRoute = k.routes.users

@Component({
  selector: 'app-user-detail',
  templateUrl: './user-detail.component.html',
  styleUrls: ['./user-detail.component.scss'],
})
export class UserDetailComponent implements OnInit, OnDestroy {
  @Output() saveClicked = new EventEmitter<User>()
  @Output() cancelClicked = new EventEmitter()

  @Input() user!: User
  @Input() isSubComponentFrom = '-'
  @Input() isSubComponent = false

  mFormGroup!: FormGroup
  labels = l
  itemLabels: any = l.user
  includes = ['country', 'region', 'city', 'role', 'person', 'account', 'roles', 'photo']
  mApi = new JfApiRoute(kRoute)
  private mSubscription: any
  sending = false
  hasPermission2new = false
  hasPermission2edit = false
  currentPath!: string

  rolesAdmins = k.rolesAdmins
  rolesAreRequired = ''
  rolesSelected: Role[] = []
  rolesAvoidable = k.rolesClients

  /**
   * Imagen:
   * - requerida
   * - sin color
   */
  photo?: XFile
  uploaderPhotoUrl: any

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private crudService: JfCrudService,
    private authService: JfAuthService,
    private messageService: JfMessageService,
    private parserFormatter: NgbDateParserFormatter
  ) {
    this.user = {} as User
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2edit = JfRequestOption.isAuthorized(`/${kRoute}/edit`) || this.hasPermission2new
  }

  ngOnInit(): void {
    this.validateFormGroup()

    this.mSubscription = this.route.params.subscribe((params) => {
      const id = this.isSubComponent ? this.user?.id : params['id']

      this.currentPath = this.route.snapshot.url[0].path
      // console.log('params', params, `\nthis.user`, this.user)

      this.newUser(this.user)
      if (id !== 'new') {
        this.getUser(id)
      }
    })
  }

  ngOnDestroy(): void {
    if (!this.isSubComponent && this.mSubscription) {
      this.mSubscription.unsubscribe()
    }
  }

  newUser(tempUser?: User): void {
    this.user = tempUser || ({} as User)
    delete this.user.id
    this.user.account = undefined
    this.user.person = undefined
    this.user.roles = []
    this.user.disabled = false
    this.rolesAreRequired = ''
    this.rolesSelected = []
    this.rolesAvoidable = k.rolesClients
    this.photo = undefined

    setTimeout(() => {
      this.photo = {id: -1, entity: l.user.tableName, field: l.user.photo.field, entity_id: -1} as XFile
      switch (this.currentPath) {
        case k.routes.accounts:
          const _accountRole = {id: k.role_id_3_customer} as Role
          this.user.account = {} as Account
          this.user.role = _accountRole
          this.user.roles = [_accountRole]
          this.itemLabels = this.labels.account
          break

        // case k.routes.drivers:
        //   const _driverRole = {id: k.role_id_driver} as Role
        //   this.user.driver = {} as Driver
        //   this.user.role = _driverRole
        //   this.user.roles = [_driverRole]
        //   this.currentLabels = this.labels.driver
        //   break

        default:
          this.user.person = {} as Person
          this.itemLabels = this.labels.user
          break
      }

      this.validateFormGroup()
      this.roles2update()
      console.log('UserDetailComponent.newUser.person', this.user?.person)
    }, 75)
  }

  getUser(id: any): void {
    const mId = `${id}?includes=${JSON.stringify(this.includes)}`
    this.sending = true
    this.crudService.getEntity(kRoute, mId).subscribe({
      next: (resp: JfResponse) => {
        this.user.account = undefined
        this.user.person = undefined
        setTimeout(() => {
          this.sending = false
          this.user = resp.data
          this.photo = this.user.photo
          this.user.email_verified_at = this.parserFormatter.parse(this.user.email_verified_at)

          if (this.user.account) {
            this.user.account.birthDate = this.parserFormatter.parse(this.user.account.birthDate)
          }
          if (this.user.person) {
            this.user.person.birthDate = this.parserFormatter.parse(this.user.person.birthDate)
          }

          this.validateFormGroup()
          this.roles2update()
          console.log('UserDetailComponent.getUser.person', this.user?.person)
        }, 150)
        console.log('UserDetailComponent.getUser.person', this.user?.person)
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.user.ownName)
      },
    })
  }

  onSave(): void {
    let modelTemp = JSON.parse(JSON.stringify(this.user))
    let info = {} as any
    let _date: any

    for (const field in this.mFormGroup.controls) {
      modelTemp[field] = this.mFormGroup.controls[field].value
    }

    modelTemp.roles = modelTemp.roles || []
    // modelTemp.withEntity = this.isPathPeople ? l.person.tableName : l.account.tableName
    // const info = this.isPathPeople ? modelTemp.person : modelTemp.account

    switch (this.currentPath) {
      case k.routes.accounts:
        modelTemp.withEntity = l.account.tableName
        info = modelTemp.account
        break
      // case k.routes.drivers:
      //   modelTemp.withEntity = l.driver.tableName
      //   info = modelTemp.driver
      //   break

      default:
        modelTemp.withEntity = l.person.tableName
        info = modelTemp.person
        break
    }

    modelTemp = Object.assign(modelTemp, info)

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
    modelTemp.city_id = null
    if (modelTemp.city) {
      modelTemp.city_id = modelTemp.city.id
      delete modelTemp.city
    }
    modelTemp.role_id = null
    if (modelTemp.role) {
      modelTemp.role_id = modelTemp.role.id
      modelTemp.roles.push({id: modelTemp.role_id})
      delete modelTemp.role
    } else {
      modelTemp.role_id = modelTemp.roles[0].id
    }
    modelTemp.roles = modelTemp.roles ? modelTemp.roles.map((item: any) => item.id) : []

    if (modelTemp.password) {
      modelTemp.password = modelTemp.password
      modelTemp.password_confirmation = modelTemp.password
    } else {
      delete modelTemp.password
      delete modelTemp.password_confirmation
    }

    if (modelTemp.email_verified_at) {
      _date = modelTemp.email_verified_at
      modelTemp.email_verified_at = `${_date.year}-${_date.month}-${_date.day}`
    }

    if (modelTemp.birthDate) {
      _date = modelTemp.birthDate
      modelTemp.birthDate = `${_date.year}-${_date.month}-${_date.day}`
    }

    delete modelTemp.account
    delete modelTemp.person
    delete modelTemp.rolesAreRequired

    // modelTemp.includes = this.includes;
    // prepare
    this.sending = true
    this.crudService.updateEntity(kRoute, modelTemp).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.user.id = resp.data.id

        if (this.uploaderPhotoUrl) {
          this.photo = {id: -1, entity: l.user.tableName, field: l.user.photo.field, entity_id: -1} as XFile
          this.photo.entity_id = this.user.id
          setTimeout(() => {
            this.sending = true
            this.uploaderPhotoUrl.uploadAll()
          }, 100)
        }

        setTimeout(() => {
          this.messageService.success(k.project_name, 'Guardado')
          if (this.isSubComponent) {
            // ?? this.saveClicked.emit(this.user);
          } else {
            this.router.navigate([this.currentPath, this.user.id])
          }
        }, 200)
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.user.ownName)
      },
    })
  }

  addNew(): void {
    this.newUser()
    this.router.navigate([this.currentPath, 'new'])
  }

  onBack(): void {
    if (this.isSubComponent) {
      this.cancelClicked.emit('cancel')
      return
    }
    this.router.navigate([this.currentPath])
  }

  private validateFormGroup() {
    this.mFormGroup = this.formBuilder.group({
      name: [this.user.name, Validators.required],
      email: [this.user.email, [Validators.required, Validators.pattern('[^ @]*@[^ @]*')]],
      password: [
        this.user.password,
        [validateRequiredIf(!this.user.id), Validators.minLength(8), createPasswordStrengthValidator],
      ],
      phoneNumber: [this.user.phoneNumber],
      // email_verified_at: [this.user.email_verified_at],
      // uid: [this.user.uid],
      disabled: [this.user.disabled, Validators.required],
      country: [this.user.country, Validators.required],
      region: [this.user.region, Validators.required],
      city: [this.user.city, Validators.required],
      role: [this.user.role],
      rolesAreRequired: [this.rolesAreRequired, [validateRequiredIf(this.currentPath === k.routes.users)]],
      photo_id: [this.user.photo?.id, Validators.required],
    })

    this.mFormGroup.controls['country'].valueChanges.subscribe((val) => {
      this.mFormGroup.controls['region'].setValue(null)
    })
    this.mFormGroup.controls['region'].valueChanges.subscribe((val) => {
      this.mFormGroup.controls['city'].setValue(null)
    })
  }

  onFinishUploadFile(condition: JfCondition): void {
    console.log('condition', condition)
    switch (condition.c) {
      case this.labels.user.photo.field:
        this.user.photo = condition.v as XFile
        this.uploaderPhotoUrl = null
        this.authService.currentUser.subscribe((u) => {
          if (u.id === this.user.id) {
            u.photo = this.user.photo
          }
        })
        break
      // case 'images':
      //   this.person.images.push(condition.value as XFile);
      //   break;
      default:
        break
    }
  }

  uploaderQueue(condition: JfCondition): void {
    console.log('condition', condition)
    switch (condition.c) {
      case this.labels.user.photo.field:
        this.uploaderPhotoUrl = condition.v
        this.mFormGroup.controls['photo_id'].setValue(1)
        break
      // case 'images':
      //   this.uploaderImages = condition.value;
      //   break;
      default:
        console.log('???', condition.c)
        break
    }
  }

  // ? m2m
  roles2update($e?: any): void {
    this.user.roles = this.user.roles || []

    if (this.currentPath === k.routes.users) {
      this.rolesSelected = [...new Set([...this.user.roles, ...this.rolesSelected])]
      this.rolesAvoidable = JSON.parse(JSON.stringify(this.rolesSelected))
      const noRoles = k.rolesClients
      this.rolesAvoidable = [...new Set([...noRoles, ...this.rolesAvoidable])]

      this.user.roles = this.rolesSelected
      this.rolesAreRequired = this.user.roles.length > 0 ? '-' : ''
      this.mFormGroup.controls['rolesAreRequired'].setValue(this.rolesAreRequired)
    }

    // are required? do you need something like that?
    // this.rolesAreRequired   = this.user.roles.length > 0 ? '-' : '';
    // this.rolesAvoidable  = [...new Set([...k._1_userClients, ...this.rolesSelectable])];
  }

  role2rm(role: Role): void {
    this.user.roles = this.user.roles.filter((r: any) => r.id !== role.id)
    // this.roles2update();
  }

  role2go(role: Role): void {
    this.router.navigate([k.routes.roles, role.id])
  }
}
