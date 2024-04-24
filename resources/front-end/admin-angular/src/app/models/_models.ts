/* -------------------------------------------------------------------------- */
/* Systems                                                                    */
/* -------------------------------------------------------------------------- */

export interface User {
  id?: number
  name: string
  email: string
  password: string
  email_verified_at: any
  disabled: boolean
  phone_number: string
  uid: string
  role_id: number
  country_id: number
  region_id: number
  city_id: number
  remember_token: string
  countryName: string // mt1
  country: Country
  regionName: string // mt1
  region: Region
  cityName: string // mt1
  city: City
  roleName: string // mt1
  role: Role
  personName: string // 1tm
  person?: Person
  accountName: string // 1tm
  account?: Account
  rolesName: string // mtm
  roles: Role[]

  // customs
  token: string
  photo?: XFile
}

export interface Role {
  id?: number
  name: string
  description: string
  usersName: string // mtm
  users: User[]
  permissionName: string // mtm
  permissions: Permission[]

  // customs:
  length: number
  menus: Permission[]
  urlPermissions: string[]
  idsPermissions: number[]
}

export interface Permission {
  // Raw attributes
  id?: number
  icon?: string
  name?: string
  urlBackEnd?: string
  urlFrontEnd?: string
  isSection?: boolean
  isVisible?: boolean
  permission_id?: number
  orderInMenu?: number
  createdBy: number
  updatedBy: number

  // custom jfv
  subMenus?: Permission[]
  actions?: Permission[]
}

export interface XFile {
  // Raw attributes
  id: number
  entity: string
  field: string
  entity_id?: any
  name: string
  nameOriginal: string
  extension: string
  publicPath: string
  data: any
  created_at: string
  updated_at: string
  colors: boolean
}

export interface Person {
  id?: number
  first_name: string
  last_name: string
  cell_phone: string
  birth_date: any
  address: string
  neighborhood: string
  user_id: number
  createdBy: number
  updatedBy: number
  created_at: string
  updated_at: string
  deleted_at: string
  userName: string // mt1
  user: User
}

export interface Account {
  id?: number
  first_name: string
  last_name: string
  cell_phone: string
  birth_date: any
  address: string
  neighborhood: string
  user_id: number
  userName: string // mt1
  user: User
}

export interface VisorLogError {
  id?: number
  payload: string
  queue: string
  container_id: number
  created_at: string
  error: any
}

/* -------------------------------------------------------------------------- */
/* Catalogs                                                                   */
/* -------------------------------------------------------------------------- */

/* -------------------------------------------------------------------------- */
/* Countries                                                                  */
/* -------------------------------------------------------------------------- */

export interface Country {
  id?: number
  name: string
  code: string
  userName: string // 1tm
  users: User[]
  cityName: string // 1tm
  cities: City[]
  regionName: string // 1tm
  regions: Region[]
}

export interface Region {
  id?: number
  name: string
  code: string
  country_id: number
  userName: string // 1tm
  users: User[]
  countryName: string // mt1
  country: Country
  cityName: string // 1tm
  cities: City[]
}

export interface City {
  id?: number
  name: string
  latitude: string
  longitude: string
  country_id: number
  region_id: number
  userName: string // 1tm
  users: User[]
  countryName: string // mt1
  country: Country
  regionName: string // mt1
  region: Region
}
