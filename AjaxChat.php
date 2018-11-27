<?php
require_once "SessionManagement.php";
//Database Authentication
require("DBInfo.inc");

// Server side code
//Read form submit info post request
//$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);
//echo $data["operacion"];
$gameSessionUID = $_GET['gameSessionUID'];
$chatText = $_GET['chatText'];
$op = $_GET['op'];
$dateCheck = $_GET['dateCheck'];


if ((!empty($gameSessionUID) && ! preg_match("/^[a-zA-Z0-9]+$/", $gameSessionUID)) ||
    (!empty($op) && ! preg_match("/^[a-zA-Z0-9]+$/", $op) )||
    (!empty($dateCheckNoSpace) && ! preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$dateCheck) )
    ){
    echo "Not valid page";
    return;
}
$chatText = str_replace("'", " ", $chatText);

//Not secure post call
if(!empty($gameSessionUID) and !empty($chatText) and $op=="add") {


    //connect to database
    $connect = mysqli_connect($hostDB, $userDB, $passwordDB, $databaseDB);
    if (mysqli_connect_errno()) {
        die(" cannot connect to database " . mysqli_connect_error());
    }

    $query = "Insert into chating(gameSessionUID, chatText) VALUES ('" .
        $gameSessionUID . "','" . rawurldecode($chatText) . "')";

    $result = mysqli_query($connect, $query);
    if (!$result) {
        echo 'fail';
    } else {
        echo 'success';
    }

    mysqli_close($connect);


}else if(!empty($dateCheck) and $op=="get") {

    //connect to database
    $connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
    if(mysqli_connect_errno()){
        die(" cannot connect to database ". mysqli_connect_error());
    }
    $query ="select * from chating where chatTextDate>'". $dateCheck."' and gameSessionUID='". $gameSessionUID ."' order by chatTextDate" ;

    $result= mysqli_query($connect,$query);
    if (!$result){
        die(' Error cannot run query');
    }

    $userInfo=array();
    $hasRow=false;
    $lastChatTextDate=null;
    while ($row= mysqli_fetch_assoc($result)) {
        $userInfo[]= htmlentities($row["chatText"]) ;
        $lastChatTextDate= $row["chatTextDate"];
        $hasRow=true;
    }
    $myObj=array();
    $myObj["status"] = $hasRow;
    if($hasRow==true){
        $myObj["text"] = $userInfo;
        $myObj["lastChatTextDate"] = $lastChatTextDate;
    }

    echo  json_encode($myObj) ;

    mysqli_free_result($result);
    mysqli_close($connect);

}else{
    echo 'missing required params';
}
