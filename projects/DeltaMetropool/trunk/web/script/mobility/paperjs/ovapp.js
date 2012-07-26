/* AJAX TEST */
// var time = Math.random();
// $.post("pages/testAjax.php", {man: "Frans", time: time},
// function(data) {
// console.log(data);
// });




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
var pinkColor = new HsbColor(327, .98, .86);

/* ========================================================= */
/* Execute from here */

$(document).ready(function() {

	drawStationsGraph(stations);
	
});



function drawStationsGraph(stations) {
	var blockContainerWidth = (GRAPH_WIDTH) / stations.length;
	var capPath = new Path();
	var capOverPath = new Path();
	var capUnderPath = new Path();

	capOverPath.strokeColor = blockColorLight;
	capUnderPath.strokeColor = blockColorLight;
	capPath.strokeColor = pinkColor;

	capOverPath.strokeJoin = 'round';
	capUnderPath.strokeJoin = 'round';

	var capPaths = new Group([capPath, capOverPath, capUnderPath]);

	/* determine the largest point in graph */
	var champHeight = getChampHeight(stations);

	for (var i = 0; i < stations.length; i++) {
		/* currentIU block */
		var x = APP_INDENT + GRAPH_BLOCK_WIDTH * 2 + blockContainerWidth * i;
		var y = GRAPH_HEIGHT;
		var width = GRAPH_BLOCK_WIDTH;
		var currentIUHeight = (stations[i].currentIU / champHeight) * GRAPH_HEIGHT;
		var prevIUHeight = (stations[i].prevIU / champHeight) * GRAPH_HEIGHT;
		var progIUHeight = (stations[i].progIU / champHeight) * GRAPH_HEIGHT;
		var cap100Height = (stations[i].cap100 / champHeight) * GRAPH_HEIGHT;
		var capOverHeight = (stations[i].capOver / champHeight) * GRAPH_HEIGHT;
		var capUnderHeight = (stations[i].capUnder / champHeight) * GRAPH_HEIGHT;

		var rect = new Rectangle(new Point(x, y), new Point(x + width, y - currentIUHeight));
		var prevRect = new Rectangle(new Point(x, y), new Point(x + width, y - prevIUHeight));
		var progRect = new Rectangle(new Point(x, y), new Point(x + width, y - progIUHeight));

		var block = new Path.Rectangle(rect);
		var prevBlock = new Path.Rectangle(prevRect);
		var progBlock = new Path.Rectangle(progRect);
		block.fillColor = blockColor;

		/* prevIU block */
		prevBlock.fillColor = blockColorLight;
		prevBlock.position.x -= GRAPH_BLOCK_OFFSET;

		/* progIU block */
		progBlock.fillColor = blockColorLight;
		progBlock.position.x += GRAPH_BLOCK_OFFSET;

		/* Capacity path */
		var cap100Point = new Point(x + (GRAPH_BLOCK_WIDTH / 2), y - cap100Height);
		var cap100Circle = new Path.Circle(cap100Point, 5);
		cap100Circle.fillColor = pinkColor;
		cap100Circle.strokeColor = 'white';
		cap100Circle.strokeWidth = 3;
		capPath.add(cap100Point);

		/* Over capacity path */
		var capOverPoint = new Point(x + (GRAPH_BLOCK_WIDTH / 2), y - capOverHeight);
		capOverPath.add(capOverPoint);

		/* Under capacity path */
		var capUnderPoint = new Point(x + (GRAPH_BLOCK_WIDTH / 2), y - capUnderHeight);
		capUnderPath.add(capUnderPoint);

		//last one, add text
		if (i == (stations.length - 1)) {
			addTextNextToPoint(capOverPoint, '125,00%', 'red');
			addTextNextToPoint(cap100Point, '100,00%', 'green');
			addTextNextToPoint(capUnderPoint, '75,00%', 'blue');
		}

	};
	project.activeLayer.insertChild(-1, capPaths);
	//put capPath in the front layer

}

function addTextNextToPoint(point, text, color) {
	var capOverTextPoint = point.clone();
	capOverTextPoint.x += 35;
	capOverTextPoint.y += 8;
	
	var capOverText = new PointText(capOverTextPoint);
	capOverText.justification = 'center';
	capOverText.characterStyle = {
		fillColor : color,
		fontSize : 10,
		font : 'arial'
	};
	capOverText.content = text;
}

function getChampHeight(stations) {
	var champHeight = 0;
	for (var i = 0; i < stations.length; i++) {
		if (stations[i].currentIU > champHeight) {
			champHeight = stations[i].currentIU;
		}

		if (stations[i].prevIU > champHeight) {
			champHeight = stations[i].prevIU;
		}

		if (stations[i].progIU > champHeight) {
			champHeight = stations[i].progIU;
		}

		if (stations[i].capOver > champHeight) {
			champHeight = stations[i].capOver;
		}
	}
	return champHeight;
}

