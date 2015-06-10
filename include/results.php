<?php
/*
 * Script to display and process forms for the users predictions.
 *
 * show() - Main driver
 * navtabs() - Navigation tabs generator
 * show_fixture_sets() - list the available fixture sets
 * verify_results() - check the validity of the submitted results
 * results_form() - display a form for entering results for a fixture set
 * add_results() - add or update fixture results
 */
function show()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "
<p>Use this page to select the fixtures to enter results for.</p>\n
";

//  $today = date( "Y-m-d" );
  $today = "2007-05-10";

  /* Process the selected fixture set */
  if(isset($_POST['choosefset']) && $_POST['choosefset'] == "Submit")
  {
      $disp_str .= results_form($_POST['fset']);
  }

 /* Check for submitted form and verify error-free */
  if(isset($_POST['addresults']))
  {
    $vrfy = verify_results();
    if($vrfy)
    {
      $disp_str .= $vrfy;
      $disp_str .= results_form($_POST['fset']);
    } else
    {
      $disp_str .= "<div id=\"resultspage\"> <!-- resultspage -->\n" .
                   "<div id=\"resultlist\"> <!-- resultlist -->\n" .
                   add_results() .
                   "</div> <!-- resultlist -->\n";

  $disp_str .= "<div id=\"leaguetable\"> <!-- leaguetable -->\n" .
               show_leaguetable($_SESSION['tabnum']) .
               "</div> <!-- leaguetable -->\n" .
               "<hr />\n";

      /* Add a form with a button to apply the user scores */
      $disp_str .= update_user_scores_form() .
                   "</div> <!-- resultspage -->\n";

    }
  }

  /* Get the available fixture sets and decide what to do:
   * If only 1 fixture set diplay the fixtures,
   * If more than fixture set display a choice of fixtuere sets (via a
   * function) and check $_POST['set'] as the set to display
   */
  $now = now_by_timezone($_SESSION['tabnum']);
  $set_qry = $db->query($query_ary['results_fixture_sets'],
             array($_SESSION['tabnum'], $now));

  if(PEAR::isError($set_qry)) {
    print_r($set_qry);
    $disp_str .= "<span class=\"errmsg\">Error extracting fixture set list: " .
                 $set_qry->getMessage() . "</span><br />\n";
    return($disp_str);
  }

  $disp_str .= "<div id=\"fixturesets\"> <!-- fixturesets -->\n";

  switch($set_qry->numRows())
  {
    case '0' : /* No fixtures to display */
      $disp_str .= "No fixtures to display.<br />\n";
      break;
    case '1' : /* display the list of fixtures */
      $set_qry->fetchInto($set);
      $disp_str .= results_form($set['setid']);
      break;
    default : /* Display a list of fixture sets to choose from */
      $disp_str .= show_fixture_sets($set_qry);
      break;
  }
  $set_qry->free();

  $disp_str .= "</div> <!-- fixturesets -->\n";

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
        $tab_text = "<a href=\"?action=results" .
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
function show_fixture_sets( $set_ary = '')
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!is_object($set_ary))
  {
    return("Error: Invalid fixture set list.<br />\n");
  }

  $disp_str = "Fixture sets in red have already had results submitted.<br />\n";

  $form = new HTML_QuickForm('hostform', 'post', "?action=results&tabnum=" .
              $_SESSION['tabnum']);

  /* Get a list of all fixture sets that have not had results submitted */
  $ns_ary = $db->getAssoc($query_ary['not_submitted']);

  while($set_ary->fetchInto($fset))
  {
    $r_label = $fset['league_name'] . " on " . $fset['first_date'];
    if(!$ns_ary[$fset['set_id']])
    {
      $r_label = "<span class=\"already_resulted\">" . $r_label . "</span>";
    }

    /* Add the radio button to the form */
    $r = new HTML_QuickForm_radio('fset', $r_label, '', $fset['set_id'],
                                  array('class' => "radio"));
    $form->addElement($r);
  }

  /* Add submit and reset buttons */
  $sub = new HTML_QuickForm_submit('choosefset', 'Submit', array('class' => 'button'));
  $res = new HTML_QuickForm_reset('reset', 'Clear', array('class' => 'button'));
  $sgrp = new HTML_QuickForm_group('submitreset', '',
                  array($sub, $res), '', FALSE);

  $form->addElement($sgrp);

  $disp_str .= $form->toHtml();

  return($disp_str);
}

