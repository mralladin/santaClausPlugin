<?php 






if(empty($_POST['cityname'])){
    $cityname='Deutschland';
}
else{
    $cityname = $_POST['cityname']; 
}



 $geocodeObject = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyC9wMbx-lXmvZMEmrv0aaFzC-pK4JMhazg&address='.$cityname.',germany'), true);


// latutude und longtitude vom objekt speichern
$latitude = $geocodeObject['results'][0]['geometry']['location']['lat'];
$longitude = $geocodeObject['results'][0]['geometry']['location']['lng'];
//$latitude='49.755405';        Trier Test
//$longitude='6.643762';

// set request options
$response = 'short'; // welche länge die antwort haben soll
$size = 'cities15000'; // die minimale anzahl an einwohner eine stadt haben muss



if(empty($_POST['radius'])){
    $radius='100';
}
else{
    $radius = $_POST['radius']; //  radius in KM
}
if($_POST['radius']>=280){
    $radius='280';
}







$maxRows = 1300; // maximale anzahl an reihen welche zurückgegeben werden sollen
$username = 'lippoldp'; 

// liefert alle städte als array zurück, welche in dieses suchschema passen
$nearbyCities = json_decode(file_get_contents('http://api.geonames.org/findNearbyPlaceNameJSON?lat='.$latitude.'&lng='.$longitude.'&style='.$response.'&cities='.$size.'&radius='.$radius.'&maxRows='.$maxRows.'&username='.$username, true));

//print_r($nearbyCities);

$a=array();




foreach($nearbyCities->geonames as $cityDetails)
{
    array_push($a,$cityDetails -> name);
}
array_push($a,$latitude);
array_push($a,$longitude);
array_push($a,$radius);




echo json_encode($a);







?>