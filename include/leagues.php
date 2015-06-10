<?php
/*
 * Section for managing predictable leagues
 * Options exist to promote and relegate teams to and from the leagues
 * identified in the database table.
 *
 * Functions:
 * show() - main driver
 * navtab() - Navigation tabs
 * show_league_teams() - show the teams in the league
 * leagues_insert() - add a new league.
 * leagues_update() - update details of a league
 * add_new_league_form() - display a form for a new league
 * add_new_team_form() - display a form for a new team
 * league_members_save() - store the teams that make up a league
 * save_new_team() - add a new team to the database
 * update_deductions() - save updates to points deductions
 * update_playoff_places() - identify where playoff teams are
 * leagues_promote_relegate() - promote and relegate teams
 */

/*
 * Driver controlling the actions of the predictable leagues section
 * @params:
 *  none
 * @returns:
 *  string containing HTML to display on the page
 */
function show()
{
  $disp_str = "";

  /* Check for a new team request */
  if($_POST['addteam'] == "Add team")
  {
    return(add_new_team_form());
  }

  /* Add a new league */
  if($_POST['newleague'] == "New league")
  {
    return(add_new_league_form());
  }

  if($_POST['saveteam'] == "Add new team" &&
     $_POST['team_name'])
  {
    $disp_str .= save_new_team();
  }

  /* Save the general league details */
  if($_POST['saveleague'] == "Save league")
  {
    $res = leagues_update();
    if(substr($res, 0, 6) == "ERROR:") {
      $disp_str .= "<span class=\"errmsg\">" .
                   substr($res, 6) .
                   "</span><br />\n";
    } else {
      $disp_str .= $res;
    }
  }

  /* Add a new league details */
  if($_POST['saveleague'] == "Save new league")
  {
    $res = leagues_insert();
    if(substr($res, 0, 6) == "ERROR:") {
      $disp_str .= "<span class=\"errmsg\">" .
                   substr($res, 6) .
                   "</span><br />\n";
      return($disp_str . add_new_league_form());
    } else {
      $disp_str .= $res;
    }
  }

  /* process the promotion/relegation form */
  if($_POST['updown'] == 'Save changes')
  {
    $res = leagues_promote_relegate();
    if(substr($res, 0, 6) == "ERROR:") {
      $disp_str .= "<span class=\"errmsg\">" .
                   substr($res, 6) .
                   "</span><br />\n";
    } else {
      $disp_str .= $res;
    }
  }

  /* Process the playoff places */
  if($_POST['saveplayoff'] == "Save playoff settings")
  {
    $res = update_playoff_places();
    if(substr($res, 0, 6) == "ERROR:") {
      $disp_str .= "<span class=\"errmsg\">" .
                   substr($res, 6) .
                   "</span><br />\n";
    } else {
      $disp_str .= $res;
    }
  }

  /* Process the team points deductions */
  if($_POST['savededuct'] == "Save deductions")
  {
    $res = update_deductions();
    if(substr($res, 0, 6) == "ERROR:") {
      $disp_str .= "<span class=\"errmsg\">" .
                   substr($res, 6) .
                   "</span><br />\n";
    } else {
      $disp_str .= $res;
    }
  }

  /* Update the team membership details */
  if($_POST['saveteams'] == 'Save teams')
  {
    $res = league_members_save();
    if(substr($res, 0, 6) == "ERROR:") {
      $disp_str .= "<span class=\"errmsg\">" .
                   substr($res, 6) .
                   "</span><br />\n";
    } else {
      $disp_str .= $res;
    }
  }

  $disp_str .= show_league_teams();

  return($disp_str);
}

/*
 * Function to construct the navigation tabs for the section navigation
 * This needs to limit the display to three items with first, prev, next
 * and last links to keep the display tidy.
 * @params:
 *  none
 * @returns:
 *  string contains text to display on the page
 */
