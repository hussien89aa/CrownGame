<?php require 'headerTab.php' ; ?>

<?php
//Database Authentication
require("DBInfo.inc");

// Server side code
//Read form submit info post request
$gameSessionUID = $_REQUEST['gameSessionUID'];
$requestStatus = $_REQUEST['requestStatus'];

//Not secure post call
if(!empty($gameSessionUID) ) {

    //prevent injection
    if (! preg_match("/^[a-zA-Z0-9]+$/", $gameSessionUID) ||
        ! preg_match("/^[a-zA-Z0-9]+$/", $requestStatus)){
        echo "Not valid page";
        return;
    }

//connect to database
    $connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
    if(mysqli_connect_errno()){
        die(" cannot connect to database ". mysqli_connect_error());
    }

    $query ="UPDATE  playRequests SET requestStatus='".$requestStatus ."' WHERE gameSessionUID='". $gameSessionUID . "'" ;

    $result= mysqli_query($connect,$query);
    if (!$result){
        die(' Error cannot run query');
    }

    mysqli_close($connect);

    if ($requestStatus=="accept"){
      echo "<script>  document.location=\"playBoard.php?gameSessionUID=".  $gameSessionUID ."\";</script>";
      return;
    }

}

?>
<script>
    function sendRequest() {
     document.location="Requests.php"
    }
    function acceptRequest(gameSessionUID, action) {
        var formSubmit = document.getElementById("submit-form");
        formSubmit.gameSessionUID.value=gameSessionUID;
        formSubmit.requestStatus.value=action ;
        formSubmit.submit();
    }
    function startGame(gameSessionUID) {
        document.location="playBoard.php?gameSessionUID="+ gameSessionUID;
    }
</script>
    <body style="background-color: #f9f9f9">
    <div class="container" style="background-color: #fff">
        <br/>


        <div class="alert alert-info">
            <div class="row">
                <div class="col-sm-10">
                    <strong>List of game play requests </strong>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-success"onclick="sendRequest()">Send Play Request</button>
                </div>
            </div>



        </div>
        <table  class="table table-hover">
            <thead>
            <tr>
                <td> <strong>Request from</strong></td>
                <td>  <strong>Status</strong></td>
                <td>  <strong>Date</strong></td>
                <td> <strong>Action</strong></td>
            </tr>
            </thead>
            <tbody>



            <?php

            //Database Authentication
            require("DBInfo.inc");

            //connect to database
            $connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
            if(mysqli_connect_errno()){
                die(" cannot connect to database ". mysqli_connect_error());
            }

            $query ="SELECT gameSessionUID,(SELECT userName FROM login WHERE userUID=playerFromUID) as playerFromUserName , requestDate,requestStatus,numberOfActivePlayer FROM playRequests
 WHERE playerToUID='". $_SESSION["userUID"]."'   ORDER BY requestDate DESC " ;

            $result= mysqli_query($connect,$query);
            if (!$result){
                die(' Error cannot run query');
            }

            $userInfo=array();
            $loginInUser=null;
            $lastRequestDate=null;
            $hasRows=false;
            while ($row= mysqli_fetch_assoc($result)) {
                $hasRows=true;
                if($lastRequestDate==null){
                    $lastRequestDate = $row["requestDate"];
                }
                //$userInfo[]= $row ;
            if($row["requestStatus"] == "pending" || $row["requestStatus"] == "accept") {
                echo " <tr>";

                echo "  <td>" . htmlentities($row["playerFromUserName"]) . "</td>";
                if ($row["requestStatus"]=="pending"){
                    echo "<td class=\" btn-warning\"  >" . $row["requestStatus"] . "</td>";
                }else{
                    echo " <td class=\" btn-info\">" . $row["requestStatus"] . "</td>";
                }

                echo " <td>" . $row["requestDate"] . "</td>";
                if ($row["requestStatus"] == "pending") {
                    echo " <td>";
                    echo "<div class=\"btn-group\">";
                    echo "<button class=\"btn btn-success\"onclick=\"acceptRequest('" . $row["gameSessionUID"] . "','accept')\">Accept</button>";
                    echo "<button class=\"btn btn-danger\"onclick=\"acceptRequest('" . $row["gameSessionUID"] . "','reject')\">Reject</button>";
                    echo "</div>";
                    echo "</td>";

                } else if ($row["requestStatus"] == "accept") {

                    echo " <td> <button class=\"btn btn-success\"onclick=\"startGame('" . $row["gameSessionUID"] . "')\">Start Game</button> </td>";
                }

                echo " </tr>";
            }
            }

            mysqli_free_result($result);
            mysqli_close($connect);


            ?>

            </tbody>
        </table>

        <?php
        if($hasRows==false){
            echo "<div class=\"alert alert-danger\" role=\"alert\">No active Request</div>" ;
        }
        ?>

    </div>
<script>
    var  lastRequestDate="<?= $lastRequestDate==null?"2016-01-01 18:49:13" : $lastRequestDate ?>";
    var userUID ="<?=$_SESSION["userUID"]?>";
    var url="<?=$onlineURL?>";


    // load new chat
    setInterval(function(){
         loadChat(lastRequestDate)  ;
    }, 3000);

    function loadChat(dateCheck) {

        $.ajax({
            type: "GET", // default
            async: true, // default
            cache: false,// default
            url: url+"/AjaxControl.php",
            data: "dateCheck="+ dateCheck +"&userUID="+ userUID ,
            dataType: "text", // defaults to the content-type of the response
            success: function( data) {
                var  obj = JSON.parse(data);
                //console.log(obj.status);
                if(obj.status==true){

                    document.location="ControlPanel.php";
                }

            }

        });

    }
</script>
    <form id="submit-form">
        <input type="hidden" id="gameSessionUID" name="gameSessionUID" value="0">
        <input type="hidden" id="requestStatus" name="requestStatus" value="0">
    </form>
    </body>
<?php require 'footerTab.php'?>