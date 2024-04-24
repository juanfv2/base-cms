import {NgModule} from '@angular/core'
import {RouterModule, Routes} from '@angular/router'

import {k} from '../../../environments/k'

import {AllComponentsModule} from '../all-components/all-components.module'

import {CityDetailComponent} from '../all-components/components/_countries/city/city-detail.component'
import {CityListComponent} from '../all-components/components/_countries/city/city-list.component'
import {CountryDetailComponent} from '../all-components/components/_countries/country/country-detail.component'
import {CountryListComponent} from '../all-components/components/_countries/country/country-list.component'
import {RegionDetailComponent} from '../all-components/components/_countries/region/region-detail.component'
import {RegionListComponent} from '../all-components/components/_countries/region/region-list.component'

const _Routes: Routes = [
  {path: `${k.routes.countries}/:id`, component: CountryDetailComponent},
  {path: k.routes.countries, component: CountryListComponent},

  {path: `${k.routes.regions}/:id`, component: RegionDetailComponent},
  {path: k.routes.regions, component: RegionListComponent},

  {path: `${k.routes.cities}/:id`, component: CityDetailComponent},
  {path: k.routes.cities, component: CityListComponent},

  // ...
]

@NgModule({
  declarations: [],
  imports: [AllComponentsModule, RouterModule.forChild(_Routes)],
})
export class CountriesModule {}
/**/
