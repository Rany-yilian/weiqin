<?php

/**
 *卸载模块
 *
 */
pdo_query("DROP TABLE IF EXISTS ".tablename('sudo_sudo_ku').";");
pdo_query("DROP TABLE IF EXISTS ".tablename('sudo_sudo_ku_prize').";");
pdo_query("DROP TABLE IF EXISTS ".tablename('sudo_sudo_ku_user').";");
pdo_query("DROP TABLE IF EXISTS ".tablename('sudo_sudo_ku_user_record').";");
pdo_query("DROP TABLE IF EXISTS ".tablename('sudo_sudo_ku_share').";");
pdo_query("DROP TABLE IF EXISTS ".tablename('sudo_sudo_ku_user_award').";");

?>