<?php

/************
Created by Eric Morales - SoundHealth - 2015
RemindMed
************/

error_reporting(E_ERROR);
session_start();
//declare some global vars
$thisURL=$_SERVER[HTTP_HOST].'/'.basename(__DIR__);
$globalPath = $_SERVER['DOCUMENT_ROOT'].'/'.basename(__DIR__).'/';

//includes
require_once('Classes/Globals.php');//security
require_once('Classes/DataAccess.php');//database
require_once('nurseDB.php');//nurse object
require_once('displayData.php');//data object
require_once('userData.php');//userData object
require_once('PHPMailer_5.2.0/class.phpmailer.php');


//grab some global vars
$getGlobal = new Globals;
$maxUsers = DataAccess::getLims();
$centerUser = $getGlobal->centerUser;
$centerPW = $getGlobal->centerpw;

//declare nurse object
$nurse = new nurse;
$nurse->centerName = $getGlobal->centerName;
if(empty($_SESSION['username']) || empty($_SESSION['password'])){

    $nurse->nurseId = $_GET['nId'];
    $nurse->globalReference = $getGlobal;
    $nurse->getNurseDbData();

}else{
    $nurse->username = $_SESSION['username'];
    $nurse->password = $_SESSION['password'];
}

if(!isset($nurse->username) && !isset($nurse->password)){

    if($_GET['nId'] == 0){
        $nurse->username = $getGlobal->centerUser;
        $nurse->password = $getGlobal->centerpw;
    }
    
}



//place entries in global array for use in the next script
$_SESSION['username'] = $nurse->username;
$_SESSION['password'] = $nurse->password;
//var_dump($_SESSION);
//declare redirect locations
$prefix = 'Location: http://'.$thisURL;
$noMatchRedirect = $prefix.'/admin/index.php?x=1';

//check to see if the user is super user or sub user
$pass = 0;

if($nurse->username == $getGlobal->centerUser && $nurse->password == $getGlobal->centerpw){//match means super user

    $pass = 1;

    $nurse->nurseId = 0;
    $nurse->nurseFname = $getGlobal->fname;
    $nurse->nurseLname = $getGlobal->lname;
    $nurse->nurseFullName = $getGlobal->fname.' '.$getGlobal->lname;
    $nurse->email = $getGlobal->email;

}else{//no match check for sub user in NurseDB

    $res = DataAccess::getNurses();

    while ($row = mysqli_fetch_assoc($res)){//get each entry in the DB

        $user = $row['username'];
        $pass = $row['password'];

        if ($nurse->username == $user && $nurse->password == $pass){

            $pass = 1;
            $nurse->nurseId = $row['nurseDBId'];
            $nurse->fname = $row['fname'];
            $nurse->lname = $row['lname'];
            $nurse->fullName = $row['fname'].' '.$row['lname'];
            $nurse->email = $row['email'];

        }

    }

}

//check for pass condition, true only if super/sub user match found
if($pass){

    /************
    Main page script
    ************/


    $displayData = setAllData($getGlobal, $nurse);

    placePageHead();

    echo'
            <div class="col-sm-7">
                <div class="row">';

    displayGauges($displayData);

    echo '      </div>
            </div>
            <div class="col-sm-4">';

    nonComplianceTable($getGlobal, $nurse, $displayData);

    echo '<br><br>';

    complianceTable($getGlobal, $nurse, $displayData);


    echo '
            </div>
        </div> <!-- //end container -->
    </body>
    </html>
    ';

}

//if no match, redirect
else{

    header($noMatchRedirect);
    die();

}

