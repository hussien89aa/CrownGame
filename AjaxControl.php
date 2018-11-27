<?php
require_once "SessionManagement.php";
//Database Authentication
require("DBInfo.inc");

$dateCheck = $_GET['dateCheck'];
$userUID = $_GET['userUID'];

if ((!empty($userUID) &&  ! preg_match("/^[a-zA-Z0-9]+$/", $userUID) )||
    (!empty($dateCheckNoSpace) &&  ! preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$dateCheck) )
    ){
    echo "Not valid page";
    return;
}

//Not secure post call
 if(!empty($dateCheck) ) {

    //connect to database
    $connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
    if(mysqli_connect_errno()){
        die(" cannot connect to database ". mysqli_connect_error());
    }
    $query ="select * from playRequests where requestDate>'". $dateCheck ."' and playerToUID='". $userUID ."'" ;

    $result= mysqli_query($connect,$query);
    if (!$result){
        die(' Error cannot run query');
    }

    $hasRow=false;
    while ($row= mysqli_fetch_assoc($result)) {
        $hasRow=true;
    }
    $myObj=array();
    $myObj["status"] = $hasRow;
     // set user active in HeartBeat
     $query = "update login set LastActiveDate= now() where   userUID='" . $userUID . "'";
     $result = mysqli_query($connect, $query);
     if (!$result) {
         $myObj["heartBeat2"] = 'fail';
     } else {
         $myObj["heartBeat2"] = 'success';
     }

    echo  json_encode($myObj) ;


    mysqli_close($connect);

}else{
    echo 'missing required params';
}
