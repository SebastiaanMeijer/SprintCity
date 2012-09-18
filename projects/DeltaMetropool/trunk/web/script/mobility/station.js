function Station(name, networkValue, prevIU, currentIU, progIU, cap100, capOver, capUnder) {
    this.name = name;
    this.networkValue = networkValue;

    this.prevIU = prevIU;
    this.currentIU = currentIU;
    this.progIU = progIU;

    this.cap100 = cap100;
    
    if (capOver != -1 && capUnder != -1) {
        this.capOver = capOver;    
        this.capUnder = capUnder;
    }
    else {
        this.capOver = this.cap100 * 1.25;
        this.capUnder = this.cap100 * 0.75;
    }
}


Station.prototype.setCurrentIU = function(newIU) {
    this.prevIU = this.currentIU;
    this.currentIU = newIU;

/* TODO: prognose here */
};

Station.fillStations = function(data) {
    stations = new Array();
    
    for (i = 0; i < data['stations'].length; i++) {
            
        stations.push(
            new Station(
                data['stations'][i].name, 
                data['stations'][i].networkValue,
                data['stations'][i].prevIU,
                data['stations'][i].currentIU,
                data['stations'][i].progIU,
                data['stations'][i].cap100,
                data['stations'][i].capOver,
                data['stations'][i].capUnder
                ));
    }
}

/* public callback function */
var fillStationsAndDraw = function(data) {  
    console.log("got station data back");
    Graph.clearPaper();
    Station.fillStations(data);
    Graph.drawGraph();
    
}



/* static function */
Station.refreshStations = function() {
    Load.loadAll(fillStationsAndDraw);
}