function complianceTable($globalVars, $nurse, $displayData){

    $user = new userData;

    echo '
        <div class="content">
            <p style="font-size:2em;"><span></span>Waiting for Confirmation</p>
        </div> 
        <table  style="border: 1px solid #AAA9AA; background-color:#2b2b2b; width:100%; color:#AAA9AA;">
        <thead align="left">
            <tr>
                <th><b><span style="font-size:18px">Name</span></b></th>
                <th><b><span style="font-size:18px">Phone</span></b></th>
                <th><b><span style="font-size:18px">Missed</span></b></th>
                <th><b><span style="font-size:18px">Protocol</span></b></th>
            </tr>
        </thead>
        <tbody>';

    $resz = $user->getUsersByNurseId($globalVars, $nurse->nurseId);

    while ($newRow = mysqli_fetch_assoc($resz)) {

        $id = $newRow['id'];
        $center = $newRow['center_name'];
        $fname = $newRow['fname'];
        $lname = $newRow['lname'];
        $phone = $newRow['phone'];
        $missed = $newRow['missed'];
        $actionTaken = $newRow['actionTaken'];
        $currentProtocol = $newRow['currentProtocol'];
        if($currentProtocol === NULL)
        {
            $currentProtocol= 'not set';
        }

        if($nurse->centerName == $center && !$actionTaken){

            echo '
            <tr onclick="location.href=\'users.php?action=e&id='.$id.'\';" style="border-top: solid 1px #AAA9AA;">
                 <td><span style="font-size:18px">'.$fname.' '.$lname.'</span></td>
                 <td><span style="font-size:18px">'.$phone.'</span></td>
                 <td><span style="font-size:18px;color:red">'.$missed.'</span></td>
                 <td><span style="font-size:18px">'.$currentProtocol.'</span></td>
            </tr>
            ';

        }

    }

    echo '
    <p style="font-size:1em;"><span></span>These users have received today\'s protocol but have not yet confirm it.</p> 
    </tbody></table><br><br>';

}

function nonComplianceTable($getGlobal, $nurse, $displayData){

    $user = new userData;

    echo '
        <div class="content">
        <!--
            <div class="metric"><p style="font-size:0.75em;">'.$displayData->nonCompliantUsers.' Total</p></div>
            -->
            <p style="font-size:2em;"><span></span>Non-Compliant Patient(s)</p>

        </div> 
        <table  style="border: 1px solid #AAA9AA; background-color:#2b2b2b; width:100%; color:#AAA9AA;">
        <thead align="center">
            <tr>
                <th><b><span style="font-size:18px">Name</span></b></th>
                <th><b><span style="font-size:18px">Phone</span></b></th>
                <th><b><span style="font-size:18px">Missed</span></b></th>
                <th><b><span style="font-size:18px">Protocol</span></b></th>
            </tr>
        </thead>
        <tbody>';

        $resz = $user->getUsersByNurseId($getGlobal, $nurse->nurseId);

        while ($newRow = mysqli_fetch_assoc($resz)) {

            $id = $newRow['id'];
            $center = $newRow['center_name'];
            $fname = $newRow['fname'];
            $lname = $newRow['lname'];
            $phone = $newRow['phone'];
            $missed = $newRow['missed'];
            $currentProtocol = $newRow['currentProtocol'];

            if($nurse->centerName == $center && $missed > 0){

                echo '
                <tr onclick="location.href=\'users.php?action=e&id='.$id.'\';" style="border-top: solid 1px #AAA9AA;">
                    <td><span style="font-size:18px">'.$fname.' '.$lname.'</span></td>
                    <td><span style="font-size:18px">'.$phone.'</span></td>
                    <td><span style="font-size:18px;color:red">'.$missed.'</span></td>
                    <td><span style="font-size:18px">'.$currentProtocol.'</span></td>
                </tr>
                ';

            }

        }

        //
        echo '
        <p style="font-size:1em;"><span></span>These users have not been following their protocols</p> 
        </tbody></table>
        ';

}

