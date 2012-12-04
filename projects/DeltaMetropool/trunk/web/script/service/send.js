var Send = Send || {};

Send.sendTrain = function(trainId, stationStops, callback) {
    console.log(trainId + " | Sending train..." + stationStops);
    /* send AJAX request */
    $.post('pages/mobility/mobility_service.php',
    {
        trainId: trainId,
        stationStops: stationStops
    },
    function(data) {},
    'json'
    );
}

Send.sendOVAmbition = function(callback) {
    
    $("#ambition-form input[type='checkbox']:checked").each(
        function(){
            var id = $(this).val();
            var motivation = $('textarea#motivatie').val();
            if (motivation == "Vul hier je motivatie in... " || motivation == "") {
                motivation = "[Geen motivatie ingevuld]";
            }
        
            $.post('pages/mobility/mobility_sendvalue.php',
            {
                valueInstanceId: id,
                motivation: motivation
            },
            function(data){},
            'json'
            );
        }
        );
}

Send.addFacility = function() {
    if (confirm('Bouw een ' + $("#facility-form #bonus-bouw option:selected").text() + ' in de buurt van ' + $("#facility-form #bonus-stations option:selected").text() + '?')) {
        $.post(
            'pages/province/add_facility.php',
            {
                stationId: $("#facility-form #bonus-stations").val(),
                facilityId: $("#facility-form #bonus-bouw").val()
            },
            function(data) {
                $("#facility-form .log").append(data);
            },
            'json'
        );
    }
}

Send.addRestriction = function() {
    $.post(
        'pages/province/add_restriction.php',
        {
            stationId: $("#restriction-form #bonus-stations").val(),
            typeId: $("#restriction-form #penalty").val()
        },
        function(data)
        {
            $("#restriction-form .log").html(data);
        },
        'text'
    );
}

Send.removeRestriction = function(stationId, typeId) {
    $.post(
        'pages/province/remove_restriction.php',
        {
            stationId: stationId,
            typeId: typeId
        },
        function(data)
        {
            $("#restriction-form .log").html(data);
        },
        'text'
    );
}
