<?php
    $sgf = file_get_contents("./src/go.sgf");
    $change = file_get_contents("./src/change.json");
    $comments = file_get_contents("./src/comments.json");
    $x = array();
    $y = array();
    $a = explode(";", $sgf);
    $a = array_slice($a, 2, sizeof($a)-2);
    for ($i = 0; $i < sizeof($a); $i++) {
        $x[$i] = ord($a[$i][2]) - 97;
        $y[$i] = ord($a[$i][3]) - 97;
    }
    $data = (array)(json_decode($change));
    $changedNum = array();
    for ($i = 1; $i <= sizeof($a); $i++) {
        if (array_key_exists($i, $data)) {
            $data[$i] = (array)($data[$i]);
            $changedNum[$i] = sizeof($data[$i]);
        }
        else $changedNum[$i] = 0;
    }
    $word = (array)(json_decode($comments));
?>


<!DOCTYPE html>
<html>

<head>
    <title>go</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" />
    <link rel="stylesheet" href="./css/style.css" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</head>

<body>

<div class="container_go">
    <canvas id="board_background" width="1000" height="1000"></canvas>
    <canvas id="board_lines" width="1000" height="1000"></canvas>
    <canvas id="board_dots" width="1000" height="1000"></canvas>
    <canvas id="board_pieces" width="1000" height="1000"></canvas>
</div>


<div class="controler_go">

    <form class="form-inline">
        <button type="button" class="btn btn-primary" onclick="getPre()" style="margin-left: 42px">Previous (A)</button>
        <div class="form-group" style="margin-left: 80px">
            <input type="number" class="form-control" id="number_pieces" style="width: 100px" value="0" >
            <div class="input-group input-group-addon" id="total" style="font-size: 35px; margin-left: 10px"></div>
        </div>
        <button type="button" class="btn btn-danger" onclick="toNum()" style="margin-left: 50px; width: 200px" >Goto</button>
        <button type="button" class="btn btn-primary" onclick="getNext()" style="width: 200px" >Next (D)</button>
    </form>

    <div class="comment_go">
        <textarea id="cmts" class="form-control" rows="3" style="font-size: 28px; width: 700px"></textarea>
    </div>

    <div class="change_go">
        <select size="4" multiple class="form-control" style="width: 300px; font-size: 21px" >
            <option id="opt_0" selected="true" onclick="choose0()">当前局面</option>
            <option id="opt_1" onclick="choose1()">变化1: </option>
            <option id="opt_2" onclick="choose2()">变化2: </option>
            <option id="opt_3" onclick="choose3()">变化3: </option>
        </select>
    </div>

</div>

<!---
<div class="controler_go">
    <button class="btn btn-primary" onclick="getPre()">Pre</button>
    <input type="number" class="form-control" id="number_pieces" value="0">
    <label id="total"></label>
    <button class="btn btn-danger" color="primary" onclick="toNum()">Goto</button>
    <button class="btn btn-primary" onclick="getNext()" >Next</button>
</div>
-->

<script>
    
    var gap = 50;
    var position = new Array();
<?php
    echo "    var goNum = ", sizeof($x), ";\n";
    for ($i = 0; $i < sizeof($x); $i++) {
        echo "    position[$i] = [", $x[$i], ", ", $y[$i], "];\n";
    }
?>

    var changedNum = new Array();
    changedNum[0] = 0;
    for (var i = 1; i <= goNum; i++) changedNum[i] = 0;

<?php
    //init all changedNum = 0
    for ($k = 1; $k <= sizeof($a); $k++) {
        if($changedNum[$k] != 0)
            echo "    changedNum[$k] = ", $changedNum[$k], ";\n";
    }
?>

    var comments = new Array();
    for (var i = 0; i <= goNum; i++) {
        comments[i] = "";
    }

<?php
    for ($i = 0; $i <= sizeof($a); $i++) {
        if (array_key_exists($i, $word))
            echo "    comments[$i] = \"", $word[$i], "\";\n";
    }
?>
    $("#cmts").text(comments[0]);

    var changedDetails = new Array();
    var changedPlayedCount = new Array();
    for (var i = 1; i <= goNum; i++) {
        changedPlayedCount[i] = new Array();
        for (var j = 1; j <= changedNum[i]; j++) {
            changedPlayedCount[i][j] = 0;
        }
    }
    for (var i = 1; i <= goNum; i++) {
        changedDetails[i] = new Array();
        for (var j = 1; j <= changedNum[i]; j++) {
            changedDetails[i][j] = new Array();
            for (var k = 0; k < changedPlayedCount[i][j]; k++) {
                changedDetails[i][j][k] = [0, 0, 0];
            }
        }
    }

