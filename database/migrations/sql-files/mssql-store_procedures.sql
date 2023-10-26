DROP PROCEDURE sp_has_permission
GO

CREATE PROCEDURE sp_has_permission
@user_id int,
@urlFrontEnd varchar(255)
AS
SELECT count(*) AS aggregate FROM auth_users
INNER JOIN auth_role_user       ON auth_role_user.user_id = auth_users.id
INNER JOIN auth_roles           ON auth_role_user.role_id = auth_roles.id
INNER JOIN auth_permission_role ON auth_permission_role.role_id = auth_roles.id
INNER JOIN auth_permissions     ON auth_permission_role.permission_id = auth_permissions.id
WHERE auth_permissions.urlBackEnd = @urlFrontEnd
AND auth_users.id =  @user_id
AND auth_users.deleted_at IS NULL;
GO
