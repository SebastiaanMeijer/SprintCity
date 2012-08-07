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

