<?php
/*
 * Script to display the users prediction performance
 * show() - Main driver
 * navtabs() - Top navigation items
 * options_form() - display the options form
 * save_options() - save the submitted options
 * save_password() - save a new password
 */
function show()
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted
  
  $disp_str = "
<p>
User site options: subscribed leagues and password reset.<br />
Use this page to update the leagues that predictions are made for, up to a 
maximum of four leagues.<br />
You may also change your password.
</p>";

  if($_POST['saveoptions']) /* Request to save options */
  {

    /* Throw an error if no leagues found */
    if(!count($_POST['leagues']))
    {
      $disp_str .= "<span class=\"errmsg\">At least one league must be selected</span>\n";
    } else
    {
      /* Check for a valid timezone */
      $err = user_timezone_validate($_POST['timezone']);
      if($err) { $disp_str .= "<span class=\"errmsg\">" . $err . "</span>\n"; }
      else {
        $disp_str .= save_options();
      }
    }
  }

  if($_POST['newpassword']) /* Request to save options */
  {
     if($_POST['password1'] != $_POST['password2'])
     {
       $disp_str .= "(<span class=\"errmsg\">" .
                    "The passwords do no match</span><br />\n";
     }
     else
     {
       $disp_str .= save_password($_POST['password1'], $_POST['password2']);
     }
   }

  $disp_str .= options_form();

  return($disp_str);
}

/*
 * Function to construct the navigation tabs for the section navigation
 * @param:
 *  none
 * @returns:
 *  string contains text to display on the page
 */
function navtabs()
{
  $navstr = "<ul><li class=\"selected\">Options</li></ul>\n";

  return($navstr);
}

/*
 * Function to create the form displaying the user options
 * @param:
 *  none
 * @returns:
 *  string containing the forms to be displayed
 */
function options_form()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array including available database queries */

  $disp_str = "<p>
<form action=\"?action=opts\" method=\"post\" name=\"nextset\">
<fieldset><legend>League & timezonesettings</legend><dl>
<dt><label for=\"leagues[]\">Leagues</label></dt>
";

  $disp_str .= "<dd>" .
               user_subscribed_select($_SESSION['uid']) .
               "</dd>\n";

  $disp_str .= "
<dt><label for=\"timezone\">Timezone</label></dt>
<dd>" . user_timezone_select($_SESSION['uid']) . "</dd>\n";

  /* Add submit and reset buttons */
  $sub  = new HTML_QuickForm_submit('saveoptions', 'Save leagues', "class=button");
  $res  = new HTML_QuickForm_reset('reset',    'Undo',  "class=button");
  $sgrp = new HTML_QuickForm_group('submitreset', '',
                  array($sub, $res), '', FALSE);

  /* Add submit and reset buttons */
  $sub  = new HTML_QuickForm_submit('saveoptions', 'Save leagues', "class=button");
  $res  = new HTML_QuickForm_reset('reset',    'Undo',  "class=button");
  $sgrp = new HTML_QuickForm_group('submitreset', '',
                  array($sub, $res), '', FALSE);

  $disp_str .= "</dl>";

  $disp_str .= $sgrp->toHtml();

  $disp_str .= "</fieldset>\n</form>\n</p>";

  /* Password change form */
  $disp_str .= "
<p>
<form action=\"?action=opts\" method=\"post\" name=\"newpasswd\">

<fieldset><legend>Change password</legend><dl>
<dt><label for=\"password1\">New password</label></dt>
";

  $pass1 = new HTML_QuickForm_text(
                'password1',
                'New password');

  $pass2 = new HTML_QuickForm_text(
                'password2',
                'Confirm password');

  $disp_str .= "<dd>" .
               $pass1->toHtml() .
               "</dd>" .
               "<dt><label for=\"password2\">Confirm password</label></dt>" .
               "<dd>" .
               $pass2->toHtml() .
               "</dd>\n";

  /* Add submit and reset buttons */
  $sub  = new HTML_QuickForm_submit('newpassword', 'Change password', "class=button");
  $res  = new HTML_QuickForm_reset('reset',    'Reset',  "class=button");
  $sgrp = new HTML_QuickForm_group('newpass', '',
                  array($sub, $res), '', FALSE);

  $disp_str .= "</dl>";

  $disp_str .= $sgrp->toHtml();

  $disp_str .= "</fieldset></p>";
  $disp_str .= "</form>\n";

  return($disp_str);

}

/*
 * Save the league subscriptions for the user
 * Create a temporary table and copy the user's subscriptions into it.
 * Then delete the user's subscriptions from the live table,
 * verify the posted values and add them to the subscription list.
 * This needs to verify the following:
 *  - that a league requested is listed in the database
 *  - whether a requested league was previously subscribed to. This will
 *    preserve any existing predictions.
 * If the verification fails, then ignore the specific request but continue
 * with the remaining requests.
 * @param:
 *  none
 * @returns:
 *  string showing actions that have been performed.
 */
function save_options()
{
  global $db; /* Global database handle */

  global $query_ary; /* List of acceptable queries */

  $valid_leagues = get_valid_leagues();

  if(count($valid_leagues))
  {
    $disp_str .= subscribe_user_to_leagues($_SESSION['uid'], $valid_leagues);
  }
  else /* Don't do anything other than warn the user */
  {
    $disp_str .= "<span class=\"errmsg\">No valid leagues found in update.</span><br />\n";
  }

  /* Update the timezone */
  $res = $db->query($query_ary['save_user_timezone'],
                    array($_POST['timezone'], $_SESSION['uid']));
  if(PEAR::isError($res)) {
    $disp_str .= "<span class=\"errmsg\">Error updating timezone.</span><br />\n";
  } else {
    $disp_str .= "Saved timezone as " . $_POST['timezone'] . ".<br />\n";
  }

  return($disp_str);
}

/*
 * Function to verify that the password and confirmation are the same
 * and update the database with the new entry.
 * @param:
 *  $p1 - new password
 *  $p2 - password confirmation
 * @return
 *  string showing outcome of the operation
 */
function save_password( $p1 = "", $p2 = "")
{
    global $db; /* Global database handle */

  global $query_ary; /* List of acceptable queries */

  $disp_str = "Saving new password.<br />\n";

  if($db->query($query_ary['save_password'], array(md5($p1), $_SESSION['uid'])))
  {
    $disp_str .= "Password changed.<br />\n";
  }
  else
  {
    $disp_str .= "Password change failed.<br />\n";
  }

  return($disp_str);
}
?>
