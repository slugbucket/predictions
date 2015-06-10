<?php
/*
 * Script to handle userteam management
 *
 * show() - Main driver
 * navtabs() - Top navigation items
 * userteams_list() - list all userteams
 * userteams_form() - Display form for a userteam
 * userteams_validate() - validate submitted userteam details
 * userteams_update() - Save updated details for a userteam
 * userteams_add() - save the details of a new userteam
 * userteams_delete() - delete a userteam
 */
/* Main driver function interpreting requests to thi saction
 * @params
 *  none
 * @returns
 * Sting with HTML to display on page
 */
function show()
{
  global $db; // Global database handle

  global $query_ary; // Database queries that can be submitted

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "<p>Manage userteams.</p>\n";

  /* Trap request for a new user form */
  if($_POST['newuserteam'] == "New user team")
  {
    return($disp_str . userteams_form());
  }

  /* Trap request to add a new user team */
  if($_POST['updateuserteam'] == "Save details" && !$_GET['id'])
  {
    $res .= userteams_add();
    if(preg_match("/^ERROR:/", $res))
    {
      $disp_str .= error_message($res);
      return($disp_str . userteams_form());
    }
  }

  /* Trap request to update an existing userteam */
  if($_POST['updateuserteam'] == "Save details" && $_GET['id'])
  {
    $res .= userteams_update();
    if(preg_match("/^ERROR:/", $res))
    {
      $disp_str .= error_message($res);
      return($disp_str . userteams_form());
    }
  }

  if($_GET['id'] && is_numeric($_GET['id']))
  {
    return($disp_str . userteams_form());
  }

  $disp_str .= userteams_list();

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
  $navstr = "<ul><li class=\"selected\">Userteams<li></ul>\n";

  return($navstr);
}

/*
 * Function to create a list of users starting with 'tabnum' for display
 * and modification.
 * @params:
 *  none
 * @returns:
 *  string containing HTML to be displayed.
 */
function userteams_list()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  $disp_str = "Edit existing userteams or add a new userteam.<br />\n";

  $uteams = $db->query($query_ary['user_team_list']);

  if($uteams->numRows())
  {

    $disp_str .= "
<!-- div id=\"leagues\" -->
<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">
<tr>
 <th>&nbsp;</th><th>Team name</th><th>Created</th><th>&nbsp;</th>
</tr>
";

    $endclass="dark";

    while($uteams->fetchInto($uteam))
    {

      $class="body";
      $class = ($uteam['active'] ? "" : " =\"inactive\"");

      $edit = "<a href=\"" . $_SERVER[REQUEST_URI] .
              "&id=" . $uteam['id'] . "\">edit</a>";

      $disp_str .=
"<tr$class>" .
"<td class=\"pointless\">&nbsp;</td>" .
"<td class=\"$endclass\">" . $uteam['name']    . "</td>" .
"<td>" . $uteam['created'] . "</td>" .
"<td>" . $edit . "</td>" .
"</tr>\n";

      $endclass = ($endclass == "dark" ? "light" :"dark");
    }

    $disp_str .= "</table>\n<!-- /div --><!-- leagues -->\n";
  }
  else
  {
    $disp_str .= "There are no users matching the request.<br />\n";
  }

  /* Add a new user button */
  $sub = new HTML_QuickForm_submit('newuserteam', 'New user team',
                                   array('class' => 'button'));
  $disp_str .= "<p>
<form name=\"newuserteam\" method=\"post\" " .
      "action=\"" . $_SERVER[REQUEST_URI] . "\">\n" .
$sub->toHtml() .
"</form>\n</p>\n";

  $uteams->free();

  return($disp_str);
}

/*
 * Function to display the details of an individual userteam, allowing the
 * team to be en/disabled and showing the users in the team
 *  Get the details from the database and overlay any submitted form values.
 * @params:
 *  none
 * @returns:
 *  string containing HTML to be displayed.
 */
