import {k as Constants} from 'base-cms' // from '@juanfv2/base-cms'
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
k.versionV = 'd-2023_01_18T22_23_50_800Z'
k.timeToLive = 60 * 60 // 60 - min
k.timeToLiveProd = 60 * 30 // 30 - min
// console.log('k', k)
