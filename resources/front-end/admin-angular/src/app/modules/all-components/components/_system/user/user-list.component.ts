import {Component, Input, OnInit, OnChanges, SimpleChanges} from '@angular/core'
import {ActivatedRoute, Router} from '@angular/router'
import {NgbModal} from '@ng-bootstrap/ng-bootstrap'

import {
  DBType,
  JfSort,
  JfUtils,
  JfApiRoute,
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

import {User, Country, Region, City, Role} from '../../../../../models/_models'

const kRoute = k.routes.users
const kConditions = `${k.suggestions}${kRoute}`

@Component({
  selector: 'app-user-list',
  templateUrl: './user-list.component.html',
  styleUrls: ['./user-list.component.scss'],
})
export class UserListComponent extends BaseCmsListComponent implements OnInit, OnChanges {
  mCountry?: Country
  @Input()
  set country(country: Country) {
    if (country) {
      this.mCountry = country
      this.isSubComponentFrom = 'country'
    }
  }

  mRegion?: Region
  @Input()
  set region(region: Region) {
    if (region) {
      this.mRegion = region
      this.isSubComponentFrom = 'region'
    }
  }

  mCity?: City
  @Input()
  set city(city: City) {
    if (city) {
      this.mCity = city
      this.isSubComponentFrom = 'city'
    }
  }

  mRole?: Role
  @Input()
  set role(role: Role) {
    if (role) {
      this.mRole = role
      this.isSubComponentFrom = 'role'
    }
  }
  override itemCurrent?: User

  isPathPeople = false

  constructor(
    public override router: Router,
    public override modalService: NgbModal,
    public override crudService: JfCrudService,
    public override messageService: JfMessageService,
    private route: ActivatedRoute
  ) {
    super()

    this.itemLabels = l.user
    this.labels = l
    this.kRoute = kRoute
    this.kConditions = kConditions
    this.mApi = new JfApiRoute(kRoute)
    this.responseList = new JfResponseList<User | any>(0, 0, [])

    this.fieldsInList = l.getDBFields(this.itemLabels).filter((_f) => !_f.hidden)
    this.fieldsSearchable = this.fieldsInList.filter((_f) => _f.allowSearch)

    this.hasPermission2show = JfRequestOption.isAuthorized(`/${kRoute}/show`)
    this.hasPermission2new = JfRequestOption.isAuthorized(`/${kRoute}/new`)
    this.hasPermission2delete = JfRequestOption.isAuthorized(`/${kRoute}/delete`)
    this.storageSession = true
  }

  ngOnInit(): void {
    this.isPathPeople = this.route.snapshot?.url[0]?.path === k.routes.users
    this.kConditions = `${this.kConditions}-${this.isPathPeople}`

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
      conditionCountry: new JfSearchCondition(),
      conditionRegion: new JfSearchCondition(),
      conditionCity: new JfSearchCondition(),
      conditionRole: new JfSearchCondition(),
      cModel: '-App-Models-Auth-User',
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
      this.modelSearch.conditionCountry.value = this.mCountry
      this.modelSearch.conditionRegion.value = this.mRegion
      this.modelSearch.conditionCity.value = this.mCity
      this.modelSearch.conditionRole.value = this.mRole
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

    const inCondition = this.isPathPeople ? 'not-in' : 'in'

    conditions.push(
      new JfCondition(
        `AND ${this.labels.user.role_id.field} ${inCondition}`,
        k.rolesClients.map((r: Role) => r.id)
      )
    )

    this.filtersFromAutocomplete(conditionsAC)

    if (this.modelSearch?.conditions?.length) {
      for (const c of this.modelSearch.conditions) {
        nextOperator = JfUtils.addCondition(c, nextOperator, conditionsGeneric)
      }
    }

    conditions.push(conditionsAC)
    conditions.push(conditionsGeneric)

    this.modelSearch.lazyLoadEvent.joins = [
      new JfCondition(`${this.labels.country.tableName}.id.country_id`, [
        // `${this.labels.country.tableName}.id as country_id`,
        `${this.labels.country.tableName}.name as countryName`,
      ]),
      new JfCondition(`${this.labels.region.tableName}.id.region_id`, [
        // `${this.labels.region.tableName}.id as region_id`,
        `${this.labels.region.tableName}.name as regionName`,
      ]),
      new JfCondition(`${this.labels.city.tableName}.id.city_id`, [
        // `${this.labels.city.tableName}.id as city_id`,
        `${this.labels.city.tableName}.name as cityName`,
      ]),
      new JfCondition(`${this.labels.role.tableName}.id.role_id`, [
        // `${this.labels.role.tableName}.id as role_id`,
        `${this.labels.role.tableName}.name as roleName`,
      ]),
    ]
    this.modelSearch.lazyLoadEvent.conditions = conditions
    this.modelSearch.lazyLoadEvent.additional = [new JfCondition('to_index', '.')]
    this.modelSearch.lazyLoadEvent.cWith = ['roles', 'photo']
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

  override onRowSelect(user: JfCondition): void {
    if (this.isSubComponent) {
      this.itemCurrent = {id: user.v?.id} as User
    } else {
      const id = user?.v?.id
      const withEntity = this.isPathPeople ? kRoute : k.routes.accounts
      this.router.navigate([withEntity, id])
    }
  }

  override onAddNew(m: any): void {
    if (this.isSubComponent) {
      this.itemCurrent = {
        id: 'new',
        country: this.mCountry,
        region: this.mRegion,
        city: this.mCity,
        role: this.mRole,
      } as unknown as User
    } else {
      const withEntity = this.isPathPeople ? kRoute : k.routes.accounts
      this.router.navigate([withEntity, 'new'])
    }
  }

  private filtersFromAutocomplete(conditions: any[]) {
    let nextOperator = 'AND'

    nextOperator = JfUtils.x2one({
      conditions,
      conditionModel: this.modelSearch.conditionCountry,
      foreignKName: this.itemLabels.country_id.field,
      primaryKName: this.labels.country.id.name,
      nextOperator,
    })

    nextOperator = JfUtils.x2one({
      conditions,
      conditionModel: this.modelSearch.conditionRegion,
      foreignKName: this.itemLabels.region_id.field,
      primaryKName: this.labels.region.id.name,
      nextOperator,
    })

    nextOperator = JfUtils.x2one({
      conditions,
      conditionModel: this.modelSearch.conditionCity,
      foreignKName: this.itemLabels.city_id.field,
      primaryKName: this.labels.city.id.name,
      nextOperator,
    })

    nextOperator = JfUtils.x2one({
      conditions,
      conditionModel: this.modelSearch.conditionRole,
      foreignKName: this.itemLabels.role_id.field,
      primaryKName: this.labels.role.id.name,
      nextOperator,
    })
  }
}