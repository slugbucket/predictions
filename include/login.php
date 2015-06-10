<?php
/*
 * Function to show either the login form or the results of a login attempt
 * @params:
 *  none
 * @return:
 *  String indicating the login status
 */
function show_login()
{

  if($_POST['login'])
  {
    $disp_str = check_login();
  }
  else
  {
     $disp_str = "";
  }

  return($disp_str);
}

/*
 * Function to construct the navigation tabs for the section navigation
 * @param:
 *  none
 * @returns:
 *  string contains text to display on the page
 */
function toptabs()
{
  $navstr = "<ul><li></li></ul>\n";

  return($navstr);
}

/*
 * Function to attempt a login for the supplied username and password
 * @params:
 *  none (input taken from global $_POST array)
 * @returns:
 *  Users full name on success, error message on failure
 */
function check_login()
{
  global $db;
  
  global $query_ary;

  /* Don't accept input fields longer than what the database field is */
  $username = trim(substr(stripslashes($_POST['username']), 0, 16));
  $password = trim(substr(stripslashes($_POST['password']), 0, 32));

  /* MDB2 $login_qry = "SELECT id,fullname, isadmin FROM predict_users WHERE uname = ? AND password = md5(?) AND active ='1' LIMIT 1";
  $login_res = $db->query( $login_qry, array($username, $password));
  */
  $lrow = mdb2_fetchRow('check_login', array($username, $password));

  /* MDB2 $login_res->numRows()) */
  if($lrow) {
    $_SESSION['uid']      = $lrow['id'];
    $_SESSION['username'] = $lrow['fullname'];
    $_SESSION['isadmin']  = $lrow['isadmin'];
    
    $disp_str = "Welcome,<br />" . $lrow['fullname'] . "<br /><br />\n";

    /* Record the login in the predict_users table */
    mdb2_query('update_last_login', array(date("Y-m-d H:m:s"), $lrow['id']));
  }
  else
  {
    $disp_Str = "Incorrect username or password.<br />\n";
  }

  return($disp_str);
}

/*
 * Function to display the user login form
 * @params:
 *  none
 * @return:
 *  String containing login form
 */
function login_form()
{
  /* Construct a form requesting the username and password */
  $disp_str = "
<div id=\"login\">
<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=\"login\">

Username
";

  $username = new HTML_QuickForm_text(
                'username',
                'username',
                array('size' => "12", 'maxlength' => '16'));

  $password = new HTML_QuickForm_password(
                'password',
                'Password',
                 array('size' => "12", 'maxlength' => '16'));

  $disp_str .= $username->toHtml() .
               "<br />Password<vbr />" .
               $password->toHtml() .
               "<br />\n";

  $sub  = new HTML_QuickForm_submit('login', 'Login', "class=button");
  $disp_str .= $sub->toHtml();

  $disp_str .= "</form>\n</div><!-- login -->";

  return($disp_str);
}
?>
