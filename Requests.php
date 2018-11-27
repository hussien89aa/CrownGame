<?php require 'headerTab.php'?>
<script>
    function sendRequest(userUID) {
        var formSubmit = document.getElementById("submit-form");
        formSubmit.playerToUID.value=userUID;
        formSubmit.submit();
    }
</script>
    <body style="background-color: #f9f9f9">
    <div class="container" style="background-color: #fff">
        <br/>
        <div class="alert alert-info">
            <strong>List of Active Users  </strong>
        </div>
        <table  class="table table-hover">
            <thead>
            <tr>
                <td><strong>UserName</strong>  </td>
                <td><strong> Action</strong></td>
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
            // get users who was active last three minutes
            $query ="SELECT userUID,userName,  (SELECT count(*) from playRequests WHERE playerFromUID ='". $_SESSION["userUID"]."'
  and playerToUID=userUID and requestStatus='pending') as countRequests FROM login WHERE TIMESTAMPDIFF(MINUTE,  LastActiveDate,now())<3 and userUID !='". $_SESSION["userUID"]."'" ;
 
            $result= mysqli_query($connect,$query);
            if (!$result){
                die(' Error cannot run query');
            }

            $userInfo=array();
            $hasRows=false;
            while ($row= mysqli_fetch_assoc($result)) {
                $hasRows=true;
                echo " <tr>";
                echo "  <td>". htmlentities($row["userName"])."</td>";
                if($row["countRequests"]!="0"){
                    echo " <td> Request sent</td>";

                }else{
                    echo " <td> <button class=\"btn btn-success\"onclick=\"sendRequest('". $row["userUID"] ."')\">Send Request</button> </td>";

                }

                echo " </tr>";
            }

            mysqli_free_result($result);
            mysqli_close($connect);

            ?>

            </tbody>
        </table>
        <?php
        if($hasRows==false){
            echo "<div class=\"alert alert-danger\" role=\"alert\">No active users</div>" ;
        }
        ?>
    </div>
    <form id="submit-form">
        <input type="hidden" id="playerToUID" name="playerToUID" value="0">
    </form>
    </body>


<?php
//Database Authentication
require("DBInfo.inc");

// Server side code
//Read form submit info post request
$playerToUID = $_REQUEST['playerToUID'];

//Not secure post call
if(!empty($playerToUID) ) {

    //prevent injection
    if (! preg_match("/^[a-zA-Z0-9]+$/", $playerToUID)){
        echo "Not valid page";
        return;
    }

//connect to database
$connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
if(mysqli_connect_errno()){
    die(" cannot connect to database ". mysqli_connect_error());
}

$query ="Insert into playRequests(playerFromUID,playerToUID,gameSessionUID) VALUES ('" .
    $_SESSION["userUID"] ."','" . $playerToUID ."','". uniqid() ."')" ;

    $result= mysqli_query($connect,$query);
if (!$result){
    die(' Error cannot run query');
}else{


    // Get Game session ID

    $query ="SELECT gameSessionUID FROM playRequests WHERE playerFromUID='". $_SESSION["userUID"] . "' and playerToUID='". $playerToUID."' and requestStatus='pending'" ;

    $result= mysqli_query($connect,$query);
    if (!$result){
        die(' Error cannot run query');
    }

    $gameSessionUID=null;
    while ($row= mysqli_fetch_assoc($result)) {
        $gameSessionUID=$row["gameSessionUID"] ;
    }

    if($gameSessionUID!=null){
        echo "<script>  document.location='playBoard.php?gameSessionUID=" . $gameSessionUID ."';</script>";
    }


}

mysqli_close($connect);

}

?>
<?php require 'footerTab.php'?>