<?php
    // init all changedPlayerCount = 0
    for ($k = 1; $k <= sizeof($a); $k++) {
        for ($i = 1; $i <= $changedNum[$k]; $i++) {
            $changedA = explode(";", $data[$k][$i]);
            echo "    changedPlayedCount[$k][$i] = ", sizeof($changedA), ";\n";
        }
    }
   for ($k = 1; $k <= sizeof($a); $k++) {
        for ($i = 1; $i <= $changedNum[$k]; $i++) {
            $changedA = explode(";", $data[$k][$i]);
            for ($j = 0; $j < sizeof($changedA); $j++) {
                echo "    changedDetails[$k][$i][$j] = ", "[", $changedA[$j][0]=='B'?1:-1, ", ", ord($changedA[$j][2]) - 97, ", ", ord($changedA[$j][3]) - 97, "];\n";
            }
        }
   }
?>

    var canvPieces = document.getElementById("board_pieces");
    var ctxPieces = canvPieces.getContext("2d");
/*    function putPiecesOnCanvas(x, y, color) {*/
        //if (color == 1) {
            //var blackObj = new Image();
            //blackObj.src = "./img/black.png";
            //blackObj.onload = function() {
                //ctxPieces.drawImage(blackObj, x*gap - 0.5*gap, y*gap - 0.5*gap);
            //}
        //}
        //else {
            //var whiteObj = new Image();
            //whiteObj.src = "./img/white.png";
            //whiteObj.onload = function() {
                //ctxPieces.drawImage(whiteObj, x*gap - 0.5*gap, y*gap - 0.5*gap);
            //}
        //}
    /*}*/
    function putPiecesOnCanvas(x, y, color, num) {
        var imgObj = new Image();
        imgObj.src = (color==1) ? "./img/black.png" : "./img/white.png";
        imgObj.onload = function() {
            ctxPieces.drawImage(imgObj, x*gap - 0.5*gap, y*gap - 0.5*gap);
            if (num != 0) {
                ctxPieces.fillStyle = (color==1) ? "white" : "black";
                ctxPieces.font = "34px 'Miama'";
                ctxPieces.textAlign = "center";
                ctxPieces.fillText(num.toString(), x*gap, y*gap + 12);
            }
        }
    }

/*    fuction putNumberOnPieces(x, y, color, num) {*/
        //ctxPieces.fillstyle = "red";
        //ctxPieces.font = "60px";
        //ctxPieces.textAlign = "center";
        //ctxPieces.fillText(num.toString(), x*gap - 0.5*gap, y*gap - 0.5*gap);
    /*}*/

    function clearPiecesOnCanvas(x, y) {
        var whiteObj = new Image();
        whiteObj.src = "./img/white.png";
        whiteObj.onload = function() {
            ctxPieces.fillRect(x*gap - 0.5*gap, y*gap - 0.5*gap, gap, gap);
            ctxPieces.clearRect(x*gap - 0.5*gap, y*gap - 0.5*gap, gap, gap);
        }
    }

    document.onkeydown = function(e) {
        if((e||event).keyCode==68) getNext();
        if((e||event).keyCode==65) getPre();
    };

</script>

