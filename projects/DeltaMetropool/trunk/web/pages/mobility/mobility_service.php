<?php

// Get post variables that load.js gives

if (isset($_POST['get'])) {

    if ($_POST['get'] == 'stations') {
        $stations = array(
            array("name" => "Den Haag CS", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Den Haag HS", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Den Haag Moerwijk", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Rijswijk", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Delft", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Delft Zuid", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Schiedam Kethel", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Schiedam Centraal", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Rotterdam Centraal", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1)
        );
        echo json_encode($stations);
    }
    
    elseif ($_POST['get'] == 'trains') {
        $trains = array(
            array("id" => "1", "name" => "Sneltrein", "beginStation" => "Amsterdam", "endStation" => "Breda", 
                "stationStops" => array(0,2,0,2,2,0,0,0,2), "avgIU" => "1436"),
            array("id" => "545", "name" => "Int.", "beginStation" => "Amsterdam", "endStation" => "Brussel", 
                "stationStops" => array(0,2,0,0,0,0,0,0,2), "avgIU" => "1226")
        );
        
        echo json_encode($trains);
    }
}



?>
