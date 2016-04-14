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
require_once('displayData.php');//data object
require_once('userData.php');//userData object

//declare some global vars
$thisURL=$_SERVER[HTTP_HOST].'/'.basename(__DIR__);
$globalPath = $_SERVER['DOCUMENT_ROOT'].'/'.basename(__DIR__).'/';

//grab some global vars
$getGlobal = new Globals;
$maxUsers = DataAccess::getLims();
$centerUser = $getGlobal->centerUser;
$centerPW = $getGlobal->centerpw;

//declare nurse object
$nurse = new nurse;
$nurse->centerName = $getGlobal->centerName;

//place entries in global array for use in the next script
if(empty($_SESSION['username']) || empty($_SESSION['password'])){

    $nurse->nurseId = $_GET['nId'];
    $nurse->globalReference = $getGlobal;
    $nurse->getNurseDbData();

}else{
    $nurse->username = $_SESSION['username'];
    $nurse->password = $_SESSION['password'];
}

//declare redirect locations
$prefix = 'Location: http://'.$thisURL;
$noMatchRedirect = $prefix.'/admin/index.php?x=1';

//check to see if the user is super user or sub user
$pass = 0;

if($nurse->username == $getGlobal->centerUser && $nurse->username == $getGlobal->centerpw){//match means super user
    
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
//if($pass){
    $action=$_GET['action'];
    $PassFail=$_GET['p'];
    $notifier=$_GET['n'];

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta charset="utf-8">
        <title>RemindMed - Protocols Page</title>
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
        .sameSize{
            height : 200px;
            margin:auto;
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
            text-align:left;
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
            var themeColour=\'black\';
        </script>
        <script src="js/controlfrog.js"></script>
    </head>
    <body class="black">

        <div class="cf-nav cf-nav-state-min">
            
                <ul>
                    
                    <li class="cf-nav-shortcut">
                        <a href="orders.php?action=a"><img style="margin-left:.5%;height:25px;width:25px;" id="add" name="add"  src="buttons/addCenter.png"></a>
                    </li>
                </ul>
            </div>

        <div class="cf-container cf-nav-active">
            <div class="custom1" style="text-align:left;">
                <table style="width:50vw; text-align:left;">
                    <tr style="width:50vw;">
                        <td style="width:5%;">
                            <a href="centerMain.php?nId='.$nurse->nurseId.'">
                                <img style="width:50%;" id="analyticsButton" name="analyticsButton" src="buttons/analytics.png">
                            </a>
                        </td>
                        <td style="width:5%;">
                            <a href="users.php?nId='.$nurse->nurseId.'">
                                <img style="width:50%;" id="centerButton" name="centerButton" src="buttons/patients.png">
                            </a>
                        </td>
                        <td class="td2" style="width:5%;">
                            <a href="protocols.php?nId='.$nurse->nurseId.'">
                                <img style="width:50%;" id="journeyButton" name="journeyButton" src="buttons/protocols.png">
                            </a>
                        </td>
                        <td class="td2" style="width:5%;">
                            <a href="orders.php?nId='.$nurse->nurseId.'">
                                <img style="width:50%;" id="resourcesButton" name="resourcesButton" src="buttons/orders.png">
                            </a>
                        </td>
                        <td class="td2" style="width:5%;">
                            <a href="logout.php?nId='.$nurse->nurseId.'">
                                <img style="width:50%;" id="resourcesButton" name="resourcesButton" src="buttons/logoutButton.png">
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';
        if($action=='a'){
            echo '
            <div class="row" style="margin-top:1%; margin-left:5%; margin-right:5%;">
                <form id="addProtocolForm" name="addProtocolForm" method="post" action="Media/upload.php" enctype="multipart/form-data">
                    <table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" style="width:100%; font-size:14px; color:#AAA9AA; text-align:left; margin:auto;">
                        <tr>
                            <td colspan="2"><b>Add Protocol:</b></td><td></td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA;">Name: </td>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="newName" type="text" id="newName" size="125" placeholder="Give this protocol a name"/></td>
                        </tr>
                        <tr>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;">Message: </td>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="newMessage" type="text" id="newMessage" size="125" placeholder="Instruct the user on how much to take"/></td>
                        </tr>
                        <tr>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;">Description: </td>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="newDescription" type="text" id="newDescription" size="125" placeholder="Brief description of this protocol"/></td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr class="data">
                            <td colspan="2" style="color:#AAA9AA;">
                                <table style="width:100%;">
                                    <tr>
                                        <td style="text-align:left; width:50%;">
                                            Medicine:
                                        </td>
                                        <td style="text-align:left; width:50%;">
                                            Information Resource:
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:50%;">
                                            <input type="file" name="medicine" id="medicine" style="margin:auto; color:#AAA9AA; text-align:left;">
                                        </td>
                                        <td style="width:50%;">
                                            <input type="file" name="info" id="info" style="margin:auto; color:#AAA9AA; text-align:left;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; width:50%; vertical-align:top;">
                                            An image of the medicine will appear on the page shown to the user.
                                        </td>
                                        <td style="text-align:left; width:50%; vertical-align:top;">
                                            A video (.mp4, .mov), image (.jpg,.png) or pdf that contains information about this protocol for the user. 
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr class="data">
                            <td colspan="2">
                                <input type="hidden" name="type" id="type" value="add" />
                                <input style="background-color:#F49118; width:90%; color:#fff;" type="submit" value="Submit" name="submit" id="submit"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
                </form>
            </div>';    
        }
        if($action=='e' && isset($_GET['id'])){
            $id=$_GET['id'];
            $res=DataAccess::getProtocolByID($id);
            $row=mysqli_fetch_assoc($res);
            $protoID=$row['id'];
            $protoName=$row['name'];
            $medicine=$row['medicine'];
            $message=$row['message'];
            $interval=$row['interval'];
            $description=$row['description'];
            $videoRes=$row['videoResource'];
            $docuRes=$row['documentResource'];
            $imageRes=$row['graphicResource'];
            echo '
            <div class="row" style="margin-top:1%; margin-left:5%; margin-right:5%;">
                <form id="editProtocolForm" name="editProtocolForm" method="post" action="Media/upload.php" enctype="multipart/form-data">
                    <table border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="width:100%; font-size:14px; color:#AAA9AA; text-align:left; margin:auto;">
                        <tr>
                            <td colspan="2"><b>Edit Protocol:</b></td><td></td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr class="data">
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA;">Name: </td>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="newName" type="text" id="newName" value="'.$protoName.'" size="125" placeholder="Give this protocol a name"/></td>
                        </tr>
                        <tr class="data">
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;">Message: </td>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="newMessage" type="text" id="newMessage" value="'.$message.'" size="125" placeholder="Instruct the user on how much to take"/></td>
                        </tr>
                        <tr class="data">
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;">Description: </td>
                            <td style="background-color:#2b2b2b; color:#AAA9AA; border-top:1px solid #AAA9AA;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="newDescription" type="text" id="newDescription" value="'.$description.'" size="125" placeholder="Brief description of this protocol"/></td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr class="data">
                            <td colspan="2" style="color:#AAA9AA;">
                                <table style="width:100%;">
                                    <tr>
                                        <td style="text-align:left; width:50%;">
                                            Medicine:
                                        </td>
                                        <td style="text-align:left; width:50%;">
                                            Information Resource:
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:50%;">
                                            <input type="file" name="medicine" id="medicine" style="margin:auto; color:#AAA9AA; text-align:left;">
                                        </td>
                                        <td style="width:50%;">
                                            <input type="file" name="info" id="info" style="margin:auto; color:#AAA9AA; text-align:left;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align:left; width:50%; vertical-align:top;">
                                            An image of the medicine will appear on the page shown to the user.
                                        </td>
                                        <td style="text-align:left; width:50%; vertical-align:top;">
                                            A video (.mp4, .mov), image (.jpg,.png) or pdf that contains information about this protocol for the user. 
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr class="data">
                            <td colspan="2">
                                <input type="hidden" name="protocolID" id="protocolID" value="'.$id.'" />
                                <input type="hidden" name="type" id="type" value="edit" />
                                <input style="background-color:#F49118; width:90%; color:#fff;" type="submit" value="Submit" name="submit" id="submit"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
                </form>
            </div>';
        }
        
    echo '  <div class="row" style="text-align:left; margin:auto;">
                <table style="font-size:16px; background-color:#2b2b2b; color:#AAA9AA; width:90%; margin:auto; margin-top:1%; text-align:left;">
                    <thead>
                        <tr>
                            <th><b>Name</b></th>
                            <th><b>Medicine</b></th>
                            <th><b>Info Resource</b></th>
                            <th><b>Description</b></th>
                        </tr>
                    </thead>
                    <tbody>';
                    $res2=DataAccess::getProtocols();
                    if (!$res2) {
                        echo '<tr><td colspan="3">Click the plus sign to add an order. You will be able to view and manage their information here!<td></tr>';
                    } else {
                        while ($protoRow=mysqli_fetch_assoc($res2)) {
                            $id=$protoRow['id'];
                            $name=$protoRow['name'];
                            $medicine=$protoRow['medicine'];
                            if(!empty($medicine)){
                                $embed2 = '<img style="margin:auto;" src="'.$medicine.'" alt="'.$medicine.' image">';
                            }else{
                                $embed2 = 'No Image Set';
                            }
                            $resource=$protoRow['resource'];
                            if(!empty($resource)){
                                $resourceType=explode('.', $resource);
                                if($resourceType[1] == 'mp4' || $resourceType[1] == 'mov'){
                                    $embed='<video style="margin:auto; width:75%;" controls><source src="'.$resource.'" type="video/mp4">Your browser does not support the video tag.</video>';
                                }else if($resourceType[1] == 'jpg' || $resourceType[1] == 'png'){
                                    $embed='<img src="'.$resource.'" alt="Image Resource" style="margin:auto; width:75%;">';
                                }else if($resourceType[1] == 'pdf'){
                                    $embedURL='http://docs.google.com/gview?url=http://'.$thisURL.'/'.$resource.'&embedded=true';
                                    $embed='<iframe style="margin:auto; width:75%;" src="'.$embedURL.'" frameborder="0"></iframe>';
                                }
                            }else{
                                $embed = 'No Image Set';
                            }
                            
                            $description=$protoRow['description'];
                            echo '<tr style="width:100%; border-top: solid 3px #AAA9AA;">
                                    <td style="width:25%;" class="sortable"><a href =\'orders.php?nId='.$nurse->nurseId.'&action=e&id='.$id.'\';"><b>'.$name.'</b></a></td>
                                    <td style="width:25%; padding:2%;" class="sameSize">'.$embed2.'</td>
                                    <td style="width:25%; padding:2%;" class="sameSize">'.$embed.'</td>
                                    <td style="width:25%;"><b>'.$description.'</b></td>';
                        }
                        echo '</tbody></table>';
                    }




$_SESSION['username'] = $nurse->username;
$_SESSION['password'] = $nurse->password;



    echo '      </div>
            </div>
        </div> <!-- //end container -->'.$displayVars.'
    </body>
    </html>
    ';
?>