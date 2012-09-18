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
    console.log("got train data back");
    
    // check if the server date matches the client data
    if (trains.length > 0) {
        for (i = 0; i < data.length; i++) {
            for (j = 0; j < data[i].stationStops.length; j++) {
                if (trains[data[i].id].stationStops[j] != data[i].stationStops[j]) {
                    // if server and client do not match, request it again, and do not update trains on the client
                    Station.refreshStations();
                    Train.refreshTrains();
                    return;
                }
            }
        }
    }
    
    trains = new Array();
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
    $('#trajecten-container').empty();
    startApp();
}

Train.initTrains = function() {
    Load.loadTrains(fillTrainArray);      
}

Train.refreshTrains = function() {
    Load.loadTrains(fillTrainArray);
}