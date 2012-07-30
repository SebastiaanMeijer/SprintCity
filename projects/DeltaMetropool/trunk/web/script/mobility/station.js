function Station(name, networkValue, currentIU, cap100) {
    this.name = name;
    this.networkValue = networkValue;

    this.prevIU = 50;
    this.currentIU = currentIU;
    this.progIU = 100;

    this.cap100 = cap100;
    this.capOver = this.cap100 * 1.25;
    this.capUnder = this.cap100 * 0.75;
}


Station.prototype.setCurrentIU = function(newIU) {
    this.prevIU = this.currentIU;
    this.currentIU = newIU;

/* TODO: prognose here */
};

/* static function */
Station.initMockStations = function(stationArray) {
    stationArray.push(new Station("Den Haag CS", 10, 60, 123));
    stationArray.push(new Station("Den Haag HS", 30, 140, 10));
    stationArray.push(new Station("Den Haag Moerwijk", 20, 50, 60));
    stationArray.push(new Station("Rijswijk", 4220, 60, 90));
    stationArray.push(new Station("Delft", 12, 200, 180));
    stationArray.push(new Station("Delft zuid", 134, 20, 40));
    stationArray.push(new Station("Schiedam Kethel", 14, 40, 50));
    stationArray.push(new Station("Schiedam Centraal", 40, 10, 170));
    stationArray.push(new Station("Rotterdam Centraal", 70, 200, 50));
        
    return stationArray;
}