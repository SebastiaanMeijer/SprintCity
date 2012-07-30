function Train(name, beginStation, endStation, stationStops, avgIU, maxIU, minIU) {
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
    trainArray.push(new Train("IC", "Amsterdam", "Breda", [0, 2, 0, 2, 2, 0, 0, 0, 2], 1436, 1656, 994));
    trainArray.push(new Train("IC", "Amsterdam", "Vlissingen", [0, 2, 0, 0, 2, 0, 0, 0, 2], 1285, 1480, 888));
    trainArray.push(new Train("Benelux", "Amsterdam", "Brussel", [0, 1, 1, 0, 0, 0, 0, 0, 1], 2245, 2758, 1285));
    trainArray.push(new Train("Sprinter", "Utrecht", "Dordrecht", [0, 2, 2, 2, 2, 2, 2, 2, 2], 1244, 1742, 1045));
    trainArray.push(new Train("IC", "Den Haag CS", "Venlo", [2, 2, 0, 0, 2, 0, 0, 0, 2], 984, 1357, 814));
    trainArray.push(new Train("Sprinter", "Den Haag CS", "Roosendaal", [2, 2, 2, 2, 2, 2, 2, 2, 2], 996, 1376, 825));        
    
    return trainArray;
}