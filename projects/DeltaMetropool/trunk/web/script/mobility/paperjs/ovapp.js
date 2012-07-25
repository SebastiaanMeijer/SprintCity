/* AJAX TEST */
// var time = Math.random();
// $.post("pages/testAjax.php", {man: "Frans", time: time},
// function(data) {
// console.log(data);
// });

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
/* Constants */

var OVAPP_WIDTH = $("#ovapp").width();
var APP_INDENT = 87;
var GRAPH_WIDTH = OVAPP_WIDTH - APP_INDENT;
var GRAPH_HEIGHT = 193;
var GRAPH_BLOCK_WIDTH = 7;
var GRAPH_BLOCK_MARGIN = 3;
var GRAPH_BLOCK_OFFSET = GRAPH_BLOCK_WIDTH + GRAPH_BLOCK_MARGIN;

var blockColor = new HsbColor(240, .14, .84);
var blockColorLight = new HsbColor(240, .03, .91);

/* ========================================================= */
/* Execute from here */

$(document).ready(function() {
	var stations = new Array();

	initMockStations(stations);
	drawStationsGraph(stations);
});

function initMockStations(stations) {
	stations.push(new Station("Den Haag CS", 10, 120, 153));
	stations.push(new Station("Den Haag HS", 30, 140, 100));
	stations.push(new Station("Den Haag Moerwijk", 20, 50, 60));
	stations.push(new Station("Rijswijk", 40, 60, 90));
	stations.push(new Station("Delft", 12, 200, 120));
	stations.push(new Station("Delft zuid", 14, 20, 40));
	stations.push(new Station("Schiedam Kethel", 14, 40, 50));
	stations.push(new Station("Schiedam Centraal", 40, 210, 170));
	stations.push(new Station("Rotterdam Centraal", 70, 200, 500));
}

function drawStationsGraph(stations) {
	var blockContainerWidth = (GRAPH_WIDTH) / stations.length;

	/* determine the largest block */
	var champHeight = 0;
	for (var i = 0; i < stations.length; i++) {
		if (stations[i].currentIU > champHeight) {
			champHeight = stations[i].currentIU;
		}
		
		if(stations[i].prevIU > champHeight) {
			champHeight = stations[i].prevIU;
		}
		
		if(stations[i].progIU > champHeight) {
			champHeight = stations[i].progIU;
		}
	}

	for (var i = 0; i < stations.length; i++) {
		/* currentIU block */
		var x = APP_INDENT + GRAPH_BLOCK_WIDTH * 2 + blockContainerWidth * i;
		var y = GRAPH_HEIGHT;
		var width = GRAPH_BLOCK_WIDTH;
		var currentIUHeight = (stations[i].currentIU / champHeight) * GRAPH_HEIGHT;
		var prevIUHeight = (stations[i].prevIU / champHeight) * GRAPH_HEIGHT;
		var progIUHeight = (stations[i].progIU / champHeight) * GRAPH_HEIGHT;

		var rect = new Rectangle(new Point(x, y), new Point(x + width, y - currentIUHeight));
		var prevRect = new Rectangle(new Point(x, y), new Point(x + width, y - prevIUHeight));
		var progRect = new Rectangle(new Point(x, y), new Point(x + width, y - progIUHeight));
		
		var block = new Path.Rectangle(rect);
		var prevBlock = new Path.Rectangle(prevRect);
		var progBlock = new Path.Rectangle(progRect);
		block.fillColor = blockColor;
		
		prevBlock.fillColor = blockColorLight;
		prevBlock.position.x -= GRAPH_BLOCK_OFFSET;		
		
		progBlock.fillColor = blockColorLight;
		progBlock.position.x += GRAPH_BLOCK_OFFSET;
		
		/* prevIU block */
		
		
		/* progIU block */
		
	};

}

