<?php

require_once('../../../wp-load.php');

          global $current_user;
          $message='';
          wp_get_current_user();
          
          if (isset($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
            }

            if (isset($_POST['message'])) {
                $message = esc_attr($_POST['message']);
            }

          $user = get_user_by('id',$user_id);

          $spec_mail = $current_user->user_email;
   
    $to=$user->user_email;
    $subject='Anfrage von:'. $spec_mail;
    //Mail an den User schicken mit wp_mail und den 3 PArametern, welche per POST übergeben werden
    if(is_user_logged_in()){
   wp_mail( $to, $subject, $message );
    }


?>