function navtabs()
{
#  $navstr = "<ul><li class=\"selected\">Leagues</li></ul>\n";
  /* Get the league subscriptions for the user
echo "Running query:<br />" . $query_ary['league_list'] . " with uid =1.<br />
";
   */
  $lges = mdb2_query('league_list');

  /* Trap common error conditions */
  if(!$lges) { return("Subscription navigation tab error.<br />\n"); }
#  if(!$lges->numRows()) { return("<ul></ul>\n"); }
  
  $navstr = "<ul>";
  $tcnt = 0;
  while($tab = $lges->fetchRow())
  {
    $class = "";

    $tab_text = $tab['name']; /* What to display in the tab */
    /* Default to first tab if nothing specified in URL (GET) */
    if(!$tcnt && !$_SESSION['tabnum']) {
      $class = " class=\"selected\"";
      $_SESSION['tabnum'] = $tab['id'];
    }
    else
    {
      if($_SESSION['tabnum'] == $tab['id'])
      {
        $class = " class=\"selected\"";
      }
      else /* Display a link to subscribed league */
      {
        $tab_text = "<a href=\"?action=leagues" .
                    "&tabnum=" . $tab['id'] .
                    "\" class=\"leaguetab\">" .
                    $tab_text . "</a>";
      }
    }
    $navstr .= "<li" . $class . ">" . $tab_text . "</li>\n";

    ++$tcnt;
  }
  $lges->free();

  $navstr .= "</ul>\n";

  return($navstr);
}

/*
 * Display the league details with promotion and relegation links as
 * appropriate.
 * @params:
 *  none. (all variables taken from $_SESSION and $_POST
 * @returns:
 * string containing HTML to display on the page
 */