function displayGauges($displayData){
    $res = DataAccess::getPatientsinfo();
      

    echo '
        <div class="inner">
        <!--
            <div class="row">
                <div class="col-sm-4">
                
                    <div class="content">
                    
                        <div class="metric"><p style="font-size:0.75em;">'.$displayData->activeUsers.'</p></div>
                        <p style="font-size:4em;"><span></span>Users</p>
                    </div> 
                    
                </div>
                 
                <div class="col-sm-4">
                    <div class="content" style="margin-bottom: 10px;">
                        <div class="metric"><p style="font-size:0.75em;">'.$displayData->remainingUsers.'</p></div>
                        <p style="font-size:4em;"><span></span>Available</p>
                    </div>
                </div>
            </div>
            -->
            <div class="row">              
                <div class="col-sm-4 cf-item">
                    <div class="content cf-svp clearfix" id="svp-7">
                        <header style="margin-bottom:5%;">
                            <p><span></span>Confirmed</p>
                        </header>
                        <div class="chart" data-percent="'.$displayData->percentCompliance.'" data-layout="l-6-12-6">
                            <canvas></canvas>
                        </div>
                        <p style="font-size:1em;"><span></span>Percent of users who have confirmed today\'s delivered protocols</p>
                        <div class="metrics" style="margin-bottom:35%;">
                            <span class="metric" style="font-size:7em;">'.$displayData->percentCompliance.'</span>
                            <span class="metric-small">%</span>
                        </div>
                    </div>             
                </div> <!-- //end cf-item -->
              
                <div class="col-sm-5 cf-item">
               <form method="post" action="centerMain.php">
                    <div class="content">
                        <header>
                            <p><span></span>Message a Patient</p>
                        </header>
                       
                            <p><span style="font-size:18px">Send a message</span></p>
                            <style>
                                #patients {
                                        -webkit-appearance: menulist-text;
                                        height: 34px;
                                        width: 335px;
                                }
                            </style>
                            <div class="form-group">

                            <label for="name" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-10">
                            <select id ="patients" name="patients">
                                <option value = "" selected>Please Select a Patient</option>';
                                                        while($row=mysqli_fetch_assoc($res)){
                                                            echo '<option value="'.$row['id'].'" selected>'.$row['fname'].' '.$row['lname'].'</option>';
                                                        }
                                                echo'   </select>
                                              
                                    </div>

                            </div>
                            <br>
                            <br>

                            <div class="form-group">
                                    <label for="message" class="col-sm-2 control-label">Message</label>
                                      <br>
                                     <div class="col-sm-10">
                                        <textarea class="form-control" rows="4" id ="message" name="message"></textarea>
                                        <br>
                                    </div>
                             </div>
                              <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-2">
                                    <input id="submit" name="submit" type="submit" value="Send" class="btn btn-warning">
                                    </div>
                        </div>
                       </form>
                        
                        <div class="canvas">
                            <canvas id="cf-gauge-1-g"></canvas>
                        </div>
                    </div> 
                 
                                        
                </div>
               

                 <!-- //end cf-item --> 
            </div><!-- //end row -->
            <div class="row"> 
            </div><!-- //end row -->
        </div> <!-- //end inner -->
    ';
if(isset($_POST['submit'])){
      $row = mysqli_fetch_assoc($res);
      $message = $_POST['message'];
      $id = $_POST['patients'];
      $res = DataAccess::getUserinfoByID($id);
            $row = mysqli_fetch_assoc($res);
            $phone = $row['phone'];
            $carrier = $row['carrier'];
            switch ($carrier){
                case 'Verizon':
                    $carrieremail = '@vtext.com';
                    break;
                case 'AT&T':
                    $carrieremail = '@txt.att.net';
                    break;
                case 'Sprint':
                    $carrieremail = '@messaging.sprintpcs.com';
                    break;
                case 'T-Mobile':
                    $carrieremail = '@tmomail.net';
                    break;
                case 'MetroPCS':
                    $carrieremail = '@mymetropcs.com';
                    break;
                default:
                    die('No Carrier');
            }
            $getGlobal=new Globals;
            $mail = new PHPMailer();
            $mail->IsSMTP();            // set mailer to use SMTP
            $mail->Host = "localhost";  // specify main and backup server
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->Username = $getGlobal->email;  // SMTP username
            $mail->Password = $getGlobal->emailpw; // SMTP password
            $mail->From = $getGlobal->email;
            $mail->FromName = "RemindMed";
            $to = $phone.$carrieremail;  
            
            $mail->AddAddress($to);
            $mail->WordWrap = 100;
            $mail->IsHTML(true);
            $mail->Body    = $message;
            // if(!$mail->Send()){
            //     echo "Message could not be sent. <p>";
            
            // }else{
            //     echo '<br>Message was sent!<br>';
                
            // }
            
        
      } 
}

