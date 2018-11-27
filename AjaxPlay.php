<?php
require_once "SessionManagement.php";
//Database Authentication
require("DBInfo.inc");



$gameSessionUID = $_GET['gameSessionUID'];
$userUID = $_GET['userUID'];
$dateCheck = $_GET['dateCheck'];
$op = $_GET['op'];


if ((!empty($gameSessionUID) && ! preg_match("/^[a-zA-Z0-9]+$/", $gameSessionUID) ) ||
    (!empty($userUID) &&  ! preg_match("/^[a-zA-Z0-9]+$/", $userUID) )||
    (!empty($op) &&  ! preg_match("/^[a-zA-Z0-9]+$/", $op) )||
    (!empty($dateCheckNoSpace) &&   ! preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$dateCheck) )){
    echo "Not valid page". $dateCheckNoSpace;
    return;
}

//Not secure post call
if(!empty($gameSessionUID) and !empty($userUID) and $op=="set" ) {

    //connect to database
    $connect = mysqli_connect($hostDB, $userDB, $passwordDB, $databaseDB);
    if (mysqli_connect_errno()) {
        die(" cannot connect to database " . mysqli_connect_error());
    }

    // check if invliad request

    $query = "select playerUID,diceValue from gameSession where gameSessionUID='" . $gameSessionUID . "' ORDER BY moveDate DESC";
    $result = mysqli_query($connect, $query);
    if (!$result) { die(' Error cannot run query'); }
    $lastPlayerUID = null;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($lastPlayerUID == null) {
            $lastPlayerUID = $row["playerUID"];
            break;
        }
    }
    if($lastPlayerUID==$userUID){
        $myObj = array();
        $myObj["errorMessage"] = "Not valid request, isnot your turn";
        $myObj["isValidMove"] = false;
        $myObj["steps"] = $steps;
        echo json_encode($myObj);
        mysqli_close($connect);
        return;
    }

    $query = "select playerLocation from gameSession where gameSessionUID='" . $gameSessionUID . "' and playerUID='" . $userUID . "' ORDER BY moveDate DESC";
    $result = mysqli_query($connect, $query);
    if (!$result) { die(' Error cannot run query'); }
    $playerLocation = null;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($playerLocation == null) {
            $playerLocation = $row["playerLocation"];
            break;
        }
    }

    //
    // set start location location
    $row = 0;
    $col = 6;
    if ($playerLocation != null) {
        $rowAndCol = explode("|", $playerLocation);
        $row = (int)$rowAndCol[0];
        $col = (int)$rowAndCol[1];
    }
    $newLocation=$row . "|". $col;

    $steps = rand(1, 6);
    $gridSize = 7;
    for ($i = 0; $i < $steps; $i++) {
        if (($row + 1) < $gridSize) {
            $row = $row + 1;
        } else {
            $row = 0;
            $col = $col - 1;
        }
    }
    // set back if go fire
    $errorMessage = null;
    $isValidMove = true;
    if ((($row + $col) % 4 == 0)) {
        $errorMessage = "Fire area no move";
        $isValidMove = false;
    }
    if ($col == -1) {
        $errorMessage = "Cannot pass crown location area no move";
        $isValidMove = false;
    }
    if ($row == 6 && $col == 0) {
        $errorMessage = $userUID + "Wins !!";
        $isValidMove = true;
    }

    $dataBaseMessage = null;
    if ($isValidMove == true) {
        $newLocation=  $row . "|" . $col ;
    }

        $query = "Insert into gameSession(gameSessionUID,playerUID,playerLocation,diceValue) VALUES ('" .
            $gameSessionUID . "','" . $userUID . "','" . $newLocation . "'," . $steps . ")";
        $result = mysqli_query($connect, $query);
        if (!$result) {
            $dataBaseMessage = 'fail';
        } else {
            $dataBaseMessage = 'success';
        }



    $myObj = array();
    $myObj["errorMessage"] = $errorMessage;
    $myObj["isValidMove"] = $isValidMove;
    $myObj["dataBaseMessage"] = $dataBaseMessage;
    $myObj["newLocation"] = $row . "|" . $col;
    $myObj["steps"] = $steps;
    echo json_encode($myObj);



    mysqli_close($connect);

}else if(!empty($gameSessionUID)  and $op=="get" ) {
    //connect to database
    $connect = mysqli_connect($hostDB, $userDB, $passwordDB, $databaseDB);
    if (mysqli_connect_errno()) {
        die(" cannot connect to database " . mysqli_connect_error());
    }


    //Get last player UID
    $query = "select playerUID,diceValue from gameSession where gameSessionUID='" . $gameSessionUID . "' ORDER BY moveDate DESC";
    $result = mysqli_query($connect, $query);
    if (!$result) { die(' Error cannot run query'); }
    $lastPlayerUID = null;
    $diceValue=null;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($lastPlayerUID == null) {
            $lastPlayerUID = $row["playerUID"];
            $diceValue= $row["diceValue"];
            break;
        }
    }

    // get players uid
    $playerToUID=null;
    $playerFromUID=null;
    $requestDate = null;
    $playerFromLastActiveDate = null;
    $playerToLastActiveDate = null;
    $query1 ="SELECT playerToUID,playerFromUID, TIMESTAMPDIFF(SECOND,  requestDate,now())  AS 'requestDate' , TIMESTAMPDIFF(SECOND,  playerToLastActiveDate,now())   AS 'playerToLastActiveDate' ,TIMESTAMPDIFF(SECOND,  playerFromLastActiveDate,now())   AS 'playerFromLastActiveDate' FROM playRequests WHERE gameSessionUID='". $gameSessionUID ."'" ;
    $result= mysqli_query($connect,$query1);
    if (!$result){ die(' Error cannot run query'); }
    $row1= array();
    while ($row= mysqli_fetch_assoc($result)) {
        $row1[] = $row;
        $playerToUID = $row["playerToUID"];
        $playerFromUID = $row["playerFromUID"];
        $requestDate =   $row["requestDate"]  ;
        $playerFromLastActiveDate = $row["playerFromLastActiveDate"];
        $playerToLastActiveDate =  $row["playerToLastActiveDate"];
        break;
    }

   // get $playerToUID location
    $query = "select playerLocation from gameSession where gameSessionUID='" . $gameSessionUID . "' and playerUID='" . $playerToUID . "' ORDER BY moveDate DESC";
    $result = mysqli_query($connect, $query);
    if (!$result) { die(' Error cannot run query');}
    $playerToUIDLocation = "0|6";
    while ($row = mysqli_fetch_assoc($result)) {
        if ($playerToUIDLocation = "0|6") {
            $playerToUIDLocation = $row["playerLocation"];
            break;
        }
    }

    // get $playerFromUID location
    $query = "select playerLocation from gameSession where gameSessionUID='" . $gameSessionUID . "' and playerUID='" . $playerFromUID . "' ORDER BY moveDate DESC";
    $result = mysqli_query($connect, $query);
    if (!$result) { die(' Error cannot run query');}
    $playerFromUIDLocation = "0|6";
    while ($row = mysqli_fetch_assoc($result)) {
        if ($playerFromUIDLocation = "0|6") {
            $playerFromUIDLocation = $row["playerLocation"];
            break;
        }
    }

    $myObj = array();

    //Get last chating
    $query ="select * from chating where chatTextDate>'". $dateCheck."' and gameSessionUID='". $gameSessionUID ."' order by chatTextDate" ;
    $result= mysqli_query($connect,$query);
    if (!$result){die(' Error cannot run query');}
    $hasRow=false;
    $lastChatTextDate=null;
    while ($row= mysqli_fetch_assoc($result)) {
        $userInfo[]= htmlentities($row["chatText"]);
        $lastChatTextDate= $row["chatTextDate"];
        $hasRow=true;
    }


    $myObj["chatStatus"] = $hasRow;
    if($hasRow==true){
        $myObj["chatText"] = $userInfo;
        $myObj["lastChatTextDate"] = $lastChatTextDate;
    }
    $myObj["playerFromUID"] = $playerFromUID;
    $myObj["playerFromUIDLocation"] = $playerFromUIDLocation;
    $myObj["playerToUID"] = $playerToUID;
    $myObj["playerToUIDLocation"] = $playerToUIDLocation;
    $myObj["lastPlayerUID"]= $lastPlayerUID;
    $myObj["diceValue"]=$diceValue;

    $date = new DateTime();

    //HeartBeat update my location
    $requestDate = null;
    $playerFromLastActiveDate = null;
    $playerToLastActiveDate = null;
    $query= null;
    if ($userUID == $playerToUID){
        $query = "update playRequests set playerToLastActiveDate= now() where gameSessionUID='". $gameSessionUID ."'  and playerToUID='" . $userUID . "'";
    }else{
        $query = "update playRequests set playerFromLastActiveDate= now() where gameSessionUID='". $gameSessionUID ."'  and playerFromUID='" . $userUID . "'";
    }
    $myObj["row"] =   $row1;
    $result = mysqli_query($connect, $query);
    if (!$result) {
        $myObj["heartBeat1"] = 'fail';
    } else {
        $myObj["heartBeat1"] = 'success';
    }


    // set user active in HeartBeat
    $query = "update login set LastActiveDate= now() where   userUID='" . $userUID . "'";
    $result = mysqli_query($connect, $query);
    if (!$result) {
        $myObj["heartBeat2"] = 'fail';
    } else {
        $myObj["heartBeat2"] = 'success';
    }


    mysqli_close($connect);


    echo json_encode($myObj);

}else{
    echo 'missing required params';
}
