import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core'
import {FormBuilder, FormGroup, Validators} from '@angular/forms'
import {ActivatedRoute, Router} from '@angular/router'

import {
  JfResponse,
  JfApiRoute,
  JfCrudService,
  JfRequestOption,
  JfMessageService,
  JfLazyLoadEvent,
  JfCondition,
  JfSort,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import {Role, User, Permission} from 'src/app/models/_models'

const kRoute = k.routes.roles

@Component({
  selector: 'app-role-detail',
  templateUrl: './role-detail.component.html',
  styleUrls: ['./role-detail.component.scss'],
})
export class RoleDetailComponent implements OnInit, OnDestroy {
  @Output() saveClicked = new EventEmitter<Role>()
  @Output() cancelClicked = new EventEmitter()

  @Input() role: Role
  @Input() isSubComponentFrom = '-'
  @Input() isSubComponent = false

  mFormGroup!: FormGroup
  labels = l
  includes = ['idsPermissions']
  mApi = new JfApiRoute(kRoute)
  private mSubscription: any
  sending = false
  hasPermission2new = false
  hasPermission2edit = false
  tabActive = '1'
  permissionSource: Permission[] = []
  roleIdsPermissionStr = ''

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private crudService: JfCrudService,
    private messageService: JfMessageService
  ) {
    this.role = {} as Role
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2edit = JfRequestOption.isAuthorized(`/${kRoute}/edit`) || this.hasPermission2new
  }

  ngOnInit(): void {
    this.validateFormGroup()

    this.mSubscription = this.route.params.subscribe((params) => {
      const id = this.isSubComponent ? this.role?.id : params['id']
      // console.log('params', params, `\nthis.role`, this.role);
      this.getPermissions()
      this.newRole(this.role)
      if (id !== 'new') {
        this.getRole(id)
      }
    })
  }

  ngOnDestroy(): void {
    if (!this.isSubComponent && this.mSubscription) {
      this.mSubscription.unsubscribe()
    }
  }

  newRole(tempRole?: Role): void {
    this.role = tempRole || ({} as Role)
    delete this.role.id

    this.validateFormGroup()
    this.users2update()
    this.permissions2update()
  }

  getRole(id: any): void {
    const mId = `${id}?includes=${JSON.stringify(this.includes)}`
    this.sending = true
    this.crudService.getEntity(kRoute, mId).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.role = resp.data

        this.validateFormGroup()
        this.users2update()
        this.permissions2update()
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.role.ownName)
      },
    })
  }

  onSave(): void {
    const modelTemp = JSON.parse(JSON.stringify(this.role))

    for (const field in this.mFormGroup.controls) {
      modelTemp[field] = this.mFormGroup.controls[field].value
    }

    // prepare
    modelTemp.permissions = this.role.idsPermissions
    // modelTemp.includes = this.includes;
    // prepare
    this.sending = true
    this.crudService.updateEntity(kRoute, modelTemp).subscribe({
      next: (resp: JfResponse) => {
        this.sending = false
        this.role.id = resp.data.id
        this.messageService.success(k.project_name, 'Guardado')
        if (this.isSubComponent) {
          // ?? this.saveClicked.emit(this.role);
        } else {
          this.router.navigate([kRoute, this.role.id])
        }
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error, this.labels.role.ownName)
      },
    })
  }

  addNew(): void {
    this.newRole()
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
      name: [this.role.name, Validators.required],
      description: [this.role.description, Validators.required],
      roleIdsPermissionStr: [this.roleIdsPermissionStr, Validators.required],
    })
  }
  // ? m2m
  users2update($e?: any): void {
    this.role.users = this.role.users || []
    // are required? do you need something like that?
    // this.usersAreRequired   = this.role.users.length > 0 ? '-' : '';
    // this.usersAvoidable  = [...new Set([...k.roleClients, ...this.usersSelectable])];
  }

  user2rm(user: User): void {
    this.role.users = this.role.users.filter((r: any) => r.id !== user.id)
    // this.users2update();
  }

  user2go(user: User): void {
    this.router.navigate([k.routes.users, user.id])
  }

  permissions2update($e?: any): void {
    this.role.idsPermissions = this.role.idsPermissions || []
    this.roleIdsPermissionStr = this.role?.idsPermissions?.join(',')
    this.mFormGroup?.controls['roleIdsPermissionStr']?.setValue(this.roleIdsPermissionStr)

    // are required? do you need something like that?
    // this.permissionsAreRequired   = this.role.permissions.length > 0 ? '-' : '';
    // this.permissionsAvoidable  = [...new Set([...k.roleClients, ...this.permissionsSelectable])];
  }

  permission2rm(permission: Permission): void {
    this.role.permissions = this.role.permissions.filter((r: any) => r.id !== permission.id)
    // this.permissions2update();
  }

  permission2go(permission: Permission): void {
    this.router.navigate([k.routes.permissions, permission.id])
  }

  getPermissions(): void {
    const lazyLoadEvent = new JfLazyLoadEvent()
    lazyLoadEvent.conditions = [new JfCondition('isSection', 1)]
    lazyLoadEvent.additional = [new JfCondition('cp', this.mApi.show())]
    lazyLoadEvent.sorts = [new JfSort('orderInMenu', JfSort.asc)]
    lazyLoadEvent.includes = [{subMenus: ['actions']}]
    lazyLoadEvent.rows = -1

    this.crudService.getPage(k.routes.permissions, lazyLoadEvent).subscribe({
      next: (resp: JfResponse) => (this.permissionSource = resp.data.content),
      error: (error) => this.messageService.danger(k.project_name, error, 'Permisos'),
    })
  }

  permissionSelected(id: number, all: boolean = false): void {
    const exist = this.role?.idsPermissions?.find((p: any) => p === id) !== undefined ///> 0;
    if (exist) {
      // remove
      this.role.idsPermissions = this.role.idsPermissions.filter((obj) => obj !== id)
      if (all) {
        // permission parent
        const p: any = this.permissionSource.find((mP) => mP.id === id)
        if (p) {
          // subMenus of permission parent
          p.subMenus.forEach((m: any) => {
            this.role.idsPermissions = this.role.idsPermissions.filter((p01) => p01 !== m.id)
            // actions of subMenus
            m.actions.forEach(
              (a: any) => (this.role.idsPermissions = this.role.idsPermissions.filter((p02) => p02 !== a.id))
            )
          })
        }
      }
    } else {
      // add
      this.role.idsPermissions.push(id)
      if (all) {
        const p: any = this.permissionSource.find((mP) => mP.id === id)
        if (p) {
          p.subMenus.forEach((m: any) => {
            this.role.idsPermissions.push(m.id)
            m.actions.forEach((a: any) => this.role.idsPermissions.push(a.id))
          })
        }
      }
    }
    // console.log('this.role.idsPermissions', this.role.idsPermissions);
    this.permissions2update()
  }

  permissionIsSelected(id: number): boolean {
    if (id > 0) {
      const b = this.role?.idsPermissions?.find((p) => p === id) !== undefined ///> 0;
      // console.log('id:', id, ' b', b);
      return b
    }
    return false
    // $event.binary = true
  }
}
