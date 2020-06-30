<?php 
   include 'ChromePhp.php';
/*
Plugin Name: Nikolaus Vermittler Profil-Plugin
Plugin URI: https://bueltge.de/
Description: WP-Plugin zur Vermittlung von Nikolaus Darstellern einfach aktivieren
Version: 1.0
Author: Philipp Lippold
Author URI: https://bueltge.de/
Update Server: https://bueltge.de/wp-content/download/wp/
Min WP Version: 1.5
Max WP Version: 2.0.4
*/
//damit niemand die urls nach php datein durchsuchen kann
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
setlocale(LC_TIME, "de_DE");

require_once(ABSPATH.'wp-admin/includes/user.php' );
//Biografie Box entfernen personal_options hook wird vor allen anderen dingen ausgeführt
add_action( 'personal_options', 'bufferout_start' );

//Entfernt/versteck unnötige Menüpunkte
add_action( 'admin_head-user-edit.php', 'remove_menu_elements' );
add_action( 'admin_head-profile.php',   'remove_menu_elements' );
add_action('admin_menu','remove_menu_elems');
//Login weiterleitung
add_filter('login_redirect','login_redirection_func',10,3);
add_action('load-index.php','profil_redirection_func');

//Extra Menüoptionen im Profil
add_action( 'show_user_profile', 'new_user_fields' );
add_action( 'edit_user_profile', 'new_user_fields' );

//Speichern in der Datenbank
add_action( 'personal_options_update', 'save_new_profile_fields' );
add_action( 'edit_user_profile_update', 'save_new_profile_fields' );

//Jeder nutzer sieht nur seine eigenen Uploads
add_filter( 'ajax_query_attachments_args', 'show_only_media_of_own_user' );

//Erlauben Abonennten Daten upzuloaden
add_action('admin_init', 'allow_upload_files');
//Login und registrations Menüpunkt hinzufügen
add_filter('wp_nav_menu_items','second_last_nav_elem');
add_filter('wp_nav_menu_items','last_nav_elem');

//Wenn ein User sich registriert wird der Lastlogin gesetzt
add_action( 'user_register', 'last_login', 10, 1 );


function last_login( $user_id ) {
    update_user_meta(  $user_id , 'last_login', time() );
    update_user_meta( $user_id, 'mail', 'no' );
}

//Wenn sich der User einloggt wird der Lastlogin resetet
function user_last_login( $user_login, $user ) {
    update_user_meta( $user->ID, 'last_login', time() );
    update_user_meta( $user->ID, 'mail', 'no' );
}
add_action( 'wp_login', 'user_last_login', 10, 2 );

