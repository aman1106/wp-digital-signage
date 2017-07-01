<?php
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );

    function convert_to_screen($hook_name) {
        if (!class_exists('WP_Screen', false)) {
            _doing_it_wrong('convert_to_screen(), add_meta_box()', __("Likely direct inclusion of wp-admin/includes/template.php in order to use add_meta_box(). This is very wrong. Hook the add_meta_box() call into the add_meta_boxes action instead."), '3.3.0');
            return (object) array('id' => '_invalid', 'base' => '_are_belong_to_us');
        }

        return WP_Screen::get($hook_name);
    }

    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class My_List_Table extends WP_List_Table {

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'group_name' => 'Group Name',
            'location' => 'Location',
            'displays' => 'Displays',
            'status' => 'Status',
        );
        return $columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function __construct() {
        parent::__construct(array(
            'singular' => 'Group', //Singular label
            'plural' => 'Groups', //plural label
            'ajax' => true //We won't support Ajax for this table
        ));
    }

    function process_bulk_action() {

        if ('delete' === $this->current_action()) {
            foreach ($_POST['group'] as $group) {
                global $wpdb;
              $delete = $wpdb->delete("wpds_group_displays", array('id' => $group));
                if (!$delete) {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e('Could not delete Display. Please try again or contact support', 'sample-text-domain'); ?></p>
                    </div>
                    <?php
                } else if ($delete > 0) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e('Successfully deleted ' . $delete . ' displays', 'sample-text-domain'); ?></p>
                    </div>
                    <?php
                }
            }
        }
    }

    function prepare_items_wpds($a) {
        //$this->search_box('search', 'search_id'); 
        $this->process_bulk_action();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // $this->items = $a;

        usort($a, array(&$this, 'usort_reorder'));
//$this->items = $a;
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($a);

        $found_data = array_slice($a, (($current_page - 1) * $per_page), $per_page);
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ));
        $this->items = $found_data;
        global $query_string;

        $query_args = explode("&", $query_string);
        $search_query = array();

        foreach ($query_args as $key => $string) {
            $query_split = explode("=", $string);
            $search_query[$query_split[0]] = urldecode($query_split[1]);
        } // foreach

        $search = new WP_Query($search_query);

        // print_r($_POST); 
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'group_name':
            case 'location':
            case 'displays':
            case 'status':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id' => array('id', false),
            'group_name' => array('name', false),
            'location' => array('location', false),
            'displays' => array('displays', false),
            //'guid' => array('guid', false),
            'status' => array('status', false),
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b) {
        // If no sort, default to title
        $orderby = (!empty($_GET['orderby']) ) ? $_GET['orderby'] : 'ID';
        // If no order, default to asc
        $order = (!empty($_GET['order']) ) ? $_GET['order'] : 'dsc';
        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);
        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }

    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id']
        );
    }

    function column_id($item) {
        $actions = array(
            'edit' => sprintf('<a href="?page=wpds_add_group&edit_group=%s">Edit</a>', $item['id']),
            'delete' => sprintf('<a href="?page=page=wpds_add_group&del_group=%s">Delete</a>', $item['id']),
        );
        return sprintf('%1$s %2$s', $item['id'], $this->row_actions($actions));
    }

}
?>
<div class="icon32" id="icon-edit">
</div>
<?php
$myListTable = new My_List_Table();
echo '<div class="wrap"><h2> Display Groups Manager<a style="float:right" href="admin.php?page=wpds_add_group"><input type="submit" name="show_add" value="Create New Group »" class="button-primary"></a></h2><br />';?>

<?php
$myListTable->prepare_items_wpds($a);
?><form method="post">

    <input type="hidden" name="page" value="my_list_test" />

    <?php $myListTable->search_box('search', 'search_id'); ?>

<?php $myListTable->display(); ?>
</form><?php
echo '</div>';
?>
    
