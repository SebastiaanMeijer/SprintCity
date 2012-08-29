var Load = Load || {};

Load.loadStations = function(callback) {
    /* send AJAX request */
    $.post('pages/mobility/mobility_service.php',
    {
        get: 'stations'
    },
    callback, 'json'
    );
           
}

Load.loadTrains = function(callback) {
    /* send AJAX request */
    $.post('pages/mobility/mobility_service.php',
    {
        get: 'trains'
    },
    callback, 'json'
    );
    
}