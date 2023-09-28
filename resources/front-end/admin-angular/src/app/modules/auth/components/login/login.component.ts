import {Component, isDevMode, OnInit} from '@angular/core'
import {FormBuilder, FormGroup, Validators} from '@angular/forms'
import {Title} from '@angular/platform-browser'
import {Router, ActivatedRoute} from '@angular/router'

import {JfAuthService, JfMessageService, JfResponse, JfUtils} from 'base-cms' // from '@juanfv2/base-cms'

import {k} from 'src/environments/k'
import {l} from 'src/environments/l'
import {User} from 'src/app/models/_models'
import {Development} from 'src/environments/resources'

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit {
  mFormGroup!: FormGroup
  project_name = k.project_name
  labels = l
  sending = false
  returnUrl = ''
  countrySelected: any = {id: 194, name: 'El Salvador', code: 'sv', flag: 'assets/flags/sv.png'}

  constructor(
    private title: Title,
    private router: Router,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private authService: JfAuthService,
    private messageService: JfMessageService
  ) {
    // this.getCompanyFormServer()
  }

  ngOnInit(): void {
    let e = ''
    let p = ''
    if (isDevMode()) {
      const t = new Development()
      // console.log('t', t.u())
      e = t.u()
      p = t.p()
    }
    this.mFormGroup = this.formBuilder.group({
      email: [e, [Validators.required, Validators.pattern('[^ @]*@[^ @]*')]],
      password: [p, Validators.required],
    })

    // reset login status
    this.sending = false
  }

  login(): void {
    const includes = ['token', 'person', 'photo', {roles: [{menus: ['subMenus']}, 'urlPermissions']}]

    this.sending = true
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/dashboard'

    this.authService.login(this.mFormGroup.value.email, this.mFormGroup.value.password, includes).subscribe({
      next: (resp: JfResponse) => {
        const user = resp.data as User
        this.sending = false
        if (user && user.token) {
          JfUtils.mStorage.setItem(k.expire, `${new Date().getTime() + k.expireTime * 60 * 60 * 1000}`)

          user.role = user.roles[0]
          if (user.role_id) {
            user.role = user.roles.find((r) => r.id === user.role_id)!
          }
          if (!user.role) {
            this.messageService.danger(k.project_name, 'No tiene ningún rol definido')
            return
          }
          user.role.urlPermissions.push('/dashboard')
          user.role.urlPermissions.push('/not-authorized')
          user.role.urlPermissions.push('/not-found')

          // const cDev = JfUtils.mStorage.getItem(k.dev)
          // const entityGlobalId = `${JfUtils.mStorage.getItem(k.entityGlobalId)}`
          // const userEntityGlobalId = user.country ? user.country.id : entityGlobalId

          const paths = JfUtils.mStorage.getPath()
          const cDev = paths[k.path.dev] === 'dev' ? 'dev' : ''
          const cName = paths[k.path.company] || 'admin'
          const entityGlobalId = paths[k.path.country] || 'sv'

          // JfUtils.mStorage.setItem(k._1_user, JSON.stringify(user))
          // JfUtils.mStorage.setItem(k._2_user_role_id, `${user.role.id}`)
          // JfUtils.mStorage.setItem(k._3_user_id, `${user.id}`)
          // JfUtils.mStorage.setItem(k.entityGlobalId, `${userEntityGlobalId}`)
          // JfUtils.mStorage.setItem( k.entityGlobal, JSON.stringify(user.country) || '{"id":194,"name":"El Salvador", "code": "sv"}')
          // JfUtils.mStorage.setItem(k.token, user.token)
          // JfUtils.mStorage.setItem(k.permissions, JSON.stringify(user.role.urlPermissions))

          JfUtils.mStorage.setItem(k._1_user, JSON.stringify(user))
          JfUtils.mStorage.setItem(k._2_user_role_id, `${user.role.id}`)
          JfUtils.mStorage.setItem(k._3_user_id, `${user.id}`)
          JfUtils.mStorage.setItem(k._6_entityGlobal, JSON.stringify(this.countrySelected))
          JfUtils.mStorage.setItem(k._10_token, user.token)
          JfUtils.mStorage.setItem(k._11_permissions, JSON.stringify(user.role.urlPermissions))

          this.authService.currentUser.next(user)

          // this.authService.isSideBarVisible.next(k.isSidebarVisibleOpen)
          // JfUtils.mStorage.setItem(k.isSidebarVisible, `${k.isSidebarVisibleOpen}`)
          // if (userEntityGlobalId === entityGlobalId) {
          //   this.router.navigate([this.returnUrl], {replaceUrl: true})
          // } else {
          //   const rDevelop = cDev || ''
          //   const newLocal = `/admin${rDevelop}/${userEntityGlobalId}#${this.returnUrl}`
          //   // console.log('newLocal', newLocal);
          //   location.href = newLocal
          // }

          this.router.navigate([this.returnUrl], {replaceUrl: true})
          // if success change to the body/dashboard component
          this.messageService.success(k.project_name, 'Ahora está conectado.')
        }
      },
      error: (error: any) => {
        this.sending = false
        this.messageService.danger(k.project_name, error)
        // console.log('error', error);
      },
    })
  }
}