function show_league_teams()
{
  $disp_str = "";

  $promoted_to  = mdb2_fetchRow('league_promoted',
                                array($_SESSION['tabnum']));
  $relegated_to = mdb2_fetchRow('league_relegated',
                                array($_SESSION['tabnum']));
  $match_type   = mdb2_fetchOne('get_default_match_type',
                                $_SESSION['tabnum']);
  $lge_tmzn     = mdb2_fetchOne('get_league_timezone',
                                $_SESSION['tabnum']);

  if(!$promoted_to && !$relegated_to)
  {
    return("ERROR:No league found matching the requested id, " . $_SESSION['tabnum'] . ".<br />\n");
  }

  $lge_name  = $promoted_to['league_name'];
  $active    = $promoted_to['active'];
  $num_up    = $promoted_to['num_promoted'];
  $goto_up   = $promoted_to['up_to'];
  $up_id     = $promoted_to['leagueid'];
  $num_down  = $relegated_to['num_relegated'];
  $goto_down = $relegated_to['down_to'];
  $down_id   = $relegated_to['leagueid'];

  /* Get whether the league can have its tournament status changed (has not
   * any fixtures submitted.
   */
  $tbox = 1;
  if(mdb2_fetchOne('league_has_fixtures', array($_SESSION['tabnum'])))
  {
    $tbox = 0;
    $tval = 0;
  } else { /* Get the current tournament status flag */
    $tval = mdb2_fetchOne('league_is_tournament',
                        array($_SESSION['tabnum']));
  }

  $disp_str .=  "<p>
Use this form to change the name of a league and set the number of teams
that are promoted and relegated and how many teams make the change.
</p>\n";
  /* Display a form to manage where teams are promoted/relegated to and how
   * many teams change at the end of the season
   */
  $lge_details = new HTML_QuickForm('hostform', 'post', $_SERVER['URI']);
  $disp_str .= "<p>
<form name=\"league\" method=\"post\" action=\"" . $_SERVER['URI'] . "\">\n";

  $lge_nbox = new HTML_QuickForm_text('league_name', 'League name',
                                      array('value' => $lge_name));

  $lge_tmzn = new HTML_QuickForm_text('timezone', 'Timezone',
                                      array('value' => $lge_tmzn));

  $lge_act = new HTML_QuickForm_checkbox('active', '1');
  $lge_act->setChecked($active);
  
  $num_prom = new HTML_QuickForm_select('promoted', 'Number promoted',
                                        array('0', '1', '2', '3', '4', '5'));
  $num_prom->setSelected($num_up);

  $num_reld = new HTML_QuickForm_select('relegated', 'Number relegated',
                                        array('0', '1', '2', '3', '4', '5'));
  $num_reld->setSelected($num_down);

  $league_rows = mdb2_fetchAssoc('league_select_all');

  $league_prm = new HTML_QuickForm_select('lge_up', 'lge_up', $league_rows);
  $league_prm->addOption('None', '0');
  $league_prm->setSelected($up_id);

  $league_rel = new HTML_QuickForm_select('lge_dn', 'lge_dn', $league_rows);
  $league_rel->addOption('None', '0');
  $league_rel->setSelected($down_id);

  /* Conditionally include the tournament checkbox */
  if($tbox)
  {
    $lge_tbox = new HTML_QuickForm_checkbox('tournament', $tval, '');
    $lge_tbox->setChecked($tval);
  }

  /* Add the default match type */
  $mt_sel = match_type_select("defaultmatchtype", $match_type, '');

  /* Add some buttons at the bottom */
  $league_sub = new HTML_QuickForm_submit('saveleague', 'Save league',
                    array("class" => "button"));
  $league_res = new HTML_QuickForm_reset('reset', 'Undo',
                    array("class" => "button"));
  $league_new = new HTML_QuickForm_submit('newleague', 'New league',
                    array("class" => "button"));

  $disp_str .= "<fieldset><legend>League details</legend>" .
               "<dl>\n" .
               "<dt><label for=\"league_name\">League name</label></dt>" .
               "<dd>" . $lge_nbox->toHtml() .
               "Active" . $lge_act->toHtml() .
               ($lge_tbox ? "Tournament?" . $lge_tbox->toHtml() : "") .
               "</dd>\n" .
               "<dt><label for=\"timezone\">Timezone</label></dt>" .
               "<dd>" . $lge_tmzn->toHtml() . "</dd>\n";

  /* Only display if league is not a tournament */
  if(!$tval)
  {
    $disp_str .=
               "<dt><label for=\"promoted\">Promote</label></dt>" .
               "<dd>" . $num_prom->toHtml() . " teams to " .
               $league_prm->toHtml() . "</dd>\n" .

               "<dt><label for=\"relegated\">Relegate</label></dt>" .
               "<dd>" . $num_reld->toHtml() . " teams to " .
               $league_rel->toHtml() . "</dd>\n";
  }

  /* Add the match type selector */
  $disp_str .=
               "<dt><label for=\"defaultmatchtype\">Default match type</label></dt>" .
               "<dd>" . $mt_sel . "</dd>";

  $disp_str .= "</dl>\n";

  $disp_str .= $league_sub->toHtml() . $league_res->toHtml() . "<br />" .
               $league_new->toHtml() .
               "</fieldset>" .
               "</form>\n" .
               "</p>\n";

  /* Show a form with two select boxes with available and selected teams to
   * make up the league membership
   */
  $notinlge = mdb2_fetchOrdered('teams_not_in_league', array($_SESSION['tabnum']));
  $notinlge_select = new HTML_QuickForm_select("available",
                                       "Available teams",
                                       $notinlge,
                                       array('multiple' => 'yes',
                                             'size'     => '4',
                                             'id'       => 'available')
                                      );
  $inlge = mdb2_fetchOrdered('teams_in_league', array($_SESSION['tabnum']));
  $inlge_select = new HTML_QuickForm_select("teamsinlge",
                                       "Selected teams",
                                       $inlge,
                                       array('multiple' => 'yes',
                                             'size'     => '4',
                                             'id'       => 'teamsinlge')
                                      );

  /* Add some buttons at the bottom */
  $memb_sub = new HTML_QuickForm_submit('saveteams', 'Save teams',
                  array("class" => "button"));
  $memb_rst = new HTML_QuickForm_reset('reset', 'Undo',
                  array("class" => "button"));
  $memb_add = new HTML_QuickForm_submit('addteam', 'Add team',
                  array("class" => "button"));

  /* Format the form for display */
  $disp_str .= "<p>
<form name=\"leaguemebers\" method=\"post\" " .
      "action=\"" . $_SERVER['URI'] . "\" onSubmit=\"select_all_teams();\">\n";
  $disp_str .= "<fieldset><legend>League membership</legend>" .
               "Add or remove teams from a league or add a new team to the " .
               "league.<br />" .
               "<dl>";

  $disp_str .= "<div id=\"addremove\" style=\"float: left;\">\n" .
               "<dt>" .
               "<label for=\"available[]\">All teams</label>" .
               "</dt>" .
               "<dd>" . $notinlge_select->toHtml() . "</dd>" .
               "</div>\n" .

               "<div id=\"addremlge\">" .
               "<input type=\"button\" name=\"add\" value=\"add\" onclick=\"javascript:add_to_league()\" />" .
               "<br />\n" .
               "<input type=\"button\" name=\"remove\" value=\"remove\" onclick=\"javascript:remove_from_league()\" />" .
               "</div>\n" .

               "<dt>" .
               "<label for=\"teamsinlge\">$lge_name</label>" .
               "</dt>" .
               "<dd>" .  $inlge_select->toHtml() . "</dd>\n";

  $disp_str .= "</dl>\n" .
               $memb_sub->toHtml() . $memb_rst->toHtml() . "<br />" .
               $memb_add->toHtml() . "\n" .
               "</div>\n" .
               "</fieldset>" .
               "</form>\n" .
               "<script language=\"JavaScript\">set_button_state();</script>\n" .
               "</p>\n";

  /* Build the form elements for managing playoff places */
  $playoff_qry = mdb2_fetchRow('get_playoff_range', array($_SESSION['tabnum']));
  $plstart = $playoff_qry['playoff_start'];
  $plcount = $playoff_qry['playoff_cnt'];

  $plstart_select = new HTML_QuickForm_select("playoffstart",
                                       "Playoff start position",
                                       array(0, 1, 2, 3, 4, 5),
                                       array('size'     => '1')
                                      );
  $plstart_select->setSelected($plstart);

  $plcount_select = new HTML_QuickForm_select("playoffcount",
                                       "Number of playoff teams",
                                       array(0, 1, 2, 3, 4, 5),
                                       array('size'     => '1')
                                      );
  $plcount_select->setSelected($plcount);

  /* Add some buttons at the bottom */
  $plff_sub = new HTML_QuickForm_submit('saveplayoff', 'Save playoff settings',
                  array("class" => "button"));
  $plff_rst = new HTML_QuickForm_reset('reset', 'Undo',
                  array("class" => "button"));

  /* Show the form for managing teams in the playoff places for a league */
  $disp_str .= "<p>
<form name=\"playoffplaces\" method=\"post\" " .
      "action=\"" . $_SERVER['URI'] . "\" onSubmit=\"select_all_teams();\">\n";
  $disp_str .= "<fieldset><legend>Playoff places</legend>" .
               "Select where the playoff places start in a league and how " .
               "many teams are in the playoff places." .
               "<br />" .
               "<dl>" .
               "<dt>" .
               "<label for=\"playoffstart\">Position of first playoff team</label>" .
               "</dt>" .
               "<dd>" .  $plstart_select->toHtml() . "</dd>\n" .

               "<label for=\"playoffcount\">Number of playoff teams</label>" .
               "</dt>" .
               "<dd>" .  $plcount_select->toHtml() . "</dd>\n" .

               "</dl>\n" .
               $plff_sub->toHtml() . $memb_rst->toHtml() . "<br />\n";

  $disp_str .= "</fieldset>" .
               "</form>\n" .
               "</p>\n";
  /* End of playoff form */

  /* Points deduction form */
  $deduct_qry = mdb2_fetchOrdered('get_all_points_deducted', array($_SESSION['tabnum']));

  /* Format a sub-section containing the team name, the points deducted and
   * a delete deduction checkbox
   */
  $dlist = "";
  foreach($deduct_qry as $tid => $deduction)
  {
    $dbox_name = 'clrdeduct' . $tid;
    $dbox_cbox = new HTML_QuickForm_checkbox($dbox_name, '', 'Remove');
    $dbox_cbox->setChecked($_POST[$dbox_name]);

    if($deduction)
    {
      $dlist .= "<dd>" . mdb2_fetchOne('team_name', array($tid)) . " " .
                         $deduction . " points " .
                         $dbox_cbox->toHtml() .
                "</dd>\n";
    }
  }

  $deduct_text  = new HTML_QuickForm_text('deductpoints', 'Deduct',
                                      array('value' => $_POST['deductpoints'],
                                            'size'  => '3'));

  $nodeduct_qry = mdb2_fetchAssoc($query_ary['no_points_deducted'], array($_SESSION['tabnum']),
                                DB_FETCHMODE_ASSOC
                               );

  $deduct_select = new HTML_QuickForm_select("teamsnodeduct",
                                               "Selected teams",
                                               $nodeduct_qry
                                              );

  /* Add some buttons at the bottom */
  $deduct_sub = new HTML_QuickForm_submit('savededuct', 'Save deductions',
                  array("class" => "button"));
  $deduct_rst = new HTML_QuickForm_reset('reset', 'Undo',
                  array("class" => "button"));

  /* Show the form for managing points deductions for teams in a league */
  $disp_str .= "<p>
<form name=\"deductions\" method=\"post\" " .
      "action=\"" . $_SERVER['URI'] . "\">\n";

  $disp_str .= "<fieldset><legend>Points deductions</legend>" .
               "Deduct points from a team in the league" .
               "<br />" .
               "<dl>";

  /* Only show deducted section if there are teams with deducted points */
  if($dlist)
  {
     $disp_str .= "<dt>" .
                  "<label for=\"team_name\">Teams with points deducted</label>" .
                  $dlist .
                  "</dt>";
  }

  $disp_str .= "<label for=\"deductpoints\">Deduct</label>" .
               "<dt>" .
               "<dd>" .  $deduct_text->toHTML() . " points from " .
                         $deduct_select->toHtml() .
               "</dd>\n" .

               "</dl>\n" .
               $deduct_sub->toHtml() . $deduct_rst->toHtml() . "<br />\n";

  $disp_str .= "</fieldset>" .
               "</form>\n" .
               "</p>\n";
  /* End of points deduction form */

  /* Don't show promote/relegate for a tournament */
  if($tval) { return($disp_str); }

  /* Show a form for the teams in the league and select which of those will be
   * relegated and promoted as radio buttons
   */
  /* Get the teams in the league */
  $disp_str .= "<hr />\n<p>
Use this form to change the promotion and relegation status of teams in
a league.
</p>
<!-- div id=\"leagues\" -->
<form name=\"deductions\" method=\"post\" " .
      "action=\"" . $_SERVER['URI'] . "\">\n
<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">
<colgroup span=\"1\" />
<colgroup span=\"1\" width=\"60%\" />
<colgroup span=\"1\" width=\"20%\" />
<colgroup span=\"1\" width=\"20%\" />
<tr>
 <th>&nbsp;</th><th>Team</th><th align=\"center\">Promote</th> <th align=\"center\">Relegate</th>
</tr>";

  $toggle = 0; /* flag to choose the row colour */

  $lge_teams = mdb2_fetchOrdered('league_team_list', array($_SESSION['tabnum']));
  $endclass = "dark";

  foreach($lge_teams as $team_id => $team_name)
  {

    $b1 = new HTML_QuickForm_radio('prorel'.$team_id, '', '', 'up',
                                   array('class' => "radio"));
    $b2 = new HTML_QuickForm_radio('prorel'.$team_id, '', '', 'down',
                                   array('class' => "radio"));

    $disp_str .= "<tr>" .
                 "<td class=\"pointless\">&nbsp;</td>" .
                 "<td class=\"$endclass\">" . $team_name . "</td>" .
                 "<td class=\"radio\">" .
                   ($num_up   ? $b1->toHtml() : "&nbsp;") . "</td>" .
                 "<td class=\"radio\">" .
                   ($num_down ? $b2->toHtml() : "&nbsp;") . "</td>" .
                 "</tr>\n";

    $endclass = ($endclass == "dark" ? "light" :"dark");
  }

  $disp_str .= "</table>\n<!-- /div --><!-- leagues -->";

  /* Add some buttons at the bottom */
  $league_sub = new HTML_QuickForm_submit('updown', 'Save changes',
                    array("class" => "button"));
  $league_res = new HTML_QuickForm_reset('reset', 'Undo',
                    array("class" => "button"));

  $disp_str .= $league_sub->toHtml() . $league_res->toHtml() . "<br />";

  return($disp_str);
}

