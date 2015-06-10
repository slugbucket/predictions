<?php
/*
 * Section for managing user accounts
 * show() - Main driver
 * navtabs() - Top navigation items
 * list_all_users() - show all users for editing
 * users_form() - show the user details in a form
 * users_validate() - validate submitted user details
 * users_update() - save submitted user details
 * users_add() - save details of a new user
 */

/*
 * Driver controlling the actions of the user management section
 * @params:
 *  none
 * @returns:
 *  string containing HTML to display on the page
 */
function show()
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "<p>Add new or update existing user accounts.</p>\n";

  /* Trap request for a new user form */
  if($_POST['newuser'] == "New user")
  {
    return($disp_str . users_form());
  }

  /* Trap request to add a new user */
  if($_POST['updateuser'] == "Save details" && !$_GET['id'])
  {
    $res .= users_add();
    if(preg_match("/^ERROR:/", $res))
    {
      $disp_str .= error_message($res);
      return($disp_str . users_form());
    }
  }

  /* Trap request to update an existing user */
  if($_POST['updateuser'] == "Save details" && $_GET['id'])
  {
    $res = users_update();
    if(preg_match("/^ERROR:/", $res))
    {
      $disp_str .= error_message($res);
      return($disp_str . users_form());
    }
    else
    {
      $disp_str .= $res;
    }
  }
  elseif($_GET['id'] && is_numeric($_GET['id']))
  {
    return($disp_str . users_form());
  }

  $disp_str .= list_all_users();

  /* Add a form with a button to apply the user scores */
  $disp_str .= update_user_scores_form($_SESSION['tabnum']);

  return($disp_str);
}

/*
 * Function to construct the navigation tabs for the section navigation
 * Limit the display to 12 tabs with next and prev links where appropriate
 * @param:
 *  none
 * @returns:
 *  string contains text to display on the page
 */
function navtabs()
{
#  $navstr = "<ul><li class=\"selected\">Users</li></ul>\n";

  $alpha_ary = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
                     'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
                     'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
                     'y', 'z');

  $navstr = "<ul>";
  $tcnt = 0;
  foreach($alpha_ary as $tab)
  {
    $class = "";

    $tab_text = $tab;

    if(!$tcnt && !$_SESSION['tabnum'])
    {
      $class = " class=\"selected\"";
      $_SESSION['tabnum'] = $tab;
    }
    else
    {
/*
 * FIXME: Test for tabunm is for numeric value
 * the tabs in this code are alpha
 */
      if($_SESSION['tabnum'] == $tab)
      {
        $class = " class=\"selected\"";
      }
      else /* Display a link to subscribed league */
      {
        $tab_text = "<a href=\"?action=users" .
                    "&tabnum=" . $tab .
                    "\" class=\"leaguetab\">" .
                    $tab_text . "</a>";
      }
    }
    $navstr .= "<li" . $class . ">" . $tab_text . "</li>\n";

    ++$tcnt;
  }
  $navstr .= "</ul>\n";

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
function list_all_users()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  $disp_str = "";

  $users = $db->query($query_ary['list_users_byname'],
                     array($_SESSION['tabnum'] . '%'));

  if($users->numRows())
  {

    $disp_str .= "
<!-- div id=\"leagues\" -->
<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">
<tr class=\"header\">
 <th>&nbsp;</th><th>Username</th><th>Full name</th><th>Member since</th><th>Last login</th><th>&nbsp;</th>
</tr>
";

    $endclass = "dark";

    while($users->fetchInto($user_row))
    {

      $class = ($user_row['active'] ? "" : "=\"inactive\"");

      $edit = "<a href=\"" . $_SERVER[REQUEST_URI] .
              "&id=" . $user_row['id'] . "\">edit</a>";

      $disp_str .=
"<tr$class\">" .
"<td class=\"pointless\">&nbsp;</td>" .
"<td class=\"$endclass\">" . $user_row['uname']      . "</td>" .
"<td>" . $user_row['fullname']   . "</td>" .
"<td>" . $user_row['joined']     . "</td>" .
"<td>" . $user_row['last_login'] . "</td>" .
"<td>" . $edit . "</td>" .
"</tr>\n";

      $endclass = ($endclass == "dark" ? "light" :"dark");
    }

    $disp_str .= "</table>\n</div><!-- leagues -->\n";

  }
  else
  {
    $disp_str .= "There are no users matching the request.<br />\n";
  }

  /* Add a new user button */
  $sub = new HTML_QuickForm_submit('newuser', 'New user',
                                   array('class' => 'button'));
  $disp_str .= "<p>
<form name=\"newuser\" method=\"post\" " .
      "action=\"" . $_SERVER[REQUEST_URI] . "\">\n" .
$sub->toHtml() .
"</form>\n</p>\n";

  $users->free();

  return($disp_str);
}