/*
 * Function to check that the submitted prediction values are all
 * numeric
 * Verify that submission time is not too late
 * Verify that user has subscribed to the leagues predictions have been
 * posted for
 * @param
 *  none
 * @return
 *  '' - all predicted values numeric and subscribed to 
 *  'error message indicating non-numeric input
 */
function verify_results()
{
  $disp_str = "";

  foreach($_POST as $pred => $goals)
  {
    if(preg_match("/^home/", trim($red)))
    {

      /* Extract the id of the fixture */
      $fid = substr($pred, 4);

      /* Get the names of the teams */
      $fixture_teams = $db->getRow($query_ary['fixture_home_away'],
                                   array($fid));

      /* Assume that if there's no home team then the fixture does not
       * exist
       */
      if(!$fixture_teams['home_team'])
      {
        $disp_str .= "<span class=\"error\">There is no record of the " .
                     "requested fixture.</span><br />\n";
        continue;
      }


    }

    /* Check for non-numeric scores */
    if((preg_match("/^home/", $pred) && ! is_numeric($goals)) ||
       (preg_match("/^away/", $pred) && ! is_numeric($goals))   )
    {
      $disp_str .= "<span class=\"errmsg\">Fixture goal values must be " .
                   "numeric.</span><br />\n";

    }
  }

  return($disp_str);
}

/*
 * Return the form displaying the next set of matches for which predictions
 * can be made
 */
function results_form($fset = "")
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!$fset) { return("<span class=\"errmsg\">Error: missing start date.</span><br />\n"); }

  $now = now_by_timezone($_SESSION['tabnum']);
  $nf = $db->query($query_ary['results_fixtures'],
                   array($_SESSION['tabnum'], $fset, $now));

  /* Make sure that there are fixtures to be predicted */
  if(!$nf->numRows())
  {
    return("<span class=\"errmsg\">There are currently no fixtures available.</span><br />\n");
  }

  /* Construct a form with the fixtures listed */
  $form = new HTML_QuickForm('results', 'post');

  $disp_str .= "<form action=\"?action=results&tabnum=" .
                                 $_SESSION['tabnum'] . "\" " .
               "method=\"post\" name=\"results\">\n";

  $disp_str .= "<!-- div id=\"predictions\" -->\n" .
               "<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">\n";

  $curr_date = ""; /* Trap changes in match dates */

  $endclass="dark";

  while($nf->fetchInto($fixture))
  {

    /* Get the match type - to constrain the result type list */
    $mt = $db->fetchOne($query_ary['get_fixture_match_type'],
                      array($fixture['fixture_id']));

    /* Display a header showing a different date for the match */
    if( $curr_date != $fixture['match_date'])
    {
      $curr_date = $fixture['match_date'];
      $disp_str .= "<tr>" .
                   "<th colspan=\"7\">" . $curr_date . "</th>" .
                   "</tr>\n";
    }

    /* Build the home team columns */
    $h_elem = "home" . $fixture['fixture_id'];
    $hattrs = array('value' => ($_POST[$h_elem] ? $_POST[$h_elem] : $fixture['home']));

    /* Set input class to error if non-numeric score passed */
    if($_POST[$h_elem] && !is_numeric($_POST[$h_elem]))
    {
      $hattrs['class'] = "error";
      $hattrs['value'] = $fixture['home'];
    }

    $home = new HTML_QuickForm_text(
                $h_elem,
                $fixture[home_team],
                $hattrs
                                   );
    $home->setMaxLength(2);
    $home->setSize(2);

    /* Build the away team columns */
    $a_elem = "away" . $fixture['fixture_id'];
    $aattrs = array('value' => ($_POST[$a_elem] ? $_POST[$a_elem] : $fixture['away']));

    /* Set input class to error if non-numeric score passed */
    if($_POST[$a_elem] && !is_numeric($_POST[$a_elem]))
    {
      $aattrs['class'] = "error";
      $aattrs['value'] = $fixture['away'];
    }

    $away = new HTML_QuickForm_text(
                $a_elem,
                $fixture['away_team'],
                $aattrs
                                   );
    $away->setMaxLength(2);
    $away->setSize(2);

    /* Get a select element for the result type */
    $rt_val = 'rtype' . $fixture['fixture_id'];
    $rt_select = ($_POST[$rt_val] ? $_POST[$rt_val] : $fixture['result_type']);
    $rt_select = result_type_select($rt_val, $rt_select, $mt);

    /* Home team : home goals : vs. : away goals : away team */
    $disp_str .= "<tr>" .
                 "<td class=\"pointless\">&nbsp;</td>" .
                 "<td class=\"$endclass\">" . $fixture[home_team]   . "</td>" .
                 "<td>" . $home->toHtml()       . "</td>" .
                 "<td>" . " vs "                . "</td>" .
                 "<td>" . $away->toHtml()       . "</td>" .
                 "<td>" . $fixture['away_team'] . "</td>" .
                 "<td>" . $rt_select            . "</td>" .
                 "</tr>\n";

    $endclass = ($endclass == "dark" ? "light" :"dark");

  }

  /* Add submit and reset buttons */
  $sub  = new HTML_QuickForm_submit('addresults',
                                    'Save results',
                                    "class=button");

  $res  = new HTML_QuickForm_reset('reset',    'Clear',  "class=button");
  $sgrp = new HTML_QuickForm_group('submitreset', '',
                  array($sub, $res), '', FALSE);


  $disp_str .= "</table>\n<!-- /div --><!-- predictions -->";
  $disp_str .= $sgrp->toHtml();
  $disp_str .= "<hr />\n";

  $nf->free();

  return($disp_str);
}

