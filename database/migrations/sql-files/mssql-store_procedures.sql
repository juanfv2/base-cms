DROP PROCEDURE sp_has_permission
GO

CREATE PROCEDURE sp_has_permission
@user_id int,
@urlFrontEnd varchar(255)
AS
SELECT count(*) AS aggregate FROM auth_users
INNER JOIN auth_user_role       ON auth_user_role.user_id = auth_users.id
INNER JOIN auth_roles           ON auth_user_role.role_id = auth_roles.id
INNER JOIN auth_role_permission ON auth_role_permission.role_id = auth_roles.id
INNER JOIN auth_permissions     ON auth_role_permission.permission_id = auth_permissions.id
WHERE auth_permissions.urlBackEnd = @urlFrontEnd
AND auth_users.id =  @user_id
AND auth_users.deleted_at IS NULL;
GO
