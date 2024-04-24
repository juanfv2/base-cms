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

export const account_auto_complete_control_value_accessor: any = {
  provide: NG_VALUE_ACCESSOR,
  useExisting: forwardRef(() => AccountAutoCompleteComponent),
  multi: true,
}

const kRoute = k.routes.accounts

@Component({
  selector: 'app-account-auto-complete',
  template: jfTemplateAutoComplete,
  providers: [account_auto_complete_control_value_accessor],
})
export class AccountAutoCompleteComponent extends BaseCmsAutoCompleteComponent implements ControlValueAccessor {
  override labels = l
  override kRoute = kRoute

  // override formatter = (x: Account) => `${ x.name || ''}`;

  override searchTerm(term: string): Observable<any> {
    this.previousTerm = term
    const conditions: any[] = []
    if (this.avoidable && this.avoidable.length > 0) {
      conditions.push(
        new JfCondition(
          `and ${this.labels.account.id.field} not-in`,
          this.avoidable.map((r) => r.id)
        )
      )
    }

    if (term) {
      const g: any[] = []
      g.push(new JfCondition(`OR ${this.labels.account.id.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.account.first_name.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.account.last_name.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.account.cell_phone.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.account.birth_date.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.account.address.field} like`, term))
      g.push(new JfCondition(`OR ${this.labels.account.neighborhood.field} like`, term))
      conditions.push(g)
    }
    const mEvent = new JfLazyLoadEvent()
    mEvent.select = [
      this.labels.account.id.field,
      this.labels.account.first_name.field,
      this.labels.account.last_name.field,
      this.labels.account.cell_phone.field,
      this.labels.account.birth_date.field,
      this.labels.account.address.field,
      this.labels.account.neighborhood.field,
    ]
    mEvent.sorts = [new JfSort(`${this.labels.account.id.field}`, JfSort.asc)]
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
        this.messageService.danger(k.project_name, error, this.labels.account.ownName)
        return of([])
      })
    )
  }
}
