<?php

//--- Load JS and CSS files for the plugin
function wpds_enqueue_script() {
    wp_register_script('datetime-js', plugins_url('/assets/js/foundation-datepicker.js', plugin_dir_path(__FILE__)));
    wp_register_script('datetime-js-min', plugins_url('assets/js/foundation-datepicker.min.js', plugin_dir_path(__FILE__)));
    wp_enqueue_script('jquery');
    //- FOR DateTime drop down
    wp_enqueue_script('datetime-js');
    wp_enqueue_script('datetime-js-min');
    wp_register_style('datetime-css', plugins_url('/assets/css/foundation-datepicker.css', plugin_dir_path(__FILE__)));
    wp_enqueue_style('datetime-css');
}

add_action('admin_enqueue_scripts', 'wpds_enqueue_script');
//--- Load JS and CSS FILES -- END

if (!function_exists('is_user_logged_in')) :

    function is_user_logged_in() {
        $user = wp_get_current_user();
        if (empty($user->ID))
            return false;
        return true;
    }

endif;

/*
  Custom Menu Page for the Plugin
 */
add_action('admin_menu', 'wpds_settings_page');

function wpds_settings_page() {
    // Plugin Menu Pages
    add_menu_page('Digital Signage WordPress Plugin', 'Digital Signage', 'delete_others_pages', 'wpds_display', 'wpds_display');
    // Plugin Submenus
    add_submenu_page('wpds_display', 'Add a Display Device', 'Add Display', 'delete_others_pages', 'wpds_add_display', 'wpds_add_display');
    add_submenu_page('wpds_display', 'wpds_display', 'Display Groups Manager', 'delete_others_pages', 'wpds_group_display', 'wpds_group_display');
    add_submenu_page('wpds_display', 'Create a New Display Group', 'Create Display Group', 'delete_others_pages', 'wpds_add_group', 'wpds_add_group');
    add_submenu_page('wpds_display', 'Events Manager', 'Events Manager', 'delete_others_pages', 'wpds_events', 'wpds_events');
    add_submenu_page('wpds_display', 'Create a New Event', 'Add New Event', 'delete_others_pages', 'wpds_add_event', 'wpds_add_event');
    add_submenu_page('wpds_display', 'FloorMaps Managment', 'FloorMaps', 'delete_others_pages', 'wpds_floormaps', 'wpds_floormaps');
    add_submenu_page('wpds_display', 'Alerts', 'Alerts', 'delete_others_pages', 'wpds_alerts', 'wpds_alerts');
}

/*
 * Plugin Settings  Group
 */
add_action('admin_init', 'wpds_settings');
/*
* Setting function
*/
function wpds_settings() {
    register_setting('wpds_display_group', 'display_name');
    register_setting('wpds_display_group', 'display_location');
}

/*
 * Main Displays Manager page
 */

function display_main() {
    global $customFields;
    $customFields = "'wpds_displays'";
    global $current_user;

    if (!(isset($_POST['s'])) || empty($_POST['s']))
        $customPosts = new WP_Query();

    add_filter('posts_join', 'get_custom_field_posts_join');
    add_filter('posts_groupby', 'get_custom_field_posts_group');
    if (!(empty($_POST['s']))) {

        function filter_where($where = '') {

            $title = $_POST['s'];
            $where .= "AND post_title LIKE '%$title%' OR post_content LIKE '%$title%'";
            return $where;
        }

        add_filter('posts_where', 'filter_where');

        $customPosts = new WP_Query();
    }
    if ($current_user->caps['administrator'] == '1')
        $customPosts->query('posts_per_page=-1');
    else
        $customPosts->query('&author=' . $current_user->ID . '&posts_per_page=-1');
    remove_filter('posts_join', 'get_custom_field_posts_join');
    remove_filter('posts_groupby', 'get_custom_field_posts_group');
    $arr = array();
    $a = array();
    $i = 0;
    while ($customPosts->have_posts()) : $customPosts->the_post(); // Inserting values in table
        $arr['ID'] = get_the_ID();

        //-------------------Getting Display Name -----------------------------------------

        $arr['status'] = get_post_status(get_the_ID());
        $arr['author'] = get_the_author();

        $a[$i] = $arr;
        $sts = get_post_status(get_the_ID());



        $i = ($i + 1);
    endwhile;
    include_once (plugin_dir_path(__FILE__) . 'list_table.php');       //  table head to display list of videos
    wp_reset_query();
}


function get_custom_field_posts_join($join) {

    global $wpdb, $customFields;
    return $join . "  JOIN $wpdb->postmeta postmeta ON (postmeta.post_id = $wpdb->posts.ID and postmeta.meta_key in ($customFields)) ";
}

function get_custom_field_posts_group($group) {

    global $wpdb;
    $group .= " $wpdb->posts.ID ";
    return $group;
}

/*
 *
 *  Get the Display names from the array of Display IDs
 *
 */

function wpds_get_display_name($id_array) {
    global $wpdb;
    $table_name = 'wpds_displays';
    $table_name_gr = 'wpds_group_displays';
    $i = 0;
    foreach ($id_array as $id) {
        if (strstr($id, 'gr_') != FALSE) {
            $id = substr($id, 3);
            // Selecting group name from the table wpds_group_displays
            $get_gr_name = $wpdb->get_results("SELECT group_name FROM $table_name_gr WHERE id = '$id'");
            $display_names[$i] = $get_gr_name[0]->group_name . ' (Group)';
        } else {
            // Selecting display name from the table wpds displays
            $get_name = $wpdb->get_results("SELECT name FROM $table_name WHERE id = '$id'");
            $display_names[$i] = $get_name[0]->name;
        }
        $i++;
    }
    return $display_names;
}
function wpds_get_display_id($id_array) {
    global $wpdb;
    $table_name = 'wpds_displays';
    $table_name_gr = 'wpds_group_displays';
    $i = 0;
    foreach ($id_array as $id) {
        if (strstr($id, 'gr_') != FALSE) {
            $id = substr($id, 3);
            // Selecting group name from the table wpds_group_displays
            $get_gr_id = $wpdb->get_results("SELECT id FROM $table_name_gr WHERE id = '$id'");
            $display_id[$i] = $get_gr_id[0]->id . ' (Group)';
        } else {
            // Selecting display name from the table wpds displays
            $get_id = $wpdb->get_results("SELECT id FROM $table_name WHERE id = '$id'");
            $display_id[$i] = $get_id[0]->id;
        }
        $i++;
    }
    return $display_id;
}
