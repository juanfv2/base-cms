import {Component, Input, OnInit, OnChanges, SimpleChanges} from '@angular/core'
import {ActivatedRoute, Router} from '@angular/router'
import {NgbModal} from '@ng-bootstrap/ng-bootstrap'

import {
  DBType,
  JfSort,
  JfUtils,
  JfApiRoute,
  JfResponse,
  JfCondition,
  JfCrudService,
  JfResponseList,
  JfLazyLoadEvent,
  JfRequestOption,
  JfMessageService,
  JfSearchCondition,
  JfStorageManagement,
  BaseCmsListComponent,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from '../../../../../../environments/k'
import {l} from '../../../../../../environments/l'

import {VisorLogError} from '../../../../../models/_models'

const kRoute = k.routes.visorLogErrors
const kConditions = `${k.suggestions}${kRoute}`

@Component({
  selector: 'app-visor-log-error-list',
  templateUrl: './visor-log-error-list.component.html',
  styleUrls: ['./visor-log-error-list.component.scss'],
})
export class VisorLogErrorListComponent extends BaseCmsListComponent implements OnInit, OnChanges {
  override itemCurrent?: VisorLogError

  constructor(
    public override router: Router,
    public override modalService: NgbModal,
    public override crudService: JfCrudService,
    public override messageService: JfMessageService,
    private route: ActivatedRoute
  ) {
    super()

    this.itemLabels = l.visorLogError
    this.labels = l
    this.kRoute = kRoute
    this.kConditions = kConditions
    this.mApi = new JfApiRoute(kRoute)
    this.responseList = new JfResponseList<VisorLogError | any>(0, 0, [])

    this.fieldsInList = l.getDBFields(this.itemLabels).filter((_f) => !_f.hidden)
    this.fieldsSearchable = this.fieldsInList.filter((_f) => _f.allowSearch)

    this.hasPermission2show = JfRequestOption.isAuthorized(`/${kRoute}/show`)
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2delete = JfRequestOption.isAuthorized(`/${kRoute}/delete`)

    this.storageSession = true
  }

  ngOnInit(): void {
    this.initSearch()
    this.onLazyLoad()
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (this.isSubComponent) {
      this.initSearch()
      this.onLazyLoad()
    }
  }

  initSearchModel(): any {
    const search = !this.isSubComponent ? JfUtils.mStorage.getItem(this.kConditions, this.storageSession) : null
    const mSearch = {
      lazyLoadEvent: new JfLazyLoadEvent(10, 1, [new JfSort(this.itemLabels.id.field, JfSort.desc)]),
      cModel: '-App-Models-Misc-VisorLogError',
      fields: this.fieldsInList,
      fieldsSelected: this.fieldsInList.filter((_f: DBType) => _f.allowInList),
    }

    const r = search ? JSON.parse(search) || mSearch : mSearch

    this.currentFields(r)

    // console.log('r', r);

    return r
  }

  override initSearch(): void {
    this.modelSearch = this.initSearchModel()
    if (this.isSubComponent) {
    } else {
      if (this.modelSearch?.conditions?.length) {
        Promise.resolve(this.searchField).then(() => {
          for (const condition of this.modelSearch.conditions) {
            this.addFilter(condition)
          }
        })
      }
    }
  }

  override onLazyLoad(strAction = ''): void {
    if (this.loading) {
      return
    }

    // console.log('onLazyLoad this.loading', this.loading);
    // console.log('onLazyLoad this.loading', this.modelSearch);

    this.loading = true
    // prepare
    let nextOperator = 'AND'
    const conditions: any[] = []
    const conditionsAC: any[] = []
    const conditionsGeneric: any[] = []

    this.filtersFromAutocomplete(conditionsAC)

    if (this.modelSearch?.conditions?.length) {
      for (const c of this.modelSearch.conditions) {
        nextOperator = JfUtils.addCondition(c, nextOperator, conditionsGeneric)
      }
    }

    conditions.push(conditionsAC)
    conditions.push(conditionsGeneric)

    this.modelSearch.lazyLoadEvent.joins = []
    this.modelSearch.lazyLoadEvent.conditions = conditions
    this.modelSearch.lazyLoadEvent.additional = [new JfCondition('to_index', '.')]
    this.modelSearch.lazyLoadEvent.includes = ['error']

    const mSearch = JSON.stringify(this.modelSearch)

    switch (strAction) {
      case 'export':
        this.onLazyLoadExport(strAction)
        break
      default:
        this.onLazyLoadList(mSearch)
        break
    }
  }

  override onAddNew(m: any): void {
    throw new Error('Method not implemented.')
  }

  private filtersFromAutocomplete(conditions: any[]) {
    let nextOperator = 'AND'
  }

  copyInputMessage(valueText: any) {
    // console.log('inputElement', inputElement.innerText)
    const selBox = document.createElement('textarea')
    selBox.style.position = 'fixed'
    selBox.style.left = '0'
    selBox.style.top = '0'
    selBox.style.opacity = '0'
    selBox.value = valueText
    document.body.appendChild(selBox)
    selBox.focus()
    selBox.select()
    document.execCommand('copy')
    document.body.removeChild(selBox)
  }
}