//Schedule Cron Job in Wordpress
function zeit_plan( $schedules ) {
    $schedules['every_hour'] = array(
        'interval' =>  1 * HOUR_IN_SECONDS, 
        'display'  => __( 'Jede Stunde' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'zeit_plan' );

//Schedule eine aktion wenn noch nicht getan
if ( ! wp_next_scheduled( 'cron_hook' ) ) {
    wp_schedule_event( time(), 'every_hour', 'cron_hook' );
}

///Die Funktion hooken welche alle 6 Stunden ausgeführt werden soll
 add_action( 'cron_hook', 'cron_function' );

 function isSiteAdmin($user_id){
  
    return   user_can( $user_id, 'manage_options' );
}

//Cron Function
function cron_function() {
    $users = get_users( array( 'fields' => array( 'ID' ) ) );
    foreach($users as $user_id){
        $delta = time() - get_the_author_meta( "last_login", $user_id->ID );
        
        $delta2 = 11104800 - $delta  ;
        /*
        4 Monate= 10500000 in sekunden
        1 Woche=  11104800
        */
        if(!(isSiteAdmin($user_id->ID))){
            $mail=get_the_author_meta( "mail", $user_id->ID );
            if($delta2<10500000&&$mail=="no"){
                update_user_meta( $user_id->ID, 'mail', 'yes' );
               $user = get_user_by('id',$user_id->ID);
               $to=$user->user_email;
               $subject='Sind Sie noch Nikolaus? :)';
               $message = "Hallo sind Sie noch bei der Nikolausvermittlung dabei? Falls Ja loggen Sie sich bitte auf Ihrem Nutzerkonto ein, da dieses sonst Automatisch gelöscht wird.";
               wp_mail( $to, $subject, $message );
            }
            else if($delta2<=0){
                wp_delete_user($user_id->ID);
               delete_user_meta($user_id->ID);
            }
    
        }
    
        }
    
    
}






//Navigation erweitern
function second_last_nav_elem($items) {
    $current_user = wp_get_current_user();

     if (is_user_logged_in()){
        return $items .= '<li ><div><a style="padding:5px; display: inline; color:darkgreen; text-decoration: underline;" href="'.get_edit_profile_url(get_permalink()).' ">' .esc_html( $current_user->user_login).'!</a><span> oder</span> <a style="padding:5px; display: inline;" href="'.wp_logout_url(get_permalink()).' ">     Ausloggen!</a></div></li>';
      
      }
      else   {
      
        return $items .=  '<a style="color:darkgreen;display: inline;" href="'.wp_login_url(get_permalink()) .'">Login</a>';
      }
}



function last_nav_elem($items) {


    $url=site_url('/wp-login.php?action=register&redirect_to=' . get_permalink());
  
    if (!is_user_logged_in()){
      
        return $items .= '<li ><a padding:8px;  style="display: inline; color:red;" href="'.$url.'">
        Registrieren
        </a></li>';
    }
    else{
        return $items;
    }
    
  }



//Datum wird in der Navigation hinzugefügt
add_filter('wp_nav_menu_items','add_todaysdate_in_menu');
add_filter('the_time', 'modify_date_format');

function add_todaysdate_in_menu( $items ) {
    
    $date = date_create();
 
    $date_f = date_format($date,'d.m.Y ');
    $items .=  '<li  style="margin-left:20px;">' .  $date_f .  '</li>';
   
    return $items;
}



//Upload von Dateien für Subscriber erlauben
function allow_upload_files( ) {
    $role = 'subscriber';
    if(!current_user_can($role) || current_user_can('upload_files'))
    return;
    $subscriber = get_role( $role );
    $subscriber->add_cap('upload_files');
    } 
    

//Jeder User sieht nur seine upgeloadeten Files
function show_only_media_of_own_user( $query ) {
    $user_id = get_current_user_id();
    if ( $user_id && !current_user_can('activate_plugins') && !current_user_can('edit_others_posts
'       ) ) {
        $query['author'] = $user_id;
    }
    return $query;
} 


function profil_redirection_func(){
    wp_redirect(admin_url('profile.php'));
}


function login_redirection_func( $redirect_to, $request, $user ){
    return admin_url('profile.php');
}

//Entfernen der Menüs Dashboard
function remove_menu_elems () {
    global $menu;
    $restricted = array(__('Dashboard'));
    //$restricted = array(__('Dashboard'), __('Posts'), __('Media'), __('Links'), __('Pages'), __('Appearance'), __('Tools'), __('Users'), __('Settings'), __('Comments'), __('Plugins'));
    end($menu);
    while(prev($menu)){
        $value = explode(' ',$menu[key($menu)][0]);
        if(in_array($value[0]!= NULL?$value[0]:'',$restricted))
        {
            unset($menu[key($menu)]);
        }
    }
}


function remove_menu_elements()
{
    wp_enqueue_media();
 
    echo '<style>tr.user-url-wrap{ display: none; }</style>';
    echo '<style>tr.user-first-name-wrap{ display: none; }</style>';
    echo '<style>tr.user-last-name-wrap{ display: none; }</style>';
    echo '<style>tr.user-admin-bar-front-wrap{ display: none; }</style>';
    echo '<style>tr.user-profile-picture{ display: none; }</style>';
    
}



    function bufferout_start()
    {
       
    
        ob_start();
       
    }
    
   
function delete_biobox()
{
    
    //In Html wird der gesamte gesammelte output buffer gespeichert
    $html = ob_get_contents();
    ob_end_clean();
 
    
    //H2 entfernen
    $headline = __( IS_PROFILE_PAGE ? 'About Yourself' : 'About the user' );
    $html = str_replace( '<h2>' . $headline . '</h2>', '', $html );
    
    //Tabellen spalte entfernen
    $html = preg_replace( '~<tr class="user-description-wrap">\s*<th><label for="description".*</tr>~imsUu', '', $html );
    print $html;
    
    
}





function save_new_profile_fields( $user_id ) {
      
    
    
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }

    //Müssen nicht geprüft werden, da immer einen Wert ob checked oder nicht
    update_user_meta( $user_id, 'nikolaus', $_POST['nkwerden'] );
    update_user_meta( $user_id, 'tag1', $_POST['tag1'] );
    update_user_meta( $user_id, 'tag2', $_POST['tag2'] );
    update_user_meta( $user_id, 'tag3', $_POST['tag3'] );
    update_user_meta( $user_id, 'tag4', $_POST['tag4'] );
    update_user_meta( $user_id, 'tag5', $_POST['tag5'] );
    update_user_meta( $user_id, 'tag6', $_POST['tag6'] );
    update_user_meta( $user_id, 'tag7', $_POST['tag7'] );
    update_user_meta( $user_id, 'tag8', $_POST['tag8'] );
  

    //Falls kein Profil Bild oder Zertifikat vorhanden ist eine Fall Back Lösung
    $current_url = home_url( add_query_arg( array(), $wp->request ) );
    $fall_back_pic1 = '/wp-content/plugins/nk_plugin/assets/no_pic.png';
    $fall_back_pic2 = '/wp-content/plugins/nk_plugin/assets/empty.jpeg';
    $fall_back_pic = $current_url.$fall_back_pic1;
    $fall_back_pic_certificate = $current_url.$fall_back_pic2;
    $url = str_replace('\\', '/', $fall_back_pic);
    $url2 = str_replace('\\', '/',  $fall_back_pic_certificate);

    //Variablen immer auf Empty überprüfen
    if(empty($_POST['firstname'])){
        update_user_meta( $user_id, 'firstname', '/' );
    }
    else{
        update_user_meta( $user_id, 'firstname', $_POST['firstname'] );
    }
    if(empty($_POST['lastname'])){
        update_user_meta( $user_id, 'lastname', '/' );
    }
    else{
    update_user_meta( $user_id, 'lastname', $_POST['lastname'] );
    }

    if(empty($_POST['address'])){
        update_user_meta( $user_id, 'address', '/' );
    }
    else{
    update_user_meta( $user_id, 'address', $_POST['address'] );
    }

    
    if(empty($_POST['city'])){
        update_user_meta( $user_id, 'city', '/' );
    }
    else{
    update_user_meta( $user_id, 'city', $_POST['city'] );
    }

    if(empty($_POST['zipcode'])){
        update_user_meta( $user_id, 'zipcode', '/' );
    }
    else{
    update_user_meta( $user_id, 'zipcode', $_POST['zipcode'] );
    }


    if(empty($_POST['land'])){
        update_user_meta( $user_id, 'land', '/' );
    }
    else{
    update_user_meta( $user_id, 'land', $_POST['land'] );
    }

    if(empty($_POST['age'])){
        update_user_meta( $user_id, 'age', '/' );
    }
    else{
    update_user_meta( $user_id, 'age', $_POST['age'] );
    }
    if(empty($_POST['profile_picture'])){
        update_user_meta( $user_id, 'profile_picture', $url );
    }
    else{
    update_user_meta( $user_id, 'profile_picture', $_POST['profile_picture'] );
    }

    if(empty($_POST['radius'])){
        update_user_meta( $user_id, 'radius', '0' );
    }
    else{
        update_user_meta( $user_id, 'radius', $_POST['radius'] );
    }

    if(empty($_POST['bio'])){
        update_user_meta( $user_id, 'bio', '/' );
    }
    else{
        update_user_meta( $user_id, 'bio', $_POST['bio'] );
    }
    if(empty($_POST['zertifikat'])){
        update_user_meta( $user_id, 'zertifikat', $url2 );
    }
    else{
        update_user_meta( $user_id, 'zertifikat', $_POST['zertifikat'] );
    }
    
    if(esc_url(get_the_author_meta('zertifikat', $user_id ))==esc_url($url2)){
        update_user_meta( $user_id, 'kein_zertifikat', "true" );       
    }
    else{
        update_user_meta( $user_id, 'kein_zertifikat', "false" );
    }
    

    //Koordinaten für Punkt speicher sofern gesetzt, sonst -1
    $latitude = $_POST['latmarker'];
    $longitude = $_POST['lngmarker'];

    if(empty($latitude)||empty($longitude)){
        update_user_meta( $user_id, 'latitude', '-1' );
        update_user_meta( $user_id, 'longitude',  '-1'  );
    }
    else{
        update_user_meta( $user_id, 'latitude', $latitude );
        update_user_meta( $user_id, 'longitude',  $longitude  );
    }
        
    
    
}



function new_user_fields( $user ) { 
    delete_biobox();
    ?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
   <script>

    
        $( document ).ready(function() {
            
        //Nicknamen verstecken wird eh nicht benötigt
         $("#nickname,#display_name").parent().parent().hide();


            //ALLE Checkboxen checken, welche zuvor vom User gechecked wurden
            if("<?php echo esc_attr(get_the_author_meta( 'nikolaus', $user->ID )); ?>"=="YES")
            {
            
                $('#nkwerden').prop('checked', true);
            }
            else{
              
                $('#nkwerden').prop('checked', false);
            }

            if("<?php echo esc_attr(get_the_author_meta( 'tag1', $user->ID )); ?>"=="YES")
            {
            
                $('#tag1').prop('checked', true);
            }
            else{
              
                $('#tag1').prop('checked', false);
            }


            if("<?php echo esc_attr(get_the_author_meta( 'tag2', $user->ID )); ?>"=="YES")
            {
            
                $('#tag2').prop('checked', true);
            }
            else{
              
                $('#tag2').prop('checked', false);
            }


            if("<?php echo esc_attr(get_the_author_meta( 'tag3', $user->ID )); ?>"=="YES")
            {
            
                $('#tag3').prop('checked', true);
            }
            else{
              
                $('#tag3').prop('checked', false);
            }


            if("<?php echo esc_attr(get_the_author_meta( 'tag4', $user->ID )); ?>"=="YES")
            {
            
                $('#tag4').prop('checked', true);
            }
            else{
              
                $('#tag4').prop('checked', false);
            }


            if("<?php echo esc_attr(get_the_author_meta( 'tag5', $user->ID )); ?>"=="YES")
            {
            
                $('#tag5').prop('checked', true);
            }
            else{
              
                $('#tag5').prop('checked', false);
            }


            if("<?php echo esc_attr(get_the_author_meta( 'tag6', $user->ID )); ?>"=="YES")
            {
            
                $('#tag6').prop('checked', true);
            }
            else{
              
                $('#tag6').prop('checked', false);
            }


            if("<?php echo esc_attr(get_the_author_meta( 'tag7', $user->ID )); ?>"=="YES")
            {
            
                $('#tag7').prop('checked', true);
            }
            else{
              
                $('#tag7').prop('checked', false);
            }


            if("<?php echo esc_attr(get_the_author_meta( 'tag8', $user->ID )); ?>"=="YES")
            {
            
                $('#tag8').prop('checked', true);
            }
            else{
              
                $('#tag8').prop('checked', false);
            }



   


//Profilbild und Zertifikat handling
var file_frame;
 $('.additional-user-image').on('click', function( event ){

   event.preventDefault();

   //Wenn das Medienframe bereits exisitert neu öffnen
   if ( file_frame ) {
     file_frame.open();
     return;
   }

   //Ansonsten Medienframe erstellen
   file_frame = wp.media.frames.file_frame = wp.media({
     title: $( this ).data( 'uploader_title' ),
     button: {
       text: $( this ).data( 'uploader_button_text' ),
     },
     multiple: false 
   });

   file_frame.on( 'select', function() {
     attachment = file_frame.state().get('selection').first().toJSON();
     //Wenn das hochgeladene Bild kein JPEG/GIF/PNG ist oder es nicht kleiner als 4MB abbrechen
if (!( attachment.mime == "image/jpeg" || attachment.mime == "image/gif" || attachment.mime == "image/png" && attachment.filesizeInBytes < 4000000))
{
 
 return;
}    
  
  

     $(profile_picture).val(attachment.url);
     //Die URL vom Bild wird einem HTML Wert zugewiesen
   });

   //Das Modale Medienframe fesnter öffnen
   file_frame.open();
 });





 var file_frame2;
 $('.additional-certificate-image').on('click', function( event ){
   event.preventDefault();
    //Wenn das Medienframe bereits exisitert neu öffnen
   if ( file_frame2 ) {
    file_frame2.open();
     return;
   }
  //Ansonsten Medienframe erstellen
   file_frame2 = wp.media.frames.file_frame = wp.media({
     title: $( this ).data( 'uploader_title' ),
     button: {
       text: $( this ).data( 'uploader_button_text' ),
     },
     multiple: false  
   });
   file_frame2.on( 'select', function() {
          //Wenn das hochgeladene Bild kein JPEG/GIF/PNG ist oder es nicht kleiner als 4MB abbrechen
     attachment = file_frame2.state().get('selection').first().toJSON();

if (!( attachment.mime == "image/jpeg" || attachment.mime == "image/gif" || attachment.mime == "image/png" && attachment.filesizeInBytes < 4000000))
{
 
 return;
}    
     $(zertifikat2).val(attachment.url);
       //Die URL vom Bild wird einem HTML Wert zugewiesen
   });

  //Das Modale Medienframe fesnter öffnen
   file_frame2.open();
 });






    });
    </script>


    <link rel="stylesheet" href="../wp-content/plugins/nk_plugin/assets/css/registration.css" >
    <!-- Ab hier einbinden neuer Formular Objekte in HTML -->
    <h3><?php _e("Nikolaus werden?", "blank"); ?></h3>
    <div>
    <input type="number" id="latmarker" style="display:none;" name="latmarker"></input>
    <input type="number" id="lngmarker" style="display:none;" name="lngmarker"></input>
    <input name="nkwerden" value="NO" type="hidden">
    <input type="checkbox" id="nkwerden"  name="nkwerden" value="YES" />
    <label for="nikolauswerden">Nikolaus werden?</label>
    <p style="color:red;font-weight:bold;">Achtung, falls Sie ein Nikolaus werden wollen, stimmen Sie zu, dass ein Teil Ihrer Daten (Stadt) veröffentlicht werden.</p>
  </div>
    <table class="form-table">
    <tr>
        <th><label for="firstname"><?php _e("Vorname"); ?></label></th>
        <td>
            <input type="text" name="firstname" id="firstname" value="<?php echo esc_attr( get_the_author_meta( 'firstname', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Bitte ihren Vornamen eingeben."); ?></span>
        </td>
    </tr>
    <tr>
        <th><label for="lastname"><?php _e("Nachname"); ?></label></th>
        <td>
            <input type="text" name="lastname" id="lastname" value="<?php echo esc_attr( get_the_author_meta( 'lastname', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Bitte ihren Nachnamen eingeben."); ?></span>
        </td>
    </tr>
    <tr>
        <th><label for="address"><?php _e("Adresse"); ?></label></th>
        <td>
            <input type="text" name="address" id="address" value="<?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Bitte ihre Adresse eingeben."); ?></span>
        </td>
    </tr>
    <tr>
    <th><label style="color:red;" for="city"><?php _e("Stadt"); ?></label></th>
        <td>
        <input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" /><br />
        <span class="description"><?php _e("Bitte ihre Stadt eingeben. Andernfalls tauchen Sie in Suchen nicht auf."); ?></span>
        </td>
    </tr>

    <tr>
        <th><label style="color:red;" for="position"><?php _e("Position"); ?></label></th>
        <td>
            <input id="pac-input" class="controls" type="text" placeholder="Search Box">
            <div id="map"> </div> 
            <span class="description"><?php _e("Bitte ihre Position auf der Karte klicken."); ?></span>
        </td>
    </tr>
    <tr>
    <th><label for="zipcode"><?php _e("Postleitzahl"); ?></label></th>
        <td>
            <input type="text" name="zipcode" id="zipcode" value="<?php echo esc_attr( get_the_author_meta( 'zipcode', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Bitte ihre Postleitzahl eingeben."); ?></span>
        </td>
    </tr>
    <tr>
    <th><label for="land"><?php _e("Land"); ?></label></th>
        <td>
            <input type="text" name="land" id="land" value="<?php echo esc_attr( get_the_author_meta( 'land', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Bitte ihr Land eingeben."); ?></span>
        </td>
    </tr>
    <tr>
    <th><label for="age"><?php _e("Alter"); ?></label></th>
        <td>
            <input type="text" name="age" id="age" value="<?php echo esc_attr( get_the_author_meta( 'age', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Bitte ihr Alter eingeben."); ?></span>
        </td>
    </tr>   
    <tr>
    <th><label for="bio"><?php _e("Zusätzliche Informationen"); ?></label></th>
        <td>
            <textarea cols="50" rows="5" name="bio" id="bio" class="regular-text" /><?php echo esc_attr( get_the_author_meta( 'bio', $user->ID ) ); ?> </textarea><br />
            <span class="description"><?php _e("Erzählen Sie etwas über sich.."); ?></span>
        </td>
    </tr>   
  
    <tr>
    <th><label for="radius"><?php _e("In welcher Umgebung möchten Sie arbeiten?"); ?></label></th>
        <td>
   
    <input name="tag1" value="NO" type="hidden">
    <input type="checkbox" id="tag1"  name="tag1" value="YES" />
    <label class="tagcheckbox"  for="nikolauswerden">Ehrenamtlich</label>

     <input name="tag2" value="NO" type="hidden">
    <input type="checkbox" id="tag2"  name="tag2" value="YES" />
    <label class="tagcheckbox"  for="nikolauswerden">Krankenhäuser</label>

    </br>
    <input name="tag3" value="NO" type="hidden">
    <input type="checkbox" id="tag3"  name="tag3" value="YES" />
    <label class="tagcheckbox"  for="nikolauswerden">Familien Feste</label>

    
    <input name="tag4" value="NO" type="hidden">
    <input type="checkbox" id="tag4"  name="tag4" value="YES" />
    <label class="tagcheckbox" for="nikolauswerden">Kaufhäuser</label>

    </br>
    <input name="tag5" value="NO" type="hidden">
    <input type="checkbox" id="tag5"  name="tag5" value="YES" />
    <label class="tagcheckbox"  for="nikolauswerden">Kirche</label>

    
    <input name="tag6" value="NO" type="hidden">
    <input type="checkbox" id="tag6"  name="tag6" value="YES" />
    <label class="tagcheckbox"  for="nikolauswerden">Schulen</label>
    </br>
    
    <input name="tag7" value="NO" type="hidden">
    <input type="checkbox" id="tag7"  name="tag7" value="YES" />
    <label class="tagcheckbox"  for="nikolauswerden">Stadt</label>

    
    <input name="tag8" value="NO" type="hidden">
    <input type="checkbox" id="tag8"  name="tag8" value="YES" />
    <label class="tagcheckbox"  for="nikolauswerden">Andere</label>

    </td>
    </tr>  


    <tr>
    <th><label for="radius"><?php _e("In wieviel KM Radius wollen sie arbeiten?"); ?></label></th>
        <td>
            <input type="number" min="0" max="1000" name="radius" id="radiusage" value="<?php echo esc_attr( get_the_author_meta( 'radius', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Bitte Radius eingeben in Kilometern."); ?></span>
        </td>
    </tr>   
    </tr>