/*
 * Function to insert the details of a new league.
 * @params:
 *  none
 * @returns:
 *  string containing the success (or otherwise) of the operation
 */
function leagues_insert()
{
  $league_name = $_POST['league_name'];
  if(!$league_name)
  {
    return("ERROR:The league must be given a name.");
  }

  /* Check whether the submitted name already exists */
  $qry = str_replace("LEAGUE_NAME", $league_name,
                     $query_ary['league_name_exist']);
  if(mdb2_fetchOne($qry))
  {
    return("ERROR:League '$league_name' already exists. " .
           "Please choose another name.");
  }

  /* Check the timezone for a valid value */
  $tz = $_POST['timezone'];

  $lid = mdb2_fetchOne('leagues_nextid');
  $trn = $_POST['tournament'] ? '1' : '0';
  $res = mdb2_query('leagues_insert', array($lid, $league_name, $tz,
                                      '0', '0', '0', '0', '1', $trn));

  if(PEAR::isError($res))
  {
    return("ERROR:Failed to save details for " . $_POST['league_name'] . ".");
  } else
  {
    return("Saved details for " . $_POST['league_name'] . ".");
  }
}

/*
 * Function to check the league details form details and save any. Before
 * storing the tournament details, check that a change in status can be
 * supported; if it can't use the existing status value.
 * valid changes.
 * @params:
 *  none
 * @returns:
 *  string containing the success (or otherwise) of the operation
 */
