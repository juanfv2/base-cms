CREATE OR REPLACE PROCEDURE sp_has_permission(
    INOUT aggregate integer,
    _urlBackEnd character varying
)
LANGUAGE plpgsql

as $$
begin

aggregate = (SELECT  count(*)        FROM auth_users
INNER JOIN auth_users_has_roles       ON auth_users_has_roles.user_id = auth_users.id
INNER JOIN auth_roles                 ON auth_users_has_roles.role_id = auth_roles.id
INNER JOIN auth_roles_has_permissions ON auth_roles_has_permissions.role_id = auth_roles.id
INNER JOIN auth_permissions           ON auth_roles_has_permissions.permission_id = auth_permissions.id
WHERE auth_permissions.\"urlBackEnd\" = _urlBackEnd
AND auth_users.id = aggregate
AND auth_users.deleted_at IS NULL);

return;
end ;
$$
