DROP procedure IF EXISTS `sp_has_permission`;

CREATE PROCEDURE `sp_has_permission`(
IN `_user_id` bigint,
IN `_urlParent` varchar(255),
IN `_urlChild` varchar(255)
)
BEGIN
-- . --

IF `_urlChild` = '-.-' THEN
	SELECT count(*) AS `aggregate`     FROM `auth_users`
	INNER JOIN `auth_role_user`       ON `auth_role_user`.`user_id` = `auth_users`.`id`
	INNER JOIN `auth_roles`           ON `auth_role_user`.`role_id` = `auth_roles`.`id`
	INNER JOIN `auth_permission_role` ON `auth_permission_role`.`role_id` = `auth_roles`.`id`
	INNER JOIN `auth_permissions`     ON `auth_permission_role`.`permission_id` = `auth_permissions`.`id`
	WHERE `auth_permissions`.`urlBackEnd` = `_urlParent`
	AND `auth_users`.`id` = `_user_id`
	AND `auth_users`.`deleted_at` IS NULL;
ELSE
	SELECT count(*) AS `aggregate`     FROM `auth_users` `_u`
	INNER JOIN `auth_role_user`             `_ur` ON `_ur`.`user_id`         = `_u`.`id`
	INNER JOIN `auth_roles`                 `_r`  ON `_ur`.`role_id`         = `_r`.`id`
	INNER JOIN `auth_permission_role`       `_rp` ON `_rp`.`role_id`         = `_r`.`id`
	INNER JOIN `auth_permissions`           `_p1` ON `_rp`.`permission_id`   = `_p1`.`id`
	INNER JOIN `auth_permission_permission` `_pp` ON `_pp`.`parent_id`       = `_p1`.`id`
	INNER JOIN `auth_permissions`           `_p2` ON `_pp`.`child_id`        = `_p2`.`id`
	WHERE `_u`.`id` = `_user_id`
    AND `_p1`.`urlBackEnd` = `_urlParent`
	AND `_p2`.`urlBackEnd` = `_urlChild`
	AND `_u`.`deleted_at` IS NULL;
END IF;

-- . --
END
;


DROP procedure IF EXISTS `sp_save_permission_permission`;

CREATE PROCEDURE `sp_save_permission_permission`(
IN `_urlParent` varchar(255),
IN `_urlChild` varchar(255)
)
BEGIN
-- . --
DECLARE `_parent_id` bigint DEFAULT 0;
DECLARE `_child_id` bigint DEFAULT 0;

SELECT `p_1`.`id` into `_parent_id` FROM `auth_permissions` `p_1` where `p_1`.`urlBackEnd` = `_urlParent`;
SELECT `p_2`.`id` into `_child_id`  FROM `auth_permissions` `p_2` where `p_2`.`urlBackEnd` = `_urlChild`;

INSERT INTO `auth_permission_permission`
(`parent_id`, `child_id`) VALUES(`_parent_id`, `_child_id`)
ON DUPLICATE KEY UPDATE
`parent_id` = `_parent_id`, `child_id` = `_child_id`;

-- . --
END
;
