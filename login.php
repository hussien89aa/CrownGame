<?php
require_once "SessionManagement.php";
//Clear all sessions that we used
$_SESSION["userName"] = null;
$_SESSION["userUID"] = null;

require 'headerTab.php';

?>
    <body style="background-color: #f9f9f9">
    <div class="container" style="background-color: #fff">
        <br/>
        <form id='login' action="login.php" method='post' accept-charset='UTF-8'>
            <div class="panel panel-primary">
                <div class="panel-heading">Login User</div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for='username' >UserName*:</label>
                        <input type='text' name='userName' id='username'  maxlength="50"  required/>
                    </div>
                    <div class="form-group">
                        <label for='password' >Password*:</label>
                        <input type='password' name='password' id='password' maxlength="50" required/>
                    </div>
                    <input type='submit' class="btn btn-default" name='Submit' value='Login' />
                </div>
            </div>
        </form>

        <?php
        require("DBInfo.inc");
        // secure post call
        if(!empty($_POST['userName']) and !empty($_POST['password'])) {
            $password = md5(trim($_POST['password']));

            $mysqli = new mysqli($hostDB, $userDB,$passwordDB,$databaseDB);
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
            $query ="select userName,userUID from login  where userName=? and password=?" ;
            $loginInUser=null;
            /* create a prepared statement */
            if ($stmt = $mysqli->prepare($query)) {
                /* bind parameters for markers */
                $stmt->bind_param("ss", $_POST['userName'],$password);
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


            if (! empty($loginInUser)) {
                $_SESSION["userName"] = htmlentities($loginInUser);
                $_SESSION["userUID"] = $userUID;

                echo "<script> document.location='ControlPanel.php';</script>";
            }else{
                echo "<pre>";
                echo "</br><div class=\"alert alert-danger\">Database Message: Fail login</div>";
                echo "</pre>";
            }
        }

        ?>
    </div>
    </body>
<?php require 'footerTab.php'?>