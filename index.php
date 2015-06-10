<?php
include "include/settings.php";
include "include/queries.php";
include "include/functions.php";
include "include/leftnav.php";
// Load the main QuickForm class - if not already loaded
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/checkbox.php';
require_once 'HTML/QuickForm/date.php';
require_once 'HTML/QuickForm/group.php';
require_once 'HTML/QuickForm/hidden.php';
require_once 'HTML/QuickForm/password.php';
require_once 'HTML/QuickForm/radio.php';
require_once 'HTML/QuickForm/reset.php';
require_once 'HTML/QuickForm/select.php';
require_once 'HTML/QuickForm/static.php';
require_once 'HTML/QuickForm/submit.php';
require_once 'HTML/QuickForm/text.php';
require_once 'HTML/QuickForm/textarea.php';

/* Clean up any incoming form data to reduce attack risks */
if (!get_magic_quotes_gpc()) {
  addslashGpc();
}

/* Prepare a session for application-wide settings */
session_start();

if($_GET['do'] == "rpc") {include "rpc.php";}


/* Logout with a meeaningful message */
if($_GET['action'] == "logout")
{
  unset($_SESSION);
  session_destroy();
  include "include/logout.php";
  $navstr = navtabs();
  $disp_str = show();
}

/* Check the login status and store user settings */
if(!isset($_SESSION['username']))
{
  include "include/login.php";
  $leftnav = login_form();
  if($_GET['action'] != "logout") {$navstr  = toptabs();}
  show_login();
}

/* Store any page tab id that has been submitted so long as it is numeric */
unset($_SESSION['tabnum']);
if(isset($_GET['tabnum']) && is_string($_GET['tabnum']))
{
  $_SESSION['tabnum'] = trim($_GET['tabnum']);
}

/*
 * Identify the requested action if the user has logged in
 */
$action = "summary";
if($_SESSION['username'])
{
  switch($_GET['action'])
  {
    case 'nextset':
      $action = "next";
      if(!$_SESSION['tabnum'])
      {
        $_SESSION['tabnum'] = $db->getOne($query_ary['get_first_league'],
                                          array($_SESSION['uid'],
                                                date("Y-m-d H:m:s")));
      } else {
        if(!$db->getOne($query_ary['get_league_from_subs'],
                  array($_SESSION['uid'], $_SESSION['tabnum'])))
        {
          $_SESSION['tabnum'] = $db->getOne($query_ary['get_first_league'],
                                            array($_SESSION['uid'],
                                                  date("Y-m-d H:m:s")));
        }
      }
      break;
    case 'history':
      $action = "hist";
      break;
    case 'team':
      $action = "team";
      break;
    case 'league':
      $action = "league";
      break;
    case 'opts':
      $action = "options";
      break;
    case 'help':
      $action = "help";
      break;
    case 'logout':
      $action = "logout";
      break;
    /*
     * Admin operations
     */
    case 'users':
      if(isadmin()) {$action = "users";}
      break;
    case 'fixtures':
      if(isadmin()) {$action = "fixtures";}
      break;
    case 'leagues':
      if(isadmin()) {$action = "leagues";}
      break;
    case 'tournaments':
      if(isadmin()) {$action = "tournaments";}
      break;
    case 'results':
      if(isadmin()) {$action = "results";}
      break;
    case 'search':
      if(isadmin()) {$action = "search";}
      break;
    case 'seasons':
      if(isadmin()) {$action = "seasons";}
      break;
    case 'userteams':
      if(isadmin()) {$action = "userteams";}
      break;
  }
  /*
   * The action script will return a string with the content to be displayed
   */
  include   "include/$action" . ".php";
  $leftnav  = leftnav();
  $navstr   = navtabs();
  $disp_str = show();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/predictions.css">
<title>YJFC Match Predictor</title>
<link rel="stylesheet" type="text/css" href="yui/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="yui/calendar/assets/skins/sam/calendar.css" />
<script type="text/javascript" src="yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="yui/calendar/calendar-min.js"></script>

<?php
 if(file_exists("css/$action.css")) {
  echo "<link rel=\"stylesheet\" href=\"css/$action.css\" type=\"text/css\" />\n";
}
 if(file_exists("ajax/$action.js")) {
  echo "<script src=\"ajax/$action.js\" type=\"text/javascript\"></script>\n";
}
?>
<script src="expand.js" type="text/javascript"></script>
<script language="JavaScript">
<?php if($_SESSION['uid']) { ?>
  var g_fDoFocus = false;
<?php } else { ?>
  var g_fDoFocus = true;
<?php } ?>
  function setFocus()
  {
    if (g_fDoFocus) window.document.login.username.focus();
  }

setBrowser();
</script>
</head>
<body onload="setFocus()" class="yui-skin-sam">

<div id="body">

<div id="banner">
<img src="images/yjfc_top.jpg" border="0" width="780" height="100" alt="YJFC top banner" />
</div> <!-- banner -->
<div id="showuser">
<?php
if($_SESSION['username']) { echo "&nbsp;&nbsp;Welcome, " . $_SESSION['username'] . " at " . date("H:i:s T"); }
else { echo "&nbsp;&nbsp;Not logged in"; }
?>
</div><!-- showuser -->

<div id="main">

<div id="leftnav">
<?php echo $leftnav . "\n" .  top_five_users() . "<br />\n"; ?>
</div> <!-- leftnav -->

<div id="content">

<div id="navtabs">
<?php echo $navstr; ?>
</div> <!-- navtabs -->

<div id="inner">
<?php
if(!$disp_str)
{
  include "preamble.html";
} else
{
  echo $disp_str;
}
?>
</div> <!-- inner -->
</div> <!-- content -->

</div> <!-- main -->


<div id="footer">
PHP coding jrawcliffe for YJFC, July 2007
</div> <!-- footer -->

<!-- end of body -->
</div>
</body>
</html>
