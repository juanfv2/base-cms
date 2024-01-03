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
import {k} from '../../../../../../environments/k'
import {l} from '../../../../../../environments/l'

import {Country} from '../../../../../models/_models'

// Resource: http://almerosteyn.com/2016/04/linkup-custom-control-to-ngcontrol-ngmodel

export const region_auto_complete_control_value_accessor: any = {
  provide: NG_VALUE_ACCESSOR,
  useExisting: forwardRef(() => RegionAutoCompleteComponent),
  multi: true,
}

const kRoute = k.routes.regions

@Component({
  selector: 'app-region-auto-complete',
  template: jfTemplateAutoComplete,
  providers: [region_auto_complete_control_value_accessor],
})
export class RegionAutoCompleteComponent extends BaseCmsAutoCompleteComponent implements ControlValueAccessor {
  @Input() country!: Country

  override labels = l
  override kRoute = kRoute

  // override formatter = (x: Region) => `${ x.name || ''}`;

  override searchTerm(term: string): Observable<any> {
    this.previousTerm = term
    const conditions: any[] = []
    if (this.avoidable && this.avoidable.length > 0) {
      conditions.push(
        new JfCondition(
          `and ${this.labels.region.id.field} not-in`,
          this.avoidable.map((r) => r.id)
        )
      )
    }
    if (this.country) {
      conditions.push(new JfCondition(this.labels.region.country_id.field, this.country.id))
    }
    if (term) {
      const g: any[] = []
      g.push(new JfCondition(`OR ${this.labels.region.id.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.region.name.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.region.code.field} like`, term))
      conditions.push(g)
    }
    const mEvent = new JfLazyLoadEvent()
    mEvent.select = [this.labels.region.id.field, this.labels.region.name.field, this.labels.region.code.field]
    mEvent.sorts = [new JfSort(`${this.labels.region.id.field}`, JfSort.asc)]
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
        this.messageService.danger(k.project_name, error, this.labels.region.ownName)
        return of([])
      })
    )
  }
}
