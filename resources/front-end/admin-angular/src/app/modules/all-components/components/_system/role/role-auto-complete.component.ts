import {Component, forwardRef} from '@angular/core'
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

// Resource: http://almerosteyn.com/2016/04/linkup-custom-control-to-ngcontrol-ngmodel

export const role_auto_complete_control_value_accessor: any = {
  provide: NG_VALUE_ACCESSOR,
  useExisting: forwardRef(() => RoleAutoCompleteComponent),
  multi: true,
}

const kRoute = k.routes.roles

@Component({
  selector: 'app-role-auto-complete',
  template: jfTemplateAutoComplete,
  providers: [role_auto_complete_control_value_accessor],
})
export class RoleAutoCompleteComponent extends BaseCmsAutoCompleteComponent implements ControlValueAccessor {
  override labels = l
  override kRoute = kRoute

  // override formatter = (x: Role) => `${ x.name || ''}`;

  override searchTerm(term: string): Observable<any> {
    this.previousTerm = term
    const conditions: any[] = []
    if (this.avoidable && this.avoidable.length > 0) {
      const noValid = this.avoidable.map((r) => r.id)
      conditions.push(new JfCondition(`and ${this.labels.role.id.field} not-in`, noValid))
    }

    if (term) {
      const g: any[] = []
      g.push(new JfCondition(`OR ${this.labels.role.id.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.role.name.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.role.description.field} like`, term))
      conditions.push(g)
    }
    const mEvent = new JfLazyLoadEvent()
    mEvent.select = [this.labels.role.id.field, this.labels.role.name.field]
    mEvent.sorts = [new JfSort(`${this.labels.role.id.field}`, JfSort.asc)]
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
        this.messageService.danger(k.project_name, error, this.labels.role.ownName)
        return of([])
      })
    )
  }
}
