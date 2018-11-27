<?php require 'headerTab.php'?>
<body style="background-color: #f9f9f9">
<div class="container" style="background-color: #fff">
    <br/>

<form id='login' action="addUser.php" method='post' accept-charset='UTF-8'>
    <div class="panel panel-primary">
        <div class="panel-heading">Add User</div>
        <div class="panel-body">
            <div class="form-group">
            <label for='username' >UserName*:</label>
            <input type='text' name='userName' id='username'  maxlength="50" required />
            </div>
            <div class="form-group">
            <label for='password' >Password*:</label>
            <input type='password' name='password' id='password' maxlength="50" required />
            </div>

            <input type='submit' class="btn btn-default" name='Submit' id='submit' value='Add' />

        </div>
        <div class="alert alert-info" style="margin: 20px">
            <strong>Rules</strong>
            <ul>
                <li>User name could has number and character only no less than 5 character</li>
            </ul>
        </div>
    </div>

</form>
</div>


<?php
//Database Authentication
require("DBInfo.inc");

// Server side code
//Read form submit info post request
$userName = $_POST['userName'];

$password = md5(trim($_POST['password']) );


//Not secure post call
if(!empty($userName) and !empty($password)) {


    if (! preg_match("/^[a-zA-Z0-9]+$/", $userName) || strlen($userName)<5){
        echo "<pre>";
        echo "</br><div class=\"alert alert-danger\">User name Has not valid characters use only characters and numbers</div>";
        echo "</pre>";
    }else{

        $mysqli = new mysqli($hostDB, $userDB,$passwordDB,$databaseDB);
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        // if user already exisit
        $query ="select userName,userUID from login  where userName=?" ;
        $loginInUser=null;
        /* create a prepared statement */
        if ($stmt = $mysqli->prepare($query)) {
            /* bind parameters for markers */
            $stmt->bind_param("s", $userName);
            /* execute query */
            $stmt->execute();
            /* bind result variables */
            $stmt->bind_result($loginInUser,$userUID);
            /* fetch value */
            $stmt->fetch();
            /* close statement */
            $stmt->close();
        }
        /* close connection */
        $mysqli->close();

        if($loginInUser!=null){
            echo "<pre>";
            echo "</br><div class=\"alert alert-danger\">User name already use! enter different name</div>";
            echo "</pre>";
        }else{

            //connect to database
            $connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
            if(mysqli_connect_errno()){
                die(" cannot connect to database ". mysqli_connect_error());
            }

            $query ="Insert into login(userName,password,userUID) VALUES ('" .
                $userName ."','" . $password ."','". uniqid() ."')" ;

            $result= mysqli_query($connect,$query);
            if (!$result){
                die(' Error cannot run query');
            }else{
                echo "<pre>";
                echo "</br><div class=\"alert alert-success\">Account is created!! login to start play game</div>";
                echo "</pre>";
            }
            mysqli_close($connect);
        }
    }









 }

?>

<script>
    var myInput = document.getElementById("username");
    var myInputPassword = document.getElementById("password");
    var submitBtn=document.getElementById("submit");
    submitBtn.disabled = true;
    // When the user starts to type something inside the password field
    myInput.onkeyup = function() {
        check();
    }
    myInputPassword.onkeyup = function() {
        check();
    }

    function check() {
        submitBtn.disabled = true;
        // Validate lowercase letters
        var lowerCaseLetters = /^[0-9a-zA-Z]+$/;
        if (!myInput.value.match(lowerCaseLetters)|| myInputPassword.value.length<1) {
            return;
        }
        if(myInput.value.length >=5)  {
            submitBtn.disabled = false;
        }
    }
</script>
</body>
<?php require 'footerTab.php'?>