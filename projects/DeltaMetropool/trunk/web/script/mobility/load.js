var Load = Load || {};

Load.loadStations = function(callback) {

    $.ajax({
        url:   'pages/mobility/mobility_service.php',
        data: {
            get: 'stations'
        },
        success: callback,
        error: function() {
          console.log("Error in loading stations. Trying again...");  
          Load.loadStations(callback);
        },
        dataType: 'json',
        timeout: 100000
    });
           
}

Load.loadTrains = function(callback) {
    /* send AJAX request */
    $.ajax({
        url:   'pages/mobility/mobility_service.php',
        data: {
            get: 'trains'
        },
        success: callback,
        error: function() {
          console.log("Error in loading trains. Trying again...");  
              Load.loadStations(Graph.init);

          
        },
        dataType: 'json',
        timeout: 100000
    });
    
}

Load.loadAmbition = function(callback) {
    /* send AJAX request */
    $.post('pages/mobility/mobility_value.php',
    {
        
        },
        callback, 'json'
        );
}