function leagues_update()
{
  /* Only change the tournament status if no fixtures are present */
  if(mdb2_fetchOne('league_has_fixtures', array($_SESSION['tabnum'])))
  {
    $tstat = mdb2_fetchOne('league_is_tournament',
                         array($_SESSION['tabnum']));
  } else {
    $tstat = $_POST['tournament'] ? '1' : '0';
  }

  /* Check for a request to save a new league and use the INSERT query */
  $res = mdb2_query($query_ary['save_league_details'],
                    array($_POST['league_name'],
                          $_POST['timezone'],
                          $_POST['promoted'],
                          $_POST['relegated'],
                          $_POST['lge_up'],
                          $_POST['lge_dn'],
                          $_POST['defaultmatchtype'],
                          $_POST['active'],
                          $tstat,
                          $_SESSION['tabnum']));

  if(PEAR::isError($res))
  {
    return("ERROR:Failed to save details for " . $_POST['league_name'] . ".");
  } else
  {
    return("Saved details for " . $_POST['league_name'] . ".");
  }
}

/*
 * Function to prompt for a new league to be added to a league
 * @params:
 *  none, but _SESSION variables are used
 * @returns:
 *  string containing HTML form
 */
function add_new_league_form()
{
  $disp_str .= "<p>
Use this form to add a new league. Check the 'Use for tournament' box if
fixtures will submitted for a knockut or league tournament.
<form name=\"newteam\" method=\"post\" action=\"" . $_SERVER['URI'] . "\">\n
<fieldset><legend>Add new league</legend>
<dl>";

  $new_league = new HTML_QuickForm_text('league_name', 'League name',
                                      array('value' => $_POST['league_name']));

  $tz = ($_POST['timezone'] ? $_POST['timezone'] : "Europe/London");
  $tzbox   = new HTML_QuickForm_text('timezone', 'Timezone',
                                      array('value' => $tz));

  /* Select whether to use the league for a tournament */
  $trnbox = new HTML_QuickForm_checkbox('tournament', 'yes');

  /* Add the default match type */
  $mt_sel = match_type_select("defaultmatchtype",
                              $_POST['defaultmatchtype'], '');

  /* Add some buttons at the bottom */
  $league_sub = new HTML_QuickForm_submit('saveleague', 'Save new league',
                    array("class" => "button"));
  $league_res = new HTML_QuickForm_reset('reset', 'Clear',
                    array("class" => "button"));

  $disp_str .= "<dt>Team name</dt>" .
               "<dd>" . $new_league->toHtml() . "</dd>" .
               "<dt>Timzone</dt>"  .
               "<dd>" . $tzbox->toHtml()      . "</dd>" .
               "<dt>Use for tournament</dt>"  .
               "<dd>" . $trnbox->toHtml()     . "</dd>" .
               "<dt>Default match type</dt>"  . "</dt>" .
               "<dd>" . $mt_sel               . "</dd>" .
               $league_sub->toHtml() .
               $league_res->toHtml();

  $disp_str .= "</dl>\n" .
               "</fieldset>\n" .
               "</form>\n";

  return($disp_str);
}

