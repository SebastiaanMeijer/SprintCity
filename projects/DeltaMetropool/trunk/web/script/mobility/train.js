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

Train.initTrains = function(trainArray) {
    Load.loadTrains(trainArray);      
    
    return trainArray;
}