
DROP procedure IF EXISTS `sp_has_permission`;

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
END;