/*
 * Function to prompt for a new team to be added to a league
 * @params:
 *  none, but _SESSION variables are used
 * @returns:
 *  string containing HTML form
 */
function add_new_team_form()
{
  $disp_str = "Prompt for the name a team to add to the league.";

  if(!$_SESSION['tabnum'])
  {
    return("ERROR:No currently selected league to add team to.");
  }

  $disp_str .= "<p>
<form name=\"newteam\" method=\"post\" action=\"" . $_SERVER['URI'] . "\">\n
<fieldset><legend>Add new team</legend>
<dl>";

  $new_team = new HTML_QuickForm_text('team_name', 'Team name',
                                      array('value' => $_POST['team_name']));

  $known_as = new HTML_QuickForm_text('known_name', 'Known name',
                                      array('value' => $_POST['known_name']));

  /* Add some buttons at the bottom */
  $team_sub = new HTML_QuickForm_submit('saveteam', 'Add new team',
                    array("class" => "button"));
  $team_res = new HTML_QuickForm_reset('reset', 'Clear',
                    array("class" => "button"));

  $disp_str .= "<dt>Team name</dt>" .
               "<dd>" . $new_team->toHtml() . "</dd>" .
               "<dt>Known name</dt>" .
               "<dd>" . $known_as->toHtml() . "</dd>" .
               $team_sub->toHtml() .
               $team_res->toHtml();

  $disp_str .= "</dl>\n" .
               "</fieldset>\n" .
               "</form>\n";

  return($disp_str);
}

