function Train(id, name, beginStation, endStation, stationStops, avgIU, maxIU, minIU) {
    this.id = id;
    this.name = name;
    this.beginStation = beginStation;
    this.endStation = endStation;

    this.stationStops = stationStops;
    //array e.g. [0,0,2,2,0,2]

    this.avgIU = avgIU;
    this.maxIU = maxIU;
    this.minIU = minIU;
}

Train.initMockTrains = function(trainArray) {
    trainArray.push(new Train(1, "IC", "Amsterdam", "Breda", [0, 2, 0, 2, 2, 0, 0, 0, 2], 1436, 1656, 994));
    trainArray.push(new Train(2, "IC", "Amsterdam", "Vlissingen", [0, 2, 0, 0, 2, 0, 0, 0, 2], 1285, 1480, 888));
    trainArray.push(new Train(3, "Benelux", "Amsterdam", "Brussel", [0, 1, 1, 0, 0, 0, 0, 0, 1], 2245, 2758, 1285));
    trainArray.push(new Train(4, "Sprinter", "Utrecht", "Dordrecht", [0, 2, 2, 2, 2, 2, 2, 2, 2], 1244, 1742, 1045));
    trainArray.push(new Train(5, "IC", "Den Haag CS", "Venlo", [2, 2, 0, 0, 2, 0, 0, 0, 2], 984, 1357, 814));
    trainArray.push(new Train(6, "Sprinter", "Den Haag CS", "Roosendaal", [2, 2, 2, 2, 2, 2, 2, 2, 2], 996, 1376, 825));        
    
    return trainArray;
}