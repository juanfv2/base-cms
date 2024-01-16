import {NgModule} from '@angular/core'
import {CommonModule} from '@angular/common'

/* -------------------------------------------------------------------------- */
/* system                                                                     */
/* -------------------------------------------------------------------------- */
import {AccountAutoCompleteComponent} from './components/_system/account/account-auto-complete.component'
import {AccountDetailComponent} from './components/_system/account/account-detail.component'

import {PersonAutoCompleteComponent} from './components/_system/person/person-auto-complete.component'
import {PersonDetailComponent} from './components/_system/person/person-detail.component'

import {RoleAutoCompleteComponent} from './components/_system/role/role-auto-complete.component'
import {RoleDetailComponent} from './components/_system/role/role-detail.component'
import {RoleListComponent} from './components/_system/role/role-list.component'

import {UserAutoCompleteComponent} from './components/_system/user/user-auto-complete.component'
import {UserDetailComponent} from './components/_system/user/user-detail.component'
import {UserListComponent} from './components/_system/user/user-list.component'

import {VisorLogErrorListComponent} from './components/_system/visor-log-error/visor-log-error-list.component'

/* -------------------------------------------------------------------------- */
/* countries                                                                  */
/* -------------------------------------------------------------------------- */
import {CityAutoCompleteComponent} from './components/_countries/city/city-auto-complete.component'
import {CityDetailComponent} from './components/_countries/city/city-detail.component'
import {CityListComponent} from './components/_countries/city/city-list.component'
import {CountryAutoCompleteComponent} from './components/_countries/country/country-auto-complete.component'
import {CountryDetailComponent} from './components/_countries/country/country-detail.component'
import {CountryListComponent} from './components/_countries/country/country-list.component'
import {RegionAutoCompleteComponent} from './components/_countries/region/region-auto-complete.component'
import {RegionDetailComponent} from './components/_countries/region/region-detail.component'
import {RegionListComponent} from './components/_countries/region/region-list.component'

import {FormsModule, ReactiveFormsModule} from '@angular/forms'
import {RouterModule} from '@angular/router'
import {NgbPaginationModule, NgbDatepickerModule, NgbTypeaheadModule, NgbNavModule} from '@ng-bootstrap/ng-bootstrap'
import {BaseCmsModule} from 'base-cms'

const components = [
  /* -------------------------------------------------------------------------- */
  /* system                                                                     */
  /* -------------------------------------------------------------------------- */
  UserListComponent,
  UserDetailComponent,
  UserAutoCompleteComponent,
  RoleListComponent,
  RoleDetailComponent,
  RoleAutoCompleteComponent,

  AccountDetailComponent,
  AccountAutoCompleteComponent,

  PersonDetailComponent,
  PersonAutoCompleteComponent,

  VisorLogErrorListComponent,

  /* -------------------------------------------------------------------------- */
  /* countries                                                                  */
  /* -------------------------------------------------------------------------- */
  CountryListComponent,
  CountryDetailComponent,
  CountryAutoCompleteComponent,
  RegionListComponent,
  RegionDetailComponent,
  RegionAutoCompleteComponent,
  CityListComponent,
  CityDetailComponent,
  CityAutoCompleteComponent,
]

@NgModule({
  declarations: [...components],
  exports: [...components],
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,

    NgbPaginationModule,
    NgbDatepickerModule,
    NgbTypeaheadModule,
    NgbNavModule,

    BaseCmsModule,
  ],
})
export class AllComponentsModule {}