/* Function to store the teams that make up the league.
 * remove the existing entries before inserting all the new ones.
 * @params:
 *  none ($_POST is accessed for submitted teams, $_SESSION for league id)
 * @returns:
 *  string containing HTML showing the success (or otherwise) of the operation
 */
function league_members_save()
{
  $disp_str = "Saving league members.<br />\n";

  /* Remove any existing entries before adding the submitted teams */
  mdb2_query('remove_all_teams_from_league', array($_SESSION['tabnum']));
  if($_POST['teamsinlge'])
  {
    foreach($_POST['teamsinlge'] as $subid => $teamid)
    {
      if(mdb2_fetchOne('is_valid_team', array($teamid)))
      {
        mdb2_query('add_team_to_league',
                   array($_SESSION['tabnum'], $teamid));
        $disp_str .= "Adding " . mdb2_fetchOne('team_name', array($teamid)) .
                     " to league " .
                     mdb2_fetchOne('league_name', array($_SESSION['tabnum'])) .
                     ".<br />\n";
      } else { /* Ignore bad stuff */
        $disp_str .= "Invalid team id, $teamid, ignored.<br />\n";
      }
    }
  }

  return($disp_str);
}

/* Function to add a new team to a league
 * $query_ary['insert_new_team'], $query_ary['add_team_to_league']
 * @params:
 *  none
 * @returns:
 *  string containing HTML showing the success (or otherwise) of the operation
 */
function save_new_team()
{
  $disp_str = "Adding new team to league.<br />\n";

  $nt_id = mdb2_fetchOne('predict_teams_nextid');

  $res = mdb2_query('insert_new_team',
                    array($nt_id, $_POST['team_name'], $_POST['known_name']));
  if(PEAR::isError($res)) {
    return("ERROR:Team insert failed for " . $_POST['team_name'] . 
           ": " . $res->getMessage() . ".");
  }

  $res = mdb2_query('add_team_to_league',
                    array($_SESSION['tabnum'], $nt_id));

  if(PEAR::isError($res)) {
    return("ERROR:Failed to add " . $_POST['team_name']  . " to " .
           mdb2_fetchOne('league_name', array($_SESSION['tabnum'])) .
           ": " . $res->getMessage() . ".\n");
  } else {
    return("Added " . $_POST['team_name'] . " to " .
           mdb2_fetchOne('league_name', array($_SESSION['tabnum'])) . ".\n");
  }
}

/* Function to save the points deduction for a team or remove an existing
 * deduction.
 * @params:
 *  none,  but $SESSION and $_POST are referenced
 * @returns:
 *  String reporting the success or otherwise of the operation
 */
function update_deductions()
{
  $disp_str = "Saving points deductions for teams in the league.<br />\n";

  /* Check each POSTed var that matches 'clrdeductxx' and remove the entry
   * from the predict_team_deduct table
   */
  foreach($_POST as $var => $value)
  {
    if(substr($var, 0, 9) == "clrdeduct") {
      $tid = substr($var, 9);

      mdb2_query('delete_deduction',
                 array($tid, $_SESSION['tabnum']));

      $tname = mdb2_fetchOne('team_name', array($tid));

      $disp_str .= "Remove points deduction for $tname.<br />\n";
      $disp_str .= update_league_table($_SESSION['tabnum']);
    }
  }

  /* Process a request to deduct points from a team */
  $tid = $_POST['teamsnodeduct'];
  $pts = abs($_POST['deductpoints']);

  /* If team id and points submitted, check that the team is in the league
   * before applying the deduction
   */
  if($tid && $pts)
  {
    $tname = mdb2_fetchOne('is_team_in_league',
                         array($_SESSION['tabnum'], $tid));

    /* Delete any existing dsduction before adding the record */
    if($tname)
    {
      mdb2_query('delete_deduction',
                 array($tid, $_SESSION['tabnum']));
      mdb2_query('insert_deduction',
                 array($tid, $_SESSION['tabnum'], 0-$pts));
      $disp_str .= "Deduct $pts points from $tname.<br />\n";
      $disp_str .= update_league_table($_SESSION['tabnum']);
    } else {
      $disp_str .= "ERROR:Cannot deduct points for invalid team id, $tid.";
    }
  }

  return($disp_str);
}

