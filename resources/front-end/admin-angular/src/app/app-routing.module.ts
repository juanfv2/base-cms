import {NgModule} from '@angular/core'
import {RouterModule, Routes} from '@angular/router'

import {JfAuthGuard, NotFoundComponent} from 'base-cms' // from '@juanfv2/base-cms'

import {k} from '../environments/k'
import {AdminComponent} from './modules/auth/components/admin/admin.component'
import {LoginComponent} from './modules/auth/components/login/login.component'

const routes: Routes = [
  {redirectTo: 'dashboard', pathMatch: 'full', path: ''},
  {redirectTo: 'dashboard', pathMatch: 'full', path: k.routes.frontEnd.name},
  {redirectTo: 'dashboard', pathMatch: 'full', path: `${k.routes.frontEnd.name}/:company/:anyId`},
  {
    path: '',
    component: AdminComponent,
    canActivate: [JfAuthGuard],
    children: [{path: '', loadChildren: () => import('./modules/main/main.module').then((m) => m.MainModule)}],
  },
  {path: 'login', component: LoginComponent},
  {path: '**', component: NotFoundComponent},
]

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