/*
 * Function to display the details of an individual user, allowing the
 * account to be en/disabled and the passwd to be reset as well as setting
 * the userteam membership. Get the details from the database and overlay
 * any submitted form values.
 * @params:
 *  none
 * @returns:
 *  string containing HTML to be displayed.
 */
function users_form()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  $uname_text = "Username";
  if($_GET['id'])
  {
    $uid = $_GET['id'];

    $user = $db->getRow($query_ary['user_details'], array($uid));

    if(!$user)
    {
      return("ERROR:Requested user not found.<br />");
    }

    $disp_str = "";

    $uname_text .= "(cannot change)";

    $uname   = ($user['uname']    ? $user['uname']    : $_POST['uname']);
    $fname   = ($user['fullname'] ? $user['fullname'] : $_POST['fullname']);
    $tzone   = ($user['timezone'] ? $user['timezone'] : $_POST['timezone']);
    $joined  = ($user['joined']   ? $user['joined']   : '2006-08-10 15:00:00');
    $active  = ($user['active']   ? $user['active']   : $_POST['active']);
    $isadmin = ($user['isadmin']  ? $user['isadmin']  : $_POST['isadmin']);
    $lastlog = ($user['lastlog']  ? $user['isadmin']  : '2006-08-10 15:00:00');
  }
  else /* Trap form submitted values for a new user */
  {
    $uid = -1;
    $uname   = $_POST['uname'];
    $fname   = $_POST['fullname'];
    $tzone   = $_POST['timezone'];
    $active  = $_POST['active'];
    $isadmin = $_POST['isadmin'];
  }

  /* Build the form elements that are required */

  /* An existing users username cannot be changed */
  $uname = new HTML_QuickForm_text('uname', 'Username',
                                      array('value'    => $uname));
  if($_GET['id'])
  {
    $uname->freeze();
  }

  $fname = new HTML_QuickForm_text('fullname', 'Full name',
                                      array('value' => $fname));

  $pword = new HTML_QuickForm_text('password', 'Password', "");

  $tzone = new HTML_QuickForm_text('timezone', 'Full name',
                                      array('value' => $tzone));

  $uact = new HTML_QuickForm_checkbox('active', '1');
  $uact->setChecked($active);

  $uadm = new HTML_QuickForm_checkbox('isadmin', '0');
  $uadm->setChecked($isadmin);

  /* Add submit and reset buttons */
  $sub = new HTML_QuickForm_submit('updateuser', 'Save details',
                                   array('class' => 'button
'));
  $res = new HTML_QuickForm_reset('reset', 'Clear',
                                  array('class' => 'button'));
  $sgrp = new HTML_QuickForm_group('submitreset', '',
                  array($sub, $res), '', FALSE);

  /* Now construct the page */
  $disp_str .= "<p>
<form name=\"league\" method=\"post\" " .
      "action=\"" . $_SERVER[REQUEST_URI] . "\">\n";

  $disp_str .= "
<fieldset><legend>User details</legend>
<dl>";

  $disp_str .= "<dt>" . $uname_text . "</dt>" .
               "<dd>" . $uname->toHtml() . "</dd>\n" .
               "<dt>Full name</dt>" .
               "<dd>" . $fname->toHtml() . "</dd>\n" .
               "<dt>Password (min. 6 chars, max. 16 chars)</dt>" .
               "<dd>" . $pword->toHtml() . "</dd>\n" .
               "<dt>Timezone</dt>" .
               "<dd>" . $tzone->toHtml() . "</dd>\n" .
               "<dt>Status</dt>" .
               "<dd>" . "Active " . $uact->toHtml() .
                        "Admin? " . $uadm->toHtml() . "</dd>\n";

  /* Get the teams the user belongs to */
  $disp_str .= "<dt>User teams</dt>" .
               "<dd>" . user_team_select($uid) . "</dd>\n";
  
  /* Get the subscribed leagues */
  $disp_str .= "<dt>Subscribed leagues</dt>" .
               "<dd>" . user_subscribed_select($uid) . "</dd>\n";

  $disp_str .= $sgrp->toHtml();

  $disp_str .= "</dl>\n</form>\n</p>\n";

  return($disp_str);
}

/* Function to check the submitted data when updating or adding users.
 * The username must be supplied and be unique.
 * Must be a member of a userteam.
 * If a new user then a password must be set.
 * @params:
 *  none
 * @returns:
 *  null if valid, string containing error if not
 */
function users_validate()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  /* Verify that a username was given */
  if(!$_POST['uname'])
  {
    return("ERROR:A unique username must be entered.");
  }

  /* If a new user, password must be given ... */
  if(!$_GET['id'] && !$_POST['password'])
  {
    return("ERROR:New user must have a password.");
  }

  /* ... and it must have at least 6 charcaters */
  if(!$_GET['id'] && strlen($_POST['password']) < 6)
  {
    return("ERROR:Password must have at least 6 characters.");
  }

  /* Verify unique username for a new user request */
  if(!$_GET['id'] && $db->fetchOne($query_ary['users_unique_uname'],
                 array($_POST['uname'])))
  {
    return("ERROR:Username already exists. Please choose another.");
  }

  /* Check for league subscription */
  if(!count(array_slice($_POST['leagues'], 0, 4)))
  {
    return("ERROR:User must be subscribed to at least one league.");
  }

  /* Check for userteam membership */
  if(!count(array_slice($_POST['userteams'], 0, 4)))
  {
    return("ERROR:User must belong to at least one user team.");
  }

  return(null);
}

