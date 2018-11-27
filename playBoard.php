<?php require 'headerTab.php';


//Read form submit info post request
$gameSessionUID = $_REQUEST['gameSessionUID'];
//prevent injection
if (! preg_match("/^[a-zA-Z0-9]+$/", $gameSessionUID)){
    echo "Not valid page";
return;
}
//Database Authentication
require("DBInfo.inc");

//connect to database
$connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
if(mysqli_connect_errno()){
    die(" cannot connect to database ". mysqli_connect_error());
}

$query ="SELECT playerToUID,playerFromUID FROM playRequests WHERE gameSessionUID='". $gameSessionUID ."'" ;

$result= mysqli_query($connect,$query);
if (!$result){
    die(' Error cannot run query');
}

$playerToUID=null;
$playerFromUID=null;
while ($row= mysqli_fetch_assoc($result)) {
    $lastRequestDate = $row["requestDate"];
    $playerToUID = $row["playerToUID"];
    $playerFromUID = $row["playerFromUID"];
}

mysqli_free_result($result);
mysqli_close($connect);

?>
<link rel="stylesheet" href="./css/chat.css">
<script>
    var gameSessionUID="<?= $gameSessionUID?>";
    var url="<?=$onlineURL?>";
    var username= "<?= $_SESSION["userName"]?>";
    var userUID= "<?= $_SESSION["userUID"]?>";
    function init() {

        drawBoard();

    }
