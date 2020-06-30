<?php

require_once('../../../wp-load.php');
function unlock(){
    $unlock_data = array();
    $unlock_data_requests = array();
    if(isset($_POST['user_id']))
    {
        $user_id = $_POST['user_id'];
    }
    if(isset($_POST['id_target']))
    {
        $id_target = $_POST['id_target'];
    }
    if(!(empty(get_the_author_meta( 'unlock_requests',  $user_id ))))
    $unlock_data=get_the_author_meta( 'unlock_requests',  $user_id );
    if(!(empty(get_the_author_meta( 'unlocks',  $id_target ))))
    $unlock_data_requests=get_the_author_meta( 'unlocks',  $id_target );
    if (($key = array_search($id_target, $unlock_data)) !== false) {        //löschen des geklickten wertes aus dem array
        unset($unlock_data[$key]);
    }
    array_push($unlock_data_requests,$user_id);
    update_user_meta(  $id_target, 'unlocks', $unlock_data_requests);      // Dem Anfragenden wird das Array mit freischaltungen übergeben
    update_user_meta( $user_id, 'unlock_requests',$unlock_data);        //Vom User wird die Anfrage gelöscht da fertig
}
if(is_user_logged_in()){
    unlock();
        }
?>