<?php
require_once "SessionManagement.php";

?>


<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <title>Crown Game</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-111698541-1"></script>
    <link rel="shortcut icon" href="images/crownicon.ico" />
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-111698541-1');
    </script>

<style>

    body{
        background: #f9f9f9;
    }
    body.container{
        padding-left: 10%;
        padding-right: 10%;
        background-color:#FFFFFF;
    }
</style>

</head>
<?php
?>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">Crown Game</a>
        </div>
        <ul class="nav navbar-nav">
            <li class="active"><a href="<?= empty($_SESSION["userName"])?"login.php":"ControlPanel.php" ?>">Home</a></li>

        </ul>
        <ul class="nav navbar-nav navbar-right">

            <?php if ( !empty($_SESSION["userName"])):?>
            <li><a href="./login.php"><span class="glyphicon glyphicon-log-out"></span> Logout(<?= $_SESSION["userName"]?>)</a></li>
            <?php else:?>
                <li><a href="./addUser.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
                <li><a href="./login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
            <?php endif;?>
        </ul>
    </div>
</nav>

<?php
//Make sure user is login to have access to the pages
if(basename($_SERVER['PHP_SELF'])!="login.php" and basename($_SERVER['PHP_SELF'])!="addUser.php"){

    if( empty($_SESSION["userName"]) or $_SESSION["userName"]==null){

        die("You donot have access permission login first");
    }
}
?>