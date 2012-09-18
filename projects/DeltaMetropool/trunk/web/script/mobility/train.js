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
        for (i = 0; i < data['trains'].length; i++) {
            for (j = 0; j < data['trains'][i].stationStops.length; j++) {
                if (trains[data['trains'][i].id].stationStops[j] != data['trains'][i].stationStops[j]) {
                    // if server and client do not match, request it again, and do not update trains on the client
                    Load.refreshAll();
                    return;
                }
            }
        }
    }
    
    trains = new Array();
    for (i = 0; i < data['trains'].length; i++) {
        trains[data['trains'][i].id] = new Train(
            data['trains'][i].id, 
            data['trains'][i].name,
            data['trains'][i].route, 
            data['trains'][i].stationStops, 
            data['trains'][i].currentAvgIU,
            data['trains'][i].minAvgIU,
            data['trains'][i].maxAvgIU
            );
    }
    $('#trajecten-container').empty();
    startApp();
}

Train.initTrains = function() {
    Load.loadAll(fillTrainArray);
}

Train.refreshTrains = function() {
    Load.loadAll(fillTrainArray);
}