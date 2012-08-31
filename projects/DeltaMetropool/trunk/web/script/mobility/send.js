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

Send.sendAmbition = function(callback) {
    
    $("#ambition-form input[type='checkbox']:checked").each(
        function(){
            var id = $(this).val();
            var motivation = $('textarea#motivatie').val();
        
            $.post('pages/mobility/mobility_sendvalue.php',
            {
                valueInstanceId: id,
                motivation: motivation
            },
            callback, 'json'
            );
        }
        );
}