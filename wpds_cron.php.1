<?php
/*
*function
*/
  define( 'BLOCK_LOAD', true );
  require_once( $_SERVER['DOCUMENT_ROOT'] . '../../../wp-config.php' );
  require_once( $_SERVER['DOCUMENT_ROOT'] . '../../../wp-includes/wp-db.php' );
  $wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
  $email_id = $wpdb->get_results("SELECT option_value FROM wp_options WHERE option_name =\"admin_email\"");
  $email_id = $email_id[0]->option_value;
  $table_name="wpds_displays";
  $date = date('Y-m-d h:i:s');
  $results = $wpdb->get_results("SELECT upstatus FROM $table_name");
  foreach($results as $result) {
   if($result->upstatus == 1) {
     $last_seen = $wpdb->get_var("SELECT last_seen FROM $table_name WHERE upstatus = $result->upstatus");
     $date = strtotime($date);
     $i=0;
     $last_seen = strtotime($last_seen);
     $difference =round(($date - $last_seen)/60,2);
        $id = $wpdb->get_var("SELECT id FROM $table_name WHERE upstatus = $result->upstatus");
        $displays = $wpdb->get_results("SELECT display_id FROM wpds_alerts");
        while($displays[$i]->display_id !='') {
                $display= unserialize($displays[$i]->display_id);
                if( array_search($id,$display) !== false) {
                        $email_to = $wpdb->get_var("SELECT email_id FROM wpds_alerts WHERE display_id LIKE '%\"$id\"%'");
                } $i++;
        }
      if($difference >= 10) {
        //echo $email_to . "\n";
        //echo $email_id . "\n";
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: admin <'."$email_id" .'>' . "\r\n";
        $msg = "Upstatus is set to 0 as display stopped working";
        mail($email_to,"Display Update",$msg,$headers);
        echo "mail sent"."\n";
        $wpdb->update('wpds_displays', array('upstatus' => 0), array('upstatus' => 1));
        }
        else {
        echo "everything OK"."\n";
        }

     }

  }
 ?>