<tr>
            <th><label for="zertifikat"><?php _e( 'Nikolaus-Zertifikat' ); ?></label></th>
            <td>
                <!-- Outputs the image after save -->
                <img src="<?php echo esc_url( get_the_author_meta( 'zertifikat', $user->ID ) ); ?>" style="width:150px;"><br />
                <!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
                <input type="text" style="display:none;" name="zertifikat" id="zertifikat2" value="<?php echo esc_url_raw( get_the_author_meta( 'zertifikat', $user->ID ) ); ?>" class="regular-text" />
                <!-- Outputs the save button -->
                <input type='button' class="additional-certificate-image button-primary" value="<?php _e( 'Zertifikat hochladen', 'textdomain' ); ?>" id="zertifikat"/>
                <input type='button' class="button-primary" value="<?php _e( 'Zertifikat löschen', 'textdomain' ); ?>" onclick="deletezert()"/>
                <br/>
                <span class="description"> Bitte laden Sie Ihr <a style="font-weight:bold;" href="http://www.nikolausaktion.org/" target="_blank"> Zertifikat des Erzbistum Köln<a> hoch.  </span>
            </td>
        </tr>



  

<tr>
            <th><label for="profile_picture"><?php _e( 'Profilbild', 'textdomain' ); ?></label></th>
            <td>
                <!-- Outputs the image after save -->
                <img src="<?php echo esc_url( get_the_author_meta( 'profile_picture', $user->ID ) ); ?>" style="width:150px;"><br />
                <!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
                <input type="text" style="display:none;" name="profile_picture" id="profile_picture" value="<?php echo esc_url_raw( get_the_author_meta( 'profile_picture', $user->ID ) ); ?>" class="regular-text" />
                <!-- Outputs the save button -->
                <input type='button' class="additional-user-image button-primary" value="<?php _e( 'Bild hochladen', 'textdomain' ); ?>" id="uploadimage"/>
                <input type='button' class="button-primary" value="<?php _e( 'Profilbild löschen', 'textdomain' ); ?>" onclick="deleteprofilepic()"/>
               
                <br />
                <span class="description"><?php _e( 'Bitte laden Sie Ihr Profilbild hoch.', 'textdomain' ); ?></span>
            </td>
        </tr>

    </table>
    <script>
