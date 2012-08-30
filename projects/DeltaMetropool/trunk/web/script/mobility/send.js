var Send = Send || {};
/**
 * @author fpvanagthoven
 */

Send.sendTrain = function(trainId, stationStops, callback) {
	  /* send AJAX request */
    $.post('pages/mobility/mobility_service.php',
    {
        trainId: trainId,
        stationStops: stationStops
    },
    callback, 'json'
    );
}
