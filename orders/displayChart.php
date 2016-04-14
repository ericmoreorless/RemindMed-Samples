<?php
error_reporting(E_ERROR);
require_once('Classes/Globals.php');
require_once('Classes/DataAccess.php');
$thisURL=$_SERVER[HTTP_HOST].'/'.basename(__DIR__);
$globalPath = $_SERVER['DOCUMENT_ROOT'].'/'.basename(__DIR__).'/';
$today = date('m/d/Y');
$step = $_GET['s'];
$duplicates = array();
$thisMedStart = array();
//var_dump($_GET);
if(isset($_GET['jid'])){
    $saved=1;
    $user=0;
    $id = $_GET['jid'];
}
echo '<!DOCTYPE html>
    <html lang="en">
    <head>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta charset="utf-8">
        <title>RemindMed - Journeys Page</title>
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
                color:white;
                background-color: transparent;
                text-decoration: none;
            }
            a:visited {
                color: white;
                background-color: transparent;
                text-decoration: none;
            }
            a:hover {
                color: #F49118;
                background-color: transparent;
                text-decoration: none;
            }
            a:active {
                color:white;
                background-color: transparent;
                text-decoration: none;
            }
        ';
        if($step ==1){
            echo '
        body { 
            overflow-y:hidden;
        }
            ';
        }
        echo '
        </style>    
        <script>
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
    <body class="black">';
    if($step == 1){
        $type = $_GET['type'];
        //echo 'type is '.$type;//probe
        if($type == 'u'){
            $uid=$_GET['uid'];
            //display new user journey set form
            $res=DataAccess::getSavedProtocols();
            //color???
            echo'
            <div class="row" style="margin-top:1%;">
                <table class="data" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="width:90%; font-size:14px; color:#fff; text-align:left; margin-left:4%; margin-right:5%;">
                    <tbody style="width:90%;">
                        <td class="td1" style="vertical-align : top;">
                            <form id="addUserProtocol" name="addUserProtocol" method="post" action="userMed/add_userMed.php">
                                <tr class="data">
                                    <td class="td1" class="td2" style="color:#AAA9AA; font-size:1.2em; text-align:left;" colspan="2"><b>Craft a New Protocol</b></td><td class="td1"></td>
                                </tr>
                                <tr class="data">
                                    <td class="td1" style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA; border-right:0px;">Select From Saved Protocols:</td>
                                    <td class="td1" style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA; border-left:0px;">
                                    <select id="savedprotocols" name="savedprotocols">
                                                        <option value="" selected>Select a Protocol</option>';
                                                    while($row=mysqli_fetch_assoc($res)){
                                                        echo '<option value="'.$row['id'].'" selected>'.$row['name'].'</option>';
                                                    }
                                                        
                                            echo'   </select>
                                    
                                    </td>
                                </tr>
                                <tr class="data">
                                    <td class="td1" style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA; border-right:0px;">Start Date: </td>
                                    <td class="td1" style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA; border-left:0px;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="startDate" type="text" id="startDate" size="125" placeholder="mm/dd/yyyy"/></td>
                                </tr>
                                <tr>
                                    <input type="hidden" name="userID" id="userID" value="'.$uid.'" />
                                    <input type="hidden" name="new" id="new" value="1" />
                                </tr>
                                <tr class="data">
                                    <td class="td1" colspan="2"><input style="background-color:#F49118; width:30%; color:#fff;padding:20px 100px 20px 100px;" type="submit" value="Next" name="submit" id="submit"/></td>
                                </tr>
                                <tr class="data">
                                    <td class="td1" colspan="2">&nbsp;</td>
                                </tr>
                            </form>
                        </td>
                    </tbody>
                </table>
            </div>';
        }else{
            //display new saved journey form
            echo'
            <div class="row" style="margin-top:1%;">
                <table class="data" border="0" cellpadding="0" cellspacing="3" bordercolor="#FFFFFF" style="width:90%; font-size:14px; color:#fff; text-align:left; margin-left:4%; margin-right:5%;">
                    <tbody style="width:90%;">
                        <td class="td1" style="vertical-align : top;">
                            <form id="newProtoName" name="newProtoName" method="post" action="displayChart.php?s=2&type=s">
                                <tr class="data">
                                    <td class="td1" style="color:#AAA9AA; font-size:1.2em; text-align:left;" colspan="2"><b>Craft a New Protoccol</b></td><td class="td1"></td>
                                </tr>
                                <tr class="data">
                                    <td class="td1" style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA; border-right:0px;">Name: </td>
                                    <td class="td1" style="background-color:#2b2b2b; color:#AAA9AA; border-top:0px solid #AAA9AA; border-left:0px;"><input style="width:100%; text-align:left; background-color:#2b2b2b; color:#AAA9AA;" name="newProtoName" type="text" id="newProtoName" size="125" placeholder="Give this protocol a name"/></td>
                                </tr>
                                <tr class="data">
                                    <td class="td1" colspan="2"><input style="background-color:#F49118; width:30%; color:#fff;padding:20px 100px 20px 100px;" type="submit" value="Next" name="next" id="next"/></td>
                                </tr>
                                <tr class="data">
                                    <td class="td1" colspan="2">&nbsp;</td>
                                </tr>
                            </form>
                        </td>
                    </tbody>
                </table>
            </div>';
        }
            
    }else{
        if($step == 2){
            $type=$_GET['type'];
            if($type=='u'){
                //add a row to uprotocols (user)
                $protocol=$_GET['sp'];
                $startDate=$_GET['sd'];
                $uid=$_GET['uid'];
                $res = DataAccess::adduProtocolNew($uid, $protocol, $startDate);
                echo '
                <script>
                location.href = "displayChart.php?s=3&type=u&uid='.$uid.'";
                </script>
                ';
            }else if($type=='s' && isset($_POST['newProtoName'])){
                //add to protocols (saved)
                $name = escapeshellcmd($_POST['newProtoName']);

                $res = DataAccess::addProtocolNew($name);

                $con = Globals::getConnection();
                $sql = 'SELECT * FROM `protocols` WHERE name LIKE \''.$name.'\'';
                $res1 = mysqli_query($con, $sql);
                $row = mysqli_fetch_assoc($res1);
                $id = $row['id'];
             
                echo '
                <script>
                location.href = "displayChart.php?s=3&savedId='.$id.'";
                </script>
                ';
            }
            
        }
        $type = $_GET['type'];
        if($type == 'u'){
            $con = Globals::getConnection();
            $uid = $_GET['uid'];
            $sql = 'SELECT * FROM `center` WHERE id LIKE '.$uid;
            $res = mysqli_query($con, $sql);
            $row = mysqli_fetch_assoc($res);
            $name = $row['fname'].' '.$row['lname'].'\'s Protocol';
            $userMedRows = array();
            $sql = 'SELECT * FROM `userMed` WHERE centerId LIKE '.$uid.' ORDER BY orderId DESC';
            $res = mysqli_query($con, $sql);
            $cnt = 0;
            while($row = mysqli_fetch_assoc($res)){
                $push = 1;
                // for($i=0;$i<$cnt;$i++){
                //     if($row['centerId'] == $userMedRows[$i]['centerId'] && $row['orderId'] == $userMedRows[$i]['orderId']){
                //         $push = 0;
                //         array_push($duplicates, $row);
                //     }
                // }
                if($push){
                    array_push($userMedRows, $row);
                    $cnt = count($userMedRows);
                }
            }
            $numMeds = count($userMedRows);
            $numDupes = count($duplicates);
            $sql = 'SELECT * FROM `uprotocols` WHERE uid LIKE '.$uid;
            $res = mysqli_query($con, $sql);
            $row = mysqli_fetch_assoc($res);
        }else{
            if(isset($id)){//true if edit
                $con = Globals::getConnection();
                $sql = 'SELECT * FROM `protocols` WHERE id LIKE '.$id;
                $res = mysqli_query($con, $sql);
                $row = mysqli_fetch_assoc($res);
                $name = $row['name'];
            }else{//true if add
                $id = $_GET['savedId'];
                //var_dump($id);
                $con = Globals::getConnection();
                $sql = 'SELECT * FROM `protocols` WHERE id LIKE \''.$id.'\'';
                $res = mysqli_query($con, $sql);
                $row = mysqli_fetch_assoc($res);
                $name = $row['name'];
                //var_dump($name);
    
            }
        }
        
        if($type == 'u'){
            $i=0;
            $id=$row['id'];
            //$numMeds=$row['medNum'];
            $numAppts=$row['apptNum'];
            $numLevels=$row['levelNum'];
            $days=1;
            echo '
                    <div class="row" style="margin-top:1%;">
                        <table border="0" cellpadding="0" cellspacing="3" style="overflow:scroll;">
                            <tbody>
                                <tr>
                                    <td class="td1" colspan="1" style="text-align:left; color:#fff; font-size:16px;"><b>Build A Protocol - Building '.$name.'  </b></td>
                                </tr>
                                <tr>
                                    <td class="td1">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="td1" style="background-color:#2b2b2b;">
                                        <table style="font-size:16px; color:#fff; text-align:left;">
                                            <tr>
                                                <td class="td1"><b>Day</b></td>';
                    while($i<$numMeds){
                        array_push($thisMedStart, $userMedRows[$i]['start']);
                        $end=$userMedRows[$i]['end'];
                        $maxdays = (strtotime($end) - strtotime($userMedRows[$i]['start']))/(60*60*24);
                        if($days<$maxdays){
                            $days=$maxdays;
                        }
                        $i++;
                    }
                    $i=0;
                    while ($i<=$days){
                        $i++;
                        echo '
                                                <td class="td1"><b>'.$i.'</b></td>
                            ';
                    }
                    $i=0;
                    echo '                  </tr>
                                            <tr>
                                                <td class="td1"><b>Date</b></td>';

                    $chk = 0;
                    $earliest = 0;
                    foreach ($thisMedStart as $startDateCheck) {

                        if($chk == 0){

                            $earliest = $startDateCheck;

                        }else{

                            if(strtotime($startDateCheck) <= strtotime($earliest)){

                                $earliest = $startDateCheck;

                            }

                        }

                    }

                    while ($i <= $days){

                        $date = date('m/d/Y', strtotime($earliest.' +'.$i.' day'));
                        echo '
                                                <td class="td1"><b>'.$date.'</b></td>
                            ';
                        $i++;
                    }
                    $i=0;
                    $skip=0;
                    $n=0;
                    while($n<$numMeds){
                        $skip=0;
                        $thisUserMedId=$userMedRows[$n]['userMedId'];
                        $med=$userMedRows[$n]['orderId'];
                        $start=$userMedRows[$n]['start'];
                        $end=$userMedRows[$n]['end'];
                        $interval=$userMedRows[$n]['intervalDays'];
                        $AM=$userMedRows[$n]['AM'];
                        $PM=$userMedRows[$n]['PM'];
                        $dateAdded=$userMedRows[$n]['dateAdded'];
                        $res2 = DataAccess::getProtocolByID($med);//added
                        $row2 = mysqli_fetch_assoc($res2);
                        $medName = $row2['name'];
                        echo '                  </tr>

                                                <tr>
                                                    <!-- onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=e&t=med&s='.$step.'&medN='.$n.'&uid='.$uid.'&umid='.$thisUserMedId.'\';"" -->
                                                    <td onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=e&t=med&s='.$step.'&medN='.$n.'&uid='.$uid.'&umid='.$thisUserMedId.'\';"" class="td1"><a href="onclick()"><b>'.$medName.'</b></a></td>';
                        while ($i<=$days){
                            $date = date('m/d/Y', strtotime($earliest.' +'.$i.' day'));
                           /* for($x=0;$x<$numDupes;$x++){
                                if($med == $duplicates[$x]['orderId']){
                                    if(strtotime($date) == strtotime($duplicates[$x]['dateAdded'])){
                                        $PM=$duplicates[$x]['PM'];
                                    }else if(strtotime($date) > strtotime($duplicates[$x]['dateAdded'])){
                                        $AM=$duplicates[$x]['AM'];
                                        $PM=$duplicates[$x]['PM'];
                                        $interval = $duplicates[$x]['intervalDays'];
                                    }
                                }
                            }*/
                            $skip++;
                            if(strtotime($date) == strtotime($start)){
                                $skip=0;
                                    echo '
                                                        <td class="td1"><b>'.$AM.' AM<br>'.$PM.' PM</b></td>
                                    ';
                            }
                            else if(strtotime($date) > strtotime($start)){

                                if(strtotime($date) <= strtotime($end)){
                                    if($skip != $interval){
                                        echo '
                                                        <td class="td1"><b></b></td>
                                   	    ';
                                    }else{
                                        $skip=0;
                                        echo '
                                                        <td class="td1"><b>'.$AM.' AM<br>'.$PM.' PM</b></td>
                                        ';
                                    }
                                }
                            }else{
                                $skip=0;
                                echo '
                                                        <td class="td1"><b></b></td>
                                    ';
                            }
                            $i++;                    
                        }
                        $i=0;
                        $n++;
                    }
                    $n=0;
                    $days++;
                    echo '                  </tr>
                                            <tr>
                                                <td class="td1" colspan="'.$days.'" style="border-bottom:solid #AAA9AA 1px; background-color:#2b2b2b;" onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=a&t=med&s='.$step.'&uid='.$uid.'\';"><u><a href="onclick()">+ Add a Medicine</u></a></td>
                                            </tr>
                                            ';
                    $days--;
                    $sql = 'SELECT * FROM `uprotocols` WHERE uid LIKE '.$uid;
                    $res = mysqli_query($con, $sql);
                    $row = mysqli_fetch_assoc($res);
                    while($n<$numAppts){
                        $n++;
                        $appt=$row['appt'.$n];
                        $location=$row['appt'.$n.'Location'];
                        /*
                        if($n==1){
                            $topBorder='border-top:solid #AAA9AA 1px;';
                        }else{
                            $topBorder='';
                        }
                        if($n==$numAppts){
                            $bottomBorder='border-bottom:solid #AAA9AA 1px;';
                        }else{
                            $bottomBorder='';
                        }
                        */
                        echo '                  
                                            <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=e&t=appt&s='.$step.'&apptN='.$n.'&uid='.$uid.'&num='.$numAppts.'\';">
                                                <td class="td1"><b><a href="onclick()">'.$appt.'</b></a></td>';
                        while ($i <= $days){
                            $date = date('m/d/Y', strtotime($thisMedStart[0].' +'.$i.' day'));
                            if(strtotime($date) == strtotime($row['appt'.$n.'Date'])){
                                //$date = date('m/d/Y', strtotime($today.' +'.$i.' day'));
                                echo '
                                                <td class="td1"><b>'.$location.'</b></td>
                                ';
                            }else{
                                echo '
                                                <td class="td1"><b> </b></td>
                                ';
                            }
                            $i++;
                        }
                        $i=0;
                        echo '
                                            </tr>
                        ';
                    }
                    $days++;
                    echo '
                                            <tr style="border-bottom:solid #AAA9AA 1px;" onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=a&t=appt&s='.$step.'&uid='.$uid.'&num='.$numAppts.'\';">
                                                <td class="td1" colspan="'.$days.'" style="background-color:#2b2b2b;"><u><a href="onclick()">+ Add an Appointment</u></a></td>
                                            </tr>
                                            ';
                    $days--;
                    $n=0;
                    
                    while($n < $numLevels){
                        $n++;
                        $level=$row['level'.$n];
                        $val=$row['level'.$n.'val'];
                        echo '
                                            <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=e&t=level&s='.$step.'&levelN='.$n.'&uid='.$uid.'&num='.$numAppts.'\';">
                                                <td class="td1"><b><a href="onclick()">'.$level.'</b></a></td>';
                        while ($i < $days){
                            $i++;
                            if($i==1){
                                echo '
                                                    <td class="td1"><b>'.$val.'</b></td>
                                    ';
                            }else{
                                echo '
                                                    <td class="td1"><b></b></td>
                                    ';
                            }
                        }
                        $i=0;
                        echo '
                                            </tr>
                        ';
                    }
                    $n=0;
                    $days++;
                    echo '
                                            <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=a&t=level&s='.$step.'&uid='.$uid.'&num='.$numLevels.'\';">
                                                <td class="td1" colspan="'.$days.'" style="background-color:#2b2b2b;"><u><a href="onclick()">+ Add a Result</u></a></td>
                                            </tr>';
                    $days--;
                    echo '
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>';
        }else{
            $i=0;
            $id=$row['id'];
            $numMeds=$row['medNum'];
            $numAppts=$row['apptNum'];
            $numLevels=$row['levelNum'];
            $days=1;
            echo '
                    <div class="row" style="margin-top:1%;">
                        <table border="0" cellpadding="0" cellspacing="3" style="overflow:scroll;">
                            <tbody>
                                <tr>
                                    <td class="td1" colspan="1" style="text-align:left; color:#fff; font-size:16px;"><b>Build A Protocol - Building '.$name.'</b></td>
                                </tr>
                                <tr>
                                    <td class="td1">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="td1" style="background-color:#2b2b2b;">
                                        <table style="font-size:16px; color:#fff; text-align:left;">
                                            <tr>
                                                <td class="td1"><b>Day</b></td>';
                    $n=0;
                    while($i<$numMeds){
                        $i++;
                        $interval[$i] = $row['interval'.$i];
                        $end=$row['end'.$i];
                        if($days<$end){
                            $days=$end;
                        }
                    }

                    $i=0;
                    while ($i<$days){
                        $i++;
                        echo '
                                                <td class="td1"><b>'.$i.'</b></td>
                            ';
                    }
                    $i=0;
                    echo '                  </tr>
                                            <tr>
                                                <td class="td1"><b>Date</b></td>';
                    while ($i < $days){
                        $i++;
                        $date = date('m/d/Y', strtotime($today.' +'.$i.' day'));
                        echo '
                                                <td class="td1"><b>'.$date.'</b></td>
                            ';
                    }
                    $i=0;
                    $skip=0;
                    $res = DataAccess::getNewProtosByID($id);
                    $row = mysqli_fetch_assoc($res);

                    while($n<$numMeds){
                        $skip=0;
                        $n++;
                        $med=$row['med'.$n];
                        $start=$row['start'.$n];
                        $end=$row['end'.$n];
                        $AM=$row['AM'.$n];
                        $PM=$row['PM'.$n];
                        if(isset($med)){
                            $res2 = DataAccess::getProtocolByID($med);//added
                            $row2 = mysqli_fetch_assoc($res2);
                            $medName = $row2['name'];
                        }
                        
                      
                        echo '                  </tr>
                                                <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=e&t=med&s='.$step.'&n='.$n.'&jid='.$id.'&mid='.$med.'\';"">
                                                    <td class="td1"><b><a href="onclick()">'.$medName.'</a></b></td>';
                        while ($i < $days){
                            $i++;
                            $skip++;
                            if($i == $start){
                                $skip=0;
                                    echo '
                                                        <td class="td1"><b>'.$AM.' AM<br>'.$PM.' PM</b></td>
                                    ';
                            }
                            else if($i > $start){
                            	if($i <= $end){
                            		if($skip != $interval[$n]){
                                    		echo '
                                                        <td class="td1"><b></b></td>
                                    		';
                                	}else{
                                    		$skip=0;
                                    		echo '
                                                        <td class="td1"><b>'.$AM.' AM<br>'.$PM.' PM</b></td>
                                    		';
                                	} 
                                }
                                
                            }else{
                                $skip=0;
                                echo '
                                                        <td class="td1"><b></b></td>
                                    ';
                            }                    
                        }
                        $i=0;
                    }
                    $n=0;
                    $days++;
                    echo '                  </tr>
                                            <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=a&t=med&s='.$step.'&jid='.$id.'\';">
                                                <td class="td1" colspan="'.$days.'" style="border-bottom:solid #AAA9AA 1px; background-color:#2b2b2b;"><u><a href="onclick()">+ Add a Medicine</u></a></td>
                                            </tr>
                                            ';
                    $days--;
                    $sql = 'SELECT * FROM `protocols` WHERE id LIKE '.$id;
                    $res = mysqli_query($con, $sql);
                    $row = mysqli_fetch_assoc($res);
                    while($n<$numAppts){
                        $n++;
                        $appt=$row['appt'.$n];
                        $location=$row['appt'.$n.'Location'];
                        /*
                        if($n==1){
                            $topBorder='border-top:solid #AAA9AA 1px;';
                        }else{
                            $topBorder='';
                        }
                        if($n==$numAppts){
                            $bottomBorder='border-bottom:solid #AAA9AA 1px;';
                        }else{
                            $bottomBorder='';
                        }
                        */
                        echo '                  
                                            <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=e&t=appt&s='.$step.'&n='.$n.'&jid='.$id.'&num='.$numAppts.'\';">
                                                <td class="td1"><b><a href="onclick()">'.$appt.'</a></b></td>';
                        while ($i < $days){
                            $i++;
                            if($i == $row['appt'.$n.'Day']) {
                                $date = date('m/d/Y', strtotime($today.' +'.$i.' day'));
                                echo '
                                                <td class="td1"><b>'.$location.'</b></td>
                                ';
                            }else{
                                echo '
                                                <td class="td1"><b> </b></td>
                                ';
                            }
                        }
                        $i=0;
                        echo '
                                            </tr>
                        ';
                    }
                    $days++;
                    echo '
                                            <tr style="border-bottom:solid #AAA9AA 1px;" onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=a&t=appt&s='.$step.'&id='.$id.'&num='.$numAppts.'\';">
                                                <td class="td1" colspan="'.$days.'" style="background-color:#2b2b2b;"><u><a href="onclick()">+ Add an Appointment</a></u></td>
                                            </tr>
                                            ';
                    $days--;
                    $n=0;
                    
                    while($n < $numLevels){
                        $n++;
                        $level=$row['level'.$n];
                        echo '
                                            <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=e&t=level&s='.$step.'&n='.$n.'&jid='.$id.'&num='.$numAppts.'\';">
                                                <td class="td1"><b><a href="onclick()">'.$level.'</a></b></td>';
                        while ($i < $days){
                            $i++;
                            echo '
                                                <td class="td1"><b> </b></td>
                                ';
                        }
                        $i=0;
                        echo '
                                            </tr>
                        ';
                    }
                    $n=0;
                    $days++;
                    echo '
                                            <tr onclick="window.top.location.href =\'http://'.$thisURL.'/protocols.php?action=a&t=level&s='.$step.'&id='.$id.'&num='.$numLevels.'\';">
                                                <td class="td1" colspan="'.$days.'" style="background-color:#2b2b2b;"><u><a href="onclick()">+ Add a Result</a></u></td>
                                            </tr>';
                    $days--;
                    echo '
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>';
        }
            
    }
    echo '
    </body>
</html>
';
?>