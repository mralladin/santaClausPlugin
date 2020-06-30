<?php
/*
Plugin Name: Nikolaus Vermittler Map
Description: Plugin, welches die Hauptkarte anzeigt mit Shortcode: [nikolaus_plugin_main] auf Seite einfügen. Unter Einstellungen die Permalinks auf "Einfach" stellen (WICHTIG!!!).
Version: 1.0
Author: Philipp Lippold
Author URI: https://lippold-it.de/
Min WP Version: 1.5
Max WP Version: 5.0.4
*/




function getUser_Data_search(){
$user_logged_in=is_user_logged_in();
$current_user = wp_get_current_user();
//Wenn der Nutzer eingeloggt ist, wird sein letzter Login Timestamp aktualisiert.
if($user_logged_in){
  update_user_meta( $current_user->ID, 'last_login', time() );
  update_user_meta( $current_user->ID, 'mail', 'no' );
}
$User_data = array();
$users = get_users( array( 'fields' => array( 'ID' ) ) );
foreach($users as $user_id){
 
if(get_user_meta($user_id->ID, 'nikolaus', true )=="YES"){
/*Array voller Arrays welches jeweils die wichtigen Daten enthält.
  Ist der User ein Nikolaus wird er in diesem Array aufgenommen.
  dann wird gefiltert ob er registriert ist oder nicht.

*/

array_push($User_data,$array = array( 
  get_userdata($user_id->ID)->user_login,
  $user_id->ID,
  get_the_author_meta( 'profile_picture', $user_id->ID ),
  get_author_posts_url($user_id->ID) ,
  get_user_meta($user_id->ID, 'latitude', true ),
  get_user_meta($user_id->ID, 'longitude', true ),
  get_the_author_meta( 'tag1', $user_id->ID ),
  get_the_author_meta( 'tag2', $user_id->ID ),
  get_the_author_meta( 'tag3', $user_id->ID ),
  get_the_author_meta( 'tag4', $user_id->ID ),
  get_the_author_meta( 'tag5', $user_id->ID ),
  get_the_author_meta( 'tag6', $user_id->ID ),
  get_the_author_meta( 'tag7', $user_id->ID ),
  get_the_author_meta( 'tag8', $user_id->ID ),
  "nikolaus",
  get_the_author_meta( 'radius', $user_id->ID ),
  get_the_author_meta( 'city', $user_id->ID ),
  get_the_author_meta( 'kein_zertifikat', $user_id->ID )));
/*
[0]login_name
[1]user Id
[2]profile_pic_url
[3]user_profile_url
[4]latitude
[5]longtitude
[6]tag1
[7]tag2
[8]tag3
[9]tag4
[10]tag5
[11]tag6
[12]tag7
[13]tag8
[14]Nikolaus
[15]radius
[16]stadt
[17]zertifiziert
[18]last login
*/
}
}
return $User_data;
}

