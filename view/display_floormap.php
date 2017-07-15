<?php
//checking whether class exists
if (!class_exists('WP_List_Table')) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class My_List_Table extends WP_List_Table {

    //creating table displaying devices for floormap
    function get_columns() {
        $columns = array(
            'ID' => 'ID',
            'Name' => 'Name',
        );
        return $columns;
    }

    function __construct() {
        parent::__construct(array(
            'singular' => 'Floormap', //Singular label
            'plural' => 'Floormaps', //plural label
            'ajax' => true //We won't support Ajax for this table
        ));
    }

    function prepare_items_wpds() {

        $columns = $this->get_columns();
        $hidden = array();

        $this->_column_headers = array($columns, $hidden);
        $data = $this->table_data();
        $this->items = $data;
    }

    function column_default( $item, $column_name) {
            switch ($column_name) {
            case 'ID' :
            case 'Name':
                return $item[$column_name];

            default:
                return print_r( $item, true); //Show the whole array for troubleshooting purposes
        }
    }
    function table_data() {

    global $wpdb;
    $name = $_GET['floormap_displays'];

    $data = array();
    //getting result from table where condition is true
    $result = $wpdb->get_results("SELECT wpds_displays.name AS name, wpds_displays.id AS id FROM wpds_displays INNER JOIN wpds_floormaps ON wpds_displays.floormap = wpds_floormaps.floormap WHERE wpds_floormaps.id = \"$name\"");

    $i=0;
    while($result[$i] != '') {
       $a=$result[$i]->name;
       $id=$result[$i]->id;
       $i++;
       //inserting selected data in an array
       $data[] = array(
        'ID' => $id,
        'Name'=> $a,
      );
    }
      return $data;
    }
}
?>
<div class="icon32" id="icon-edit">
</div>
<?php
$myListTable = new My_List_Table();
echo '<div class="wrap"><h2>Displays Added on the Floormap</h2><br />';
$myListTable->prepare_items_wpds();
$myListTable->display();
?>
