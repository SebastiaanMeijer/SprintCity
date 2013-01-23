window.Graph = window.Graph || {};

/* ========================================================= */
/* Constants */

var CANVAS_WIDTH = $("#graphCanvas").width();
var CANVAS_HEIGHT = $("#graphCanvas").height();
var APP_INDENT = 100;
var GRAPH_WIDTH = CANVAS_WIDTH - APP_INDENT;
var GRAPH_HEIGHT = 193;
var GRAPH_BLOCK_WIDTH = 7;
var GRAPH_BLOCK_MARGIN = 3;
var GRAPH_BLOCK_OFFSET = GRAPH_BLOCK_WIDTH + GRAPH_BLOCK_MARGIN;

var blockColor = new HsbColor(240, .14, .84);
var blockColorLight = new HsbColor(240, .03, .91);
var pinkColor = new HsbColor(327, .98, .86);

/* Final variables */
var blockContainerWidth = 0;

var firstGraphBlockCenter = 0;

/* ========================================================= */

Graph.init = function(data) {

    Station.fillStations(data);
    
    blockContainerWidth = Math.round((GRAPH_WIDTH) / stations.length);
    firstGraphBlockCenter = APP_INDENT + GRAPH_BLOCK_WIDTH * .5;
    Graph.clearPaper();
    Graph.drawGraph();
    Train.initTrains();
}


Graph.drawGraph = function(){
    var layer = new Layer();
    layer.activate();
    
    drawStationsGraph(stations);
    drawStationNetworkValue(stations);
    drawTravelersPerDayValue(stations);
    drawStationTags(stations);
    drawStationNames(stations);
    
    view.draw(); /* Fixes bug that doesn't draw before mouse movement */
}

function drawStationsGraph(stations) {
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
        var x = APP_INDENT  + blockContainerWidth * i;
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
            addTextNextToPoint(capOverPoint, '110,00%', 'red');
            addTextNextToPoint(cap100Point, '100,00%', 'green');
            addTextNextToPoint(capUnderPoint, '90,00%', 'blue');
        }

    }
    project.activeLayer.insertChild(-1, capPaths);
//put capPath in the front layer

}

function addTextNextToPoint(point, text, color) {
    var capOverTextPoint = point.clone();
    capOverTextPoint.x += 35;
    capOverTextPoint.y += 8;
    
    var fontSize;
    
    if (stations.length > 10) {
        fontSize = 10;
        text = text.replace(",00%", "%");
        capOverTextPoint.x -= 5;
    }
    else {
        fontSize = 10;
    }
    
    var capOverText = new PointText(capOverTextPoint);
    capOverText.justification = 'center';
        
    capOverText.characterStyle = {
        fillColor : color,
        fontSize : fontSize,
        font : 'arial'
    };
    capOverText.content = text;
}

function drawStationNetworkValue(stations) {
    // label
    var textPoint = getCenteredStationPoint(0, 16);
    textPoint.x -= 104;
    var labelText = new PointText(textPoint);
    labelText.justification = 'left';
    labelText.characterStyle = {
        fontSize : 11,
        fillColor : 'black'
    };
    labelText.content = 'Netwerkwaarde:';
    
    // values
    for (var i = 0; i < stations.length; i++) {
        var textPoint = getCenteredStationPoint(i, 16);

        var networkValueText = new PointText(textPoint);
        networkValueText.justification = 'center';
        networkValueText.characterStyle = {
            fontSize : 11,
            fillColor : 'black'
        };
        networkValueText.content = stations[i].networkValue;
    }
}


function drawTravelersPerDayValue(stations) {
    // label
    var textPoint = getCenteredStationPoint(0, 32);
    textPoint.x -= 104;
    var labelText = new PointText(textPoint);
    labelText.justification = 'left';
    labelText.characterStyle = {
        fontSize : 11,
        fillColor : 'black'
    };
    labelText.content = 'Reizigers/dag:';
    
    // values
    for (var i = 0; i < stations.length; i++) {
        var textPoint = getCenteredStationPoint(i, 32);

        var networkValueText = new PointText(textPoint);
        networkValueText.justification = 'center';
        networkValueText.characterStyle = {
            fontSize : 11,
            fillColor : 'black'
        };
        networkValueText.content = stations[i].totalTravelers;
    }
}

function drawStationTags(stations) {
    var rectWidth = GRAPH_WIDTH / stations.length - GRAPH_BLOCK_MARGIN * 4;
	
    var rectSize = new Size(rectWidth, 8);

    for (var i = 0; i < stations.length; i++) {
        var tagPoint = getCenteredStationPoint(i, 36, rectSize);
        var tagRectangle = new Rectangle(tagPoint, rectSize);
        var tagCornerSize = new Size(4, 4);
        var path = new Path.RoundRectangle(tagRectangle, tagCornerSize);

        /* determine color */
        if (stations[i].currentIU > stations[i].capOver) {
            path.fillColor = '#ea4d4d';
        // path.strokeColor = '#cb0000';
        } else if (stations[i].currentIU < stations[i].capUnder) {
            path.fillColor = '#5bb4ff';
        // path.strokeColor = '#008aff';
        } else {
            path.fillColor = '#33d130';
        // path.strokeColor = '#04aa00';
        }
        project.activeLayer.insertChild(0, path);

    }
}

function drawStationNames(stations) {
    $('#station-names').empty();
    $('#station-names').append('<div class="station-label" style="width:' + getLeftMargin() + 'px;">&nbsp;</div>');
    for (var i = 0; i < stations.length; i++) {
       $('#station-names').append('<div class="station-label" style="width:' + getStationBlockWidth() + 'px;">' + stations[i].name + '</div>');
    };
}

/* ========================================================= */
/* help functions */

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

function getCenteredStationPoint(i, margin, rectSize) {
    var x = firstGraphBlockCenter + blockContainerWidth * i;
    if (rectSize != null) {
        x -= rectSize.width / 2;
    }
    var marginFromGraph = margin;
    var y = GRAPH_HEIGHT + marginFromGraph;
    var point = new Point(x, y);

    return point;
}

function getLeftMargin() {
    return firstGraphBlockCenter - blockContainerWidth / 2;
}

function getStationBlockWidth() {
    return blockContainerWidth;
}

Graph.clearPaper = function(){
    project.activeLayer.remove();
}


/* Execute here */
$(document).ready(function() {
    Load.loadAll(Graph.init);
});
