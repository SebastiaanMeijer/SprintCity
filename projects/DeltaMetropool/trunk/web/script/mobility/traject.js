function Traject(i) {
    this.i = i;
    
    this.traject = document.createElement('div');
    this.trajectTitle = document.createElement('div');
    this.trajectLijn = document.createElement('div');
    
    this.traject.setAttribute('class', 'traject');
    this.trajectTitle.setAttribute('class', 'traject-title');
    this.trajectLijn.setAttribute('class', 'traject-lijn');
 
    this.yellowLineEnd = 0;
 
    this.init();
}

Traject.prototype.init = function() {
    this.draw();
    
    $('#trajecten-container').append(this.traject);
}

Traject.prototype.draw = function() {
    this.writeTrainLabel();   
    this.drawRouteBackground();
    this.writeIUstuff(this.i, this.traject);
}
    
Traject.prototype.writeTrainLabel = function() {
    var i = this.i;


    var trainType = document.createElement('h1');
    trainType.innerHTML = trains[i].name;

    var trainRoute = document.createElement('h2');
    trainRoute.innerHTML = "" + trains[i].beginStation + " -<br/>" + trains[i].endStation;

    this.trajectTitle.appendChild(trainType);
    this.trajectTitle.appendChild(trainRoute);

    this.traject.appendChild(this.trajectTitle);
}
	
Traject.prototype.drawRouteBackground = function() {

    
                
    this.drawGrayBoxes(this.trajectLijn);
        
    this.drawYellowLine(this.trajectLijn);
		
    this.traject.appendChild(this.trajectLijn);
}
    
Traject.prototype.drawGrayBoxes = function(trajectLijn) {
    for (var i=0; i < stations.length; i++) {
            
        /* draw the gray boxes */
        var grayBox = document.createElement('div');
        grayBox.setAttribute('class', 'gray-box');
        var offset = getDistanceBetweenStations() * i;
        $(grayBox).css({
            left: offset
        });
        trajectLijn.appendChild(grayBox);
    };
}
    
Traject.prototype.drawYellowLine = function(trajectLijn) {
    /* draw the yellow line */
    var yellowLine = document.createElement('div');
    yellowLine.setAttribute('class', 'yellow-line');
    var offsetYellowLineBegin = getDistanceBetweenStations();
    var yellowLineWidth = offsetYellowLineBegin * stations.length - offsetYellowLineBegin;
    
    this.yellowLineEnd = offsetYellowLineBegin + yellowLineWidth;
    
    $(yellowLine).css({
        width: yellowLineWidth
    });
    trajectLijn.appendChild(yellowLine);
}
    
Traject.prototype.writeIUstuff = function() {
    var offset = this.yellowLineEnd + 50;
    offset = '' + offset + 'px';
    
    var textBox = document.createElement('div');
    $(textBox).css({
        position: 'absolute',
        top: '17px',
        left: offset,
        fontSize: '10px'
    });
    $(textBox).append(trains[this.i].avgIU);
    this.traject.appendChild(textBox);
}
	
function getDistanceBetweenStations() {
    var canvasSize = 786;
    //magic number, i know, but its canvas size minus indent
    var xDistance = Math.round(canvasSize / stations.length); 
    return xDistance;
}