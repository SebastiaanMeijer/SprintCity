<script type="text/javascript" src="script/mobility/paperjs/paper.js"></script>
<script type="text/paperscript" src="script/mobility/paperjs/ovapp.js" canvas="graphCanvas"></script>
<script type="text/javascript">
	function Station(name, networkValue, currentIU, cap100) {
		this.name = name;
		this.networkValue = networkValue;

		this.prevIU = 50;
		this.currentIU = currentIU;
		this.progIU = 100;

		this.cap100 = cap100;
		this.capOver = this.cap100 * 1.25;
		this.capUnder = this.cap100 * 0.75;
	}


	Station.prototype.setCurrentIU = function(newIU) {
		this.prevIU = this.currentIU;
		this.currentIU = newIU;

		/* TODO: prognose here */
	};

	function Train(name, route, stationStops, avgIU, maxIU, minIU) {
		this.name = name;
		this.route = route;

		this.stationStops = stationStops;
		//array e.g. [0,0,2,2,0,2]

		this.avgIU = avgIU;
		this.maxIU = maxIU;
		this.minIU = minIU;
	}

	/* ========================================================= */
	/* Initialization */

	var stations = new Array();
	//global variable within the ovapp scope

	initMockStations(stations);

	function initMockStations(stations) {
		stations.push(new Station("Den Haag CS", 10, 120, 123));
		stations.push(new Station("Den Haag HS", 30, 140, 10));
		stations.push(new Station("Den Haag Moerwijk", 20, 50, 60));
		stations.push(new Station("Rijswijk", 4220, 60, 90));
		stations.push(new Station("Delft", 12, 200, 180));
		stations.push(new Station("Delft zuid", 134, 20, 40));
		stations.push(new Station("Schiedam Kethel", 14, 40, 50));
		stations.push(new Station("Schiedam Centraal", 40, 10, 170));
		stations.push(new Station("Rotterdam Centraal", 70, 200, 50));


	}
</script>

<!-- HTML STUFF -->

<div id="grafiek">
	<canvas id="graphCanvas" width=886 height=250></canvas>
</div>
<div id="trajecten-container">
	<div class="traject">
		<title>IC<span>Amsterdam - Breda</span></title>
		<div class="traject-lijn"></div>
		<div class="traject-gem-iu">
			<!-- 										1656<span>1436</span>994 -->
		</div>
	</div>
</div>