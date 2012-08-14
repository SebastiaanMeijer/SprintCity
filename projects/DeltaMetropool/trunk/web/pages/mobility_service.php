<?php

// Get post variables that load.js gives

if (isset($_POST['get'])) {

    if ($_POST['get'] == 'stations') {
        $stations = array(
            array("name" => "Den Haag CS", "networkValue" => 100, "currentIU" => 60, "cap100" => 123),
            array("name" => "Den Haag HS", "networkValue" => 30, "currentIU" => 140, "cap100" => 90),
            array("name" => "Den Haag Moerwijk", "networkValue" => 20, "currentIU" => 50, "cap100" => 60),
            array("name" => "Rijswijk", "networkValue" => 20, "currentIU" => 60, "cap100" => 90),
            array("name" => "Delft", "networkValue" => 12, "currentIU" => 200, "cap100" => 180),
            array("name" => "Delft Zuid", "networkValue" => 134, "currentIU" => 20, "cap100" => 40),
            array("name" => "Schiedam Kethel", "networkValue" => 12, "currentIU" => 40, "cap100" => 50),
            array("name" => "Schiedam Centraal", "networkValue" => 40, "currentIU" => 10, "cap100" => 170),
            array("name" => "Rotterdam Centraal", "networkValue" => 70, "currentIU" => 200, "cap100" => 50)
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
