<script type="text/javascript" src="script/mobility/paperjs/paper.js"></script>
<script type="text/javascript" src="script/mobility/station.js"></script>
<script type="text/javascript" src="script/mobility/train.js"></script>
<script type="text/javascript" src="script/mobility/traject.js"></script>
<script type="text/paperscript" src="script/mobility/paperjs/ovgraph.js" canvas="graphCanvas"></script>
<script type="text/javascript">

    /* ========================================================= */
    /* Initialization */
    var READ_ONLY = false;

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
        
        if (!READ_ONLY){
            $('.train-stop').click(function(){
                handleTrainStopClick(this);
            });
        
            $('.traject-title').click(function(){
                handleTrainClick(this);
            });
        }
    });
    
    function handleTrainStopClick(trainStop) {        
        var stopNum = $(trainStop).text();
        if (stopNum == 0) {
            $(trainStop).removeClass('invisible');
        }
        if (stopNum < 7){
            stopNum++;
            $(trainStop).text(stopNum);
        }
        else {
            stopNum = 0;
            $(trainStop).addClass('invisible');
            $(trainStop).text("");
        }
        
        /* prognose */
    }
    
    function handleTrainClick(trainTitle) {
        /* Go inside container div and look for all divs with class train-stop
         * and fire handleTrainStopClick for each one.
         */
        
        $(trainTitle).parent().children('.traject-lijn').children('.train-stop').each(function() {

            $(this).animate({color: 'black'}, 20, "swing", function(){
                $(this).animate({color: '#f0098d'}, 20, "swing");
            });
            handleTrainStopClick(this);
            
        });
        
        
    }
</script>
<!-- HTML STUFF -->

<div id="grafiek">
    <canvas id="graphCanvas" width=886 height=250></canvas>
</div>
<div id="trajecten-container">

</div>