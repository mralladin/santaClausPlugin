<?php

require_once('../../../wp-load.php');




function deleteallrequests(){
    
    $unlock_data = array();
    $unlock_data_requests = array();

    if(isset($_POST['user_id']))
    {
        $user_id = $_POST['user_id'];
        
    }
    

    $user_logged_in=is_user_logged_in();
$current_user = wp_get_current_user();

$User_data = array();
$users = get_users( array( 'fields' => array( 'ID' ) ) );
//Es wird über alle User iteriert und über jedem User sein Freischalt Array und überall die User Id des Nutzer gelöscht
foreach($users as $user_id){
$unlock_array = get_user_meta($user_id->ID, 'unlocks');
for($i=0;$i<$unlock_array.count();$i++){
if (($key = array_search($user_id, $unlock_array)) !== false) {
    unset($unlock_array[$key]);
}
}

update_user_meta( $user_id->ID, 'unlocks', $unlock_array );

}


  

  
}

if(is_user_logged_in()){
    deleteallrequests();
        }

    
  

?>