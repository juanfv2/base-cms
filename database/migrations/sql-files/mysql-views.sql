CREATE OR REPLACE VIEW `vw_auth_user_visor_list_file_version_logs` AS
    SELECT
        `l`.`visualizations` AS `visualizations`,
        `l`.`viewed_at` AS `viewed_at`,
        `u`.*
    FROM
        (`auth_users` `u`
        LEFT JOIN (SELECT
            COUNT(`ll`.`id`) AS `visualizations`,
                MAX(`ll`.`viewed_at`) AS `viewed_at`,
                `ll`.`user_id` AS `user_id`
        FROM
            `visor_list_file_version_logs` `ll`
        GROUP BY `ll`.`user_id`) `l` ON (`u`.`id` = `l`.`user_id`));
