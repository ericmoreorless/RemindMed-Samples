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
require_once('userData.php');//userData object
require_once('protocolData.php');//userData object
require_once('userMed/userMed.php');//Dennis' userMed class
require_once($globalPath."PHPMailer_5.2.0/class.phpmailer.php");

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
$nurse->globalReference = $getGlobal;

//var_dump($_GET);
if(empty($_SESSION['username']) || empty($_SESSION['password'])){

    if($_GET['nId'] == 0){

        $nurse->nurseId = 0;
        $nurse->globalReference = $getGlobal;
        $nurse->getNurseDbData();
        $nurse->username = $getGlobal->centerUser;
        $nurse->password = $getGlobal->centerpw;

    }else{
        $nurse->nurseId = $_GET['nId'];
        $nurse->globalReference = $getGlobal;
        $nurse->getNurseDbData();
    }

    

}else{
    $nurse->username = $_SESSION['username'];
    $nurse->password = $_SESSION['password'];
}



//declare redirect locations
$prefix = 'Location: http://www.'.$thisURL;
$noMatchRedirect = $prefix.'/admin/index.php?x=1';

//check to see if the user is super user or sub user
$pass = 1;

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

    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;
    $centerName = $getGlobal->centerName;
    $action = $_GET['action'];
    $PassFail = $_GET['p'];
    $notifier = $_GET['n'];

    placePageHead($nurse);

    
    if($action=='a'){

        if(isset($_GET['uid'])){

            //do user protocol add code
            $uid=$_GET['uid'];
            $step = $_GET['s'];
            $savedoruser = 'u';

            $res1=DataAccess::getUserProtoByUserID($uid);

            if($res1==0){

                echo'<iframe id="frame" name="frame" src="http://www.remindmed.org/r/displayChart.php?s=1&type=u&uid='.$uid.'" scrolling="yes" onload="resizeIframe(this)"></iframe>';
            
            }

            $res1=DataAccess::getProtocols();

        }else{
            if(isset($_GET['jid'])){
            $protoID = $_GET['jid'];
            $saved = 1;
            $user = 0;

        }

            //do saved protocol add code
            $res1=DataAccess::getProtocols();
            $res2=DataAccess::getNewProtosByID($protoID);
            $step = $_GET['s'];
            $savedoruser = 's';

            if($step > 2){

                $res2=DataAccess::getNewProtosByID($protoID);
                $pRow=mysqli_fetch_assoc($res2);
                $name=$pRow['name'];

                if(isset($_GET['id'])){

                    $protoID = $_GET['id'];
                    
                   

                }else{

                    $testID = $_POST['protoID'];
                   

                }

                $resx=DataAccess::getNewProtosByID($testID);
                $rowx = mysqli_fetch_assoc($resx);

                $medNum=$rowx['medNum'];
                $apptNum=$rowx['apptNum'];
                $levelNum=$rowx['levelNum'];

            }

        }

            //
            $medNum++;
            $apptNum++;
            $levelNum++;
            $type=$_GET['t'];
            switch ($type) {
                case 'med':
                    $med=1;
                    break;
                case 'appt':
                    $appt=1;
                    break;
                case 'level':
                    $level=1;
                    break;
                default:
                    $med=0;
                    $appt=0;
                    $level=0;
                    break;
            }
            if($savedoruser == 'u'){
                if($type == 'med' || $type == 'appt' || $type == 'level'){
                    echo'<iframe id="frame" name="frame" src="http://www.remindmed.org/r/displayChart.php?s=3&uid='.$uid.'&type=u" scrolling="yes" onload="iframeLoaded()"></iframe>';
                }   
                else{
                    echo'<iframe id="frame" name="frame" src="http://www.remindmed.org/r/displayChart.php?s=1&type=u" scrolling="yes" onload="iframeLoaded()"></iframe>';
                }
            }else if ($savedoruser == 's'){
                if($type == 'med' || $type == 'appt' || $type == 'level'){
                    echo'<iframe id="frame" name="frame" src="http://www.remindmed.org/r/displayChart.php?s='.$step.'&savedId='.$protoID.'&type=s" scrolling="yes" onload="iframeLoaded()"></iframe>';
                }   
                else{
                    echo'<iframe id="frame" name="frame" src="http://www.remindmed.org/r/displayChart.php?s=1&type=s" scrolling="yes" onload="iframeLoaded()"></iframe>';
                }
            }
            
            if($med){//Medicine
                if(isset($_POST['submit'])){
                    $med = $_POST['med'];
                    $interval = $_POST['intervalDays'];
                    $startDay = $_POST['start'];
                    $endDay = $_POST['end'];
                    $AMdose = $_POST['AM'];
                    $PMdose = $_POST['PM'];
                    $medNum= $_POST['medNum'];
                    // $medNum++;

                    $res = DataAccess::setMedStep($medNum, $protoID, $med, $interval, $startDay, $endDay, $AMdose, $PMdose);
                }else{
                    $res = DataAccess::getProtocols();
                    if($savedoruser == 's'){
                        $res2 = DataAccess::getNewProtosByID($protoID);
                        $row2 = mysqli_fetch_assoc($res2);
                        echo '
                            <div class="row" style="margin-top:1%;">
                                <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                                    <tbody style="width:100%;">
                                        <td class="td2" style="vertical-align : top;">
                                            <form id="addMedicine" name="addMedicine" method="post" action="protocols.php?action=a&t=med&s=3&id='.$protoID.'">
                                                <tr class="data">
                                                    <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Add Medicine</b></td><td class="td2"></td>
                                                </tr>
                                                <tr>
                                                        <input type="hidden" name="centerId" id="centerId" value="'.$centerId.'" />
                                                        <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                        <input type="hidden" name="protocolTableId" id="protocolTableId" value="'.$protocolTableId.'" />
                                                        <input type="hidden" name="addMed" id="addMed" value="1" />
                                                        <input type="hidden" name="medNum" id="medNum" value="'.$medNum.'" />
                                                        <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                </tr>
                                                <tr class="data">
                                                    <td class="td2" class="docInput" colspan="2">Select from Orders:
                                                        <select id="med" name="med">
                                                            <option value="" selected>Select an Order</option>';
                                                        while($row=mysqli_fetch_assoc($res)){
                                                            echo '<option value="'.$row['id'].'" selected>'.$row['name'].'</option>';
                                                        }
                                                            
                                                echo'   </select>
                                                    </td>
                                                </tr>
                                                <tr class="data">
                                                    <td class="td2" class="docInput" colspan="2">How often should this med be taken:
                                                        <select id="intervalDays" name="intervalDays">
                                                            <option value="" selected>Select an Interval</option>
                                                            <option value="1">Every Day</option>
                                                            <option value="2">Every Other Day</option>
                                                            <option value="3">Every Three Days</option>
                                                            <option value="4">Every Four Days</option>
                                                            <option value="5">Every Five Days</option>
                                                            <option value="6">Every Six Days</option>
                                                            <option value="7">Once a Week</option>
                                                            ';                                                
                                                echo'   </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" class="docInput">Start Day: </td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="start" type="text" id="start" value="" size="110" placeholder="mm/dd/yyyy"/></td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" class="docInput">End Day: </td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="end" type="text" id="end" value="" size="110" placeholder="mm/dd/yyyy"/></td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" class="docInput">Morning Dosage (AM): </td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" color:#000;" name="AM" type="text" id="AM" value="" size="110"/></td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" class="docInput">Evening Dosage (PM): </td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" color:#000;" name="PM" type="text" id="PM" value="" size="110"/></td>
                                                </tr>
                                                <tr>
                                                    <td class="td2">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" colspan="2" style="width:100%;">
                                                        <table style="width:100%;">
                                                            <tr>
                                                                <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff; width:90%;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                            </tr> 
                                                        </table>
                                                    </td>
                                                </tr>
                                            </form>
                                        </td>
                                    </tbody>
                                </table>
                            </div>
                            ';
                    }else if($savedoruser == 'u'){
                       
                        userMed::addUserMedForm($uid, $step, $res);
                    }
                    
                }
            }
            else if($appt){
                    if(isset($_POST['submit']) and $_POST['allowSave']){

                    $appt = $_POST['appt'];
                    $apptDay = $_POST['apptDay'];
                    $apptLoc = $_POST['apptLoc'];
                    $ras = DataAccess::setApptStep($apptNum, $protoID, $appt,$apptDay,$apptLoc);

                    }else{

                        if($savedoruser == 's'){

                            echo'
                            <div class="row" style="margin-top:1%;">
                                <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                                    <tbody style="width:100%;">
                                        <td class="td2" style="vertical-align : top;">
                                            <form id="addAppt" name="addAppt" method="post" action="protocols.php?action=a&t=appt&id='.$protoID.'&s='.$step.'">
                                            

                                                <tr class="data">
                                                    <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Add Appointment</b></td><td class="td2"></td>
                                                </tr>
                                                <tr>
                                                        <input type="hidden" name="protoID" id="protoID" value="'.$protoID.'" />
                                                        <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                        <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                        <input type="hidden" name="apptNum" id="apptNum" value="'.$apptNum.'" />
                                                </tr>';
                                $appt = $_POST['appt'];
                                $apptDay = $_POST['apptDay'];
                                $apptLoc = $_POST['apptLoc'];
                                echo '
                                               <tr>
                                                    <td class="td2" class="docInput">Appointment Name</td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="appt" type="text" id="appt" value="" size="110" placeholder="What is the name/reason of this appointment?"/></td>
                                                </tr>
                                               <tr>
                                                    <td class="td2" class="docInput">Appointment Day</td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptDay" type="text" id="apptDay" value="" size="110" placeholder="How many days into the protocol should the user come in for his appointment?"/></td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" class="docInput">Location: </td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptLoc" type="text" id="apptLoc" value="" size="110" placeholder="Which location should the user visit to have this appointment?"/></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td class="td2" colspan="2" style="width:100%;">
                                                        <table style="width:100%;">
                                                            <tr>
                                                                <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff; width:90%;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                            </tr> 
                                                        </table>
                                                    </td>
                                                </tr>
                                            </form>
                                        </td>
                                    </tbody>
                                </table>
                            </div> 
                            ';


                        }else if($savedoruser == 'u'){

                            echo'
                            <div class="row" style="margin-top:1%;">
                                <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                                    <tbody style="width:100%;">
                                        <td class="td2" style="vertical-align : top;">
                                            <form id="addAppt" name="addAppt" method="post"action="addUserProtocol.php">
                                            

                                                <tr class="data">
                                                    <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Add Appointment</b></td><td class="td2"></td>
                                                </tr>
                                                <tr>
                                                        <input type="hidden" name="uid" id="uid" value="'.$uid.'" />
                                                        <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                        <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                        <input type="hidden" name="apptNum" id="apptNum" value="'.$apptNum.'" />
                                                </tr>';
                                $appt = $_POST['appt'];
                                $apptDay = $_POST['apptDay'];
                                $apptLoc = $_POST['apptLoc'];
                                echo '
                                               <tr>
                                                    <td class="td2" class="docInput">Appointment Name</td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="appt" type="text" id="appt" value="" size="110" placeholder="What is the name/reason of this appointment?"/></td>
                                                </tr>
                                               <tr>
                                                    <td class="td2" class="docInput">Appointment Day</td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptDay" type="text" id="apptDay" value="" size="110" placeholder="Enter the date of the appointment."/></td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" class="docInput">Location: </td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptLoc" type="text" id="apptLoc" value="" size="110" placeholder="Which location should the user visit to have this appointment?"/></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td class="td2" colspan="2" style="width:100%;">
                                                        <table style="width:100%;">
                                                            <tr>
                                                                <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff; width:90%;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                            </tr> 
                                                        </table>
                                                    </td>
                                                </tr>
                                            </form>
                                        </td>
                                    </tbody>
                                </table>
                            </div> 
                            ';


                        }
                        
                    }
            }
            else if($level){
                if(isset($_POST['submit']) and $_POST['allowSave']){
                    $levelName = $_POST['level'];
                    $ris = DataAccess::setLevelStep($levelNum, $protoID, $levelName);
                }else{

                        if($savedoruser == 's'){
                                    echo'
                            <div class="row" style="margin-top:1%;">
                                <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                                    <tbody style="width:100%;">
                                        <td class="td2" style="vertical-align : top;">
                                            <form id="addLevel" name="addLevel" method="post" action="protocols.php?action=a&t=level&id='.$protoID.'&s='.$step.'">
                                            

                                                <tr class="data">
                                                    <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Add Result</b></td><td class="td2"></td>
                                                </tr>
                                                <tr>
                                                    <input type="hidden" name="protoID" id="protoID" value="'.$protoID.'" />
                                                    <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                    <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                    <input type="hidden" name="levelNum" id="levelNum" value="'.$levelNum.'" />
                                                </tr>';
                                $level = $_POST['level'];
                                echo '
                                                <tr>
                                                    <td class="td2" class="docInput">Result Name</td>
                                                    <td class="td2" class="docFieldHolder"><input class="docInputField" name="level" type="text" id="level" value="" size="110" placeholder="What will be measured in the tests?"/></td>
                                                </tr>
                                                <tr>
                                                    <td class="td2" colspan="2" style="width:100%;">
                                                        <table style="width:100%;">
                                                            <tr>
                                                                <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff; width:90%;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                            </tr> 
                                                        </table>
                                                    </td>
                                                </tr>
                                            </form>
                                        </td>
                                    </tbody>
                                </table>
                            </div> 
                            ';

                        }
                        else if($savedoruser == 'u'){
                            echo'
                                <div class="row" style="margin-top:1%;">
                                    <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                                        <tbody style="width:100%;">
                                            <td class="td2" style="vertical-align : top;">
                                                <form id="addLevel" name="addLevel" method="post" action="addUserLevel.php">
                                                

                                                    <tr class="data">
                                                        <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Add Result</b></td><td class="td2"></td>
                                                    </tr>
                                                    <tr>
                                                        <input type="hidden" name="uid" id="uid" value="'.$uid.'" />
                                                        <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                        <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                        <input type="hidden" name="levelNum" id="levelNum" value="'.$levelNum.'" />
                                                    </tr>';
                                    $level = $_POST['level'];
                                    echo '
                                                    <tr>
                                                        <td class="td2" class="docInput">Level Name</td>
                                                        <td class="td2" class="docFieldHolder"><input class="docInputField" name="level" type="text" id="level" value="" size="110" placeholder="What will be measured in the tests?"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="td2" colspan="2" style="width:100%;">
                                                            <table style="width:100%;">
                                                                <tr>
                                                                    <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff; width:90%;" type="submit" value="Sumbit" name="submit" id="submit" /></td>
                                                                </tr> 
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </form>
                                            </td>
                                        </tbody>
                                    </table>
                                </div> 
                                ';

                        }


                    
                }
            }            
    }   
        
    /*
    *
    *  Edit
    *
    */
    if($action=='e') {
        if(isset($_GET['jid'])){
            $protoID = $_GET['jid'];
            $saved = 1;
            $user = 0;
        }else{
            $type = 'u';
            $uid = $_GET['uid'];
            $saved = 0;
            $user = 1;
            $res = DataAccess::checkIfUserSet($uid);
            if($res == 0){
                $frameStep = 1;
            }else{
                $frameStep = 3;
            }
        }
        $editType = $_GET['t'];
        switch ($editType) {
            case 'med':
                $med=1;
                break;
            case 'appt':
                $appt=1;
                break;
            case 'level':
                $level=1;
                break;
            default:
                $med=0;
                $appt=0;
                $level=0;
                break;
        }
        if($user){
            echo'<iframe id="frame" name="frame" src="http://www.remindmed.org/r/displayChart.php?s='.$frameStep.'&uid='.$uid.'&type='.$type.'" scrolling="yes" onload="iframeLoaded()"></iframe>';
            if($med){
                if(isset($_POST['submit']) && $_POST['allowSave']){
                    $position = $_POST['position'];
                    $med = $_POST['med'];
                    $interval = $_POST['interval'];
                    $startDay = fixDate($_POST['startDay']);
                    $endDay = fixDate($_POST['endDay']);
                    $AMdose = $_POST['AMdose'];
                    $PMdose = $_POST['PMdose'];
                    $medNum = $_POST['medNum'];
                    $res = DataAccess::editUserMedStep($position, $uid, $med, $interval, $startDay, $endDay, $AMdose, $PMdose);
                }else if(isset($_POST['delete'])){
                    $position = $_POST['position'];
                    $medNum = $_POST['medNum'];
                    DataAccess::deleteFromUser($uid, $position, $medNum, $editType);
                    echo'
                    <script>
                        var source=\'http://www.remindmed.org/r/displayChart.php?s=3&uid='.$uid.'&type='.$type.'\';
                        frame.attr(\'src\', source);
                    </script>';
                }else{
                    $res = DataAccess::getProtocols(); 
                    $userMedId = $_GET['umid'];
                    $n = $_GET['n'];
                    userMed::editUserMedForm($userMedId, $n, $res);


                }
            }else if($appt){
                    if(isset($_POST['submit']) and $_POST['allowSave']){
                    $position=$_POST['position'];
                    $appt = $_POST['appt'];
                    $apptDay = fixDate($_POST['apptDay']);
                    $apptLoc = $_POST['apptLoc'];
                    $res = DataAccess::editUserApptStep($position, $uid, $appt, $apptDay, $apptLoc);
                }else if(isset($_POST['delete'])){
                    $position = $_POST['position'];
                    $apptNum = $_POST['apptNum'];
                    DataAccess::deleteFromUser($uid, $position, $apptNum, $editType);
                    echo'
                    <script>
                        var source=\'http://www.remindmed.org/r/displayChart.php?s=3&uid='.$uid.'&type='.$type.'\';
                        frame.attr(\'src\', source);
                    </script>';
                }else{
                    $apptN = $_GET['apptN'];
                    $resy = DataAccess::getUserProtocol($uid);
                    $rowy = mysqli_fetch_assoc($resy);
                    $appt =$rowy['appt'.$apptN];
                    $day =unfixDate($rowy['appt'.$apptN.'Date']);
                    $loc =$rowy['appt'.$apptN.'Location'];
                    $apptNum = $rowy['apptNum'];
                    echo'
                    <div class="row" style="margin-top:1%;">
                        <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                            <tbody style="width:100%;">
                                <td class="td2" style="vertical-align : top;">
                                    <form id="addAppt" name="addAppt" method="post" action="protocols.php?action=e&t=appt&uid='.$uid.'&s='.$step.'">
                                    

                                        <tr class="data">
                                            <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Edit Appointment</b></td><td class="td2"></td>
                                        </tr>
                                        <tr>
                                                <input type="hidden" name="position" id="position" value="'.$apptN.'" />
                                                <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                <input type="hidden" name="apptNum" id="apptNum" value="'.$apptNum.'" />
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">Appointment Name</td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="appt" type="text" id="appt" value="'.$appt.'" size="110" placeholder="What is the name/reason of this appointment?"/></td>
                                        </tr>
                                       <tr>
                                            <td class="td2" class="docInput">Appointment Day</td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptDay" type="text" id="apptDay" value="'.$day.'" size="110" placeholder="How many days into the protocol should the user come in for his appointment?"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">Location: </td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptLoc" type="text" id="apptLoc" value="'.$loc.'" size="110" placeholder="Which location should the user visit to have this appointment?"/></td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="td2" colspan="2" style="width:100%;">
                                                <table style="width:100%;">
                                                    <tr>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#F49118;padding:20px 140px; color:#fff;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#570604;padding:20px 100px; color:#fff;" type="submit" value="Delete Appointment" name="delete" id="delete" /></td>
                                                    </tr> 
                                                </table>
                                            </td>
                                        </tr>
                                    </form>
                                </td>
                            </tbody>
                        </table>
                    </div> 
                    ';
                }
            }else if($level){
                if(isset($_POST['submit']) and $_POST['allowSave']){
                    $position=$_POST['position'];
                    $levelName = $_POST['level'];
                    $levelVal = $_POST['levelvalue'];
                    $ris = DataAccess::editUserLevelStep($position, $uid, $levelName, $levelVal);
                }else if(isset($_POST['delete'])){
                    $position = $_POST['position'];
                    $levelNum = $_POST['levelNum'];
                    DataAccess::deleteFromUser($uid, $position, $levelNum, $editType);
                    echo'
                    <script>
                        var source=\'http://www.remindmed.org/r/displayChart.php?s=3&uid='.$uid.'&type='.$type.'\';
                        frame.attr(\'src\', source);
                    </script>';
                }else{
                    $levelN = $_GET['levelN'];
                    $resy = DataAccess::getUserProtocol($uid);
                    $rowy = mysqli_fetch_assoc($resy);
                    $level=$rowy['level'.$levelN];
                    $val=$rowy['level'.$levelN.'val'];
                    $levelNum = $rowy['levelNum'];
                    echo'
                    <div class="row" style="margin-top:1%;">
                        <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                            <tbody style="width:100%;">
                                <td class="td2" style="vertical-align : top;">
                                    <form id="addLevel" name="addLevel" method="post" action="protocols.php?action=e&t=level&uid='.$uid.'&s='.$step.'">
                                    

                                        <tr class="data">
                                            <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Edit Level</b></td><td class="td2"></td>
                                        </tr>
                                        <tr>
                                            <input type="hidden" name="position" id="position" value="'.$levelN.'" />
                                            <input type="hidden" name="step" id="step" value="'.$step.'" />
                                            <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                            <input type="hidden" name="levelNum" id="levelNum" value="'.$levelNum.'" />
                                        </tr>';
                        echo '
                                        <tr>
                                            <td class="td2" class="docInput">Level Name</td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="level" type="text" id="level" value="'.$level.'" size="110" placeholder="What will be measured in the tests?"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">Value</td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="levelvalue" type="text" id="levelvalue" value="'.$val.'" size="110" placeholder="What will be measured in the tests?"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" colspan="2" style="width:100%;">
                                                <table style="width:100%;">
                                                    <tr>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#F49118;padding:20px 140px; color:#fff; width:90%;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#570604;padding:20px 140px; color:#fff; width:90%;" type="submit" value="Delete Level" name="delete" id="delete" /></td>
                                                    </tr> 
                                                </table>
                                            </td>
                                        </tr>
                                    </form>
                                </td>
                            </tbody>
                        </table>
                    </div> 
                    ';
                }
            }
        }else if($saved){
            echo'<iframe id="frame" name="frame" src="http://www.remindmed.org/r/displayChart.php?s=3&jid='.$protoID.'" scrolling="yes" onload="iframeLoaded()"></iframe>';
            if($med){
                if(isset($_POST['submit']) && $_POST['allowSave']){
                    $position = $_POST['position'];
                    $med = $_POST['med'];
                    $interval = $_POST['interval'];
                    $startDay = $_POST['startDay'];
                    $endDay = $_POST['endDay'];
                    $AMdose = $_POST['AMdose'];
                    $PMdose = $_POST['PMdose'];
                    $medNum = $_POST['medNum'];
                    $res = DataAccess::editMedStep($position,$protoID, $med, $interval, $startDay, $endDay, $AMdose, $PMdose);
                }else if(isset($_POST['delete'])){
                    $position = $_POST['position'];
                    $medNum = $_POST['medNum'];
                    $res=DataAccess::deleteFromProtocols($protoID, $position, $medNum, $editType);
            
                    echo'
                    <script>
                        var source=\'http://www.remindmed.org/r/displayChart.php?s=3&jid='.$protoID.'&type='.$type.'\';
                        frame.attr(\'src\', source);
                    </script>';
                }else{
                    $n = $_GET['n'];
                    $resy = DataAccess::getNewProtosByID($protoID);
                    $rowy = mysqli_fetch_assoc($resy);
                    $med =$rowy['med'.$n];
                    $interval =$rowy['interval'.$n];
                    $start =$rowy['start'.$n];
                    $end =$rowy['end'.$n];
                    $AM =$rowy['AM'.$n];
                    $PM =$rowy['PM'.$n];
                    $medNum =$rowy['medNum'];
                    echo'
                    <div class="row" style="margin-top:1%;">
                        <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                            <tbody style="width:100%;">
                                <td class="td2" style="vertical-align : top;">
                                    <form id="addMedicine" name="addMedicine" method="post" action="protocols.php?action=e&t=med&jid='.$protoID.'&s='.$step.'">
                                        <tr class="data">
                                            <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Edit Medicine</b></td><td class="td2"></td>
                                        </tr>
                                        <tr>
                                                <input type="hidden" name="position" id="position" value="'.$n.'" />
                                                <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                <input type="hidden" name="medNum" id="medNum" value="'.$medNum.'" />
                                        </tr>
                                        <tr class="data">
                                            <td class="td2" class="docInput" colspan="2">Select from Orders:
                                                <select id="med" name="med">
                                                    <option value="">Select an Order</option>';
                                                $res1=DataAccess::getProtocols();
                                                while($row=mysqli_fetch_assoc($res1)){
                                                    if($row['id'] == $med){
                                                        echo '<option value="'.$row['id'].'" selected>'.$row['name'].'</option>';
                                                    }else{
                                                        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                    }
                                                }
                                        $s1=0;
                                        $s2=0;
                                        $s3=0;
                                        $s4=0;
                                        $s5=0;
                                        $s6=0;
                                        $s7=0;
                                        switch ($interval) {
                                            case 1:
                                                $s1='selected';
                                                break;
                                            case 2:
                                                $s2='selected';
                                                break;
                                            case 3:
                                                $s3='selected';
                                                break;
                                            case 4:
                                                $s4='selected';
                                                break;
                                            case 5:
                                                $s5='selected';
                                                break;
                                            case 6:
                                                $s6='selected';
                                                break;
                                            case 7:
                                                $s7='selected';
                                                break;
                                        }   
                                        echo'   </select>
                                            </td>
                                        </tr>
                                        <tr class="data">
                                            <td class="td2" class="docInput" colspan="2">How often should this med be taken:
                                                <select id="interval" name="interval">
                                                    <option value="" selected>Select an Interval</option>
                                                    <option value="1"'.$s1.'>Every Day</option>
                                                    <option value="2"'.$s2.'>Every Other Day</option>
                                                    <option value="3"'.$s3.'>Every Three Days</option>
                                                    <option value="4"'.$s4.'>Every Four Days</option>
                                                    <option value="5"'.$s5.'>Every Five Days</option>
                                                    <option value="6"'.$s6.'>Every Six Days</option>
                                                    <option value="7"'.$s7.'>Once a Week</option>
                                                    ';                                                
                                        echo'   </select>
                                            </td>
                                        </tr>
                                        ';
                                        $med = $_POST['med'];
                                        $interval = $_POST['interval'];
                                        $startDay = $_POST['startDay'];
                                        $endDay = $_POST['endDay'];
                                        $AMdose = $_POST['AMdose']; 
                                        echo'
                                        <tr>
                                            <td class="td2" class="docInput">Start: </td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="startDay" type="text" id="interval" value="'.$start.'" size="110" placeholder="How many days into the protocol should the user begin this med?"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">End: </td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="endDay" type="text" id="endDay" value="'.$end.'" size="110" placeholder="How many days into the protocol should the user stop taking this med?"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">Morning Dosage (AM): </td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" color:#000;" name="AMdose" type="text" id="AMdose" value="'.$AM.'" size="110"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">Evening Dosage (PM): </td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" color:#000;" name="PMdose" type="text" id="PMdose" value="'.$PM.'" size="110" /></td>
                                        </tr>
                                        <tr>
                                            <td class="td2">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="td2" colspan="2" style="width:100%;">
                                                <table style="width:100%;">
                                                    <tr>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff;padding:20px 140px;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#570604; color:#fff;padding:20px 140px;" type="submit" value="Delete Medicine" name="delete" id="delete" /></td>
                                                    </tr> 
                                                </table>
                                            </td>
                                        </tr>
                                    </form>
                                </td>
                            </tbody>
                        </table>
                    </div>';
                }
            }else if($appt){
                    if(isset($_POST['submit']) and $_POST['allowSave']){
                    $position=$_POST['position'];
                    $appt = $_POST['appt'];
                    $apptDay = $_POST['apptDay'];
                    $apptLoc = $_POST['apptLoc'];
                    $ras = DataAccess::editApptStep($position, $protoID, $appt, $apptDay, $apptLoc);
                }else if(isset($_POST['delete'])){
                    $position = $_POST['position'];
                    $apptNum = $_POST['apptNum'];
                    DataAccess::deleteFromProtocols($protoID, $position, $apptNum, $editType);
                    echo'
                    <script>
                        var source=\'http://www.remindmed.org/r/displayChart.php?s=3&jid='.$protoID.'&type='.$type.'\';
                        frame.attr(\'src\', source);
                    </script>';
                }else{
                    $n = $_GET['n'];
                    $resy = DataAccess::getNewProtosByID($protoID);
                    $rowy = mysqli_fetch_assoc($resy);
                    $appt =$rowy['appt'.$n];
                    $day =$rowy['appt'.$n.'Day'];
                    $loc =$rowy['appt'.$n.'Location'];
                    $apptNum = $rowy['apptNum'];
                    echo'
                    <div class="row" style="margin-top:1%;">
                        <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                            <tbody style="width:100%;">
                                <td class="td2" style="vertical-align : top;">
                                    <form id="addAppt" name="addAppt" method="post" action="protocols.php?action=e&t=appt&jid='.$protoID.'&s='.$step.'">
                                    

                                        <tr class="data">
                                            <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Edit Appointment</b></td><td class="td2"></td>
                                        </tr>
                                        <tr>
                                                <input type="hidden" name="position" id="position" value="'.$n.'" />
                                                <input type="hidden" name="step" id="step" value="'.$step.'" />
                                                <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                                <input type="hidden" name="apptNum" id="apptNum" value="'.$apptNum.'" />
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">Appointment Name</td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="appt" type="text" id="appt" value="'.$appt.'" size="110" placeholder="What is the name/reason of this appointment?"/></td>
                                        </tr>
                                       <tr>
                                            <td class="td2" class="docInput">Appointment Day</td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptDay" type="text" id="apptDay" value="'.$day.'" size="110" placeholder="How many days into the protocol should the user come in for his appointment?"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" class="docInput">Location: </td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="apptLoc" type="text" id="apptLoc" value="'.$loc.'" size="110" placeholder="Which location should the user visit to have this appointment?"/></td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="td2" colspan="2" style="width:100%;">
                                                <table style="width:100%;">
                                                    <tr>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff; width:90%;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#570604; color:#fff; width:90%;" type="submit" value="Delete" name="delete" id="delete" /></td>
                                                    </tr> 
                                                </table>
                                            </td>
                                        </tr>
                                    </form>
                                </td>
                            </tbody>
                        </table>
                    </div> 
                    ';
                }
            }else if($level){
                if(isset($_POST['submit']) and $_POST['allowSave']){
                    $position=$_POST['position'];
                    $levelName = $_POST['level'];
                    $ris = DataAccess::editLevelStep($position, $protoID, $levelName);
                }else if(isset($_POST['delete'])){
                    $position = $_POST['position'];
                    $levelNum = $_POST['levelNum'];
                    DataAccess::deleteFromProtocols($protoID, $position, $levelNum, $editType);
                    echo'
                    <script>
                        var source=\'http://www.remindmed.org/r/displayChart.php?s=3&jid='.$protoID.'&type='.$type.'\';
                        frame.attr(\'src\', source);
                    </script>';
                }else{
                    $n = $_GET['n'];
                    $resy = DataAccess::getNewProtosByID($protoID);
                    $rowy = mysqli_fetch_assoc($resy);
                    $level=$rowy['level'.$n];
                    $levelNum = $rowy['levelNum'];
                    //var_dump($level);
                    echo'
                    <div class="row" style="margin-top:1%;">
                        <table height="30%" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="font-size:14px; color:#fff; text-align:left; margin-left:5%; margin-right:5%;">
                            <tbody style="width:100%;">
                                <td class="td2" style="vertical-align : top;">
                                    <form id="addLevel" name="addLevel" method="post" action="protocols.php?action=e&t=level&jid='.$protoID.'&s='.$step.'">
                                    

                                        <tr class="data">
                                            <td class="td2" class="docInput" colspan="2" style="font-size:1.2em;"><b>Edit Level</b></td><td class="td2"></td>
                                        </tr>
                                        <tr>
                                            <input type="hidden" name="position" id="position" value="'.$n.'" />
                                            <input type="hidden" name="step" id="step" value="'.$step.'" />
                                            <input type="hidden" name="allowSave" id="allowSave" value="1" />
                                            <input type="hidden" name="levelNum" id="levelNum" value="'.$levelNum.'" />
                                        </tr>';
                        // $level = $_POST['level'];
                        echo '
                                        <tr>
                                            <td class="td2" class="docInput">Level Name</td>
                                            <td class="td2" class="docFieldHolder"><input class="docInputField" name="level" type="text" id="level" value="'.$level.'" size="110" placeholder="What will be measured in the tests?"/></td>
                                        </tr>
                                        <tr>
                                            <td class="td2" colspan="2" style="width:100%;">
                                                <table style="width:100%;">
                                                    <tr>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#F49118; color:#fff; width:90%;" type="submit" value="Submit" name="submit" id="submit" /></td>
                                                        <td class="td2" style="text-align:center;"><input style="background-color:#570604; color:#fff; width:90%;" type="submit" value="Delete" name="delete" id="delete" /></td>
                                                    </tr> 
                                                </table>
                                            </td>
                                        </tr>
                                    </form>
                                </td>
                            </tbody>
                        </table>
                    </div> 
                    ';
                }
            }
        } 

    }

    if($action == 'a' || $action=='e'){

        if(isset($_GET['uid'])){

            $type = 'u';
            $id = $_GET['uid'];


        }

        else if(isset($_GET['jid'])){

            $type = 's';
            $id = $_GET['jid'];

       }

        deleteButton($id, $type);

    }
        
    showTables($getGlobal, $nurse);
    
    echo '
            </div> <!-- //end container -->';

    echo'
        </body>
        </html>
    ';

}

else{

    header('Location: '.$thisURL.'/admin/index.php?x=2');
    die();

}

function deleteButton($id, $type){
    
    if($type == 'u'){

        echo'<div class="row" style="text-align:center; margin:auto;">
                <form style="margin:auto;" action="logs/printProtocol.php?uId='.$id.'" method="POST" id="deleteUserMed" name="deleteUserMed">
                    <input style="margin-left:5%; margin-right:5%;padding:20px 100px 20px 100px; background-color:#570604; color:#fff; width:40%;" type="submit" value="Delete" name="delete" id="delete" />
                </form>
            </div>';

    }

    else{

        echo'<div class="row" style="text-align:center; margin:auto;">
            <form style="margin:auto;" action="userMed/delete_userMed.php?t='.$type.'&sId='.$id.'" method="POST" id="deleteUserMed" name="deleteUserMed">
                <input style="margin-left:5%; margin-right:5%; background-color:#570604; color:#fff; width:30%;padding:20px 100px 20px 100px;" type="submit" value="Delete" name="delete" id="delete" />
            </form>
        </div>';

    }

}

function notifyButton(){
    echo'
    <div class="row" style="text-align:center; margin:auto;">
        <form style="margin:auto;" action="notify.php" method="POST" id="notifyUsers" name="notifyUsers">
            <input style="margin-left:5%; margin-right:5%; background-color:#F49118; color:#fff; padding:20px 100px 20px 100px;" type="submit" value="Notify Users of Protocol Changes" name="notify" id="notify" />
        </form>
    </div>';   
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

function placePageHead($nurse){

    echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta charset="utf-8">
            <title>RemindMed - Protocols Page</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="css/bootstrap.css" rel="stylesheet">
            <link href="css/inputStyle.css" rel="stylesheet">
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
            .pure-form .pure-group input{
                display:inline-block;
            }
            iframe{
                overflow:hidden;
            }
            </style>    
            <script>

                function resizeIframe(obj) {

                    obj.style.height = obj.contentWindow.document.body.scrollHeight + \'px\';
                
                }

                function iframeLoaded() {
                    var iFrameID = document.getElementById(\'frame\');
                    if(iFrameID) {
                        // here you can make the height, I delete it first, then I make it again
                        iFrameID.height = "";
                        iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight + "px";
                    }   
                }

                $(document).ready(function(){
                     $.placeholder();
                });
                var themeColour = \'black\';
                jQuery.placeholder = function() {
                    $(\'[placeholder]\').focus(function() {
                    var input = $(this);
                    if (input.hasClass(\'placeholder\')) {
                        input.val(\'\');
                        input.removeClass(\'placeholder\');
                    }
                    }).blur(function() {
                        var input = $(this);
                        if (input.val() === \'\') {
                        input.addClass(\'placeholder\');
                        input.val(input.attr(\'placeholder\'));
                    }
                    }).blur().parents(\'form\').submit(function() {
                        $(this).find(\'[placeholder]\').each(function() {
                            var input = $(this);
                            if (input.hasClass(\'placeholder\')) {
                                input.val(\'\');
                            }
                        });
                    });
                  
                    // Clear input on refresh so that the placeholder class gets added back
                    $(window).unload(function() {
                        $(\'[placeholder]\').val(\'\');
                    });
                };
                // If using AJAX, call this on all placeholders after submitting to 
                // return placeholder
                jQuery.fn.addPlaceholder = function() {
                    return this.each(function() {
                        var input = $(this);
                        input.addClass(\'placeholder\');
                        input.val(input.attr(\'placeholder\'));
                    });
                };
            </script>
            <script src="js/controlfrog.js"></script>
        </head>
        <body class="black">
            <div class="cf-nav cf-nav-state-min">
                    <ul>
                        <li class="cf-nav-shortcut">
                            <a href="protocols.php?action=a&s=0"><img style="margin-left:.5%;height:25px;width:25px;" id="addCenterButton" name="addCenterButton" src="buttons/addCenter.png"></a>
                        </li>
                    </ul>
                </div>
            <div class="cf-container cf-nav-active">
                <div class="custom1" style="text-align:left;">
                    <table style="width:50vw; text-align:left;">
                        <tr style="width:50vw;">
                            <td class="td2" style="width:5%;">
                                <a href="centerMain.php?nId='.$nurse->nurseId.'">
                                    <img style="width:50%;" id="analyticsButton" name="analyticsButton" src="buttons/analytics.png">
                                </a>
                            </td>
                            <td class="td2" style="width:5%;">
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
}

function showTables($getGlobal, $nurse){

    echo '  
            <div class="row" style="margin:auto;">
                <table style="font-size:16px; width:90%; margin-top:2%; margin-left:3%; margin-right:5%; background-color:#2b2b2b; color:#AAA9AA;">
                <tbody>';

    userProtocolsTable($getGlobal, $nurse);

    savedProtocolsTable($getGlobal, $nurse);

    echo '
                </tbody></table><br><br>';
    
    notifyButton();
    
    echo '
            </div>';

}

function userProtocolsTable($getGlobal, $nurse){

    $user = new userData;

    echo '
                    <tr style="background-color:#2b2b2b; color:#AAA9AA;">
                        <td class="td2"><b>Patient Protocols</b></td>
                    </tr>';

    $res = $user->getUsersByNurseId($getGlobal, $nurse->nurseId);

    if (!$res) {

        echo '
                    <tr>
                        <td class="td2" colspan = "3">Add a user to add/manage their protocol<td class="td2">
                    </tr>';
    
    }

    else {

        //
        while ($row = mysqli_fetch_assoc($res)) {

            $user->centerId = $row['id'];
            $user->fname = $row['fname'];
            $user->lname = $row['lname'];

            echo '

                <tr onclick="location.href=\'protocols.php?action=e&nId='.$nurse->nurseId.'&uid='.$user->centerId.'\';" style="border: solid 1px #AAA9AA;">
                    <td class="td2" style="text-align:left;"><a href=\'protocols.php?action=e&nId='.$nurse->nurseId.'&uid='.$user->centerId.'\';">'.$user->fname.' '.$user->lname.'\'s Protocol</a></td>
                </tr>
                ';
        }
        // 
    
    }

    echo '
                    <tr style="background-color:#2b2b2b; color:#fff;">
                        <td class="td2">&nbsp;</td>
                    </tr>';
}

function savedProtocolsTable($getGlobal, $nurse){

    $protocol = new protocolData;

    echo '
                <tr style="background-color:#2b2b2b; color:#AAA9AA;">
                    <td class="td2"><b>Saved Protocols</b></td>
                </tr>';

    $res = $protocol->getSavedProtocols($getGlobal);

    if (!$res) {

        echo '
                <tr><td class="td2" colspan = "3">Click the plus button to craft a Protocol.<td class="td2"></tr>';
    
    } 

    else {

        //
        while ($row = mysqli_fetch_assoc($res)) {

            $protocol->protocolId = $row['id'];
            $protocol->name = $row['name'];

            echo '
                <tr style="border: solid 1px #AAA9AA;">
                    <td class="td2" style="text-align:left;"><a href=\'protocols.php?action=e&nId='.$nurse->nurseId.'&jid='.$protocol->protocolId.'\';"> '.$protocol->name.' Protocol</a></td>
                </tr>
                ';

        }

        //
    
    }
    
    echo '              
                </tbody></table><br><br>';

}

//place entries in global array for use in the next script
$_SESSION['username'] = $nurse->username;
$_SESSION['password'] = $nurse->password;

?>