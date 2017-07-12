<?php
/**
 * Plugin Name: WordPress Digital Signage
 * Plugin URI: http://avuity.com
 * Description: This plugin is a complete WordPress digital signage solution, which offers creation of playlists, ability to add devices and much more
 * Version: 0.0.1
 * Author:  Vikrant Datta
 * Author URI: http://baseapp.com
 * License: GPL2
 */
 
 function myplugin_activate() {
     global $wpdb;
     $table_name=$wpdb->prefix."wpds_display";
     if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
     //table not in database. Create new table
     $charset_collate = $wpdb->get_charset_collate();

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
       $charset_collate = $wpdb->get_charset_collate();

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
       $charset_collate = $wpdb->get_charset_collate();

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


    $table_name=$wpdb->prefix."wpds_floormaps";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      $charset_collate = $wpdb->get_charset_collate();

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
register_activation_hook( __FILE__, 'myplugin_activate' );
//add_action('activated_plugin','myplugin_activate')
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function myscript_jquery() {
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_head' , 'myscript_jquery' );

/**
 * Endpoints for WPDS
 *
 * @class Wpds_Endpoints
 */
class Wpds_Endpoints {

    /**
     * Hook in methods.
     */
    public static function init() {
        //    add_action('init', array(__CLASS__, 'add_endpoint_rule'));
        add_action('parse_request', array(__CLASS__, 'handle_endpoint'));
        //    add_filter('query_vars', array(__CLASS__, 'add_query_vars'), 0);
        add_action('wpds_action_display', array(__CLASS__, 'get_display'));
        //      add_action( 'homeatik_action_logout', array( __CLASS__, 'logout') );
    }

    /**
     * Add endpoints
     */
    public static function add_endpoint_rule() {
        add_rewrite_endpoint('wpds_display', EP_ALL);
    }

    /**
     * Add query variables
     */
    public static function add_query_vars($vars) {
        $vars[] = 'wpds_display';
        return $vars;
    }

    /**
     * Handle  endpoints
     */
    public static function handle_endpoint() {
        global $wp;
        if (!empty($_GET['uid'])) {
            $wp->query_vars['wpds_display'] = $_GET['uid'];
            do_action('wpds_action_display');
            die();
        }
        if (!empty($wp->query_vars['wpds_display'])) {
            //$wp->query_vars['wpds_display'];

            do_action('wpds_action_display');
            die();
        }
    }

    /**
     * Get the Event from the Display ID.
     */
    public static function get_display() {
        global $wpdb, $wp;
        $display_mac = $wp->query_vars['wpds_display'];
        $query = "select * from wpds_displays where mac = '$display_mac' AND status='active'";
        $result = $wpdb->get_results($query);
        //------FOUND DISPLAY
        if (count($result) > 0) {
            $display_id = $result[0]->id;
            $group_query = $wpdb->get_results("select id from wpds_group_displays where display LIKE '%\"$display_id\"%' AND status='active'", ARRAY_A);
            /*
             * Check if display is in a group
             */
            if (count($group_query) > 0) {
                $get_group_id = $group_query[0]['id'];
                $group_id = "gr_$get_group_id";
                $in_group = TRUE;
            } else {
                $in_group = FALSE;
            }
            /*
             * Get Slider for the Event with the required  Display or Group. Get the first matched event with the group or display, which is active in the current time period.
             */
            if ($in_group) {
                foreach ($group_query as $group_check) {
                    $gr_id = "gr_" . $group_check['id'];
                    $event_query = $wpdb->get_results("select * from wpds_events where ((curtime() > time_from AND curtime() < time_to) OR (time_from='0000-00-00 00:00:00' AND time_to='0000-00-00 00:00:00')) AND (displays LIKE '%\"$display_id\"%' OR displays LIKE '%\"$gr_id\"%') AND status='active'");
                    if ($event_query != NULL)
                        break;
                }
            } else {
                $event_query = $wpdb->get_results("select * from wpds_events where displays LIKE '%\"$display_id\"%'");

            }
            $slider_id = $event_query[0]->slider;
            $get_slider_alias = $wpdb->get_results("select alias from " . $wpdb->prefix . "revslider_sliders where id = '$slider_id'");
            $slider_alias = $get_slider_alias[0]->alias;
            $event_status = $event_query[0]->updated;
            $event_id = $event_query[0]->id;

            if ($event_status == '')
                $error = "Event Status Not found";
        }
        // Send JSON to the display with the slider alias and timestamp
        $url = site_url("api-get-slider/?slider=$slider_alias&flag=$event_status");

        //--- Flag back to 0 if event is updated
        if ($event_status){
            $wpdb->update('wpds_events', array('updated' => '0'), array('id' => $event_id));
            wp_schedule_single_event( time(), 'cache_event', array($url, $slider_alias) );
        }

        wp_send_json([
            'status' => 'true',
            'url' => $url,
            'error' => $error,
            'cache_url' => "http://avuitycms.com/cache-$slider_alias.zip"
        ]);

        //$wpdb->update('wpds_events', array('updated'=> '0'), array('id' => $event_id));
    }

}

add_action('cache_event' , 'create_cache_zip','10','2');
function create_cache_zip($url,$slider_alias){
  $path = plugin_dir_url(__FILE__);
  exec("/usr/bin/php ".$path."cache.php '".$url."' ".$slider_alias);
}

$api = new Wpds_Endpoints();
$api->init();

include_once (dirname(__FILE__) . '/controller/functions.php');
include_once (dirname(__FILE__) . '/view/views.php');
if (isset($_GET['page'])) {
    if ($_GET['page'] == 'wpds_display') {
      // deleting selected display
      if(isset($_GET['del_display']) && $_GET['del_display'] != '') {
        global $wpdb;
        $table_name = "wpds_displays";
        $del = $_GET['del_display'];
        $i=0;
        $wpdb->delete($table_name, array('id' => $_GET['del_display']));

        // deleting display from the corresponding groups
        $results = $wpdb->get_results("SELECT display FROM wpds_group_displays");
        while($results[$i]->display !='') {
          //echo $results[$i]->display;
          $del = $_GET['del_display'];
        $result=unserialize($results[$i]->display);
        if (($del = array_search($del, $result)) !== false) {
        $del1 = $result[$del];
        unset($result[$del]);
      }
      $results[$i] = serialize($result);
      $wpdb->query("UPDATE wpds_group_displays SET display = '$results[$i]' WHERE display LIKE '%\"$del1\"%' LIMIT 1");
      $i=$i+1;
    }
      // deleting display from the corresponding events
      $i=0;
      $del = $_GET['del_display'];
      $results =  $wpdb->get_results("SELECT displays FROM wpds_events");
      while($results[$i]->displays !='') {
        $del = $_GET['del_display'];
      $result=unserialize($results[$i]->displays);
      if (($del = array_search($del, $result)) !== false) {
      $del1 = $result[$del];
      unset($result[$del]);
    }
    $results[$i] = serialize($result);
    $wpdb->query("UPDATE wpds_events SET displays = '$results[$i]' WHERE displays LIKE '%\"$del1\"%' LIMIT 1");
    $i++;
  }
  }
}
//-- NEW DISPLAY form submitted
    else if ($_GET['page'] == 'wpds_add_display') {
        // Check if Form submited for new display and editted display
        if ($_POST['submit'] == 'Add Display' || $_POST['submit'] == 'Edit Display') {
            if ($_POST['display_name'] == '' || $_POST['display_mac'] == '' || $_POST['display_floormap'] == '') {

                function admin_notice_fail() {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e('Error ! Display not added. Please fill all the fields of the form!', 'sample-text-domain'); ?></p>
                    </div>
                    <?php
                }

                add_action('admin_notices', 'admin_notice_fail');
            } else {
                global $wpdb;
                $table_name = "wpds_displays";
                $name = $_POST['display_name'];
                $location = $_POST['display_location'];
                $mac = $_POST['display_mac'];
                $status = $_POST['display_status'];
                $floormap = $_POST['display_floormap'];
                $lat = $_POST['display_lat'];
                $lng = $_POST['display_lng'];

                // ---- CHeck if display is being editted ----
                if (isset($_GET['edit_display']) && $_GET['edit_display'] != '') {
                    $wpdb->update($table_name, array('name' => $name, 'location' => $location, 'floormap'=> $floormap,'lat' => $lat,'lng' => $lng, 'mac' => $mac, 'status' => $status,), array('id' => $_GET['edit_display']));
                    //Update Event attached to this display
                    $id = $_GET['edit_display'];
                    $group_query = $wpdb->get_results("select id from wpds_group_displays where display LIKE '%\"$id\"%'");
                    $id_array = array();
                    $id_array[0] = $id;
                    $i = 1;
                    foreach ($group_query as $group) {
                        $id_array[$i] = "gr_" . $group->id;
                        $i++;
                    }
                    foreach ($id_array as $id) {
                        $wpdb->query("update wpds_events SET updated = '1' WHERE displays LIKE '%$id%'");
                    }
                } else {
                    $wpdb->insert($table_name, array('name' => $name, 'location' => $location, 'floormap'=> $floormap,'lat' => $lat,'lng' => $lng, 'mac' => $mac, 'status' => $status));
                }

            function admin_notice_success() {
                ?>
                <?php
                $page = $_GET['page'];
                $check_page = substr($page, 0, 8);
                if (($check_page == 'wpds_add' || $check_page =='wpds_flo') && isset($_POST['submit'])) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p>
                            <?php
                            if($check_page == 'wpds_flo')
                                $page_type = 'FloorMaps';
                            else
                                $page_type = ucwords(substr($page, 9));
                            if ((isset($_GET['edit_display']) && $_GET['edit_display'] != '') || (isset($_GET['edit_group']) && $_GET['edit_group'] != '') || (isset($_GET['edit_event']) && $_GET['edit_event'] != '')) {
                                _e("$page_type editted succesfully!", 'sample-text-domain');
                            } else {
                                _e("$page_type added succesfully!", 'sample-text-domain');
                            }
                            ?></p>
                    </div>
                    <?php
                }
            }
          }
            add_action('admin_notices', 'admin_notice_success');
        }
    }
    else if ($_GET['page'] == 'wpds_group_display') {
      if(isset($_GET['del_group']) && $_GET['del_group'] != '') {
        global $wpdb;
        $table_name = "wpds_group_displays";
        $del = "gr_" . $_GET['del_group'];
        $wpdb->delete($table_name, array('id' => $_GET['del_group']));

        //deleting groups from corresponding events
        $results = $wpdb->get_results("SELECT displays FROM wpds_events");
        $i=0;
        while($results[$i]->displays !='') {
        $del = "gr_" . $_GET['del_group'];
        $result=unserialize($results[$i]->displays);
        if (($del = array_search($del, $result)) !== false) {
        $del1 = $result[$del];
        unset($result[$del]);
      }
      $results[$i] = serialize($result);
      $wpdb->query("UPDATE wpds_events SET displays = '$results[$i]' WHERE displays LIKE '%\"$del1\"%' LIMIT 1");
      $i++;
    }
  }
}
//--- NEW / EDIT GROUP form submitted
    else if ($_GET['page'] == 'wpds_add_group') {
        // Check if Form submited for new display and editted display
        if ($_POST['submit'] == 'Create Group' || $_POST['submit'] == 'Edit Group') {
            if ($_POST['group_name'] == '') {

                function admin_notice_fail() {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e('Error ! Group not created. Please fill all the fields of the form!', 'sample-text-domain'); ?></p>
                    </div>
                    <?php
                }

                add_action('admin_notices', 'admin_notice_fail');
            } else {
                global $wpdb;
                $table_name = "wpds_group_displays";
                $name = $_POST['group_name'];
                $location = $_POST['group_location'];
                $displays = serialize($_POST['displays_selected']);
                $status = $_POST['display_status'];
                // ---- CHeck if group is being editted ----
                if (isset($_GET['edit_group']) && $_GET['edit_group'] != '') {
                    $wpdb->update($table_name, array('group_name' => $name, 'location' => $location, 'display' => $displays, 'status' => $status), array('id' => $_GET['edit_group']));
                    //Update Event attached to the Group
                    $id = "gr_" . $_GET['edit_group'];
                    $wpdb->query("update wpds_events SET updated = '1' WHERE displays LIKE '%$id%'");
                } else {
                    $wpdb->insert($table_name, array('group_name' => $name, 'location' => $location, 'display' => $displays, 'status' => $status));
                }
                function admin_notice_success() {
                    ?>
                    <?php
                    $page = $_GET['page'];
                    $check_page = substr($page, 0, 8);
                    if (($check_page == 'wpds_add' || $check_page =='wpds_flo') && isset($_POST['submit'])) {
                        ?>
                        <div class="notice notice-success is-dismissible">
                            <p>
                                <?php
                                if($check_page == 'wpds_flo')
                                    $page_type = 'FloorMaps';
                                else
                                    $page_type = ucwords(substr($page, 9));
                                if ((isset($_GET['edit_display']) && $_GET['edit_display'] != '') || (isset($_GET['edit_group']) && $_GET['edit_group'] != '') || (isset($_GET['edit_event']) && $_GET['edit_event'] != '')) {
                                    _e("$page_type editted succesfully!", 'sample-text-domain');
                                } else {
                                    _e("$page_type added succesfully!", 'sample-text-domain');
                                }
                                ?></p>
                        </div>
                        <?php
                    }
                }
              }
                add_action('admin_notices', 'admin_notice_success');
            }

    }
    else if ($_GET['page'] == 'wpds_events') {
      if(isset($_GET['del_event']) && $_GET['del_event'] != '') {
        global $wpdb;
        $table_name = "wpds_events";
        $wpdb->delete($table_name, array('id' => $_GET['del_event']));
      }

    }
    else if ($_GET['page'] == 'wpds_add_event') {
        if ($_POST['submit'] == 'Create Event' || $_POST['submit'] == 'Edit Event') {

            if ($_POST['event_name'] == '' || !(isset($_POST['displays_selected']))) {

                function admin_notice_fail() {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e('Error ! Please fill all the fields of the form!', 'sample-text-domain'); ?></p>
                    </div>
                    <?php
                }

                add_action('admin_notices', 'admin_notice_fail');
            } else {
                global $wpdb;
                $table_name = 'wpds_events';
                $name = $_POST['event_name'];
                $event_slider = $_POST['event_slider'];
                $displays = serialize($_POST['displays_selected']);
                $status = $_POST['event_status'];
                if (isset($_POST['time_always'])) {
                    $time_to = "0000-00-00 00:00:00";
                    $time_from = "0000-00-00 00:00:00";
                } else if (isset($_POST['time_from']) && isset($_POST['time_to'])) {
                    $time_from = $_POST['time_from'];
                    $time_to = $_POST['time_to'];
                }
                // ---- CHeck if event is being editted ----
                if (isset($_GET['edit_event']) && $_GET['edit_event'] != '') {
                    $wpdb->update($table_name, array('name' => $name, 'slider' => $event_slider, 'time_from' => $time_from, 'time_to' => $time_to, 'displays' => $displays, 'status' => $status, 'updated' => '1'), array('id' => $_GET['edit_event']));
                } else {
                    $wpdb->insert($table_name, array('name' => $name, 'slider' => $event_slider, 'time_from' => $time_from, 'time_to' => $time_to, 'displays' => $displays, 'status' => $status));
                }
                // ----- Confirmation messages ----
                function admin_notice_success() {
                    ?>
                    <?php
                    $page = $_GET['page'];
                    $check_page = substr($page, 0, 8);
                    if (($check_page == 'wpds_add' || $check_page =='wpds_flo') && isset($_POST['submit'])) {
                        ?>
                        <div class="notice notice-success is-dismissible">
                            <p>
                                <?php
                                if($check_page == 'wpds_flo')
                                    $page_type = 'FloorMaps';
                                else
                                    $page_type = ucwords(substr($page, 9));
                                if ((isset($_GET['edit_display']) && $_GET['edit_display'] != '') || (isset($_GET['edit_group']) && $_GET['edit_group'] != '') || (isset($_GET['edit_event']) && $_GET['edit_event'] != '')) {
                                    _e("$page_type editted succesfully!", 'sample-text-domain');
                                } else {
                                    _e("$page_type added succesfully!", 'sample-text-domain');
                                }
                                ?></p>
                        </div>
                        <?php
                    }
                }
              }
                add_action('admin_notices', 'admin_notice_success');
            }

    }
    //else if ($_GET['page'] == 'wpds_floormaps') {




    //}
    else if ($_GET['page'] == 'wpds_floormaps') {

      if(isset($_GET['del_floormap']) && $_GET['del_floormap'] != '') {
        global $wpdb;
        $table_name = "wpds_floormaps";
        $del = $_GET['del_floormap'];

        $name = $wpdb->get_results("SELECT floormap FROM wpds_floormaps WHERE id = $del");
        $a=$name[0]->floormap;

        $wpdb->delete($table_name, array('id' => $_GET['del_floormap']));

        $results = $wpdb->query("UPDATE wpds_displays SET floormap = '0' WHERE floormap LIKE \"$a\"");
      }
      /*if($_GET['new_fm'] == 'true' && $_GET['floormap_displays'] != '') {
        global $wpdb;
        $name = $_GET['floormap_displays'];



        //echo "SELECT wpds_displays.name AS name FROM wpds_displays INNER JOIN wpds_floormaps ON wpds_displays.floormap = wpds_floormaps.floormap WHERE wpds_floormaps.id = $name";
         $result = $wpdb->get_results("SELECT wpds_displays.name AS name FROM wpds_displays INNER JOIN wpds_floormaps ON wpds_displays.floormap = wpds_floormaps.floormap WHERE wpds_floormaps.id = \"$name\"");
         //echo "$result";
         $i=0;
         while($result[$i] != '') {
           $a=$result[$i]->name;
           ?>
           <div class ="wrap">
             <h2>Displays Added on the FloorMap</h2>
             <form method="post" action="">
             <table class="form-table">
                 <tr valign="top">
                     <th scope="row">
                         <label for="num_elements">
                             <?php echo ".................";
                             echo $a;
                                $i++;
                              ?>
                         </label>
                       </th>
                     </tr>
                   </table>
                 </form>
                </div>
         <?php
         }
      }*/

        if ($_GET['new_fm'] == 'true' && ($_POST['submit'] == 'Add FloorMap' || $_POST['submit'] == 'Edit FloorMap')) {

            $table_name = 'wpds_floormaps';
            $name = $_POST['name'];
            $floormap = $_POST['floormap'];
            $status = $_POST['status'];
            $create = true;
            //var_dump($_FILES);
            //var_dump($_POST);
            if ($_FILES['floormap']['tmp_name'] != '') {
                $upload_dir = wp_upload_dir();
                move_uploaded_file($_FILES['floormap']['tmp_name'], $upload_dir['basedir'] . '/fm-' . $_FILES['floormap']['name']);
                $fm_name = $_FILES['floormap']['name'];
            } else{
                if($_POST['submit'] == 'Add FloorMap'){
                     function admin_notice_fail() { ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e('Error ! FloorMap not created. Please fill all the fields of the form!', 'sample-text-domain'); ?></p>
                    </div>
                    <?php }
                add_action('admin_notices', 'admin_notice_fail');
                $create = false;
                }
            }
            global $wpdb;
            if (isset($_GET['edit_floormap']) && $_GET['edit_floormap'] != '') {
                $fm_id = $_GET['edit_floormap'];

                 if ($_FILES['floormap']['name'] == '')
                     $wpdb->update($table_name, array('name' => $name, 'status' => $status), array('id' => $fm_id));
                 else
                    $wpdb->update($table_name, array('name' => $name, 'floormap' => $fm_name, 'status' => $status), array('id' => $fm_id));
            } else if($create=='true'){
                $fm_name = $_FILES['floormap']['name'];
                sleep(3);
                $wpdb->insert($table_name, array('name' => $name, 'floormap' => $fm_name, 'status' => $status));
            }
            function admin_notice_success() {
                ?>
                <?php
                $page = $_GET['page'];
                $check_page = substr($page, 0, 8);
                if (($check_page == 'wpds_add' || $check_page =='wpds_flo') && isset($_POST['submit'])) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p>
                            <?php
                            if($check_page == 'wpds_flo')
                                $page_type = 'FloorMaps';
                            else
                                $page_type = ucwords(substr($page, 9));
                            if ((isset($_GET['edit_display']) && $_GET['edit_display'] != '') || (isset($_GET['edit_group']) && $_GET['edit_group'] != '') || (isset($_GET['edit_event']) && $_GET['edit_event'] != '')) {
                                _e("$page_type editted succesfully!", 'sample-text-domain');
                            } else {
                                _e("$page_type added succesfully!", 'sample-text-domain');
                            }
                            ?></p>
                    </div>
                    <?php
                }
            }
          }
            add_action('admin_notices', 'admin_notice_success');
    }
}