/*
 * Functions to store the fixture results
 * This needs to verify that the submitted result is for match that has been
 * played in the past.
 * This helps to avoid displaying misleading results when a set of results are
 * submitted mid-way during a fixture set so that the future games are
 * submitted as 0-0.
 * @params:
 *  none
 * @returns:
 *  String indicating the success or otherwise of the results operation
 */
function add_results()
{
  global $db; /* Global database handle */

  global $query_ary; /* Allowed queries */

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "Processing results.<br />\n";

  $lge_id = $_SESSION['tabnum'];
  if(!$lge_id)
  {
    return("<span class=\"error\">No valid league specified.</span>\n");
  }

  /* loop through the home and away submitted scores */
  foreach($_POST as $postvar => $score)
  {

    /* Extract the prediction details */
    if(preg_match("/^home/", trim($postvar)))
    {

      /* Extract the id of the fixture */
      $fid = substr($postvar, 4);

      /* Get the result type */
      $rt = trim($_POST['rtype' . $fid]);

      /* Get the match type to ensure that the correct result type is added */
      $mt = $db->fetchOne($query_ary['get_fixture_match_type'],
                        array($fid));
      if(($mt != "playoff" && $mt != "cup") &&
         ($rt == "extra" || $rt == "penalties"))
      { 
        $rt = "normal";
      }

      /* Get the names of the teams */
      $fixture_teams = $db->getRow($query_ary['fixture_home_away'],
                                   array($fid));

      /* Check that the match has completed and show an error if not */
      $now = now_by_timezone($lge_id);
      if(!$db->fetchOne('check_end_of_fixture', $fid, $now))
      {
        $disp_str .= "ERROR: Cannot submit result for " .
                     $fixture_teams['home_team'] . "vs. " .
                     $fixture_teams['home_team'] .
                     " before it has been played.<br />";
        continue;
      }

      /* Remove the old result before inserting the new one */
      $db->query($query_ary['del_result'], array($fid));
      $res = $db->query($query_ary['add_result'],
                        array($fid,
                              $rt,
                              trim($_POST['home'  . $fid]),
                              trim($_POST['away'  . $fid])));
      if(PEAR::isError($res))
      {
        $disp_str .= "Error saving result for " .
                     $fixture_teams['home_team'] . " vs. " .
                     $fixture_teams['away_team'] . "<br />\n";
      } else
      {
        $disp_str .= "Saved result for fixture " . " as " .
                     $fixture_teams['home_team'] . " " .
                     $_POST['home'  . $fid] . " " .
                     $fixture_teams['away_team'] . " " .
                     $_POST['away'  . $fid] .
                     ".<br />\n";
      }

    }
  }

  /* Update the league table */
  $disp_str .= update_league_table($lge_id);

  return($disp_str);
}
?>
