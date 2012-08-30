function Train(id, name, beginStation, endStation, stationStops, avgIU) {
    this.id = id;
    this.name = name;
    this.beginStation = beginStation;
    this.endStation = endStation;

    this.stationStops = stationStops;
    //array e.g. [0,0,2,2,0,2]

    this.avgIU = avgIU;
    this.maxIU = Math.round(this.avgIU * 1.1);
    this.minIU = Math.round(this.avgIU * 0.9);
}

var fillTrainArray = function(data) {
    for (i = 0; i < data.length; i++) {
    	
        trains[data[i].id] = new Train(
            data[i].id, 
            data[i].name,
            data[i].beginStation, 
            data[i].endStation, 
            data[i].stationStops, 
            data[i].avgIU);
            
    }
         startApp();
}

Train.initTrains = function() {
    Load.loadTrains(fillTrainArray);      
}