function userteams_form()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  $tname_text = "User team name";
  if($_GET['id'])
  {
    $tid = $_GET['id'];

    $team = $db->getRow($query_ary['userteam_details'], array($tid));

    if(!$team)
    {
      return("ERROR:Requested user team not found.<br />");
    }

    $disp_str = "Individual user team details.<br />\n";

    $tname_text .= "(cannot change)";

    $tname   = ($team['name']    ? $team['name']    : $_POST['teamname']);
    $created = ($team['created'] ? $team['created'] : '2006-08-10 15:00:00');
    $active  = ($team['active']  ? $team['active']  : $_POST['active']);
  }
  else /* Trap form submitted values for a new user */
  {
    $tid = -1;
    $uname   = $_POST['teamname'];
    $active  = ($_POST['active'] ? $_POST['active'] : '1');
  }

  /* Build the form elements that are required */

  /* An existing users username cannot be changed */
  $tname = new HTML_QuickForm_text('teamname', 'Username',
                                      array('value'    => $tname));
  if($_GET['id'])
  {
    $tname->freeze();
  }

  $tact = new HTML_QuickForm_checkbox('active', '1');
  $tact->setChecked($active);

  /* Add submit and reset buttons */
  $sub = new HTML_QuickForm_submit('updateuserteam', 'Save details',
                                   array('class' => 'button
'));
  $res = new HTML_QuickForm_reset('reset', 'Clear',
                                  array('class' => 'button'));
  $sgrp = new HTML_QuickForm_group('submitreset', '',
                  array($sub, $res), '', FALSE);

  /* Now construct the page */
  $disp_str .= "<p>
<form name=\"userteam\" method=\"post\" " .
      "action=\"" . $_SERVER[REQUEST_URI] . "\">\n";

  $disp_str .= "
<fieldset><legend>Userteam details</legend>
<dl>";

  $disp_str .= "<dt>" . $tname_text . "</dt>" .
               "<dd>" . $tname->toHtml() . "</dd>\n" .
               "<dt>Status</dt>" .
               "<dd>" . "Active " . $tact->toHtml() . "</dd>\n";

  /* Get the teams the user belongs to */
  $disp_str .= "<dt>Members</dt>" .
               "<dd>" . userteam_users_select($tid) . "</dd>\n";
  
  $disp_str .= $sgrp->toHtml();

  $disp_str .= "</dl>\n</form>\n</p>\n";

  return($disp_str);
}

/* Function to check the submitted data when updating or adding users.
 * The teamname must be supplied and be unique.
 * @params:
 *  none
 * @returns:
 *  null if valid, string containing error if not
 */
function userteams_validate()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  /* Verify that a username was given */
  if(!$_POST['teamname'])
  {
    return("ERROR:A unique username must be entered.");
  }

  /* If a new user team, name must be given */
  if(!$_GET['id'] && !$_POST['teamname'])
  {
    return("ERROR:New user team must have a name.");
  }

  /* Verify unique username for a new user request */
  if(!$_GET['id'] && $db->fetchOne($query_ary['userteams_unique_uname'],
                     array($_POST['teamname'])))
  {
    return("ERROR:Team name already exists. Please choose another.");
  }

  return(null);
}

/* Function to update the user team record.
 * Only the 'user list' snd active ubmissions are required.
 * @params:
 *  none
 * @returns:
 *  String detailing succes (or otherwise) of the operation.
 */
function userteams_update()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  /* Check form submission for required unique values */
  $valid=userteams_validate();
  if($valid) { return($valid); }

  if(!$_GET['id']) { return("ERROR:No user team to update.");}

  $tid = $_GET['id'];

  /* Check for valid user id */
  if(!$tid || !is_valid_userteam($tid))
  {
    return("ERROR:Cannot update non-existant user team.");
  }

  /* Attempt to update the existing userteam */
  $res = $db->query($query_ary['userteams_update'],
                    array(($_POST['active'] ? $_POST['active'] : '0'), $tid));

  if(!$res)
  {
    return("ERROR:There was an error updating the user.");
  }

  /* Save the users that are members of this user team */
  $valid_users = get_valid_users();

  if(count($valid_users))
  {
    $disp_str .= userteam_members($tid, $valid_users);
  }

  return($disp_str);
}

/* Function to add a new user record. Before setting the isadmin field, check
 * that the submitting user is an admin.
 * Some checks to perform:
 * Must be subscribed to at least one league, and a maximum of four.
 * @params:
 *  none
 * @returns:
 *  String detailing succes (or otherwise) of the operation.
 */
function userteams_add()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  /* Check form submission for required unique values */
  $valid=userteams_validate();
  if($valid) { return($valid); }

  $disp_str = "Add a new user.<br />";

  $newid = $db->nextId('user_leagues');

  /* Attempt to add the new user */
  $now = get_user_now($_SESSION['uid']);
  $res = $db->query($query_ary['userteams_insert'],
                    array($newid,
                          trim(stripslashes($_POST['teamname'])),
                          $now,
                          ($_POST['active']  ? $_POST['active']  : '0')));

  if(PEAR::isError($res))
  {
    return("ERROR:There was an error adding the new user team.");
  }

  /* Save the users that are members of this user team */
  $valid_users = get_valid_users();
  if(count($valid_users))
  {
    $disp_str .= userteam_members($newid, $valid_users);
  }

  return($disp_str);
}

/*
 * Function to remove a userteam
 */
function userteams_delete()
{
}
?>
