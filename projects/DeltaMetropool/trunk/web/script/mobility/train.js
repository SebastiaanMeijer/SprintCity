function Train(id, name, route, stationStops, currentAvgIU, minAvgIU, maxAvgIU) {
    this.id = id;
    this.name = name;
    this.route = route;

    this.stationStops = stationStops;
    //array e.g. [0,0,2,2,0,2]

    this.currentAvgIU = currentAvgIU;
    this.maxIU = maxAvgIU;
    this.minIU = minAvgIU;
}

Train.prototype.setStationStop = function(index, value) {
	this.stationStops[index] = value;
}

var fillTrainArray = function(data) {
    for (i = 0; i < data.length; i++) {
    	
        trains[data[i].id] = new Train(
            data[i].id, 
            data[i].name,
            data[i].route, 
            data[i].stationStops, 
            data[i].currentAvgIU,
            data[i].minAvgIU,
            data[i].maxAvgIU
        
    );
            
    }
         startApp();
}

Train.initTrains = function() {
    Load.loadTrains(fillTrainArray);      
}