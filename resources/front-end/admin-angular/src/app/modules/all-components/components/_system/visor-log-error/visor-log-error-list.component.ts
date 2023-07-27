import {Component, Input, OnInit, OnChanges, SimpleChanges} from '@angular/core'
import {ActivatedRoute, Router} from '@angular/router'
import {NgbModal} from '@ng-bootstrap/ng-bootstrap'

import {
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
  JfSort,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import {VisorLogError} from 'src/app/models/_models'

const kRoute = k.routes.visor_log_errors
const kConditions = `${k.suggestions}${kRoute}`

@Component({
  selector: 'app-visor-log-error-list',
  templateUrl: './visor-log-error-list.component.html',
  styleUrls: ['./visor-log-error-list.component.scss'],
})
export class VisorLogErrorListComponent extends BaseCmsListComponent implements OnInit, OnChanges {
  override itemCurrent?: VisorLogError
  override itemLabels = l.visorLogError
  override labels = l
  override kRoute = kRoute
  override kConditions = kConditions
  override mApi = new JfApiRoute(kRoute)
  override responseList: JfResponseList<VisorLogError | any> = new JfResponseList<VisorLogError | any>(0, 0, [])

  constructor(
    public override router: Router,
    public override modalService: NgbModal,
    public override crudService: JfCrudService,
    public override messageService: JfMessageService,
    private route: ActivatedRoute
  ) {
    super()
    this.fieldsSearchable = [
      this.itemLabels.id,
      this.itemLabels.payload,
      this.itemLabels.queue,
      this.itemLabels.container_id,
      this.itemLabels.created_at,
    ]
    this.fieldsInList = [
      this.itemLabels.id,
      this.itemLabels.queue,
      this.itemLabels.container_id,
      this.itemLabels.created_at,
      this.itemLabels.error,
    ]
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
      lazyLoadEvent: new JfLazyLoadEvent(5, 1, [new JfSort(this.labels.visorLogError.id.field, JfSort.desc)]),
      cModel: '-App-Models-Misc-VisorLogError',
    }
    this.currentFields(mSearch)

    const r = search ? JSON.parse(search) || mSearch : mSearch
    // console.log('r', r);
    return r
  }

  override initSearch(): void {
    this.modelSearch = this.initSearchModel()
    if (this.isSubComponent) {
    } else {
      if (this.modelSearch) {
        if (this.modelSearch?.conditions?.length) {
          Promise.resolve(this.searchField).then(() => {
            for (const condition of this.modelSearch.conditions) {
              this.addFilter(condition)
            }
          })
        }
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

    if (this.modelSearch?.conditions?.length) {
      for (const c of this.modelSearch.conditions) {
        nextOperator = JfUtils.addCondition(c, nextOperator, conditions)
      }
    }
    // joinType === '<' leftJoin, '>' rightJoin
    // 'joinTable.joinTablePK.ownTableFK'
    // 'joinTable.joinTablePK.ownTableFK.joinType'
    // 'joinTable.joinTablePK.ownTable.ownTableFK'
    // 'joinTable.joinTablePK.ownTable.ownTableFK.joinType'
    this.modelSearch.lazyLoadEvent.joins = []
    this.modelSearch.lazyLoadEvent.conditions = conditions
    this.modelSearch.lazyLoadEvent.additional = []
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