function setAllData($globalVars, $nurse){

    //declare user object
    $user = new userData;

    //declare data object
    $displayData = new displayData;
    $displayData->maxUsers = displayData::getMaxUsers($nurse->nurseId);
    $displayData->activeUsers = displayData::getActiveUsers($globalVars, $user, $nurse->nurseId);
    $displayData->remainingUsers = $displayData->maxUsers - $displayData->activeUsers;
    $displayData->attempted = displayData::getAttempted();
    $displayData->delivered = displayData::getDelivered();
    $displayData->nonCompliantUsers = displayData::getNonCompliantUsers($globalVars, $user, $nurse->nurseId);
    $displayData->compliantUsers = $displayData->activeUsers - $displayData->nonCompliantUsers;
    $displayData->todayCompliantUsers = displayData::getTodayCompliantUsers($globalVars, $user, $nurse->nurseId);
    $displayData->todayNonCompliantUsers = $displayData->activeUsers - $displayData->todayCompliantUsers;
    $displayData->percentCompliance = displayData::getPercentCompliance($displayData->activeUsers, $displayData->todayCompliantUsers);
    return $displayData;

}

function placePageHead(){

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta charset="utf-8">
        <title>RemindMed - Analytics Page</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/controlfrog.css" rel="stylesheet" media="screen">   
        <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
        
        <script src="//code.jquery.com/jquery-1.9.1.min.js"></script>    
        <script src="js/moment.js"></script>  
        <script src="js/easypiechart.js"></script>
        <script src="js/gauge.js"></script>   
        <script src="js/chart.js"></script>
        <script src="js/jquery.sparklines.js"></script>           
        <script src="js/bootstrap.js"></script>
        <script src="js/controlfrog-plugins.js"></script>
        
        <!--[if lt IE 9]>
            <script src="../../js/respond.min.js"></script>
            <script src="../../js/excanvas.min.js"></script>
        <![endif]-->
        
        <style>

            .patients {
                -webkit-appearance: menulist-button;
                height: 50px;
                }
            .smallNum{
                font-size:2em;
            }
            table {
                border-collapse: collapse;
            }

            td {
                padding-top: .5em;
                padding-bottom: .5em;
            }
        </style>
        
        <script>
            var themeColour = \'black\';
        </script>
        <script src="js/controlfrog.js"></script>
    </head>
    <body class="black">
        <div class="cf-container cf-nav-active" stye="max-height:100vh;">
            <div class="custom1" style="text-align:left;">
                <table style="width:50vw; text-align:left;">
                    <tr style="width:50vw;">
                        <td style="width:5%;">
                            <a href="centerMain.php">
                                <img style="width:50%;" id="analyticsButton" name="analyticsButton" src="buttons/analytics.png">
                            </a>
                        </td>
                        <td style="width:5%;">
                            <a href="users.php">
                                <img style="width:50%;" id="centerButton" name="centerButton" src="buttons/patients.png">
                            </a>
                        </td>
                        <td class="td2" style="width:5%;">
                            <a href="protocols.php">
                                <img style="width:50%;" id="journeyButton" name="journeyButton" src="buttons/protocols.png">
                            </a>
                        </td>
                        <td class="td2" style="width:5%;">
                            <a href="orders.php">
                                <img style="width:50%;" id="resourcesButton" name="resourcesButton" src="buttons/orders.png">
                            </a>
                        </td>
                        <td class="td2" style="width:5%;">
                            <a href="logout.php">
                                <img style="width:50%;" id="resourcesButton" name="resourcesButton" src="buttons/logoutButton.png">
                            </a>
                        </td>
                    </tr>
                </table>
            </div>';

}

?>