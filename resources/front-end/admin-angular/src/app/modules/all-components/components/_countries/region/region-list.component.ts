import {Component, Input, OnInit, OnChanges, SimpleChanges} from '@angular/core'
import {ActivatedRoute, Router} from '@angular/router'
import {NgbModal} from '@ng-bootstrap/ng-bootstrap'

import {
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
  BaseCmsListComponent,
} from 'base-cms' // from '@juanfv2/base-cms'
import {k} from '../../../../../../environments/k'
import {l} from '../../../../../../environments/l'

import {Country, Region} from '../../../../../models/_models'

const kRoute = k.routes.regions
const kConditions = `${k.suggestions}${kRoute}`

@Component({
  selector: 'app-region-list',
  templateUrl: './region-list.component.html',
  styleUrls: ['./region-list.component.scss'],
})
export class RegionListComponent extends BaseCmsListComponent implements OnInit, OnChanges {
  mCountry?: Country
  @Input()
  set country(country: Country) {
    if (country) {
      this.mCountry = country
      this.isSubComponentFrom = 'country'
    }
  }
  override itemCurrent?: Region

  constructor(
    public override router: Router,
    public override modalService: NgbModal,
    public override crudService: JfCrudService,
    public override messageService: JfMessageService,
    private route: ActivatedRoute
  ) {
    super()

    this.itemLabels = l.region
    this.labels = l
    this.kRoute = kRoute
    this.kConditions = kConditions
    this.mApi = new JfApiRoute(kRoute)
    this.responseList = new JfResponseList<Region | any>(0, 0, [])

    this.fieldsSearchable = [this.itemLabels.id, this.itemLabels.name, this.itemLabels.code, this.itemLabels.country_id]
    this.fieldsInList = [
      this.itemLabels.id,
      this.itemLabels.name,
      this.itemLabels.code,
      this.itemLabels.country_id,
      this.itemLabels.countryName,
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
      lazyLoadEvent: new JfLazyLoadEvent(10, 1, [new JfSort(this.itemLabels.id.field!, JfSort.desc)]),
      conditionCountry: new JfSearchCondition(),
      cModel: '-App-Models-Region',
    }
    this.currentFields(mSearch)

    const r = search ? JSON.parse(search) || mSearch : mSearch
    // console.log('r', r);
    return r
  }

  override initSearch(): void {
    this.modelSearch = this.initSearchModel()
    if (this.isSubComponent) {
      this.modelSearch.conditionCountry.value = this.mCountry
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

    nextOperator = JfUtils.x2one({
      conditions,
      conditionModel: this.modelSearch.conditionCountry,
      foreignKName: this.itemLabels.country_id.field,
      primaryKName: this.labels.country.id.name,
      nextOperator,
    })
    if (this.modelSearch?.conditions?.length) {
      for (const c of this.modelSearch.conditions) {
        nextOperator = JfUtils.addCondition(c, nextOperator, conditions)
      }
    }

    this.modelSearch.lazyLoadEvent.joins = [
      new JfCondition(`${this.labels.country.tableName}.id.country_id`, [
        // `${this.labels.country.tableName}.id as country_id`,
        `${this.labels.country.tableName}.name as countryName`,
      ]),
    ]
    this.modelSearch.lazyLoadEvent.conditions = conditions
    this.modelSearch.lazyLoadEvent.additional = [new JfCondition('to_index', '.')]
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
    this.itemCurrent = {country: this.mCountry} as unknown as Region
    super.onAddNew(m)
  }
}
