<?php

function wp_database_table() {
    global $wpdb;
    $table_name=$wpdb->prefix."wpds_display";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    //table not in database. Create new table for displays
    $charset_collate = $wpdb->get_charset_collate();
    //creating table for wpds_displays
    $sql = "CREATE TABLE `wpds_displays` (
 `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
 `location` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
 `mac` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
 `floormap` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
 `lat` int(11) NOT NULL DEFAULT '0',
 `lng` int(11) NOT NULL DEFAULT '0',
 `status` enum('active','disabled') COLLATE utf8_unicode_ci DEFAULT 'active',
 PRIMARY KEY (`id`)
) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    }


    $table_name=$wpdb->prefix."wpds_events";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      //table not in database. Create new table for events
      $charset_collate = $wpdb->get_charset_collate();
      //create table for wpds_events
      $sql = "CREATE TABLE `wpds_events` (
 `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
 `slider` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
 `time_from` datetime DEFAULT NULL,
 `time_to` datetime DEFAULT NULL,
 `displays` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
 `updated` int(5) NOT NULL DEFAULT '0',
 `status` enum('active','disabled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
 PRIMARY KEY (`id`)
) $charset_collate;";
     require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
     dbDelta( $sql );
    }


    $table_name=$wpdb->prefix."wpds_floormaps";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      //table not in database. Create new table for floormaps
      $charset_collate = $wpdb->get_charset_collate();
      //create table for wpds_floormaps
      $sql = "CREATE TABLE `wpds_floormaps` (
 `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
 `floormap` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
 `status` enum('active','disabled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
 PRIMARY KEY (`id`)
) $charset_collate;";
     require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
     dbDelta( $sql );
   }


   $table_name=$wpdb->prefix."wpds_group_displays";
   if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
     //table not in database. Create new table for group_displays
     $charset_collate = $wpdb->get_charset_collate();
     //create table for wpds_group_displays
     $sql = "CREATE TABLE `wpds_group_displays` (
 `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
 `group_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
 `location` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
 `display` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
 `status` enum('active','disabled') COLLATE utf8_unicode_ci DEFAULT 'active',
 PRIMARY KEY (`id`)
) $charset_collate;";
     require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
     dbDelta( $sql );
   }
}
register_activation_hook( __FILE__, 'wp_database_table' );
?>
