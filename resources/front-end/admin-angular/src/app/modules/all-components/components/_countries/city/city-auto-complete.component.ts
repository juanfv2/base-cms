import {Component, Input, forwardRef} from '@angular/core'
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms'

import {Observable, of} from 'rxjs'
import {tap, catchError, map} from 'rxjs/operators'

import {
  JfSort,
  JfResponse,
  JfCondition,
  JfLazyLoadEvent,
  jfTemplateAutoComplete,
  BaseCmsAutoCompleteComponent,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import {City, Country, Region} from 'src/app/models/_models'

// Resource: http://almerosteyn.com/2016/04/linkup-custom-control-to-ngcontrol-ngmodel

export const city_auto_complete_control_value_accessor: any = {
  provide: NG_VALUE_ACCESSOR,
  useExisting: forwardRef(() => CityAutoCompleteComponent),
  multi: true,
}

const kRoute = k.routes.cities

@Component({
  selector: 'app-city-auto-complete',
  template: jfTemplateAutoComplete,
  providers: [city_auto_complete_control_value_accessor],
})
export class CityAutoCompleteComponent extends BaseCmsAutoCompleteComponent implements ControlValueAccessor {
  @Input() country!: Country
  @Input() region!: Region

  override labels = l
  override kRoute = kRoute

  // override formatter = (x: City) => `${ x.name || ''}`;

  override searchTerm(term: string): Observable<any> {
    this.previousTerm = term
    const conditions: any[] = []
    if (this.avoidable && this.avoidable.length > 0) {
      conditions.push(
        new JfCondition(
          `and ${this.labels.city.id.field} not-in`,
          this.avoidable.map((r) => r.id)
        )
      )
    }
    if (this.country) {
      conditions.push(new JfCondition(this.labels.city.country_id.field, this.country.id))
    }
    if (this.region) {
      conditions.push(new JfCondition(this.labels.city.region_id.field, this.region.id))
    }
    if (term) {
      const g: any[] = []
      g.push(new JfCondition(`OR ${this.labels.city.id.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.city.name.field} like`, term))
      conditions.push(g)
    }
    const mEvent = new JfLazyLoadEvent()
    mEvent.select = [this.labels.city.id.field, this.labels.city.name.field]
    mEvent.sorts = [new JfSort(`${this.labels.city.id.field}`, JfSort.asc)]
    mEvent.additional = [new JfCondition('cp', this.currentPage)]
    mEvent.conditions = conditions
    // mEvent.rows = 10;
    return this.crudService.getPage(kRoute, mEvent).pipe(
      tap(() => (this.searchFailed = false)),
      map((resp: JfResponse) => {
        this.searchFailed = resp.data.content.length === 0
        return resp.data.content
      }),
      catchError((error) => {
        this.searchFailed = true
        this.messageService.danger(k.project_name, error, this.labels.city.ownName)
        return of([])
      })
    )
  }
}
