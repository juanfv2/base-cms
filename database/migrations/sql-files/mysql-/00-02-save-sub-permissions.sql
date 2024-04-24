truncate auth_permission_permission;

call sp_save_permission_permission('api.roles.show','api.roles.permissions');

call sp_save_permission_permission('api.users.index','api.countries.index');
call sp_save_permission_permission('api.users.index','api.regions.index');
call sp_save_permission_permission('api.users.index','api.cities.index');
call sp_save_permission_permission('api.users.index','api.roles.index');

call sp_save_permission_permission('api.users.show','api.countries.index');
call sp_save_permission_permission('api.users.show','api.regions.index');
call sp_save_permission_permission('api.users.show','api.cities.index');
call sp_save_permission_permission('api.users.show','api.roles.index');

call sp_save_permission_permission('api.regions.index','api.countries.index');
call sp_save_permission_permission('api.regions.show','api.countries.index');

call sp_save_permission_permission('api.cities.index','api.countries.index');
call sp_save_permission_permission('api.cities.index','api.regions.index');

call sp_save_permission_permission('api.cities.show','api.countries.index');
call sp_save_permission_permission('api.cities.show','api.regions.index');
