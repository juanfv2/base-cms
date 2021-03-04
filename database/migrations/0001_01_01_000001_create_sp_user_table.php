<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "
        -- -- --------- ----
        -- -- mysql     ----
        -- -- --------- ----
CREATE PROCEDURE `sp_has_permission`(
IN `_user_id` bigint,
IN `_urlBackEnd` varchar(255)
)
BEGIN
-- . --
SELECT count(*) AS `aggregate`          FROM `auth_users`
INNER JOIN `auth_users_has_roles`       ON `auth_users_has_roles`.`user_id` = `auth_users`.`id`
INNER JOIN `auth_roles`                 ON `auth_users_has_roles`.`role_id` = `auth_roles`.`id`
INNER JOIN `auth_roles_has_permissions` ON `auth_roles_has_permissions`.`role_id` = `auth_roles`.`id`
INNER JOIN `auth_permissions`           ON `auth_roles_has_permissions`.`permission_id` = `auth_permissions`.`id`
WHERE `auth_permissions`.`urlBackEnd` = `_urlBackEnd`
AND `auth_users`.`id` = `_user_id`
AND `auth_users`.`deleted_at` IS NULL;
-- . --
END";

        DB::unprepared("DROP procedure IF EXISTS `sp_has_permission`;");
        DB::unprepared($procedure);
    }
}
/*
---- --------- ----
---- sqlserver ----
---- --------- ----

DROP PROCEDURE sp_has_permission
GO

CREATE PROCEDURE sp_has_permission
@user_id int,
@urlFrontEnd varchar(255)
AS
SELECT count(*) AS aggregate FROM auth_users
INNER JOIN auth_users_has_roles       ON auth_users_has_roles.user_id = auth_users.id
INNER JOIN auth_roles                 ON auth_users_has_roles.role_id = auth_roles.id
INNER JOIN auth_roles_has_permissions ON auth_roles_has_permissions.role_id = auth_roles.id
INNER JOIN auth_permissions           ON auth_roles_has_permissions.permission_id = auth_permissions.id
WHERE auth_permissions.urlBackEnd = @urlFrontEnd
AND auth_users.id =  @user_id
AND auth_users.deleted_at IS NULL;
GO
*/
