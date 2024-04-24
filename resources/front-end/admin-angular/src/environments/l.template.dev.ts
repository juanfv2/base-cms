export const l_template: any = {
  misc: {
    slug: {
      name: '',
      label: 'Slug',
      field: 'stores.slug',
      type: 'string',
      allowSearch: true,
      allowExport: true,
      allowImport: true,
      allowInList: true,
      fixed: false,
      sorting: true,
      alias: '',
      model: '',
    },
    csv: '/assets/images/admin/ic-csv.svg',
    zip: '/assets/images/admin/ic-zip.svg',
    pdf: '/assets/images/admin/ic-pdf.svg',
    upload: '/assets/images/admin/ic-upload.svg',
    loading: '/assets/images/admin/ic-loading.svg',
    pageLimit: [
      {c: '5', v: 5},
      {c: '10', v: 10},
      {c: '50', v: 50},
      {c: '100', v: 100},
      {c: '1,000', v: 1000},
      {c: '10,000', v: 10000},
    ],
  },

  msg: {
    error: 'Ha ocurrido un error',
    success: 'Operación realizada con éxito',
    warning: 'Atención',
    info: 'Información',
    confirm: 'Confirmar',
    confirmDelete: '¿Está seguro de eliminar este registro?',
    confirmDeleteMany: '¿Está seguro de eliminar los registros seleccionados?',
    confirmDeleteManyRows: '¿Está seguro de eliminar los registros seleccionados?',
    noData: 'No hay datos para mostrar',
  },

  /* -------------------------------------------------------------------------- */
  /* System                                                                     */
  /* -------------------------------------------------------------------------- */

  user: {
    tablePK: 'id',
    tableName: 'auth_users',
    ownName: 'Usuario',
    ownNamePlural: 'Usuarios',
  },

  role: {
    tablePK: 'id',
    tableName: 'auth_roles',
    ownName: 'Rol',
    ownNamePlural: 'Roles',
  },

  permission: {
    tablePK: 'id',
    tableName: 'auth_permissions',
    ownName: 'Permiso',
    ownNamePlural: 'Permisos',
  },

  person: {
    tablePK: 'id',
    tableName: 'auth_people',
    ownName: 'Personal',
    ownNamePlural: 'Personal',
  },

  visorLogError: {
    tablePK: 'id',
    tableName: 'visor_log_errors',
    ownName: 'Visualización de error',
    ownNamePlural: 'Visualización de errores',
  },

  account: {
    tablePK: 'id',
    tableName: 'auth_accounts',
    ownName: 'Cuenta',
    ownNamePlural: 'Cuentas',
  },

  /* -------------------------------------------------------------------------- */
  /* Countries                                                                  */
  /* -------------------------------------------------------------------------- */

  country: {
    tablePK: 'id',
    tableName: 'countries',
    ownName: 'País',
    ownNamePlural: 'Países',
  },

  region: {
    tablePK: 'id',
    tableName: 'regions',
    ownName: 'Departamento/Provincia',
    ownNamePlural: 'Departamentos/Provincias',
  },

  city: {
    tablePK: 'id',
    tableName: 'cities',
    ownName: 'Ciudad',
    ownNamePlural: 'Ciudades',
  },
}