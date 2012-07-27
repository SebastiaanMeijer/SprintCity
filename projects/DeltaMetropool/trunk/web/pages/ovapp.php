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

	function Train(name, beginStation, endStation, stationStops, avgIU, maxIU, minIU) {
		this.name = name;
		this.beginStation = beginStation;
		this.endStation = endStation;

		this.stationStops = stationStops;
		//array e.g. [0,0,2,2,0,2]

		this.avgIU = avgIU;
		this.maxIU = maxIU;
		this.minIU = minIU;
	}

	/* ========================================================= */
	/* Initialization */

	var stations = new Array();
	var trains = new Array();
	//global variable within the ovapp scope

	initMockStations(stations);
	initMockTrains(trains);

	function initMockStations(stations) {
		stations.push(new Station("Den Haag CS", 10, 60, 123));
		stations.push(new Station("Den Haag HS", 30, 140, 10));
		stations.push(new Station("Den Haag Moerwijk", 20, 50, 60));
		stations.push(new Station("Rijswijk", 4220, 60, 90));
		stations.push(new Station("Delft", 12, 200, 180));
		stations.push(new Station("Delft zuid", 134, 20, 40));
		stations.push(new Station("Schiedam Kethel", 14, 40, 50));
		stations.push(new Station("Schiedam Centraal", 40, 10, 170));
		stations.push(new Station("Rotterdam Centraal", 70, 200, 50));
	}

	function initMockTrains(trains) {
		trains.push(new Train("IC", "Amsterdam", "Breda", [0, 2, 0, 2, 2, 0, 0, 0, 2], 1436, 1656, 994));
		trains.push(new Train("IC", "Amsterdam", "Vlissingen", [0, 2, 0, 0, 2, 0, 0, 0, 2], 1285, 1480, 888));
		trains.push(new Train("Benelux", "Amsterdam", "Brussel", [0, 1, 1, 0, 0, 0, 0, 0, 1], 2245, 2758, 1285));
		trains.push(new Train("Sprinter", "Utrecht", "Dordrecht", [0, 2, 2, 2, 2, 2, 2, 2, 2], 1244, 1742, 1045));
		trains.push(new Train("IC", "Den Haag CS", "Venlo", [2, 2, 0, 0, 2, 0, 0, 0, 2], 984, 1357, 814));
		trains.push(new Train("Sprinter", "Den Haag CS", "Roosendaal", [2, 2, 2, 2, 2, 2, 2, 2, 2], 996, 1376, 825));
	}

</script>
<script type="text/javascript">
	/* Put the trains on the track */
	$(document).ready(function() {	
		for (var i = 0; i < trains.length; i++) {
			writeTrainLabels(i);
		};
		
		fixDistancesBetweenStations();
	});
	function writeTrainLabels(i) {
		var traject = document.createElement('div');
		traject.setAttribute('class', 'traject');

		var trajectTitle = document.createElement('div');
		trajectTitle.setAttribute('class', 'traject-title');

		var trainType = document.createElement('h1');
		trainType.innerHTML = trains[i].name;

		var trainRoute = document.createElement('h2');
		trainRoute.innerHTML = "" + trains[i].beginStation + " -<br/>" + trains[i].endStation;

		trajectTitle.appendChild(trainType);
		trajectTitle.appendChild(trainRoute);

		traject.appendChild(trajectTitle);

		/* trajectlijn */
		drawRouteBackground(traject);

		$('#trajecten-container').append(traject);
	}
	
	function drawRouteBackground(traject) {
		var trajectLijn = document.createElement('div');
		trajectLijn.setAttribute('class', 'traject-lijn');
		
		for (var i=0; i < stations.length; i++) {
		  var grayBox = document.createElement('div');
		  grayBox.setAttribute('class', 'gray-box');
		  trajectLijn.appendChild(grayBox);
		};
		
		traject.appendChild(trajectLijn);
	}
	
	function fixDistancesBetweenStations() {
		var canvasSize = 786;
		//magic number, i know, but its canvas size minus indent
		var xDistance = Math.round(canvasSize / stations.length) - 16; 
		$('.gray-box').css('margin-right', xDistance);
	}
</script>
<!-- HTML STUFF -->

<div id="grafiek">
	<canvas id="graphCanvas" width=886 height=250></canvas>
</div>
<div id="trajecten-container">
</div>