var gridSize=7;
    function drawBoard() {
        let board = "",
            x,
            y,
            color,
            cx,
            cy;

        // create the board sequares
        for(let i=0;i<gridSize;i++){
            for( let j=0;j<gridSize;j++){
                x=90*i;
                y= 90*j ;
                if((i+j)%2===0){
                    color = "black";
                }else{
                    color = "white";
                }
                board+=`<rect x="${x}" y="${y}" width="90" height="90" stroke-width="2" stroke="red" fill="${color}" id="target_${i}${j}"/>`;

                //Add fires
                if(((i+j)%4==0)){
                    board+=`<image xlink:href="images/fire.png" x="${x}" y="${y}" width="90" height="90"   id="target_fire_${i}${j}"/>`;

                }
                //Add crown
                if(i==6 && j==0){
                    board+=`<image xlink:href="images/crown.png" x="${x}" y="${y}" width="90" height="90"   id="target_crown_${i}${j}"/>`;

                }




            }
        }

        document.getElementById("board").innerHTML=board;

        //Add Players
        setPlayerLocation("<?=$playerToUID?>",0,6);
        setPlayerLocation("<?=$playerFromUID?>",0,6);
        isPlayersInSameCell();
    }


    //set player location
    function setPlayerLocation(playerName,row,col) {
        var imagePlayer= (playerName=="<?=$playerToUID?>"?"player1":"player2");
        var x=90*row;
        var y= 90*col ;
        var board =`<image xlink:href="images/${imagePlayer}.png" x="${x}" y="${y}" width="90" height="90"   id="${playerName}"/>`;
        document.getElementById("board").innerHTML= document.getElementById("board").innerHTML +board;
    }

    //If the two players in same cell avoid overlap
    function isPlayersInSameCell() {
        var player1 = document.getElementById("<?=$playerToUID?>");
        var player2 = document.getElementById("<?=$playerFromUID?>");
        var player1X = parseInt(player1.getAttribute("x"));
        var player2X = parseInt(player2.getAttribute("x"));
        if(player1X== player2X){
            player1.setAttribute("x", player1X-10);
            player2.setAttribute("x", player2X+10);
        }


    }

    // Load all prevous chat
    var lastDateCheck="2016-01-01 18:49:13";
   /*
   loadChat(lastDateCheck);

    // load new chat
    setInterval(function(){
           loadChat(lastDateCheck)  ;
        }, 3000);

    //Load chat from server every period
    function loadChat(dateCheck) {

        $.ajax({
            type: "GET", // default
            async: true, // default
            cache: false,// default
            url: url+"/AjaxChat.php",
            data: "op=get&dateCheck="+ dateCheck+"&gameSessionUID="+ gameSessionUID  ,
            dataType: "text", // defaults to the content-type of the response
            success: function( data) {
                 var  obj = JSON.parse(data);

                 if(obj.status==true){
                     lastDateCheck= obj.lastChatTextDate;
                     for(var i=0;i< obj.text.length;i++ ){
                         var chatText = obj.text[i];
                         if(chatText.startsWith(username +":")!=0){
                             $(".messages").append($('<li>').text(chatText).addClass("otherChat").addClass("myChat"));
                         }else{
                             $(".messages").append($('<li>').text(chatText).addClass("otherChat"));
                         }
                        // window.scrollTo(0, document.body.scrollHeight);
                         $('.messages').scrollTop($('.messages').height())
                     }
                 }

            }

        });

    }

    */
    function sendChatText() {
        var inputMessage = document.getElementById("inputMessage");

        $.ajax({
            type: "GET", // default
            async: true, // default
            cache: false,// default
            url: url+"/AjaxChat.php",
            data:"op=add&chatText=<?= $_SESSION["userName"] ?>:"+  encodeURIComponent(inputMessage.value) +"&gameSessionUID="+ gameSessionUID  ,
            dataType: "text", // defaults to the content-type of the response
            success: function( data, status ) {
                console.log( data );
                inputMessage.value="";
            }
        });

    }


    //play-dice
    var  gameOver =false;

    // load new chat
    setInterval(function(){
        if (gameOver==false) {
            serverUpdate();
        }

    }, 2000);


    function serverUpdate() {

        $.ajax({
            type: "GET", // default
            async: true, // default
            cache: false,// default
            url: url+"/AjaxPlay.php",
            data: "op=get&gameSessionUID="+ gameSessionUID  +"&dateCheck="+ lastDateCheck +"&userUID="+ userUID,
            dataType: "text", // defaults to the content-type of the response
            success: function( data) {
                var  obj = JSON.parse(data);

                movePlayerAjax(obj.playerFromUID, obj.playerFromUIDLocation);
                movePlayerAjax(obj.playerToUID, obj.playerToUIDLocation);



                // update chat
                if(obj.chatStatus==true){
                    lastDateCheck= obj.lastChatTextDate;
                    for(var i=0;i< obj.chatText.length;i++ ){
                        var chatText = obj.chatText[i];
                        if(chatText.startsWith(username +":")!=0){
                            $(".messages").append($('<li>').text(chatText).addClass("otherChat").addClass("myChat"));
                        }else{
                            $(".messages").append($('<li>').text(chatText).addClass("otherChat"));
                        }
                        // window.scrollTo(0, document.body.scrollHeight);
                        $('.messages').scrollTop($('.messages').height())
                    }
                }


                //TODO: vistor could watch game
                /*
                if(obj.playerFromUID != userUID && obj.playerToUID !=userUID){
                    document.getElementById("play-dice").disabled = true;
                    document.getElementById("play-dice").innerText = "You watch game only";
                    document.getElementById("play-dice").className = "btn btn-danger";

                    console.log("visitor");
                }*/

                if(obj.lastPlayerUID!=null) {


                    if (obj.lastPlayerUID == userUID) {
                        document.getElementById("play-dice").disabled = true;
                        document.getElementById("play-dice").innerText = "Waiting other player";
                        document.getElementById("play-dice").className = "btn btn-warning";
                        document.getElementById("player-turn").innerText="Your Dice was ("+obj.diceValue+ ")";
                    } else {
                        document.getElementById("play-dice").innerText = "Play Dice";
                        document.getElementById("play-dice").disabled = false;
                        document.getElementById("play-dice").className = "btn btn-success";
                        document.getElementById("player-turn").innerText="Other player Dice was ("+obj.diceValue+ ")";

                    }

                }else{

                    if(  userUID=="<?=$playerFromUID?>" ){
                        document.getElementById("play-dice").innerText="Play Dice";
                        document.getElementById("play-dice").disabled=false;
                        document.getElementById("play-dice").className="btn btn-success";
                    }else {
                        document.getElementById("play-dice").disabled = true;
                        document.getElementById("play-dice").innerText = "Waiting other player";
                        document.getElementById("play-dice").className = "btn btn-warning";
                    }

                }



                if(obj.playerFromUIDLocation=="6|0" ){
                   if (userUID == "<?=$playerToUID?>"){
                       alert("You lose the game");
                   } else{
                       alert("You win the game");
                   }
                    document.getElementById("play-dice").disabled = true;
                    document.getElementById("play-dice").innerText = "Game is end";
                    document.getElementById("play-dice").className = "btn btn-danger";
                    gameOver=true;
                }else if(obj.playerToUIDLocation=="6|0" ){
                    if (userUID == "<?=$playerToUID?>"){
                        alert("You win the game");
                    } else{
                        alert("You lose the game");
                    }
                    document.getElementById("play-dice").disabled = true;
                    document.getElementById("play-dice").innerText = "Game is end";
                    document.getElementById("play-dice").className = "btn btn-danger";
                    gameOver=true;
                }

                // Other user status
                var alertBtn = document.getElementById("alert-btn");
                var msg="";
                if (userUID == "<?=$playerToUID?>"){
                    // get other developer last active date
                    if(obj.row[0].requestDate==obj.row[0].playerFromLastActiveDate){
                        msg = 'waiting' ;
                    }else{
                        msg = obj.row[0].playerFromLastActiveDate;
                    }
                }else{
                    if(obj.row[0].requestDate==obj.row[0].playerToLastActiveDate){
                        msg = 'waiting' ;
                    }else{
                        msg = obj.row[0].playerToLastActiveDate;
                    }
                }

                if (msg=="waiting"){
                    alertBtn.innerHTML="<strong> Waiting for other player...</strong>";
                    alertBtn.style.display = 'block';
                }else if (parseInt(msg)>30){
                    alertBtn.innerHTML=`<strong> Other user isnot active from ${msg} Seconds</strong>`;
                    alertBtn.style.display = 'block';
                }else{
                    alertBtn.style.display = 'none';           // Hide
                }


            }

        });
    }

    function diceRunAjax() {
        document.getElementById("play-dice").disabled=true;
        document.getElementById("play-dice").innerText="Waiting other player";
        document.getElementById("play-dice").className="btn btn-warning";
        $.ajax({
            type: "GET", // default
            async: true, // default
            cache: false,// default
            url: url+"/AjaxPlay.php",
            data: "op=set&userUID="+ userUID +"&gameSessionUID="+ gameSessionUID  ,
            dataType: "text", // defaults to the content-type of the response
            success: function( data) {
                var  obj = JSON.parse(data);
                console.log(obj);
                if(obj.isValidMove==true){
                    movePlayerAjax(userUID, obj.newLocation);
                    //movePlayer(userUID, obj.steps);

                }else{
                 alert(obj.errorMessage);
                }

            }

        });
    }

    function movePlayerAjax(playerName, rowAndCol) {
        var player = document.getElementById(playerName);
        var rowAndCol = rowAndCol.split("|");
        player.setAttribute("x", 90*parseInt(rowAndCol[0]));
        player.setAttribute("y", 90*parseInt(rowAndCol[1]));
        isPlayersInSameCell();
    }

    /*
    var playerTurn=1;
    function diceRun() {
        //TODO: move it to server side
        var steps= Math.floor(Math.random() * (6-1) + 1) ;
        //alert(steps);
        movePlayer(playerTurn==1?"player1":"player2", steps);
        playerTurn =(playerTurn==1? 2:1);
        document.getElementById("player-turn").innerText="Dice is ("+ steps +") turn to Player"+ playerTurn;

    }
    */

    //move player
    function movePlayer(playerName, steps) {
        document.getElementById("player-turn").innerText="Dice is ("+ steps +") turn to Player "+ (playerName==userUID?"Other":username);

        var player = document.getElementById(playerName);
        var row = Math.floor(parseInt(player.getAttribute("x"))/90) ;
        var col = Math.floor(parseInt(player.getAttribute("y"))/90);
        var xStart=90*row;
        var yStart= 90*col;
        for(var i=0;i<steps;i++){
            if((row+1)<gridSize){
                row=row+1;
            }else{
                row=0;
                col=col-1;
            }
        }

        player.setAttribute("x", 90*row);
        player.setAttribute("y", 90*col);
        console.log(row +"==" +col);
        // set back if go fire
        if(((row+col)%4==0)){
            alert("Fire area no move");
            player.setAttribute("x", xStart);
            player.setAttribute("y", yStart);
        }
        if(col==-1){
            alert("Cannot pass crown location area no move");
            player.setAttribute("x", xStart);
            player.setAttribute("y", yStart);
        }
        if(row==6 && col==0){
            alert(playerName + "Wins !!");
        }
        isPlayersInSameCell();

    }
