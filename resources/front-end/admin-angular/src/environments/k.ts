import {k as Constants} from 'base-cms' // from '@juanfv2/base-cms'
import {l} from './l'
import {routes} from './routes'
import {rolesAdmins, rolesClients} from './resources'

export const k: any = Constants

k.sentry = ''
k.mapsApi = ''
k.project_name = 'Project-Name Admin'
k.project_name_short = 'p-n'
k.routes = routes
k.rolesAdmins = rolesAdmins
k.rolesClients = rolesClients
k.role_id_1_admin = 1
k.role_id_2_sub_admin = 2
k.role_id_3_customer = 3
k.versionV = 'd-2024_01_14T23_37_48_155Z'
k.expireTimeOut = 5
k.expireTime = 60

l.k = k

/* -------------------------------------------------------------------------- */
/* override default url path                                                  */
/* -------------------------------------------------------------------------- */

// default= host-0/<company-1>/<country-2>/<dev-3>
// ej.= ransa.net/admin/sv/dev
// ej.= ransa.net/admin/sv
// host/<root-1>/<company-2>/<country-3>/<dev-4>
// ej.= ransa.net/visor/admin/00
// k.path = {
//   root= 1
//   company= 2
//   country= 3
//   dev= 4
// }

// console.log('k' k)
