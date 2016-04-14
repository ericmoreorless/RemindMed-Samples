<?php
/************
Created by Eric Morales - SoundHealth - 2015
RemindMed
************/
//Need to check this file. This is will determine if the server needs to be configrued or not.
$globalsFile = '../Classes/Globals.php';

//if the 'x' GET variable is present, it means a security exception was caught
if ($_GET['x'] == 1){//no match condition
 echo 'User not found.';
}else if ($_GET['x'] == 2){//no entry/improper entry condition
 echo 'Please log in.';
}
//checking for the existence of global file
if (file_exists($globalsFile)){//no need to initialize, show login markup
  require_once($globalsFile);
  $action = '../login.php';
  $getGlobal = new Globals;
  $client = $getGlobal->client;
  loginMarkup($action, $client);
}else{//need to initialize, show configure markup
  $action = '../initialize.php';
  confiureMarkup($action);
}
/************
markup layouts (user-defined functions)
************/
function loginMarkup($action, $client){
    echo '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
      <head>
        <body style="background-color:#2b2b2b;">
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Login</title>
        <script>
          function chkForm(el){
            var valid = true;
            var oForm = el;
            for(var i=0; i < oForm.elements.length; i++) {
              var oField = oForm.elements[i];
              if (oField.value == \'\'){
                //alert(\'You must fill in the \'+oField.name+\' field\');
                alert(\'You must fill in the username and password!\');
                valid = false;
                break;
              }
            }
            return valid;
          }
        </script>
        <style>

        </style>
      </head>
        <body>
          <table width="80%" height="40%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size: 12px;">
            <tr>
              <td>
                <center><img style=" width:25% !important; height:auto !important; display:block" src="../Images/rmOrange.png"/></center>
                <br>
              </td>
            </tr>
            <tr>
              <td>
                <form id="loginForm" name="loginForm" method="post" action="'.$action.'" onsubmit="return chkForm(this)">
                <table style="background-color: #CCCCCC; border-width: 1px; height: 400px; width:600px; font-size: 12px;" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
                  <tr height="10%">
                    <td width="33%">
                      &nbsp;
                    </td>
                    <td width="33%">
                      &nbsp;
                    </td>
                    <td width="33%">
                      &nbsp;
                    </td>
                  </tr>
                  <tr height="8%">
                    <td>
                      &nbsp;
                    </td>
                    <td>
                      <span style="font-size:24px"></span>
                    </td>
                    <td>
                      &nbsp;
                    </td>
                  </tr>
                  <tr height="8%">
                    <td>
                      &nbsp;
                    </td>
                    <td>
                      &nbsp;
                    </td>
                    <td>
                      &nbsp;
                    </td>
                  </tr>
                  <tr height="8%">
                    <td>
                      &nbsp;
                    </td>
                    <td>
                      <span style="font-size:24px">Username:</span> <input name="username" type="text" id="username" size="50" style="height:40px;font-size:18px" />
                     
                    </td>

                    <td>
                      &nbsp;
                    </td>
                  </tr>
                  <tr height="8%">
                    <td>
                      &nbsp;
                    </td>
                    <br>
                    <td>

                      <span style="font-size:24px">Password:&nbsp;</span> <input name="password" type="password" id="password" size="50" style="height:40px;font-size:18px" ; />
                      
                    </td>

                    <td>
                      &nbsp; <input name="client" type="hidden" id="client" value="vt22" />
                    </td>
                  </tr>
                  <tr height="8%">
                    <td>
                      &nbsp;
                    </td>
                    <td>
                      <br>
                      <button type="submit" value="Log in" name="submit" class="btn btn-warning" style="height:40px;width:451px;font-size:18px">Log in</button>
                    </td>
                    <td>
                      &nbsp;
                    </td>
                  </tr>
                  <tr height="30%">
                    <td>
                      &nbsp;
                    </td>
                    <td>
                      &nbsp;
                    </td>
                    <td>
                      &nbsp;
                    </td>
                  </tr>
                </table>
                </form>
              </td>
            </tr>
            <tr>
              <td align="right">
                &nbsp;&nbsp;'.$client.'
              </td>
            </tr>
          </table>
      </body>
  </html>';
}

function confiureMarkup($action){
  echo '
      <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
      <html>
        <head>
          <link rel="stylesheet" type="text/css" href="style.css">
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
          <title>Login</title>
        </head>
          <body>
            <table width="80%" height="40%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size: 12px;">
              <tr>
                <td>
                  <img style=" width:15% !important; height:auto !important; display:block" src="../Images/rmOrange.png"/>
                </td>
              </tr>
              <tr>
                <td>
                  <form id="loginForm" name="loginForm" method="post" action="'.$action.'">
                  <table style="background-color: #CCCCCC; border-width: 1px; height: 400px; width: 100%; font-size: 12px;" align="left" cellpadding="0" cellspacing="0" bordercolor="#000000">
                    <tr height="30%">
                      <td width="33%">
                        &nbsp;
                      </td>
                      <td width="33%">
                        &nbsp;
                      </td>
                      <td width="33%">
                        &nbsp;
                      </td>
                    </tr>
                    <tr height="8%">
                      <td>
                        &nbsp;
                      </td>
                      <td style="font-size:16px;">
                        Welcome to RemindMed, Your system is not yet configured, please press the \'Configure\' button below.
                      </td>
                      <td>
                        &nbsp;
                      </td>
                    </tr>
                    <tr height="8%">
                      <td>
                        &nbsp;
                      </td>
                      <td>
                        &nbsp;
                      </td>
                      <td>
                        &nbsp;
                      </td>
                    </tr>
                    <tr height="8%">
                      <td>
                        &nbsp;
                      </td>
                      <td>
                        <input type="submit" value="Configure" id="configure" name="configure" />
                      </td>
                      <td>
                        &nbsp;
                      </td>
                    </tr>
                    <tr height="30%">
                      <td>
                        &nbsp;
                      </td>
                      <td>
                        &nbsp;
                      </td>
                      <td>
                        &nbsp;
                      </td>
                    </tr>
                  </table>
                  </form>
                </td>
              </tr>
              <tr>
                <td align="left">
                  &nbsp;&nbsp;
                </td>
              </tr>
            </table>
        </body>
    </html>';
}
?>