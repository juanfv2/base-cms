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
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from 'src/environments/k'
import {l} from 'src/environments/l'

import {Role} from 'src/app/models/_models'

const kRoute = k.routes.roles
const kConditions = `${k.suggestions}${kRoute}`

@Component({
  selector: 'app-role-list',
  templateUrl: './role-list.component.html',
  styleUrls: ['./role-list.component.scss'],
})
export class RoleListComponent extends BaseCmsListComponent implements OnInit, OnChanges {
  override itemCurrent?: Role
  override itemLabels = l.role
  override labels = l
  override kRoute = kRoute
  override kConditions = kConditions
  override mApi = new JfApiRoute(kRoute)
  override responseList: JfResponseList<Role | any> = new JfResponseList<Role | any>(0, 0, [])

  constructor(
    public override router: Router,
    public override modalService: NgbModal,
    public override crudService: JfCrudService,
    public override messageService: JfMessageService,
    private route: ActivatedRoute
  ) {
    super()
    this.fieldsSearchable = [this.itemLabels.id, this.itemLabels.name, this.itemLabels.description]
    this.fieldsInList = [this.itemLabels.id, this.itemLabels.name, this.itemLabels.description]
    this.hasPermission2show = JfRequestOption.isAuthorized(`/${kRoute}/show`)
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2delete = JfRequestOption.isAuthorized(`/${kRoute}/delete`)
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
    const search = !this.isSubComponent ? JfUtils.mStorage.getItem(kConditions) : null
    const mSearch = {
      lazyLoadEvent: new JfLazyLoadEvent(),
      cModel: '-App-Models-Auth-Role',
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
        if (this.modelSearch.conditions) {
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

    if (this.modelSearch.conditions) {
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
    // this.modelSearch.lazyLoadEvent.includes = ['relation-1tm', 'relation-mt1', 'relation-1t1', ...];
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
    this.itemCurrent = {} as unknown as Role
    super.onAddNew(m)
  }
}