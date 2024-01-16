import {NgModule} from '@angular/core'
import {CommonModule} from '@angular/common'
import {DashboardComponent} from './components/dashboard/dashboard.component'
import {RouterModule, Routes} from '@angular/router'
import {TransitionComponent} from 'base-cms'

export const MainRoutes: Routes = [
  {path: 'dashboard', component: DashboardComponent},
  {path: 'transition', component: TransitionComponent},

  {
    path: '',
    children: [{path: '', loadChildren: () => import('../system/system.module').then((m) => m.SystemModule)}],
  },

  {
    path: '',
    children: [{path: '', loadChildren: () => import('../countries/countries.module').then((m) => m.CountriesModule)}],
  },

  // lazy-loading submodules
]

@NgModule({
  imports: [CommonModule, RouterModule.forChild(MainRoutes)],
  declarations: [DashboardComponent],
})
export class MainModule {}