<script>
    $("#total").html("/ " + goNum.toString());
    $("#opt_1").hide();
    $("#opt_2").hide();
    $("#opt_3").hide();
    var situation = new Array();
    var vis = new Array();
    var current = [0, 0];
    situation[0] = new Array();
    situation[0][0] = new Array();
    for (var i = 0; i < 19; i++) {
        vis[i] = new Array();
        situation[0][0][i] = new Array();
        for (var j = 0; j < 19; j++) {
            situation[0][0][i][j] = [0, 0];
            vis[i][j] = 0;
        }
    }
    var got = new Array();
    var dir = [[0, 1], [0, -1], [1, 0], [-1, 0]];
    var path = new Array();
    var pathNum = 0;
    var erase = 1;

    function getInfo(step) {
        return [position[step-1][0], position[step-1][1], ((step%2==0) ? -1 : 1), ""];
    }

    function legal(x, y) {
        return ((x>=0) && (x<19) && (y>=0) && (y<19));
    }

    function dfs(board, x, y, searchColor) {
        vis[x][y] = 1;
        for (var i = 0; i < 4; i++) {
            var nx = x + dir[i][0];
            var ny = y + dir[i][1];
            if (legal(nx, ny)) {
                if (vis[nx][ny] == 0) {
                    if (board[nx][ny][0] == 0) {
                        erase = 0;
                        return;
                    }
                    else if (board[nx][ny][0] == searchColor) {
                        path[pathNum++] = [nx, ny];
                        dfs(board, nx, ny, searchColor);
                        if (erase == 0) return;
                    }
                }
            }
        }
    }

    function init(step) {
        var tuple = getInfo(step);
        var x = tuple[0];
        var y = tuple[1];
        var color = tuple[2];
        situation[step] = new Array();
        situation[step][0] = new Array();
        for (var i = 0; i < 19; i++) {
            situation[step][0][i] = new Array();
            for (var j = 0; j < 19; j++) {
                situation[step][0][i][j] = [situation[step-1][0][i][j][0], situation[step-1][0][i][j][1]];
            }
        }
        situation[step][0][x][y][0] = color;
        var searchColor = color * (-1);
        for (var i = 0; i < 4; i++) {
            if (legal(x + dir[i][0], y + dir[i][1])) {
                if (situation[step][0][x + dir[i][0]][y + dir[i][1]][0] == searchColor) {
                    for (var it = 0; it < 19; it++) {
                        for (var jt = 0; jt < 19; jt++){
                            vis[it][jt] = 0;
                        }
                    }
                    erase = 1;
                    pathNum = 0;
                    path[pathNum++] = [x + dir[i][0], y + dir[i][1]];
                    vis[x + dir[i][0]][y + dir[i][1]] = 1;
                    dfs(situation[step][0], x + dir[i][0], y + dir[i][1], searchColor);
                    if (erase == 1) {
                        for (var j = 0; j < pathNum; j++) {
                            var nx = path[j][0];
                            var ny = path[j][1];
                            situation[step][0][nx][ny] = [0, 0];
                        }
                    }
                }
            }
        }
    }

    /*changedNum[i]: 第i手变化数 i in 1-goNum*/
    //changedPlayedCount[i][j]: 第i手第j个变化的步数, j in 1-changedNum[i]
    /*changedDetails[i][j][k]: 第i手第j个变化第k步, k in 0-changedPlayedCount[i][j]-1, [color, x, y]*/
    function initChange(step, sel) {
        var origin = new Array();
        for (var i = 0; i < 19; i++) {
            origin[i] = new Array();
            for (var j = 0; j < 19; j++) {
                origin[i][j] = [situation[step][0][i][j][0], situation[step][0][i][j][1]];
            }
        }
        situation[step][sel] = new Array();
        for (var take = 0; take < changedPlayedCount[step][sel]; take++) {
            var x = changedDetails[step][sel][take][1];
            var y = changedDetails[step][sel][take][2];
            var color = changedDetails[step][sel][take][0];
            for (var i = 0; i < 19; i++) {
                situation[step][sel][i] = new Array();
                for (var j = 0; j < 19; j++) {
                    situation[step][sel][i][j] = [origin[i][j][0], origin[i][j][1]];
                }
            }
            situation[step][sel][x][y] = [color, take + 1];
            var searchColor = color * (-1);
            for (var i = 0; i < 4; i++) {
                if (legal(x + dir[i][0], y + dir[i][1])) {
                    if (situation[step][sel][x + dir[i][0]][y + dir[i][1]][0] == searchColor) {
                        for (var it = 0; it < 19; it++) {
                            for (var jt = 0; jt < 19; jt++){
                                vis[it][jt] = 0;
                            }
                        }
                        erase = 1;
                        pathNum = 0;
                        path[pathNum++] = [x + dir[i][0], y + dir[i][1]];
                        vis[x + dir[i][0]][y + dir[i][1]] = 1;
                        dfs(situation[step][sel], x + dir[i][0], y + dir[i][1], searchColor);
                        if (erase == 1) {
                            for (var j = 0; j < pathNum; j++) {
                                var nx = path[j][0];
                                var ny = path[j][1];
                                situation[step][sel][nx][ny] = [0, 0];
                            }
                        }
                    }
                }
            }
            for (var i = 0; i < 19; i++) {
                for (var j = 0; j < 19; j++) {
                    origin[i][j] = [situation[step][sel][i][j][0], situation[step][sel][i][j][1]];
                }
            }
        }
        for (var i = 0; i < 19; i++) {
            for (var j = 0; j < 19; j++) {
                situation[step][sel][i][j] = [origin[i][j][0], origin[i][j][1]];
            }
        }
    }

    for (var i = 1; i <= goNum; i++) {
        init(i);
    }

    for (var i = 1; i <= goNum; i++) {
        for (var j = 1; j <= changedNum[i]; j++) {
            initChange(i, j);
        }
    }


    function update(latest) {
        for (var i = 0; i < 19; i++) {
            for (var j = 0; j < 19; j++) {
                if ((situation[current[0]][current[1]][i][j][0] != situation[latest[0]][latest[1]][i][j][0]) || (situation[current[0]][current[1]][i][j][1] != situation[latest[0]][latest[1]][i][j][1])) {
                    if (situation[latest[0]][latest[1]][i][j][0] == 0) clearPiecesOnCanvas(i+1, j+1);
                    else putPiecesOnCanvas(i+1, j+1, situation[latest[0]][latest[1]][i][j][0], situation[latest[0]][latest[1]][i][j][1])
                }
            }
        }
        if (current[0] != latest[0]) $("#cmts").text(comments[latest[0]]);
        current = latest;
    }

    function getNext() {
        var n = parseInt(document.getElementById("number_pieces").value) + 1;
        if (n > goNum) return;
        $("#opt_1").show();
        $("#opt_2").show();
        $("#opt_3").show();
        if (changedNum[n] < 3) $("#opt_3").hide();
        if (changedNum[n] < 2) $("#opt_2").hide();
        if (changedNum[n] < 1) $("#opt_1").hide();
        var optionObj0 = document.getElementById("opt_0");
        var optionObj1 = document.getElementById("opt_1");
        var optionObj2 = document.getElementById("opt_2");
        var optionObj3 = document.getElementById("opt_3");
        optionObj1.selected = false;
        optionObj2.selected = false;
        optionObj3.selected = false;
        optionObj0.selected = true;
        document.getElementById("number_pieces").value = n.toString();

        update([n, 0])
    }

    function getPre() {
        var n = parseInt(document.getElementById("number_pieces").value) - 1;
        if (n < 0) return;
        $("#opt_1").show();
        $("#opt_2").show();
        $("#opt_3").show();
        if (changedNum[n] < 3) $("#opt_3").hide();
        if (changedNum[n] < 2) $("#opt_2").hide();
        if (changedNum[n] < 1) $("#opt_1").hide();
        var optionObj0 = document.getElementById("opt_0");
        var optionObj1 = document.getElementById("opt_1");
        var optionObj2 = document.getElementById("opt_2");
        var optionObj3 = document.getElementById("opt_3");
        optionObj1.selected = false;
        optionObj2.selected = false;
        optionObj3.selected = false;
        optionObj0.selected = true;
        document.getElementById("number_pieces").value = n.toString();
        update([n, 0])
    }

    function toNum() {
        current[1] = 0;
        $("#opt_1").show();
        $("#opt_2").show();
        $("#opt_3").show();
        var n = parseInt(document.getElementById("number_pieces").value);
        if (n > goNum) n = goNum;
        if (changedNum[n] < 3) $("#opt_3").hide();
        if (changedNum[n] < 2) $("#opt_2").hide();
        if (changedNum[n] < 1) $("#opt_1").hide();
        var optionObj0 = document.getElementById("opt_0");
        var optionObj1 = document.getElementById("opt_1");
        var optionObj2 = document.getElementById("opt_2");
        var optionObj3 = document.getElementById("opt_3");
        optionObj1.selected = false;
        optionObj2.selected = false;
        optionObj3.selected = false;
        optionObj0.selected = true;
        document.getElementById("number_pieces").value = n.toString();
        update([n, 0]);
    }

    function choose0() {
        if (current[1] == 0) return;
        update([current[0], 0]);
    }

    function choose1() {
        if (current[1] == 1) return;
        update([current[0], 1]);
    }

    function choose2() {
        if (current[1] == 2) return;
        update([current[0], 2]);
    }

    function choose3() {
        if (current[1] == 3) return;
        update([current[0], 3]);
    }

</script>

<script>
    var canvBg = document.getElementById("board_background");
    var ctxBg = canvBg.getContext("2d");
    var backgroundObj = new Image();
    backgroundObj.src = "./img/wood.jpg";
    backgroundObj.onload = function() {
        var pattern = ctxBg.createPattern(backgroundObj, 'no-repeat');
        ctxBg.fillStyle = pattern;
        ctxBg.fillRect(0, 0, 1000, 1000);
    }
</script>

<script>
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
</script>

<script>
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
</script>

</body>
</html>