var map;
var marker;
//Zertifikat und Profilbild nach speicher löschen
function deletezert(){
    $(zertifikat2).val("");
}
function deleteprofilepic(){
    $(profile_picture).val("");
}

//Marker erstellen, falls Lat und Lng Daten bereitstehen
function initMarker(){
    var latmarker = <?php 
        if(empty ( get_the_author_meta( 'latitude', $user->ID )))
        echo '-1';
        else
        echo esc_attr(get_the_author_meta( 'latitude', $user->ID ));
        ?>;

        var lngmarker = <?php 
        if(empty ( get_the_author_meta( 'longitude', $user->ID )))
        echo '-1';
        else
        echo esc_attr(get_the_author_meta( 'longitude', $user->ID ));
        ?>;
       if(latmarker!="-1"&&lngmarker!="-1")
            {
        var myLatlng = new google.maps.LatLng(latmarker,lngmarker);
        placeMarker(myLatlng);
            }       
};

      function initMap() {
       map = new google.maps.Map(document.getElementById('map'), {
        center: {
          lat: 50.11667, lng: 8.83333}
        ,
        zoom: 6,
        mapTypeId: 'roadmap'
        });
        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        google.maps.event.addListener(map, 'click', function(event) {
        placeMarker(event.latLng);

        });

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());
        });
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();

          if (places.length == 0) {
            return;
          }

      
          // For each place, get the icon, name and location.
          var bounds = new google.maps.LatLngBounds();
          places.forEach(function(place) {
            if (!place.geometry) {
              console.log("Returned place contains no geometry");
              return;
            }
          

            if (place.geometry.viewport) {
              // Only geocodes have viewport.
              bounds.union(place.geometry.viewport);
            } else {
              bounds.extend(place.geometry.location);
            }
   


          });
          map.fitBounds(bounds);
        });



        initMarker();
      }

        function placeMarker(location) {
        if(marker!=null){
        marker.setMap(null);
        marker=null;
        }
        marker = new google.maps.Marker({
        position: location,
        map: map,
        animation: google.maps.Animation.DROP
        });  
        $("#latmarker").val(marker.getPosition().lat());
        $('#lngmarker').val(marker.getPosition().lng());
        }


    </script>

   <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyC9wMbx-lXmvZMEmrv0aaFzC-pK4JMhazg&&libraries=places&callback=initMap'
          async defer>
       
  </script>
<?php 
}


?>