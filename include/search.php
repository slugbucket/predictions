<?php
/*
 * Script to display and process forms for the users predictions.
 *
 * show() - Main driver
 * navtabs() - Navigation tabs generator
 */
function show()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "
<p>Use this page to find all the matches played by a given team and make
corrections where necessary.</p>\n
<p>
Select a team from the list below to show all the home and away matches
played by the team this season.
</p>
";

  /* Display a form for selectng a team */
  $form  = new HTML_QuickForm('teamlist', 'post', "?action=search&tabnum=" .
              $_SESSION['tabnum']);
  $onsel = "onSelect=\"javascript:sndFormReq();";
  $tstr  = league_team_select('team_list', $_SESSION['tabnum'], "", $onsel);
  $tstr .= "<input type=\"submit\" name=\"go\" value=\"Go\" />\n";
  $tsel  = new HTML_QuickForm_static('', ''. $tstr);
  
  $form->addElement($tsel);
  $disp_str .= $form->toHtml();



  $rtext = '';
  if($_POST['team_list'])
  {
    $rtext = show_team_results($_POST['team_list']);
  }
  /* Prepare the box to display the results of the search */
  $disp_str .= "<div id=\"team_results\">$rtext</div>\n";

  return($disp_str);
}

/*
 * Script to generate the top navigation tabs with a tab for each league that
 * the logged in user predicts on as listed in the database table.
 * @param:
 * none
 * @returns:
 * string containing CSS-friendly naivgation tabs
 * FIXME: does not display default tab if invalid tab num passed in URL
 */
function navtabs()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  $lges = $db->query($query_ary['league_list']);

  /* Trap common error conditions */
  if(!$lges) { return("League navigation tab error.<br />\n"); }
  
  $navstr = "<ul>";
  $tcnt = 0;

  /*
   * Experimental tab code
   */
  $tabs_per_bar = 3; /* Number of tabs to display */

  $tab_ary = $db->getAssoc($query_ary['league_list']);
  $num_tabs = count($tab_ary);

  $tin = 0;
  for($i=0; $i<$num_tabs; ++$i)
  {
    $lges->fetchInto($tab);
    if($tab['id'] == $_SESSION['tabnum']) {$tin=$i;}
  }

  /* Check to see if we need a previous tab link */
  if($tin >= $tabs_per_bar) {
    $tab_ary['prev'] = $tab_ary[$tin-1];
  }

  /* Check to see if we need a next tab link */
  if($tin <= $num_tabs - $tabs_per_bar) {
    $tab_ary['next'] = $tab_ary[$tin-2];
  }

  /*
  echo "DEBUG:selected tab is at position $tin.<br />\n";
   * End of experimental tab code
   */

  /* FIXME: remove next query when limited tab list is working */
  $lges = $db->query($query_ary['league_list']);
  while($lges->fetchInto($tab))
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
        $tab_text = "<a href=\"?action=search" .
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
 * function to display a list of fixture sets, highlighting any that
 * have already had predictions made
 * @param
 * $set_ary : result set containing formatted and unfomrated dates for display
 * in group of radio buttons
 * @return
 * string containg HTML-formatted form
 */
function show_team_results( $team_id = '')
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

//  $team_id = "9";
echo is_team_in_league($team_id);

  if(!$team_id || is_team_in_league($team_id))
  {
    return("<span class=\"errmsg\">Error:Cannot find requested team, $team_id, in league id, " . $_SESSION['tabnum'] . ".</span>\n");
  }

  $match_rs = $db->query($query_ary['results_byleague_team'],
                            array($_SESSION['tabnum'], $team_id, $team_id));


//print_r($match_rs);
  if($match_rs->numRows())
  {
    $disp_str .= "<table cellpadding=\"0\" cellspacing=\"0\" id=\"matchlist\">\n";
    $curr_date = ""; /* Trap changes in match dates */

    $endclass="dark";

    while($match_rs->fetchInto($match))
    {

      /* Display a header showing a different date for the match */
    if( $curr_date != $match['match_date'])
    {
      $curr_date = $match['match_date'];
      $disp_str .= "<tr>" .
                   "<th colspan=\"6\">" . $curr_date . "</th>" .
                   "</tr>\n";
    }

      /* Home team : home goals : vs. : away goals : away team */
      $disp_str .= "<tr>" .
                   "<td class=\"pointless\">&nbsp;</td>" .
                   "<td class=\"$endclass\">" . $match[home_team] . "</td>" .
                   "<td>" . $match[home_goals]                    . "</td>" .
                   "<td>" . $match['away_team']                   . "</td>" .
                   "<td>" . $match['away_goals']                  . "</td>" .
                   "<td>" . $match['result_type']                 . "</td>" .
                   "</tr>\n";

      $endclass = ($endclass == "dark" ? "light" :"dark");
    }
  } else {
    return("<span class=\"errmsg\">The requested team does not have any results submitted.</span><br />\n");
  }
  $disp_str .= "</table>\n<!-- /div --><!-- predictions -->";

  return($disp_str);
}

?>
