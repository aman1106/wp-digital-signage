<?php
/*
 *  Manage and Add FloorMaps
 */

function wpds_floormaps() {
    global $wpdb;
    global $customFields;
    global $current_user;
    if (isset($_GET['edit_floormap']) && $_GET['edit_floormap'] != '0') {
        global $wpdb;
        $table_name = "wpds_floormaps";
        $id = $_GET['edit_floormap'];
        $edit_data = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$id");
    }
    if ((isset($_GET['new_fm'])) && $_GET['new_fm'] && !(isset($_GET['floormap_displays']))) {
      //add new floormap
        ?>
        <div class="wrap">
            <h2>Add New FloorMap</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <?php settings_fields('wpds_floormaps'); ?>
                <?php do_settings_sections('wpds_floormaps'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Floormap Name</th>
                        <td><input type="text" name="name"  value="<?php echo $edit_data[0]->name ?>"/></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Floormap</th>
                        <td><input type="file" name="floormap" accept="image/*"/>
                            <?php
                            $upload_dir = wp_upload_dir();
                            ?><br /><br /> Current Floor Map :  <?php if (isset($edit_data[0]->floormap)) { ?>
                                <br /><img src="<?php echo get_site_url(); ?>/wp-content/uploads/fm-<?php echo $edit_data[0]->floormap; ?>" width="250px"/>
                            <?php } else { ?>
                                No Image Uploaded currently
                            <?php } ?>
                        </td>

                    </tr>

                    <tr valign="top">
                        <th scope="row">Status</th>
                        <td>
                            <?php if (isset($_GET['edit_floormap'])) { ?>
                                <select name="status"><option value="active" <?php if ($edit_data[0]->status == 'active') echo 'selected'; ?> >Active</option><option value="disabled" <?php if ($edit_data[0]->status == 'disabled') echo 'selected'; ?>>Disabled</option></select>
                            <?php } else { ?>
                                <select name="status"><option value="active" selected>Active</option><option value="disabled">Disabled</option></select>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
                <?php
                if (isset($_GET['edit_floormap'])) {
                    submit_button("Edit FloorMap");
                } else {
                    submit_button("Add FloorMap");
                }
                ?>

            </form>
        </div> <?php
    } else if (isset($_GET['floormap_displays']) && $_GET['floormap_displays'] != '0') {
        if (isset($_GET['floormap_displays']) && $_GET['floormap_displays'] != '0') {
            global $wpdb;
            $table_name = "wpds_floormaps";
            $id = $_GET['floormap_displays'];
            $edit_data = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$id");
            $fmap = $edit_data[0]->floormap;
            $get_displays = $wpdb->get_results("SELECT * FROM wpds_displays WHERE floormap='$fmap';");
            ?>
            <div class="wrap">

                <table class="from-table">
                  <tr valign="top">
                    <?php

                    require_once(ABSPATH . 'wp-content/plugins/wp-digital-signage/view/display_floormap.php'); ?>


                <form method="post" action="">
                    <table class="form-table">
                        <tr valign="top">
                        <input type="hidden" class="fm_add_dis" value="<?php echo $edit_data[0]->floormap; ?>"/>

                        <td>
                            <div id="floormap_dis" class="fm_add_div" style=""></div>
                            <script type="text/javascript">
                                var val = jQuery('.fm_add_dis').val();
                                jQuery(".leaflet-image-layer").attr('src', site_url + '/wp-content/uploads/fm-' + val);
                                jQuery("#floormap_dis").show();
                               // jQuery('.fm_add_div').show();
                                var map = L.map('floormap_dis', {
                                    crs: L.CRS.Simple,
                                    attributionControl: false,
                                    maxBounds: new L.LatLngBounds([0, 0], [height / scale, width / scale])
                                });
                                var imageUrl = site_url + '/wp-content/uploads/fm-' + val;
                                L.imageOverlay(imageUrl, new L.LatLngBounds([0, 0], [height / scale, width / scale]), {noWrap: true, maxZoom: 3, minZoom: 0}).addTo(map);
                                map.setView(new L.LatLng(height / scale / 2, width / scale / 2), 0);
                                markerURL = site_url + '/wp-content/plugins/wp-digital-signage/images/marker-';
                                var blue = L.icon({
                                    iconUrl: markerURL + 'icon.png',
                                    iconRetinaUrl: markerURL + 'icon-2x.png',
                                    iconSize: [24, 41],
                                    iconAnchor: [12, 40],
                                    popupAnchor: [-3, -20],
                                    shadowUrl: markerURL + 'shadow.png',
                                    shadowRetinaUrl: markerURL + 'shadow.png',
                                    shadowSize: [41, 41],
                                    shadowAnchor: [14, 41]
                                });
            <?php
            foreach ($get_displays as $display) {
                $lat_dis = $display->lat;
                $lng_dis = $display->lng;
                $name_dis = $display->name;
                ?>
                                    var marker = L.marker([<?php echo $lat_dis; ?>, <?php echo $lng_dis; ?>], {icon: blue}).addTo(map);
                                    var markerT = L.marker([<?php echo $lat_dis; ?>, <?php echo $lng_dis; ?>], {icon: L.divIcon({className: 'count-icon', html: "<div class='mtexts'><?php echo $name_dis; ?> </div>", iconSize: [1, 1]})}).addTo(map);
                <?php
            }
            ?></script>
                        </td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
        }
    } else {
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
// Get Floormaps from DB
        $table_name = "wpds_floormaps";
        $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name");
// Prepare array to show all the diplays
        foreach ($retrieve_data as $data) {
            $a[$i]['id'] = $data->id;
            $a[$i]['name'] = $data->name;
            $a[$i]['floopmap'] = $data->floormap;
            $fmap = $data->floormap;
            $displays = $wpdb->get_var("SELECT COUNT(*) FROM wpds_displays WHERE floormap='$fmap';");
            $a[$i]['displays'] = $displays;
            $a[$i]['status'] = $data->status;
            $i++;
        }
        if($_GET['page']=='wpds_floormaps' && $_GET['new_fm']=='true' && $_GET['floormap_displays']!='0' ) {
          require_once(ABSPATH . 'wp-content/plugins/wp-digital-signage/display_floormap.php');
        }
        include_once (plugin_dir_path(__FILE__) . 'list_floormap.php');       //  table head to display list of videos
        wp_reset_query();
    }
}

/*
  Menu page add new and edit display
 */

function wpds_add_display() {
    if (isset($_GET['edit_display']) && $_GET['edit_display'] != '0') {
        global $wpdb;
        $table_name = "wpds_displays";
        $id = $_GET['edit_display'];
        $edit_data = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$id");
    }
    ?>
    <div class="wrap">
        <h2>Add New Display</h2>

        <form method="post" action="">
            <?php settings_fields('wpds_display_group'); ?>
            <?php do_settings_sections('wpds_display_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Display Name</th>
                    <td><input type="text" id="display_name" name="display_name"  value="<?php echo $edit_data[0]->name ?>"/></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Display Location</th>
                    <td><input type="text" name="display_location" value="<?php echo $edit_data[0]->location ?>"/></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Display Mac</th>
                    <td><input type="text" name="display_mac" value="<?php echo $edit_data[0]->mac ?>" /></td>
                </tr>

                <?php
                if (isset($_GET['edit_display'])) {
                    ?> <input type="hidden"  id="edit_flag" value="1" /> <?php
                }
                ?>

                <tr valign="top">
                    <th scope="row">Display FloorMap</th>
                    <td><select name="display_floormap" class="fm_add" value="<?php echo $edit_data[0]->floormap ?>">
                            <?php
                            global $wpdb;
                            // Get FloorPlans from DB
                            $i_fp = 0;
                            $table_name = "wpds_floormaps";
                            $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name where status='active'");
                            // Prepare array to hold all the floorplans
                            foreach ($retrieve_data as $data) {
                                $fp_array[$i_fp]['id'] = $data->id;
                                $fp_array[$i_fp]['name'] = $data->name;
                                $fp_array[$i_fp]['floormap'] = $data->floormap;
                                $i_fp++;
                            }
                            ?><option value="0">None</option>
                            <?php
                            foreach ($fp_array as $fp) {
                                if (isset($_GET['edit_display'])) {
                                    if ($fp['floormap'] == $edit_data[0]->floormap) {
                                        ?><option value="<?php echo $fp['floormap']; ?>" selected=""><?php echo $fp['name']; ?> </option><?php
                                    } else {
                                        ?><option value="<?php echo $fp['floormap']; ?>"><?php echo $fp['name']; ?> </option> <?php
                                    }
                                } else {
                                    ?>
                                    <option value="<?php echo $fp['floormap']; ?>"><?php echo $fp['name']; ?> </option>
                                    <?php
                                }
                            }
                            ?></select>
                        <br /><div id="floormap" class="fm_add_div" style="<?php if (!isset($_GET['edit_display'])) { ?>display: none;<?php } ?>"><img id="floormap1" class="fm_img"  style="display: none; max-width: 800px;" /></div>

                    </td>
                </tr>
                <input type="hidden" name="display_lat" id="lat" value="<?php echo $edit_data[0]->lat ?>" />
                <input type="hidden" name="display_lng" id="lng" value="<?php echo $edit_data[0]->lng ?>" />


                <tr valign="top">
                    <th scope="row">Display Status</th>
                    <td>
                        <?php if (isset($_GET['edit_display'])) { ?>
                            <select name="display_status"><option value="active" <?php if ($edit_data[0]->status == 'active') echo 'selected'; ?> >Active</option><option value="disabled" <?php if ($edit_data[0]->status == 'disabled') echo 'selected'; ?>>Disabled</option></select>
                        <?php } else { ?>
                            <select name="display_status"><option value="active" selected>Active</option><option value="disabled">Disabled</option></select>
                        <?php } ?>
                    </td>
                </tr>
            </table>
            <?php
            if (isset($_GET['edit_display'])) {
                submit_button("Edit Display");
            } else {
                submit_button("Add Display");
            }
            ?>

        </form>
    </div> <?php
}

/*
  Menu page add new display
 */

/*
 * Display Manager View
 */

function wpds_display() {
    global $wpdb;
    global $customFields;
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
    // Get Displays from DB
    $table_name = "wpds_displays";
    $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name");
    // Prepare array to show all the diplays
    foreach ($retrieve_data as $data) {
        $a[$i]['id'] = $data->id;
        $a[$i]['name'] = $data->name;
        $a[$i]['location'] = $data->location;
        $a[$i]['mac'] = $data->mac;
        $a[$i]['status'] = $data->status;
        $i++;
    }
    include_once (plugin_dir_path(__FILE__) . 'list_table.php');       //  table head to display list of videos
    wp_reset_query();
}

/*
 * Display Manager View END
 */

/*
 * Display Groups Manager View
 */

function wpds_group_display() {
    global $wpdb;
    global $customFields;
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
    // Get Displays from DB
    $table_name = "wpds_group_displays";
    $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name");
    // Prepare array to show all the diplays
    foreach ($retrieve_data as $data) {
        $a[$i]['id'] = $data->id;
        $a[$i]['group_name'] = $data->group_name;
        $a[$i]['location'] = $data->location;
        $serialize_check = @unserialize($data->display);
        if ($serialize_check !== false) {
            $unserialize_display = unserialize($data->display);
            $display_name_array = wpds_get_display_name($unserialize_display);
            $a[$i]['displays'] = implode(',', $display_name_array);
        } else {
            $a[$i]['displays'] = $data->display;
        }
        $a[$i]['status'] = $data->status;
        $i++;
    }
    include_once (plugin_dir_path(__FILE__) . 'list_group_table.php');       //  table head to display list of videos
    wp_reset_query();
}

/*
 * Display Groups Manager View END
 */

/*
  Menu page add new  / edit display GROUP
 */

function wpds_add_group() {
    global $wpdb;
    //-- Get the Display list
    $table = 'wpds_displays';
    $get_tables = $wpdb->get_results("SELECT * FROM $table WHERE status='active'");
    $displays = array();
    $i = 0;
    foreach ($get_tables as $display) {
        $displays[$i]['id'] = $display->id;
        $displays[$i]['name'] = $display->name;
        $i++;
    }
    //--------
    if (isset($_GET['edit_group']) && $_GET['edit_group'] != '0') {
        $table_name = "wpds_group_displays";
        $id = $_GET['edit_group'];
        $edit_data = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$id");
        $display_ids = unserialize($edit_data[0]->display);
    }
    ?>
    <div class="wrap">
        <h2>Create New Display Group</h2>

        <form method="post" action="">
            <?php settings_fields('wpds_display_group'); ?>
            <?php do_settings_sections('wpds_display_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Group Name</th>
                    <td><input type="text" name="group_name"  value="<?php echo $edit_data[0]->group_name ?>"/></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Group Location</th>
                    <td><input type="text" name="group_location" value="<?php echo $edit_data[0]->location ?>"/></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Displays</th>
                    <td>
                        <?php foreach ($displays as $display) { ?>
                            <input type='checkbox' name='displays_selected[]' value='<?php echo $display['id']; ?>' <?php
                            if (isset($_GET['edit_group'])) {
                                if ((array_search($display['id'], $display_ids) !== false))
                                    echo "checked";
                            }
                            ?>/><?php echo $display['name']; ?><br>
                               <?php } ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Group Status</th>
                    <td>
                        <?php if (isset($_GET['edit_group'])) { ?>
                            <select name="display_status"><option value="active" <?php if ($edit_data[0]->status == 'active') echo 'selected'; ?> >Active</option><option value="disabled" <?php if ($edit_data[0]->status == 'disabled') echo 'selected'; ?>>Disabled</option></select>
                        <?php } else { ?>
                            <select name="display_status"><option value="active" selected>Active</option><option value="disabled">Disabled</option></select>
                        <?php } ?>
                    </td>
                </tr>
            </table>

            <?php
            if (isset($_GET['edit_group'])) {
                submit_button("Edit Group");
            } else {
                submit_button("Create Group");
            }
            ?>

        </form>
    </div> <?php
}

/*
  Menu page add new display GROUP END
 */


/*
 * Events Manager View
 */

function wpds_events() {
    global $wpdb;
    global $customFields;
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
    // Get Displays from DB
    $table_name = "wpds_events";
    $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name");
    // Prepare array to show all the diplays
    foreach ($retrieve_data as $data) {
        $a[$i]['id'] = $data->id;
        $a[$i]['event_name'] = $data->name;
        $a[$i]['slider'] = $data->slider;
        $a[$i]['time_from'] = $data->time_from;
        $a[$i]['time_to'] = $data->time_to;
        $serialize_check = @unserialize($data->displays);
        if ($serialize_check !== false) {
            $unserialize_display = unserialize($data->displays);
            $display_name_array = wpds_get_display_name($unserialize_display);
            $a[$i]['displays'] = implode(' ,', $display_name_array);
        } else {
            $a[$i]['displays'] = $data->displays;
        }
        if ($a[$i]['time_from'] == '0000-00-00 00:00:00' && $a[$i]['time_to'] == '0000-00-00 00:00:00') {
            $a[$i]['time'] = 'Always On';
        } else {
            $date_from = new DateTime($a[$i]['time_from']);
            $a[$i]['time_from'] = $date_from->format('dS F o');
            $date_to = new DateTime($a[$i]['time_to']);
            $a[$i]['time_to'] = $date_to->format('dS F o');
            $a[$i]['time'] = $a[$i]['time_from'] . ' to ' . $a[$i]['time_to'];
        }

        $a[$i]['status'] = $data->status;
        $i++;
    }
    include_once (plugin_dir_path(__FILE__) . 'list_event_table.php');       //  table head to display list of videos
    wp_reset_query();
}

/*
 * Events Manager View END
 */

/*
  Menu page add new  / edit  EVENT
 */

function wpds_add_event() {
    global $wpdb;
    //-- Get the Display list
    $table = 'wpds_displays';
    $get_tables = $wpdb->get_results("SELECT * FROM $table WHERE status='active'");
    $displays = array();
    $i = 0;
    foreach ($get_tables as $display) {
        $displays[$i]['id'] = $display->id;
        $displays[$i]['name'] = $display->name;
        $i++;
    }
    //--------
    ////-- Get the Groups list
    $table = 'wpds_group_displays';
    $get_tables = $wpdb->get_results("SELECT * FROM $table WHERE status='active'");
    $groups = array();
    $i = 0;
    foreach ($get_tables as $group) {
        $groups[$i]['id'] = $group->id;
        $groups[$i]['groups_name'] = $group->group_name;
        $i++;
    }
    //--------
    //-------- Get List of available sliders
    $table = $wpdb->prefix . 'revslider_sliders';
    $get_sliders = $wpdb->get_results("SELECT * FROM $table");
    $sliders = array();
    $i = 0;
    foreach ($get_sliders as $slider) {
        $sliders[$i]['id'] = $slider->id;
        $sliders[$i]['title'] = $slider->title;
        $i++;
    }
    //--------
    $edit_always_on = FALSE;
    if (isset($_GET['edit_event']) && $_GET['edit_event'] != '0') {
        $table_name = "wpds_events";
        $id = $_GET['edit_event'];
        $edit_data = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$id");
        $display_ids = unserialize($edit_data[0]->displays);
        if ($edit_data[0]->time_from == '0000-00-00 00:00:00' && $edit_data[0]->time_to == '0000-00-00 00:00:00') {
            $edit_always_on = TRUE;
            $edit_data[0]->time_from = '';
            $edit_data[0]->time_to = '';
        } else {
            $edit_always_on = FALSE;
        }
    }
    ?>
    <div class="wrap">
        <h2>Add New Event</h2>

        <form method="post" action="">
            <?php settings_fields('wpds_display_group'); ?>
            <?php do_settings_sections('wpds_display_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Event Name</th>
                    <td><input type="text" name="event_name"  value="<?php echo $edit_data[0]->name; ?>"/></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Slider</th>
                    <td>
                        <select name="event_slider">
                            <?php foreach ($sliders as $slider) { ?>
                                <option value="<?php echo $slider['id']; ?>"<?php if (isset($_GET['edit_event']) && $edit_data[0]->slider == $slider['id']) echo "selected"; ?> ><?php echo $slider['title']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Run Time (From - To)</th>
                    <td style="width: 18%; max-width: 192px;"><input type="text"  data-date-format="mm-dd-yyyy hh:ii:ss" id="dpd1" name="time_from" value="<?php echo $edit_data[0]->time_from; ?>" <?php
                        if ($edit_always_on) {
                            echo "disabled";
                        }
                        ?>/></td>
                    <td><input type="text" class="span2" data-date-format="mm-dd-yyyy hh:ii:ss" id="dpd2" name="time_to" value="<?php echo $edit_data[0]->time_to; ?>" <?php
                        if ($edit_always_on) {
                            echo "disabled";
                        }
                        ?>/></td>
                </tr>
                <tr>  <th style="padding: 0%" scope="row"></th><td style="padding: 0px; padding-left: 1%;"><input type="checkbox" id="always_time" name="time_always" value="checked" <?php
                        if ($edit_always_on) {
                            echo "checked";
                        }
                        ?>>Always On</td></tr>

                <tr valign="top">
                    <th scope="row">Displays</th>
                    <td>
                        Displays : <br />
                        <?php foreach ($displays as $display) { ?>
                            <input type='checkbox' name='displays_selected[]' value='<?php echo $display['id']; ?>' <?php
                            if (isset($_GET['edit_event'])) {
                                if ((array_search($display['id'], $display_ids) !== false))
                                    echo "checked";
                            }
                            ?>/><?php echo $display['name']; ?><br />
                        <?php } ?><br />Groups : <br />
                        <?php foreach ($groups as $group) { ?>
                            <input type='checkbox' name='displays_selected[]' value='gr_<?php echo $group['id']; ?>' <?php
                            if (isset($_GET['edit_event'])) {
                                if ((array_search('gr_' . $group['id'], $display_ids) !== false))
                                    echo "checked";
                            }
                            ?>/><?php echo $group['groups_name']; ?><br />
                               <?php } ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Status</th>
                    <td>
                        <?php if (isset($_GET['edit_event'])) { ?>
                            <select name="event_status"><option value="active" <?php if ($edit_data[0]->status == 'active') echo 'selected'; ?> >Active</option><option value="disabled" <?php if ($edit_data[0]->status == 'disabled') echo 'selected'; ?>>Disabled</option></select>
                        <?php } else { ?>
                            <select name="event_status"><option value="active" selected>Active</option><option value="disabled">Disabled</option></select>
                        <?php } ?>
                    </td>
                </tr>
            </table>

            <?php
            if (isset($_GET['edit_event'])) {
                submit_button("Edit Event");
            } else {
                submit_button("Create Event");
            }
            ?>

        </form>
    </div> <?php
}
/*
*
* Function to manage alerts, add and edit alerts
*
*/
function wpds_alerts() {
    global $wpdb;
    global $customFields;
    global $current_user;

    if (!(isset($_POST['s'])) || empty($_POST['s']))
        $customPosts = new WP_Query();

    add_filter('posts_join', 'get_custom_field_posts_join');
    add_filter('posts_groupby', 'get_custom_field_posts_group');
    if ((isset($_GET['new_al'])) && $_GET['new_al'] ) {
      global $wpdb;
      //-- Get the Display list
      $table = 'wpds_displays';
      $get_tables = $wpdb->get_results("SELECT * FROM $table WHERE status='active'");
      $displays = array();
      $i = 0;
      foreach ($get_tables as $display) {
          $displays[$i]['id'] = $display->id;
          $displays[$i]['name'] = $display->name;
          $i++;
      }
      //--------
      ////-- Get the Groups list
      $table = 'wpds_group_displays';
      $get_tables = $wpdb->get_results("SELECT * FROM $table WHERE status='active'");
      $groups = array();
      $i = 0;
      foreach ($get_tables as $group) {
          $groups[$i]['id'] = $group->id;
          $groups[$i]['groups_name'] = $group->group_name;
          $i++;
      }
      //--------
      $edit_always_on = FALSE;
      if (isset($_GET['edit_alert']) && $_GET['edit_alert'] != '') {
          $table_name = "wpds_alerts";
          $id = $_GET['edit_alert'];
          $edit_data = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $id");
          $display_ids = unserialize($edit_data[0]->display_id);
          if ($edit_data[0]->time_from == '0000-00-00 00:00:00' && $edit_data[0]->time_to == '0000-00-00 00:00:00') {
              $edit_always_on = TRUE;
              $edit_data[0]->time_from = '';
              $edit_data[0]->time_to = '';
          } else {
              $edit_always_on = FALSE;
          }
      }
      ?>
      <div class="wrap">
          <h2>Add New Alert</h2>

          <form method="post" action="">
              <?php settings_fields('wpds_display_group'); ?>
              <?php do_settings_sections('wpds_display_group'); ?>
              <table class="form-table">
                      <tr valign="top">
                      <th scope="row">Run Time (From - To)</th>
                      <td style="width: 18%; max-width: 192px;"><input type="text"  data-date-format="mm-dd-yyyy hh:ii:ss" id="dpd1" name="time_from" value="<?php echo $edit_data[0]->time_from; ?>" <?php
                          if ($edit_always_on) {
                              echo "disabled";
                          }
                          ?>/></td>
                      <td><input type="text" class="span2" data-date-format="mm-dd-yyyy hh:ii:ss" id="dpd2" name="time_to" value="<?php echo $edit_data[0]->time_to; ?>" <?php
                          if ($edit_always_on) {
                              echo "disabled";
                          }
                          ?>/></td>
                  </tr>
                  <tr>  <th style="padding: 0%" scope="row"></th><td style="padding: 0px; padding-left: 1%;"><input type="checkbox" id="always_time" name="time_always" value="checked" <?php
                          if ($edit_always_on) {
                              echo "checked";
                          }
                          ?>>Always On</td></tr>

                  <tr valign="top">
                      <th scope="row">Displays</th>
                      <td>
                          Displays : <br />
                          <?php foreach ($displays as $display) { ?>
                              <input type='checkbox' name='displays_selected[]' value='<?php echo $display['id']; ?>' <?php
                              if (isset($_GET['edit_alert'])) {
                                  if ((array_search($display['id'], $display_ids) !== false))
                                      echo "checked";
                              }
                              ?>/><?php echo $display['name']; ?><br />
                          <?php } ?><br />Groups : <br />
                          <?php foreach ($groups as $group) { ?>
                              <input type='checkbox' name='displays_selected[]' value='gr_<?php echo $group['id']; ?>' <?php
                              if (isset($_GET['edit_alert'])) {
                                  if ((array_search('gr_' . $group['id'], $display_ids) !== false))
                                      echo "checked";
                              }
                              ?>/><?php echo $group['groups_name']; ?><br />
                                 <?php } ?>
                      </td>
                  </tr>

                  <tr valign="top">
                      <th scope="row">Email ID</th>
                      <td><input type="email" name="email_id"  value="<?php echo $edit_data[0]->email_id; ?>"/></td>
                  </tr>
              </table>

              <?php
              if (isset($_GET['edit_alert'])) {
                  submit_button("Edit Alert");
              } else {
                  submit_button("Create Alert");
              }
              ?>

          </form>
      </div> <?php
    }


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
    // Get Displays from DB
    $table_name = "wpds_alerts";
    $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name");
    // Prepare array to show all the diplays
    foreach ($retrieve_data as $data) {
        $a[$i]['id'] = $data->id;
        $a[$i]['display_id'] = $data->display_id;
        $a[$i]['time_from'] = $data->time_from;
        $a[$i]['time_to'] = $data->time_to;
        $a[$i]['email_id'] = $data->email_id;
        $serialize_check = @unserialize($data->display_id);
        if ($serialize_check !== false) {
            $unserialize_display = unserialize($data->display_id);
            $display_name_array = wpds_get_display_name($unserialize_display);
            $a[$i]['display_id'] = implode(' ,', $display_name_array);
        } else {
            $a[$i]['display_id'] = $data->display_id;
        }
        if ($a[$i]['time_from'] == '0000-00-00 00:00:00' && $a[$i]['time_to'] == '0000-00-00 00:00:00') {
            $a[$i]['time'] = 'Always On';
        } else {
            $date_from = new DateTime($a[$i]['time_from']);
            $a[$i]['time_from'] = $date_from->format('dS F o');
            $date_to = new DateTime($a[$i]['time_to']);
            $a[$i]['time_to'] = $date_to->format('dS F o');
            $a[$i]['time'] = $a[$i]['time_from'] . ' to ' . $a[$i]['time_to'];
        }

        $i++;
    }
  if(!(isset($_GET['new_al']))) {
    include_once (plugin_dir_path(__FILE__) . 'list_alerts.php');       //  table head to display list of videos
  }
  wp_reset_query();
}



$height = 500;
$width = 800;
/*
  Menu page add new EVENT END
 */
?>
