import {Role} from '../app/models/_models'

export const rolesAdmins = [
  {
    id: 1,
    name: 'Administrador',
    description: 'Administrador',
  },
  {
    id: 2,
    name: 'Sub Administrador',
    description: 'Sub Administrador',
  },
] as Role[]

export const rolesClients = [
  {
    id: 3,
    name: 'Compañía administrador',
    description: 'Compañía administrador',
  },
] as Role[]

export class Development {
  u(): string {
    return `${this.up1()}@${this.up2()}`
  }

  up1(): string {
    return `admin`
  }

  up2(): string {
    return 'admin.com'
  }

  p(): string {
    return `${this.p1()}${this.p3()}`
  }

  p1(): any {
    return 123
  }

  p3(): any {
    return 456
  }
}