function mapload(){ 

 
?>
<head>
<link rel="stylesheet" type="text/css" href="wp-content/plugins/nk_plugin/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="wp-content/plugins/nk_plugin/font-awesome/css/font-awesome.min.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="wp-content/plugins/nk_plugin/bootstrap/js/bootstrap.min.js">
</script>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
<link rel="stylesheet" href="wp-content/plugins/nk_plugin/assets/css/start.css" >

  <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
  <meta charset="utf-8">
  <title>Suche
  </title>
</head>
<body>
    <?php
    if(is_user_logged_in()){
    echo'
     <span id="requests" onclick="showOverlay()"></span><h6 id="requests1" onClick="showOverlay()"> neue  Freischaltungsanfrage/n </h6></br>
     <div id="overlay">
     <span id="closeoverlay" onClick="hideOverlay()">X</span>
     <div id="overlaycontainer">
     <ul id="freischaltungen">
    <button class="btn-basic"  id="deleteallr">Alle Freischaltungen löschen</button>
        
    </ul>
    </div>
    </div>
    ';
    }
    ?>
  <input id="pac-input" class="controls" type="text" placeholder="Search Box">
  <div id="map">
  </div> 
  <ul class="grid">
    <h5>Arbeitsbedingungen/orte
    </h5>
    <li>
      <div class="tags" data-toggle="buttons" >
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-hands-helping"></i>
          <input class="tags" type="radio" name="ehrenamtlich"  >
          Ehrenamtlich
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-hands-helping"></i>
          <input  type="radio" name="ehrenamtlich" >
          Ehrenamtlich
        </label>           
      </div>
    </li>
    <li>
      <div class="tags" data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-hospital"></i>
          <input class="tags" type="radio" name="krankenhaus"  >
          Krankenhäuser
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-hospital"></i>
          <input  type="radio" name="krankenhaus"  >
          Krankenhäuser
        </label>      
      </div>
    </li>
    <li>   
      <div class="tags" data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-glass-martini"></i>
          <input class="tags" type="radio" name="familie"  >
          Familienfeste
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-glass-martini"></i>
          <input  type="radio"  name="familie">
          Familienfeste
        </label>         
      </div>
    </li>
    <li>
      <div class="tags"  data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-store"></i>
          <input class="tags" type="radio" name="kaufhaus"  >
          Kaufhäuser
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-store"></i>
          <input  type="radio"   name="kaufhaus"  >
          Kaufhäuser
        </label>         
      </div>
    </li>
    <li>
      <div class="tags"  data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-church"></i>
          <input class="tags" type="radio" name="kirche"  >
          Kirchen
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-church"></i>
          <input  type="radio" name="kirche" >
          Kirchen
        </label>         
      </div>
    </li>
    <li>
      <div class="tags"  data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-school"></i>
          <input class="tags" type="radio" name="schulen"  >
          Schulen
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-school"></i>
          <input  type="radio"  name="schulen"  >
          Schulen
        </label>         
      </div>
    </li>
    <li>
      <div class="tags"  data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-building"></i>
          <input class="tags" type="radio" name="stadt" >
          Stadt
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-building"></i>
          <input  type="radio"   name="stadt" >
          Stadt
        </label>         
      </div>
    </li>
    <li>
      <div class="tags"  data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
        <i class="fas fa-star"></i>
          <input class="tags" type="radio" name="andere"  >
          Andere
        </label>
        <label class="btn btn-lg btn-success active">
        <i class="fas fa-star"></i>
          <input  type="radio" name="andere" >
          Andere
        </label>         
      </div>
    </li>
    <li>
      <div class="tags"  data-toggle="buttons">
        <label class="btn btn-lg btn-danger ">
          <input class="tags" type="radio" name="zertifiziert"  >
          Zertifiziert
        </label>
        <label class="btn btn-lg btn-success active">
          <input  type="radio"   name="zertifiziert"  >
          Zertifiziert
        </label>         
      </div>
    </li>
  </ul>
  <input type="text" id="myInput"  placeholder="Nach Stadt suchen..">
  <input id="number" type="number"  max="300" step="5" placeholder="Reichweite in KM max. 300">
  <input type='button' id="search"  class="button-primary" value="<?php _e( 'Suchen', 'textdomain' ); ?>" />
  <input type='button' id="newsearch"  class="button-primary" value="<?php _e( 'Neue Suche', 'textdomain' ); ?>" /><br/>
               
  <ul id="myUL">
  </ul>
  <script>
    var map;
    var markers = [];
    var circles = [];
    var search=true;
    var icons= [];
    var tags=[];
    //SUCHE UND FILTERUNG AB HIER
    var js_user_data;
    var ul;
    var li;

//USER nicht freischalten
function nounlock_user(user_id,target_id){
jQuery.ajax({
type: "POST",
url: 'wp-content/plugins/nk_plugin/nounlock.php',
data: {id_target: target_id,user_id: user_id},
success: function(data){
},
error: function(XMLHttpRequest, textStatus, errorThrown) { 
alert("User wurde nicht nicht freigeschaltet Status: " + textStatus); alert("Error: " + errorThrown); 
}    
});
}
//Alle Freischaltungen widerrufen
function deleteallrequests(user_id){
  if(confirm("Möchten Sie wirklich alle Freischaltungen wiederrufen?")){
jQuery.ajax({
type: "POST",
url: 'wp-content/plugins/nk_plugin/deleteallrequests.php',
data: {id_target: user_id},
success: function(data){
},
error: function(XMLHttpRequest, textStatus, errorThrown) { 
alert("User wurde nicht gemeldet Status: " + textStatus); alert("Error: " + errorThrown); 
}    
});
}
}

//Freischalten von User
function unlock_user(user_id,target_id){
  if(confirm("Möchten Sie Ihr Profil freigaben für User:"+target_id+"?")){
    jQuery.ajax({
    type: "POST",
    url: 'wp-content/plugins/nk_plugin/unlock_user.php',
    data: {id_target: target_id,user_id: user_id},
    success: function(data){
  },
  error: function(XMLHttpRequest, textStatus, errorThrown) { 
  alert("User wurde nicht Freigegeben Status: " + textStatus); alert("Error: " + errorThrown); 
  }});}}

//Freischalt Overlay verstecken oder zeigen
function hideOverlay(){
  document.getElementById("overlay").style.display="none";
}

function showOverlay(){
  document.getElementById("overlay").style.display="block";
}


    $( document ).ready(function() {

   
//Für Freischaltungen
 
<?php 
           $current_user = wp_get_current_user();
           $current_user_id = $current_user->ID;
           
           ?>

           var current_user_id = <?php echo  $current_user_id; ?>;
           if(document.getElementById("deleteallr")!=null)
          document.getElementById("deleteallr").onclick= function(){ deleteallrequests(current_user_id)};

        var data = <?php 
        
        if(empty(get_the_author_meta( 'unlock_requests',  $current_user ->ID )))
        {
            echo "[]";
        }
        else{
          //$data=get_the_author_meta( 'unlock_requests',  $current_user ->ID );
        echo json_encode(get_the_author_meta( 'unlock_requests',  $current_user ->ID ));   
    }
        ?>;



        var href_array= <?php
              if(empty(get_the_author_meta( 'unlock_requests',  $current_user ->ID )))
              {
                  echo "[]";
              }
              else{
                $data=get_the_author_meta( 'unlock_requests',  $current_user ->ID );
              
                $href=array();
               for($i=0;$i<count($data);$i=$i+1){
                  array_push($href,get_author_posts_url($data[$i]));
              }
              echo json_encode( $href);   
          }
        
     
      
        ?>

        if(href_array.length>0){
        if(document.getElementById("requests")!=null)
        document.getElementById("requests").style.color="red";
      }
      //Auf null prüfen, da wenn nicht eingeloggt die Elemente nicht per Echo geliefert werden und dann Excepttion geworfen werden
      if(document.getElementById("requests")!=null)
      document.getElementById("requests").innerHTML=href_array.length;

      //Alle Freischalt elemente Erstellen
      for(var i=0;i<data.length;i++){
           var ul=document.getElementById("freischaltungen");
           var button_unlock = document.createElement("button"); 
           var button_deny = document.createElement("button");
           var user_link = document.createElement("a");
           user_link.target="_blank";
           user_link.href=href_array[i];
           user_link.innerHTML="User: "+data[i]+" ";
           user_link.className="unlocklinks";
           ul.style.listStyleType="none";
           button_unlock.className += "btn ";
           button_unlock.className += "btn-info ";
           button_unlock.className += "btn-xs ";
           button_deny.className += "btn ";
           button_deny.className += "btn-warning ";
           button_deny.className += "btn-xs ";
           var li = document.createElement("li");
           button_unlock.innerHTML="Freischalten User:"+ data[i];
           button_deny.innerHTML="Verweigern";
           button_deny.type="button";
           button_unlock.type="button";
          
           li.append(user_link,button_unlock,button_deny);
           ul.append(li);
           var data_post = data[i];
           button_unlock.onclick= function(){ unlock_user(current_user_id,data_post)};
           button_deny.onclick= function(){ nounlock_user(current_user_id,data_post)};


        }
        
//Für Freischaltungen

  $('.btn-danger').attr('disabled',true);
  $('.btn-success').attr('disabled',true);
      
 function deleteMarkersAndCircles() {
       
        if (markers) {
	        for (var i=0; i < markers.length; i++) {
            markers[i].setMap(null);
            circles[i].setMap(null);
	        }
          markers.length = 0;
          circles.length = 0;
	    }
  }


                           
      function showUserOnMap(){ 
      var ul= $("#myUL");
      var li= $( ul ).find( "li:visible" );
      //Alle Marker entfernen durch entfernen der Referenz
      deleteMarkersAndCircles()
        for(var i=0;i<li.length;i++){
          var image_url =     $(li[i]).attr('pic');
          var working_radius= $(li[i]).attr('radius');
          var displayed_name=  $(li[i]).attr('name');;
          var user_url=       $(li[i]).attr('profile');
          var user_lat=       $(li[i]).attr('lat');
          var user_lng=       $(li[i]).attr('lng');
          if(!(user_lat=='-1'||user_lat=='-1'))
          {
            var icon = {
              url: image_url, // url
              scaledSize: new google.maps.Size(42, 50), // scaled size
              origin: new google.maps.Point(0,0), // origin
              anchor: new google.maps.Point(21, 25) // anchor
            };
            var marker=new google.maps.Marker({
              map: map,
              icon: icon,
              title: displayed_name,
              url: user_url,
              position:new google.maps.LatLng(user_lat,user_lng)
            }
                                             );
            var areaCircle = new google.maps.Circle({
              draggable: false,
              editable: false,
              fillColor: '#004de8',
              fillOpacity: 0.04,
              strokeColor: '#004de8',
              strokeOpacity: 1,
              strokeWeight: 0.8,
              map: map,
              center: new google.maps.LatLng(user_lat,user_lng)
              ,
              radius:   (working_radius / 6378.1) * 6378100
            }
                                                   );
            google.maps.event.addListener(marker, 'click', function() {
              window.open(this.url,'_blank');
            }
                                         );
            markers.push(marker);
            circles.push(areaCircle);
            icons.push(icon);
          }
        }
      }






      $(".btn").click( function(event) {
      event=event.target;
      var x=$(event).find('input')[0];
      var tag_name=$(x).attr('name');
      var insert_elem=true;
      for(var i=0;i<tags.length;i++){
          if(tags[i]==tag_name){
            tags.splice(i, 1);   
            insert_elem=false;
          }
      }
      if(insert_elem)
        tags.push(tag_name);
      Filtertags();
      });
     

      function Filtertags(){
        //Jquerey Operatoren hier dringend nötig, da ich nur mit sichtbaren Werten in der Liste weiter Filtern möchte
        var li= document.getElementsByClassName('in_city_radius');
        for (j = 0; j < li.length; j++) {
          var tag_includ=false;
          var classListe = li[j].classList;
          for(var k=0;k<tags.length;k++){
            if(classListe.length<tags.length){
              tag_includ=false;
              break;
            }
            if(!(classListe[k]=='in_city_radius')){
                if(classListe.contains(tags[k])){
                    tag_includ=true;
                }
            else{
              tag_includ=false;
              break;
            }
            }
          }
          if(tag_includ){
            li[j].style.display="block";
          }
          else{
            li[j].style.display="none";
          }
          if(tags.length==0){
            li[j].style.display="block";
          }
        }
      showUserOnMap();
      }



$(window).on("load", function (e) {

reset();
showUserOnMap();

});




function deleteInput(){
  this.value='';
}

function search() {
 return $.ajax({
    url: "wp-content/plugins/nk_plugin/cityarea.php",  
    data: {
            cityname: $('#myInput').val(),
            radius: $('#number').val()
        },
    type: 'POST',
    success: function(response) {
         result = response;
      } 
  });
  
}


//Bei Neue suche reseten
$("#newsearch").click( function(event) {
reset();
});



function reset(){
  $('#newsearch').hide();
  $('#search').show();

tag=[];
tags.length=0;

$('li').removeClass('in_city_radius');
$('.btn-danger').removeClass('active');
$('.btn-success').addClass('active');
$('.btn-danger').attr('disabled',true);
$('.btn-success').attr('disabled',true);
ul = document.getElementById("myUL");
li = ul.getElementsByTagName('li');
for (i = 0; i < li.length; i++) {
    li[i].style.display = "list-item";   
}
showUserOnMap();
}



$("#search").click( function(event) {
  $('#search').hide();
  $('#newsearch').show();
  $('.btn-danger').attr('disabled',false);
  $('.btn-success').attr('disabled',false);


search().done(function(result) {
    //console.log(result);
    var myArray = JSON.parse(result);
    Filtercityarea(myArray);

}).fail(function(result) {
    alert("Ein Fehler ist aufgetreten");
});



   });



function Filtercityarea(result){
      $(window).scrollTop(0);
      var results=result;
      var ul= $("#myUL");
      var li= $( ul ).find( "li:visible" );
      var radius=results[results.length-1];
      var zoom=6;
      map.setCenter(new google.maps.LatLng(results[results.length-3],results[results.length-2]));
      
      switch (true) {
        // in Die Karte hereinzoomen, je nachdem wie Groß der Radius der Suche ist
      case radius <= 20:
      zoom=11;
    break;
  case radius >= 20 && radius <= 40:
      zoom=10.2;
    break;
  case radius >= 40 && radius <= 60:
      zoom=9.4;
    break;
    case radius >= 60 && radius <= 80:
      zoom=8.7;
    break;
    case radius >= 80 && radius <= 100:
      zoom=8.3;
    break;
    case radius >= 100 && radius <= 150:
      zoom=7;
    break;
    case radius >= 150 && radius <= 250:
      zoom=6.5;
    break;
  
  default:
    break;
      }
      map.setZoom(zoom);
 
  
      // Durch alle Listen elemente Loopen und diese anzeigen, welche dem Suchmuster entsprechen
      for (i = 0; i < li.length; i++) {
        if(results.includes($(li[i]).attr('city'))){
          li[i].style.display = "list-item";
          li[i].classList.add("in_city_radius");
        }
        else
        {
          li[i].style.display = "none";
        }
        
      }
      showUserOnMap();
    
}

      var ul = document.getElementById("myUL");
      js_user_data = <?php echo json_encode(getUser_Data_search());
      ?>;
      for(var i=0;i<js_user_data.length;i++){
        var elem1 = document.createElement("li");
        var elem2 = document.createElement("a");
        var elem3 = document.createElement("img");
        var elem4 = document.createElement("span");
        var elem5 = document.createElement("img");
        var elem6 = document.createElement("img");
        var elem7 = document.createElement("span");
        var elem8 = document.createElement("span");
        var city= js_user_data[i][16];
        var size="fa-2x";
        for(var o=6;o<18;o++){
          switch(js_user_data[i][o]) {
            case 'YES':
              if(o==6){
                elem1.classList.add("ehrenamtlich");
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer arbeitet Ehrenamtlich";
                tagsymbol.classList.add("fa-hands-helping");
                elem8.append(tagsymbol);

                
              }
              if(o==7){
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer arbeitet gern in Krankenhäusern";
                tagsymbol.classList.add("fa-hospital");
                elem1.classList.add("krankenhaus");
                elem8.append(tagsymbol);
              }
              if(o==8){
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer arbeitet gern auf Familienfeste";
                tagsymbol.classList.add("fa-glass-martini");
                elem1.classList.add("familie");
                elem8.append(tagsymbol);
              }
              if(o==9){
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer arbeitet gern in Kaufhäusern";
                tagsymbol.classList.add("fa-store");
                elem1.classList.add("kaufhaus");
                elem8.append(tagsymbol);
              }
              if(o==10){
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer arbeitet gern in Kirchen";
                tagsymbol.classList.add("fa-church");
                elem1.classList.add("kirche");
                elem8.append(tagsymbol);
              }
              if(o==11){
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer arbeitet gern in Schulen";
                tagsymbol.classList.add("fa-school");
                elem1.classList.add("schulen");
                elem8.append(tagsymbol);
              }
              if(o==12){
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer arbeitet gern in Stadt";
                tagsymbol.classList.add("fa-building");
                elem1.classList.add("stadt");
                elem8.append(tagsymbol);
              }
              if(o==13){
                var tagsymbol=document.createElement("i");
                tagsymbol.classList.add("fas");
                tagsymbol.classList.add(size);
                $(tagsymbol).attr("data-toggle", "tooltip");
                tagsymbol.title="Dieser Nutzer hat andere Präferenzen";
                tagsymbol.classList.add("fa-star");
                elem1.classList.add("andere");
                elem8.append(tagsymbol);
              }
              break;
           case 'false':
           if(o==17)
              elem1.classList.add("zertifiziert");
              break;
          }
        }
        elem2.href=js_user_data[i][3];
        elem2.target="_blank";
        elem3.src=js_user_data[i][2];
        elem3.style="width:30px;";
        elem3.id="profil_pic";
        elem3.alt="Profil-Foto";
        elem4.style.width="100px";
        elem1.setAttribute('pic', js_user_data[i][2]);
        elem1.setAttribute('name', js_user_data[i][0]);
        elem1.setAttribute('lat', js_user_data[i][4]);
        elem1.setAttribute('lng', js_user_data[i][5]);
        elem1.setAttribute('radius', js_user_data[i][15]);
        elem1.setAttribute('profile', js_user_data[i][3]);
        elem1.setAttribute('city', js_user_data[i][16]);
        elem4.style.display="inline-block";
        var username=js_user_data[i][0];
        if(username.length>10)
        {
          username= username.slice(0,10)+"...";
        }
        elem4.innerHTML=username;
        elem4.id="elem4";
        elem3.className ="li_imgs";
        elem5.style.display="none";
        elem5.style.backgroundColor="transparent";
        elem6.style.backgroundColor="transparent";
        elem6.style.display="none";
        elem6.style.float="right";
        elem5.alt="is_zertifiziert?"
        $(elem5).attr("data-toggle", "tooltip");
        $(elem6).attr("data-toggle", "tooltip");
        elem5.title="Dieser Nutzer ist ein Nikolaus";
        elem6.title="Dieser Nutzer ist ein zertifizierter Nikolaus";
        elem6.alt="is_nikolaus?";
        elem5.style.float="right";
        elem8.style.fontSize="10px";
        elem8.style.marginLeft="2%";
        elem8.style.color="#000099";
        if(city.length>12)
          city = city.substring(0, 12)+'..';
        elem7.innerHTML="Stadt: "+city;
        elem7.id="cityspan";
        elem7.style.position="inherit";
        if(js_user_data[i][17]=='false'){
            elem6.style.display="inline";
            elem6.src="./wp-content/plugins/nk_plugin/assets/certified.png"
            elem6.id="certified_img";
        }
        elem2.append(elem3,elem4,elem6,elem7,elem8);
        elem1.append(elem2);
        ul.append(elem1);
      }


         
    } );
   



    //MAP LADEN ABHIER::
    
      function initAutocomplete() {
       
       map = new google.maps.Map(document.getElementById('map'), {
        center: {
          lat: 50.11667, lng: 8.83333}
        ,
        zoom: 6,
        mapTypeId: 'roadmap'
      }
                                   );
      // Create the search box and link it to the UI element.
      var input = document.getElementById('pac-input');
      var searchBox = new google.maps.places.SearchBox(input);
      map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
      // Bias the SearchBox results towards current map's viewport.
      map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
      }
                     );



      searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();
        if (places.length == 0) {
          return;
        }
        // Clear out the old markers.
        /*markers.forEach(function(marker) {
            marker.setMap(null);
          });
          markers = [];
*/
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
          }
          else {
            bounds.extend(place.geometry.location);
          }
        }
                      );
        map.fitBounds(bounds);
      }
                           );
    }
  </script>
  <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyC9wMbx-lXmvZMEmrv0aaFzC-pK4JMhazg&libraries=places&callback=initAutocomplete'
          async defer>
  </script>
</body>
<?php
} 
add_shortcode('nikolaus_plugin_main', 'mapload' );
?>