CREATE OR REPLACE PROCEDURE sp_has_permission(
    INOUT aggregate integer,
    _urlBackEnd character varying
)
LANGUAGE plpgsql

as $$
begin

aggregate = (SELECT  count(*)        FROM auth_users
INNER JOIN auth_role_user       ON auth_role_user.user_id = auth_users.id
INNER JOIN auth_roles           ON auth_role_user.role_id = auth_roles.id
INNER JOIN auth_permission_role ON auth_permission_role.role_id = auth_roles.id
INNER JOIN auth_permissions     ON auth_permission_role.permission_id = auth_permissions.id
WHERE auth_permissions.\"urlBackEnd\" = _urlBackEnd
AND auth_users.id = aggregate
AND auth_users.deleted_at IS NULL);

return;
end ;
$$