// ----- Confirmation messages for new or edit forms submission ----
/*function admin_notice_success() {
    ?>
    <?php
    $page = $_GET['page'];
    $check_page = substr($page, 0, 8);
    if (($check_page == 'wpds_add' || $check_page =='wpds_flo') && isset($_POST['submit'])) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                if($check_page == 'wpds_flo')
                    $page_type = 'FloorMaps';
                else
                    $page_type = ucwords(substr($page, 9));
                if ((isset($_GET['edit_display']) && $_GET['edit_display'] != '') || (isset($_GET['edit_group']) && $_GET['edit_group'] != '') || (isset($_GET['edit_event']) && $_GET['edit_event'] != '')) {
                    _e("$page_type editted succesfully!", 'sample-text-domain');
                } else {
                    _e("$page_type added succesfully!", 'sample-text-domain');
                }
                ?></p>
        </div>
        <?php
    }
}

add_action('admin_notices', 'admin_notice_success');*/
// ----- Confirmation messages ---- END
//----- site_url required for Floormap display
//if(!(isset($_GET['uid']))){
if(!(isset($_GET['uid'])) && $_GET['page']=='wpds_add_display'  ){  ?>
<script type="text/javascript">
    var site_url = '<?php echo get_site_url(); ?>';
</script>

<?php } ?>