</script>
    <body style="background-color: #f9f9f9">
    <div class="container" style="background-color: #fff">
        <br/>
        <div class="alert alert-danger" id="alert-btn">
            <strong> Waiting for other player...</strong>
        </div>

                <div class="panel panel-info" style="float:left;width: 700px">
                    <div class="panel-heading">
                        Play Board
                    </div>
                    <div class="panel-body" style="text-align: center">
                        <svg xmlns="http://www.w3.org/2000/svg"    version="1.1" width="650px" height="650px"
                             onload="init();">
                            <g id="board">
                            </g>
                        </svg>
                    </div>
                </div>

        <table>
            <tr>
                <td>

                    <div class="panel panel-info" style="float:left;width: 400px; height: 100px; margin-left: 10px">
                        <div class="panel-heading" id="player-turn"> Throw Dice </div>
                        <div class="panel-body" style="text-align: center">
                            <button id="play-dice" class="btn btn-success"onclick="diceRunAjax()">Play Dice</button>
                        </div>
                    </div>

                </td>
            </tr>

            <tr>
                <td>

                    <div class="panel panel-info" style="float:left;width: 400px; margin-left: 10px">
                        <div class="panel-heading">Chat</div>
                        <div class="panel-body">
                            <div  style="width: 100%;height:480px;margin-bottom: 5px">
                                <ul class="messages">
                                </ul>
                            </div>
                            <div class="input-group">
                                <input type="text" id="inputMessage" class="form-control" placeholder="Type here..."/>

                                <span class="input-group-btn">
                        <button class="btn btn-success"onclick="sendChatText()">Send</button>
                        </span>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
        </table>




    </div>

    </body>
<?php  require 'footerTab.php';?>