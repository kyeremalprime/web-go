var gap = 50;
var canvBg = document.getElementById("board_background");
var ctxBg = canvBg.getContext("2d");
var backgroundObj = new Image();
backgroundObj.src = "./img/wood.jpg";
backgroundObj.onload = function() {
    var pattern = ctxBg.createPattern(backgroundObj, 'no-repeat');
    ctxBg.fillStyle = pattern;
    ctxBg.fillRect(0, 0, 1000, 1000);
}

var canvLines = document.getElementById("board_lines");
var ctxLines = canvLines.getContext("2d");
var m = 19;
for (var i = 1; i <= m; i++) {
    ctxLines.beginPath();
    ctxLines.moveTo(i*gap, gap);
    ctxLines.lineTo(i*gap, m*gap);
    ctxLines.closePath();
    ctxLines.lineWidth = (i==1 || i==19) ? 3 : 1;
    ctxLines.stroke();
}
for (var i = 1; i <= m; i++) {
ctxLines.beginPath();
    ctxLines.moveTo(gap, i*gap);
    ctxLines.lineTo(m*gap, i*gap);
    ctxLines.closePath();
    ctxLines.lineWidth = (i==1 || i==19) ? 3 : 1;
    ctxLines.stroke();
}

var canvDots = document.getElementById("board_dots");
var ctxDots = canvDots.getContext("2d");
var radius = 5;
ctxDots.beginPath();
ctxDots.arc(4*gap, 4*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(4*gap, 10*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(4*gap, 16*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(10*gap, 4*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(10*gap, 10*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(10*gap, 16*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(16*gap, 4*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(16*gap, 10*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.arc(16*gap, 16*gap, radius, 0, 2*Math.PI);
ctxDots.closePath();
ctxDots.fill();
