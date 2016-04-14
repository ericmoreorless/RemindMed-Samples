<?php

/************
Created by Eric Morales - SoundHealth - 2015
RemindMed
************/

error_reporting(E_ERROR);
session_start();

//includes
require_once('Classes/Globals.php');//security
require_once('Classes/DataAccess.php');//database
require_once('nurseDB.php');//nurse object
require_once('userData.php');//userData object

//declare some global vars
$thisURL=$_SERVER[HTTP_HOST].'/'.basename(__DIR__);
$globalPath = $_SERVER['DOCUMENT_ROOT'].'/'.basename(__DIR__).'/';

//get the date
date_default_timezone_set('US/Eastern');
$todaysDate = strtotime(date('m/d/Y'));

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

//place entries in global array for use in the next script
$_SESSION['username'] = $nurse->username;
$_SESSION['password'] = $nurse->password;

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

}

else{//no match check for sub user in NurseDB

    $res = DataAccess::getNurses();

    while ($row = mysqli_fetch_assoc($res)){//get each entry in the DB

        $user = $row['username'];
        $pass = $row['password'];

        if ($nurse->username == $user && $nurse->password == $pass){

            $pass = 1;

            $subNurse = 1;
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

    $numUsers = userData::countUsersByNurseId($getGlobal, $nurse->nurseId);
    $action = $_GET['action'];

    if($nurse->nurseId != 0){

        $maxUsers = userData::getSubNurseMaxUsers($getGlobal, $nurse->nurseId);
        echo " max users: ";
        var_dump($maxUsers);

    }

    placePageHead($nurse, $subNurse);

    if($action == 'a'){

        if($numUsers<$maxUsers){

            //check for error code
            if(isset($_GET['ecode']) && $_GET['ecode'] == 1){

                $displayError = '
                                <tr>
                                    <td colspan="2"><b>Failed to add patient! Please try again.</b></td><td></td>
                                </tr>';

                //get attempted entries and place them back into form.
                $newUser = getPrevEntries();

            }


            $carrierChoices = array('Verizon','AT&T','Sprint','T-Mobile','MetroPCS');

            foreach ($carrierChoices as $choice){

                if($newUser->carrier == $choice){

                    $carrierSelected[$choice] = 'selected="selected"';

                }

                else{

                    $carrierSelected[$choice] = '';

                }

            }
            
            if($_GET['t'] == 'n'){

                //display add user table
                echo'
                <div class="row" style="margin-top:1%; margin-left:3%;">
                    <form id="addUserForm" name="addUserForm" method="post" action="addNurse.php" onsubmit="return chkForm(this)">
                        <table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" style="width:95%; font-size:14px; color:#AAA9AA; text-align:left;">
                            <tbody style="width:90%;">
                                <td style="vertical-align:top;">
                                    <tr>
                                        <td colspan="2"><b>Add Nurse:</b></td><td></td>
                                    </tr>
                                    <tr colspan="2">
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-right:0px; width:5%;">First Name: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" name="fname" type="text" id="fname" value="'.$newNurse->fname.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-right:0px; width:5%;">Last Name: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" name="lname" type="text" id="lname" value="'.$newNurse->lname.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:5%;">Email: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="example@me.com" name="email" type="text" id="email" size="20" value="'.$newNurse->email.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:5%;">Username: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="" name="username" type="text" id="username" size="20" value="'.$newNurse->username.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:5%;">Password: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="" name="password" type="text" id="password" size="20" value="'.$newNurse->password.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:7%;">Maximum Users: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="the amount of users this nurse is able to add to the system." name="maxUsers" type="text" id="maxUsers" size="20" value="'.$newNurse->maxUsers.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr style="text-align:center;">
                                        <td colspan="2"><input style="background-color:#F49118; width:90%; color:#fff;" type="submit" value="Submit" name="submit" /></td>
                                    </tr>
                                </td>
                            </tbody>
                        </table>
                    </form>
                </div>';

            }else{
                //display add user table
                echo'
                <div class="row" style="margin-top:1%; margin-left:3%;">
                    <form id="addUserForm" name="addUserForm" method="post" action="addUser.php" onsubmit="return chkForm(this)">
                        <table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" style="width:100%; font-size:14px; color:#AAA9AA; text-align:left;">
                            <tbody style="width:90%;">
                                <td style="vertical-align:top;">
                                    <tr>
                                        <td colspan="2"><b>Add Patient:</b></td><td></td>
                                    </tr>
                                    <tr colspan="2">
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-right:0px; width:1%;">First Name: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" name="fname" type="text" id="fname" value="'.$newUser->fname.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-right:0px; width:1%;">Last Name: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" name="lname" type="text" id="lname" value="'.$newUser->lname.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:1%;">Phone: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="no formatting, e.g. 1112223333" name="phone" type="text" id="phone" size="20" value="'.$newUser->phone.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Carrier:
                                            <select name="carrier">
                                            <option value="">Select a Carrier</option>';

                foreach ($carrierChoices as $choice){

                    echo '
                                            <option value="'.$choice.'" '.$carrierSelected[$choice].'>'.$choice.'</option>';
                                    
                }

                echo'
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr style="text-align:center;">
                                        <td colspan="2"><input style="background-color:#F49118; width:90%; color:#fff;" type="submit" value="Submit" name="submit" /></td>
                                    </tr>
                                </td>
                            </tbody>
                        </table>
                    </form>
                </div>';

            }
            

        }else{

            //prevent adding additional users, inform user about limit.
            echo'
            <div class="row" style="margin-top:1%; margin-left:3%;">
                <table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" style="width:100%; font-size:14px; color:#AAA9AA; text-align:left;">
                    <tbody style="width:90%;">
                        <td style="vertical-align:top;">
                            <tr>
                                <td colspan="2"><b>You have reached your user limit, please purchase more users!</b></td><td></td>
                            </tr>
                        </td>
                    </tbody>
                </table>
            </div>';

        }

    }

    if($action == 'e' && isset($_GET['id'])){

        $user = new userData;

        $user->centerId = $_GET['id'];
        $user->getById($getGlobal);

        $carrierChoices = array('Verizon','AT&T','Sprint','T-Mobile','MetroPCS');

        foreach ($carrierChoices as $choice){

            if($user->carrier == $choice){

                $carrierSelected[$choice] = 'selected="selected"';

            }else{

                $carrierSelected[$choice] = '';

            }

        }

        if($_GET['t'] == 'n'){

            $nurseEdit = new nurse;
            $nurseEdit->globalReference = $getGlobal;
            $nurseEdit->nurseId = $_GET['id'];
            $nurseEdit->getNurseDbData();

            echo'
            <div class="row" style="margin-top:1%;">
                <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="width:90%; font-size:14px; color:#AAA9AA; text-align:left; margin-left:3%;">
                    <td style="vertical-align : top;">
                        <form id="editUserForm" name="editUserForm" method="post" action="editNurse.php" onsubmit="return chkForm(this)">
                            <tr>
                                <td colspan="2"><b>Edit Nurse:</b></td>
                            </tr>
                            <tr colspan="2">
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-right:0px; width:5%;">First Name: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" name="fname" type="text" id="fname" value="'.$nurseEdit->fname.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-right:0px; width:5%;">Last Name: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" name="lname" type="text" id="lname" value="'.$nurseEdit->lname.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:5%;">Email: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="example@me.com" name="email" type="text" id="email" size="20" value="'.$nurseEdit->email.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:5%;">Username: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="" name="username" type="text" id="username" size="20" value="'.$nurseEdit->username.'" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:5%;">Password: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="" name="password" type="text" id="password" size="20" value="'.$nurseEdit->password.'" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-right:0px; width:7%;">Maximum Users: </td>
                                        <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; border-bottom:1px solid #AAA9AA; border-left:0px;">
                                            <input style="width:100%; text-align:center; background-color:#2b2b2b; color:#AAA9AA;" placeholder="the amount of users this nurse is able to add to the system." name="maxUsers" type="text" id="maxUsers" size="20" value="'.$nurseEdit->maxUsers.'" />
                                        </td>
                                    </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr style="text-align:center;">
                                        <input name="id" type="hidden" id="id" value="'.$_GET['id'].'" />
                                <td><input style="background-color:#F49118; width:90%; color:#fff;" type="submit" value="Submit" id="submit" name="submit" /></td>
                                <td><input style="background-color:#570604; width:40%; color:#fff;" type="submit" value="Delete" id="delete" name="delete" onclick="return confirm(\'Are you sure you want to delete this item?\');"/></td>
                            </tr>
                        </form>
                    </td>
                </table>
            </div>';

        }

        else {

            echo'
            <div class="row" style="margin-top:1%;">
                <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="width:90%; font-size:14px; color:#AAA9AA; text-align:left; margin-left:3%;">
                    <td style="vertical-align : top;">
                        <form id="editUserForm" name="editUserForm" method="post" action="editUser.php" onsubmit="return chkForm(this)">
                            <tr>
                                <td colspan="2"><b>Edit Patient:</b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; width:100%;">First Name: <input style="background-color:#2b2b2b; color:#AAA9AA;" name="fname" type="text" id="fname" size="20" value="'.$user->fname.'" /></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; width:100%;">Last Name: <input style="background-color:#2b2b2b; color:#AAA9AA;" name="lname" type="text" id="lname" size="20" value="'.$user->lname.'" /></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA; width:100%;">Phone: <input style="background-color:#2b2b2b; text-align:left; color:#AAA9AA;" placeholder="no formatting, e.g. 1112223333" name="phone" type="text" id="phone" size="20" value="'.$user->phone.'" /></td>
                            </tr>
                            <tr>
                                <td colspan="2">Carrier:
                                    <select name="carrier">
                                        <option value="">Select a Carrier</option>';

            foreach ($carrierChoices as $choice){

                echo '
                                        <option value="'.$choice.'" '.$carrierSelected[$choice].'>'.$choice.'</option>';

            }

            echo '
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr style="text-align:center;">
                                        <input name="id" type="hidden" id="id" value="'.$user->centerId.'" />
                               <td><input style="background-color:#F49118; padding:20px 100px 20px 100px;  color:#fff;" type="submit" value="Submit" id="submit" name="submit" /></td>
                                <td><input style="background-color:#570604; padding:20px 100px 20px 100px;  color:#fff;" type="submit" value="Delete" id="delete" name="delete" onclick="return confirm(\'Are you sure you want to delete this item?\');"/></td>
                            </tr>
                        </form>
                    </td>
                </table>
            </div>';

        }

            

    }

    placeTable($getGlobal, $nurse, $subNurse); 

    echo '
    </body>
    </html>
    ';

}

//if no match, redirect
else{

    header($noMatchRedirect);
    die();

}

function fixDate($date){

    $temp = explode("/",$date);

    $fixed = $temp[2].'-'.$temp[0].'-'.$temp[1];

    return $fixed;

}

function unfixDate($date){

    $temp = explode("-",$date);

    $fixed = $temp[1].'/'.$temp[2].'/'.$temp[0];

    return $fixed;

}

function fixPhone($phone){

    $fixed = '('.$phone[0].$phone[1].$phone[2].') '.$phone[3].$phone[4].$phone[5].'-'.$phone[6].$phone[7].$phone[8].$phone[9];
    
    return $fixed;

}

function placePageHead($nurse, $subNurse){

    echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta charset="utf-8">
            <title>RemindMed - Users Page</title>
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

            input, select, textarea{
                color:#000;
                border:none;
            }

            entry{
                background-color:#000;
                color:#000;
                border:1px;
                solid white;
                width:100%;
            }

            .data{
                width:100%;
                text-align:center;
            }
            table {
                border-collapse: collapse;
            }

            td {
                padding-top: .5em;
                padding-bottom: .5em;
            }
            a:link {
                color:#CCCCCC;
                background-color: transparent;
                text-decoration: none;
            }
            a:visited {
                color: #CCCCCC;
                background-color: transparent;
                text-decoration: none;
            }
            a:hover {
                color: #F49118;
                background-color: transparent;
                text-decoration: none;
            }
            a:active {
                color:#CCCCCC;
                background-color: transparent;
                text-decoration: none;
            }
            </style>    
            <script>
                var themeColour = \'black\';

                function chkForm(el){
                    var valid = true;
                    var oForm = el;
                    var len = oForm.elements.length;
                    for(var i=0; i < len-4; i++) {
                        var oField = oForm.elements[i];
                        if (oField.value == \'\'){
                            alert(\'You must fill in the \'+oField.name+\' field\');
                            //alert(\'You must fill in the username and password!\');
                            valid = false;
                            break;
                        }
                    }
                    return valid;
                }
                
            </script>
            <script src="js/controlfrog.js"></script>
        </head>
        <body class="black">

            <div class="cf-nav cf-nav-state-min">
                    <ul>
                        <li class="current cf-nav-shortcut">
                        <li class="cf-nav-shortcut">
                            <a href="users.php?action=a"><img style="margin-left:.5%;height:25px;width:25px;margin-top:-30px" id="addCenterButton" name="addCenterButton" src="buttons/addCenter.png"></a>
                        </li>';
   /* if ($subNurse != 1){
        echo '
                        <li class="cf-nav-shortcut">
                            &nbsp;
                        <li class="cf-nav-shortcut">
                            <a href="users.php?action=a&t=n"><img style="margin-top:-50px;height:25px;width:25px; id="addNurseButton" name="addNurseButton" src="buttons/addNurse.png"></a>
                        </li>';   
    }*/

   

    echo '
              </ul>
                </div>       

            <div class="cf-container cf-nav-active">
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
                </div>
            </div>';

}

function placeNurseTableHeader(){

    echo '  <thead align="left">
                <tr>
                <th><b><span style="font-size:18px">Name</span></b></th>
                <th><b><span style="font-size:18px">Email</span></b></th>
                <th><b><span style="font-size:18px">Username</span></b></th>
                <th><b><span style="font-size:18px">maxUsers</span></b></th>
                </tr>
            </thead>';

}

function placeTableHeader(){

    echo '  <thead align="left">
                <tr>
                <th><b><span style="font-size:18px">Name</span></b></th>
                <th><b><span style="font-size:18px">Phone</span></b></th>
                <th><b><span style="font-size:18px">Missed</span></b></th>
                <th><b><span style="font-size:18px">Protocol</span></b></th>
                </tr>
            </thead>';

}

function getUserInfo($globalVars, $centerTable_data){

    //declare user object
    $user = new userData;
    $user->centerId = $centerTable_data['id'];
    $user->nurseId = $centerTable_data['nurseId'];
    $user->fname = $centerTable_data['fname'];
    $user->lname = $centerTable_data['lname'];
    $user->phone = $centerTable_data['phone'];
    $user->carrier = $centerTable_data['carrier'];
    $user->actionTaken = $centerTable_data['actionTaken'];
    $user->received = $centerTable_data['received'];
    $user->latestReceived = $centerTable_data['latestReceived'];
    $user->latestVisit = $centerTable_data['latestVisit'];
    $user->daysSince = $centerTable_data['daysSince'];
    $user->missed = $centerTable_data['missed'];

    //get uprotocols data
    $protocolTable_data = userData::getuProtocolTableData($globalVars, $user->centerId);
    $user->uProtocolId = $protocolTable_data['id'];
    $user->protocolId = $protocolTable_data['protocolId'];
    $user->apptNum = $protocolTable_data['apptNum'];
    $user->levelNum = $protocolTable_data['levelNum'];

    //get userMed data
    $user->userMedData = userData::getUserMedTableData($globalVars, $user->centerId);

    return $user;

}

function getStart($user){

    $numMeds = count($user->userMedTable_data);

    if($numMeds == 0){

        $start = 'not set';

    }else{

        for ($i = 0;$i<count($user->userMedTable_data);$i++){

            if($i == 0){

                $start = unfixDate($user->userMedTable_data[$i]["start"]);

            }

            else{

                if(strtotime($user->userMedTable_data[$i]["start"]) < strtotime($start)){

                    $start = unfixDate($user->userMedTable_data[$i]["start"]);

                }

            }

        }

    }

    return $start;

}

function getEnd($user){

    $numMeds = count($user->userMedTable_data);

    if($numMeds == 0){

        $end = 'not set';

    }

    else{

        for ($i = 0;$i<count($user->userMedTable_data);$i++){

            if($i == 0){

                $end = unfixDate($user->userMedTable_data[$i]["end"]);

            }

            else{

                if(strtotime($user->userMedTable_data[$i]["end"]) > strtotime($end)){

                    $end = unfixDate($user->userMedTable_data[$i]["end"]);

                }

            }

        }

    }

    return $end;

}

function getCurrentProtocol($user){

    if($user->protocolId == 0 || empty($user->protocolId) || !isset($user->protocolId)){

        $currentProtocol = 'not set';



    }else{

        $currentProtocol =  DataAccess::getProtocolNameByProtocolId($user->protocolId);

    }

    return $currentProtocol;

}

function checkLatestVisit($user){

    if($user->latestVisit == 0 || empty($user->latestVisit) || !isset($user->latestVisit)){

        $latestVisit = 'not set';

    }

    else{

        $latestVisit = unfixDate($user->latestVisit);

    }

    return $latestVisit;

}

function placeTable($globalVars, $nurse, $subNurse){

    echo '
        <div class="row" style="margin:auto;">
            <table style="font-size:14px; width:90%; margin-top:2%; margin-left:3%; background-color:#2b2b2b; color:#AAA9AA;">';
    
    placeTableHeader();

    echo '  <tbody>';

    $res = userData::getUsersByNurseId($globalVars, $nurse->nurseId);

    if(!$res){

        echo '
                <tr>
                    <td colspan = "3">Click yellow plus size to add a patient. You will be able to view and manage their information here!<td>
                </tr>';

    }

    else{

        //
        $i = 0;

        while ($userRow = mysqli_fetch_assoc($res)) {

            $i++;

            $user = new userData;
            $user->centerId = $userRow['id'];
            $user->getCenterTableData($globalVars);
            $user->getuProtocolTableData($globalVars);
            $user->getUserMedTableData($globalVars);



            $currentProtocol = DataAccess::getCurrentProtocolByID($user->centerId);
           
            
            echo '
                <tr style="border-top: solid 1px #AAA9AA;">
                    <td><a href=\'users.php?action=e&id='.$user->centerId.'\';"><span style="font-size:18px">'.$user->fname.' '.$user->lname.'</span></a></td>
                    <td><span style="font-size:18px">'.$user->phone.'</span></td>
                    <td><span style="font-size:18px">'.$currentProtocol.'</span></td>
                    <td><span style="font-size:18px;color:red">'.$user->missed.'</span></td>
                </tr>
                ';

        }

        //
        echo '
            </tbody></table><br><br>';

    }

    echo '
            </div>
        </div> <!-- //end container -->';

    /*if($subNurse != 1){

        echo '
        <div class="row" style="margin:auto;">
            <table style="font-size:14px; width:90%; margin-top:2%; margin-left:3%; background-color:#2b2b2b; color:#AAA9AA;">';
    
        placeNurseTableHeader();

        echo '  <tbody>';

        $res = nurse::getNurses($globalVars);

        if(!$res){

            echo '
                    <tr>
                        <td colspan = "3">Click the RED add button to add a nurse<td>
                    </tr>';

        }

        else{

            //
            $i = 0;

            while ($nurseRow = mysqli_fetch_assoc($res)) {

                $i++;

                $nurse = new nurse;
                $nurse->nurseId = $nurseRow['nurseDBId'];
                $nurse->fname = $nurseRow['fname'];
                $nurse->lname = $nurseRow['lname'];
                $nurse->email = $nurseRow['email'];
                $nurse->username = $nurseRow['username'];
                $nurse->maxUsers = $nurseRow['maxUsers'];

                echo '
                    <tr onclick="location.href=\'users.php?action=e&id='.$nurse->nurseId.'&t=n\';" style="border-top: solid 1px #AAA9AA;">
                        <td><a href=\'users.php?action=e&id='.$nurse->nurseId.'&t=n\';"><span style="font-size:18px">'.$nurse->fname.' '.$nurse->lname.'</span></a></td>
                        <td><span style="font-size:18px">'.$nurse->email.'</span></td>
                        <td><span style="font-size:18px">'.$nurse->username.'</span></td>
                        <td><span style="font-size:18px">'.$nurse->maxUsers.'</span></td>
                    </tr>
                    ';

            }

            //
            echo '
                </tbody></table><br><br>';

        }
    

    }*/

    echo '
            </div>
        </div> <!-- //end container -->';

}

function getPrevEntries(){

    $user = new userData;

    $user->fname = $_SESSION['fname'];
    $user->lname = $_SESSION['lname'];
    $user->phone = $_SESSION['phone'];
    $user->carrier = $_SESSION['carrier'];

    return $user;

}


?>