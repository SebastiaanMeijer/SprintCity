<script type="text/javascript" src="script/mobility/paperjs/paper.js"></script>
<script type="text/javascript" src="script/mobility/station.js"></script>
<script type="text/javascript" src="script/mobility/train.js"></script>
<script type="text/javascript" src="script/mobility/traject.js"></script>
<script type="text/paperscript" src="script/mobility/paperjs/ovapp.js" canvas="graphCanvas"></script>
<script type="text/javascript">

    /* ========================================================= */
    /* Initialization */

    var stations = new Array();
    var trains = new Array();
    //global variable within the ovapp scope

    stations = Station.initMockStations(stations);
    trains = Train.initMockTrains(trains);

</script>
<script type="text/javascript">
    /* Put the trains on the track */
    $(document).ready(function() {	
        for (var i = 0; i < trains.length; i++) {
            new Traject(i);
        };
	
    });
    
   
</script>
<!-- HTML STUFF -->

<div id="grafiek">
    <canvas id="graphCanvas" width=886 height=250></canvas>
</div>
<div id="trajecten-container">
</div>