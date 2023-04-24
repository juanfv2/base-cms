import {NgModule} from '@angular/core'
import {RouterModule, Routes} from '@angular/router'

import {k} from 'src/environments/k'

import {AllComponentsModule} from '../all-components/all-components.module'

import {RoleDetailComponent} from '../all-components/components/_system/role/role-detail.component'
import {RoleListComponent} from '../all-components/components/_system/role/role-list.component'
import {UserDetailComponent} from '../all-components/components/_system/user/user-detail.component'
import {UserListComponent} from '../all-components/components/_system/user/user-list.component'
import {VisorLogErrorListComponent} from '../all-components/components/_system/visor-log-error/visor-log-error-list.component'

const _Routes: Routes = [
  {path: `${k.routes.users}/:id/:profile`, component: UserDetailComponent},
  {path: `${k.routes.users}/:id`, component: UserDetailComponent},
  {path: k.routes.users, component: UserListComponent},

  {path: `${k.routes.accounts}/:id`, component: UserDetailComponent},
  {path: k.routes.accounts, component: UserListComponent},

  {path: `${k.routes.roles}/:id`, component: RoleDetailComponent},
  {path: k.routes.roles, component: RoleListComponent},

  {path: k.routes.visor_log_errors, component: VisorLogErrorListComponent},

  // ...
]

@NgModule({
  declarations: [],
  imports: [AllComponentsModule, RouterModule.forChild(_Routes)],
})
export class SystemModule {}