/* Function to save the location of the playoff places for a league
 * @params:
 *  none,  but $SESSION and $_POST are referenced
 * @returns:
 *  String reporting the success or otherwise of the operation
 */
function update_playoff_places()
{
  $disp_str = "Saving location of playoff teams in the league.<br />\n";

  $pls = $_POST['plyoffstart'];
  $plc = $_POST['plyoffcount'];
  if($pls <= 0 || $plc <= 0)
  {
    $plc = 0;
    $pls = 0;
  }

  if($pls >= 0 && $plc >= 0)
  {
    mdb2_query('update_playoff_places',
               array($pls, $plc, $_SESSION['tabnum']));
    $disp_str .= "$plc teams starting at place $pls are in the playoffs.<br />\n";
  }

  return($disp_str);
}

/*
 * Function to check the relegation/promotion form details and save any
 * valid changes.
 * Checks include:
 *  - That there is a league into teams can be promoted/relegated.
 *  - That the correct number of teams have been requested for promotion
 *  - That the requested teams are in the selected league.
 * @params:
 *  none. All form input derived from $_POST
 * @returns:
 *  string containing the success (or otherwise) of the operation
 */
function leagues_promote_relegate()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  $disp_str = "Checking promotion and relegation.<br />\n";

  $promoted_to  = mdb2_fetchRow('league_promoted',
                                array($_SESSION['tabnum']));
  $relegated_to = mdb2_fetchRow('league_relegated',
                                array($_SESSION['tabnum']));

  if(!$promoted_to && !$relegated_to)
  {
    return("ERROR:No league found matching the requested id, " . $_SESSION['tabn
um'] . ".<br />\n");
  }

  $lge_name  = $promoted_to['league_name'];
  $active    = $promoted_to['active'];
  $num_up    = $promoted_to['num_promoted'];
  $goto_up   = $promoted_to['up_to'];
  $up_id     = $promoted_to['leagueid'];
  $up_name   = $promoted_to['up_name'];
  $down_name = $relegated_to['down_name'];
  $num_down  = $relegated_to['num_relegated'];
  $goto_down = $relegated_to['down_to'];
  $down_id   = $relegated_to['leagueid'];

  /* counters for the promoted and relegated teams */
  
  /* Check through the posted variables looking for promotion checkboxes */
  /*
   * Decrement the database value for the number of relegated and promoted
   * teams for each appropriate value submitted. Both $num_up and $num_down
   * will be zero if the correct number of submissions have been made. Store
   * the id of each promoted/relegated team for later processing.
   */
  $up_teams   = array();
  $down_teams = array();
  foreach($_POST as $postvar => $postval)
  {
    if(substr($postvar, 0, 6) == "prorel")
    {

      $team_id = substr($postvar, 6);

      /* Only allow a valid team that has not previously been specified */
      $team_name = is_team_in_league($_SESSION['tabnum'], $team_id);

      if($team_name && !$up_teams[$team_id] && !$down_teams[$team_id])
      {
        if($postval == "up") /* A team has been promoted */
        {
          --$num_up;
          $up_teams[$team_id] = $team_name;
        }

        if($postval == "down") /* A team has been promoted */
        {
          --$num_down;
          $down_teams[$team_id] = $team_name;
        }
      } else {
        return("ERROR:Either team not in league or already selected.");
      }
    }
  }

  /* Now check that the correct number of teams have been selected */
  if($num_up > 0)   {return("ERROR:Not enough teams selected for promotion.");}
  if($num_up < 0)   {return("ERROR:Too many teams selected for promotion.");}
  if($num_down > 0) {return("ERROR:Not enough teams selected for relegation.");}
  if($num_down < 0) {return("ERROR:Too many teams selected for relegation.");}

  /* Perform the relegation and promotions for the league */
  foreach($up_teams as $tid => $team) /* Promotion */
  {
    mdb2_query('remove_team_from_league',
               array($_SESSION['tabnum'], $tid));
    mdb2_query('add_team_to_league',
               array($up_id, $tid));
    $disp_str .= "Promoting $team to $up_name.<br />\n";
  }
  foreach($down_teams as $tid => $team) /* Relegation */
  {
    mdb2_query('remove_team_from_league',
               array($_SESSION['tabnum'], $tid));
    mdb2_query('add_team_to_league',
               array($down_id, $tid));

    $disp_str .= "Relegating $team to $down_name.<br />\n";
  }

  return($disp_str);
}
?>
