import {Component, forwardRef, Input} from '@angular/core'
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

import {User, Country, Region, City, Role} from '../../../../../models/_models'

// Resource: http://almerosteyn.com/2016/04/linkup-custom-control-to-ngcontrol-ngmodel

export const user_auto_complete_control_value_accessor: any = {
  provide: NG_VALUE_ACCESSOR,
  useExisting: forwardRef(() => UserAutoCompleteComponent),
  multi: true,
}

const kRoute = k.routes.users

@Component({
  selector: 'app-user-auto-complete',
  template: jfTemplateAutoComplete,
  providers: [user_auto_complete_control_value_accessor],
})
export class UserAutoCompleteComponent extends BaseCmsAutoCompleteComponent implements ControlValueAccessor {
  @Input() country!: Country
  @Input() region!: Region
  @Input() city!: City
  @Input() role!: Role

  override labels = l
  override kRoute = kRoute

  // override formatter = (x: User) => `${ x.name || ''}`;

  override searchTerm(term: string): Observable<any> {
    this.previousTerm = term
    const conditions: any[] = []
    if (this.avoidable && this.avoidable.length > 0) {
      conditions.push(
        new JfCondition(
          `and ${this.labels.user.id.field} not-in`,
          this.avoidable.map((r) => r.id)
        )
      )
    }
    if (this.country) {
      conditions.push(new JfCondition(this.labels.user.country_id.field, this.country.id))
    }
    if (this.region) {
      conditions.push(new JfCondition(this.labels.user.region_id.field, this.region.id))
    }
    if (this.city) {
      conditions.push(new JfCondition(this.labels.user.city_id.field, this.city.id))
    }
    if (this.role) {
      conditions.push(new JfCondition(this.labels.user.role_id.field, this.role.id))
    }
    if (term) {
      const g: any[] = []
      g.push(new JfCondition(`OR ${this.labels.user.id.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.user.name.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.user.email.field} like`, term))
      conditions.push(g)
    }
    const mEvent = new JfLazyLoadEvent()
    mEvent.select = [this.labels.user.id.field, this.labels.user.name.field, this.labels.user.email.field]
    mEvent.sorts = [new JfSort(this.labels.user.id.field, JfSort.asc)]
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
        this.messageService.danger(k.project_name, error, this.labels.user.ownName)
        return of([])
      })
    )
  }
}
