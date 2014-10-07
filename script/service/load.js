var Load = Load || {};

Load.loadAll = function(callback) {

    $.ajax({
        url:   'pages/mobility/mobility_service.php',
        data: {
            get: 'all'
        },
        success: callback,
        error: function() {
          console.log("Error in loading stations. Trying again...");  
          Load.loadAll(callback);
        },
        dataType: 'json',
        timeout: 100000
    });
}

Load.refreshAll = function() {
    Load.loadAll(refreshCallback);
}

var refreshCallback = function(data) {
    fillTrainArray(data);
    fillStationsAndDraw(data);
}

Load.loadOVAmbition = function(callback) {
    /* send AJAX request */
    $.post('pages/mobility/mobility_value.php',
    {
        
        },
        callback, 'json'
        );
}