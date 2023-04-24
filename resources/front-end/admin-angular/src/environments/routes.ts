const rootServer = '/'
const baseFrontEnd = 'admin'
const baseBackEnd = ''
const sBaseBackEnd = `${baseBackEnd}`

export const routes = {
  api: 'api/',
  frontEnd: {
    name: baseFrontEnd,
    root: `/${baseFrontEnd}/`,
  },
  backEnd: {
    name: baseBackEnd,
    root: `${sBaseBackEnd}/`,
    sRoot: `${sBaseBackEnd}`,
    storage: `${rootServer}storage/assets/`,
    assets: `${rootServer}assets/`,
    rootServer,
  },
  misc: {
    importCsv: 'import-csv',
    exportCsv: 'export-csv',
    file: 'file/',
    xFiles: 'x_files',
    seeder: 'seeder',
    subscribe: 'subscribe',
    visor_log_errors: 'visor-log-errors',
  },
  /* -------------------------------------------------------------------------- */
  /* System                                                                     */
  /* -------------------------------------------------------------------------- */
  login: 'login',
  logout: 'logout',
  users: 'users',
  roles: 'roles',
  permissions: 'permissions',
  people: 'people',
  accounts: 'accounts',
  visor_log_errors: 'visor-log-errors',

  /* -------------------------------------------------------------------------- */
  /* Countries                                                                  */
  /* -------------------------------------------------------------------------- */
  countries: 'countries',
  regions: 'regions',
  cities: 'cities',
}