/* Function to update the user record. Before setting the isadmin field, check
 * that the submitting user is an admin.
 * Some checks to perform:
 * Must be subscribed to at least one league, and a maximum of four.
 * @params:
 *  none
 * @returns:
 *  String detailing succes (or otherwise) of the operation.
 */
function users_update()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  /* Check form submission for required unique values */
  $valid=users_validate();
  if($valid) { return($valid); }

  if(!$_GET['id']) { return("ERROR:No user to update.");}

  $uid = $_GET['id'];

  /* Check for valid user id */
  if(!$uid || !is_valid_user($uid))
  {
    return("ERROR:Cannot update non-existant user.");
  }

  /* Get passwd from database if not submitted by form */
  if($_POST['password'])
  {
    $passwd = md5(substr($_POST['password'], 0, 16));
  } else {
    $passwd = $db->fetchOne($query_ary['users_password'], $_SESSION['uid']);
  }

  /* Attempt to update the existing user */
  $res = $db->query($query_ary['users_update'],
                    array($_POST['fullname'],
                          ($_POST['active']  ? $_POST['active']  : 'Europe/London'),
                          $passwd,
                          ($_POST['active']  ? $_POST['active']  : '0'),
                          ($_POST['isadmin'] ? $_POST['isadmin'] : '0'),
                          $uid));

  if(PEAR::isError($res))
  {
    return("ERROR:There was an error updating the user.");
  }

  /* Save the leagues that the user should subscribe to */
  $valid_leagues = get_valid_leagues();
  if(count($valid_leagues))
  {
    $disp_str .= subscribe_user_to_leagues($uid, $valid_leagues);
  }
  else
  {
    return("ERROR:No leagues specified for subscription.");
  }

  /* Save the userteams that the user is a member of */
  $valid_teams = get_valid_user_teams();
  if(count($valid_teams))
  {
    $disp_str .= subscribe_user_to_teams($uid, $valid_teams);
  }
  else
  {
    return("ERROR:No user teams specified for the user.");
  }

  return($disp_str);
}

/* Function to add a new user record. Before setting the isadmin field, check
 * that the submitting user is an admin.
 * Create an empty entry in the scores table to ensure a display in the
 * group table page.
 * Some checks to perform:
 * Must be subscribed to at least one league, and a maximum of four.
 * @params:
 *  none
 * @returns:
 *  String detailing succes (or otherwise) of the operation.
 */
function users_add()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  /* Check form submission for required unique values */
  $valid=users_validate();
  if($valid) { return($valid); }

  $disp_str = "Add a new user.<br />";

  $newid = $db->nextId('predict_users');

  /* Attempt to add the new user */
  $res = $db->query($query_ary['users_add'],
                    array($newid,
                          $_POST['uname'],
                          $_POST['fullname'],
                          $_POST['timezone'],
                          substr($_POST['password'], 0, 16),
                          get_user_now($_SESSION['uid']),
                          ($_POST['active']  ? $_POST['active']  : '0'),
                          ($_POST['isadmin'] ? $_POST['isadmin'] : '0')));

  if(PEAR::isError($res))
  {
    return("ERROR:There was an error adding the new user.");
  }

  /* Add a blank entry into the predict_user_scores table */
  $db->query($query_ary['user_score_insert'],
             array($newid, '0'. '0', '0', '0'));

  /* Save the leagues that the user should subscribe to */
  $valid_leagues = get_valid_leagues();
  if(count($valid_leagues))
  {
    $disp_str .= subscribe_user_to_leagues($newid, $valid_leagues);
  }
  else
  {
    return("ERROR:No leagues specified for subscription.");
  }

  /* Save the userteams that the user is a member of */
  $valid_teams = get_valid_user_teams();
  if(count($valid_teams))
  {
    $disp_str .= subscribe_user_to_teams($newid, $valid_teams);
  }
  else
  {
    return("ERROR:No user teams specified for the user.");
  }

  return($disp_str);
}
?>
