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
    for (i = 0; i < data.length; i++) {     
            
        stations.push(
            new Station(
                data[i].name, 
                data[i].networkValue,
                data[i].prevIU,
                data[i].currentIU,
                data[i].progIU,
                data[i].cap100,
                data[i].capOver,
                data[i].capUnder
            ));  
            
    }
}

/* public callback function */
fillStationsAndDraw = function(data) {  
    
    Graph.clearPaper();
    Station.fillStations(data);
    Graph.drawGraph();
    
}



/* static function */
Station.refreshStations = function() {

    stations = new Array();
    Load.loadStations(fillStationsAndDraw);
}
