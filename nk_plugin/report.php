<?php

require_once('../../../wp-load.php');




function get_administrator_email(){
    if(isset($_POST['user_id']))
    {
        $user_id = $_POST['user_id'];
    }
    if(isset($_POST['url']))
    {
    $url = $_POST['url'];
    }
    $blogusers = get_users('role=Administrator');
    foreach ($blogusers as $user) {
        $to = $user->user_email;
        $subject = 'Profil Meldung Bitte Überprüfen';
        $escaped_url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
        $link ="<a href=" . $escaped_url . ">Jetzt überprüfen</a>";
        $message = 'Bitte das Profil von Usernr: '. $user_id.'  '.$link.'';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $to, $subject, $message,$headers );
    }    
}

if(is_user_logged_in()){
    get_administrator_email();
        }

    
  

?>