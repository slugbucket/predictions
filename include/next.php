<?php
/*
 * Script to display and process forms for the users predictions.
 *
 * show() - Main driver for the predictions page.
 * navtabs() - build the top navigation items
 * verify_next() - Check that submitted prediction values meet expectations
 * next_form() - Display the next set of predictions
 * add_predictions() - Save the submitted predictions
 */

/* Function to interpret page requests and choose what action to perform
 * @params:
 *  none
 * @returns:
 *  string containing HTML to display
 */
function show()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  $disp_str = "Update existing or enter new predictions.<br />\n" .
              "Click on a team name in the fixture list to display their form" .
              " and highlight the fixture teams in the league table.<br />" .
              "Points deducted from a team are shown in brackets in " .
              "the league table.<br />\n";

  $today = date( "Y-m-d" );
  // $today = "2007-05-10";

 /* Check for submitted form and verify error-free */
  if(isset($_POST['makepredict']))
  {
    $vrfy = verify_next();
    if($vrfy)
    {
      $disp_str .= $vrfy;
      $disp_str .= next_form($today);
    } else
    {
      $disp_str .= add_predictions();
    }
    return($disp_str);
  }

  $now = now_by_timezone($_SESSION['tabnum']);
  $nf = mdb2_query('next_fixtures',
                   array($_SESSION['uid'], $now, $_SESSION['tabnum']));

# jur
  $disp_str .= "<div id='predictguide'>\n";



  $disp_str .= "<div id=\"fixturestopredict\">\n";
  /* Make sure that there are fixtures to be predicted */
  if(!$nf->numRows())
  {
    $disp_str .= "<div class=\"errmsg\">There are currently no fixtures available.</div><br />\n";
  } else {
    /* Display a list of fixture sets to choose from */
    $disp_str .= next_form($today) . "\n</div>\n";
  }
  $disp_str .= "<div id=\"leaguetable\">" .
               show_leaguetable($_SESSION['tabnum']);
  $disp_str .= "</div> <!-- leaguetable -->\n";

  if($nf->numRows()) {
      $disp_str .= "<div id=\"formguide\">" .
                   "<p>" .
                   "<table cellspacing=\"0\">" .
                   "<tr><th>Click team name</th></tr>" .
                   "</table>" .
                   "</p>" .
                   "</div> <!-- formguide -->";
  }


# jur
  $disp_str .= "</div><!-- predictguide -->\n";



  $disp_str .= "<br />\n" .
               "<div id=\"userlist\">" .
               "<hr />\n" .
               "Select a name from the list to see what ".
               "predictions have been made by other team members.\n" .
               "<br />\n" .
               display_all_userteam_users() .
               "<br />\n" .
               "</div>\n" .
               "<div id=\"season\">" .
               season_predictions() .
               "</div><!-- season -->\n";

# jur
#  $disp_str .= "<div id=\"leaguetable\">" .
#               show_leaguetable($_SESSION['tabnum']);
#  $disp_str .= "</div> <!-- leaguetable -->\n";

#  if($nf->numRows()) {
#      $disp_str .= "<div id=\"formguide\">" .
#                   "<p>" .
#                   "<table cellspacing=\"0\">" .
#                   "<tr><th>Click team name</th></tr>" .
#                   "</table>" .
#                   "</p>" .
#                   "</div> <!-- formguide -->";
#  }
# jur
  $disp_str .= "</div> <!-- fixturestopredict -->\n";

  $nf->free();

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
  $subs = mdb2_query('subscriptions', array($_SESSION['uid']));

  /* Trap common error conditions */
  if(!$subs) { return("Subscription navigation tab error.<br />\n"); }
  
  $navstr = "<ul>";
  $tcnt = 0;
  /* MDB2 while($subs->fetchInto($tab)) */
  while( $tab = $subs->fetchRow())
  {
    $class = "";

    $tab_text = $tab['league_name']; /* What to display in the tab */

    /* Default to first tab if nothing specified in URL (GET) */
    if(!$tcnt && !$_SESSION['tabnum']) {
      $class = " class=\"selected\"";
      $_SESSION['tabnum'] = $tab['league_id'];
    }
    else
    {
      if($_SESSION['tabnum'] == $tab['league_id'])
      {
        $class = " class=\"selected\"";
      }
      else /* Display a link to subscribed league */
      {
        $tab_text = "<a href=\"?action=nextset" .
                    "&tabnum=" . $tab['league_id'] .
                    "\" class=\"leaguetab\">" .
                    $tab_text . "</a>";
      }
    }
    $navstr .= "<li" . $class . ">" . $tab_text . "</li>\n";

    ++$tcnt;
  }
  $subs->free();

  $navstr .= "</ul>\n";

  return($navstr);
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
function verify_next()
{
  $disp_str = "";

  foreach($_POST as $pred => $goals)
  {
    if(preg_match("/^home/", trim($red)))
    {

      /* Extract the id of the fixture */
      $fid = substr($pred, 4);

      /* Get the names of the teams */
      $fixture_teams = mdb2_fetchRow('fixture_home_away',
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

      /* Check if prediction is for a fixture in a league that has not been
       * subscribed to
       */
      $is_sub = mdb2_fetchOne('is_subscribed',
                     array($fid, $_SESSION['uid'], $_SESSION['tabnum']));

      if(!$is_sub['is_subscribed'])
      {

        $disp_str .= "<span class=\"error\">You have not subscribed to the ".
                     "league in which " . $fixture_teams['home_team'] .
                     " and " . $fixture_teams['away_team'] .
                     " play.</span><br />\n";

      }

      /* Check that the prediction has been made before kickoff */
      $now = now_by_timezone($_SESSION['tabnum']);
      $ko = mdb2_fetchOne($query_ary['check_ko'], array($fid, $now));
      if(PEAR::isError($ko)) {
        return("<span class=\"error\">Cannot verify prediction kickoff time for prediction $fid.<br />\n");
      }
      if(!$ko['valid_ko'])
      {
        $disp_str .= "<span class=\"error\">Cannot accept prediction for " .
                     $fixture_teams['home_team'] . " vs " .
                     $fixture_teams['away_team'] .
                     " after the kick-off.</span><br />\n";
      }

    }

    /* Check for non-numeric scores */
    if((preg_match("/^home/", $pred) && ! is_numeric($goals)) ||
       (preg_match("/^away/", $pred) && ! is_numeric($goals))   )
    {
      $disp_str .= "<span class=\"errmsg\">Predicted goal values must be " .
                   "numeric.</span><br />\n";

    }
  }

  return($disp_str);
}

/*
 * Return the form displaying the next set of matches for which predictions
 * can be made
 * @params:
 *  $date: Start date for the predictions for the league
 * @returns:
 *  string containing HTML form for the predictions
 */
function next_form($date = "")
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!$date)
  {
    return("<span class=\"errmsg\">Error: missing start date.</span><br />\n");
  }

  $disp_str .= "
Click on a team to view their form this season.<br />
Check the '<em>no save</em>' box to exclude the prediction from being saved.
";

  /* Get the name of the league's timezone */
  $tz = mdb2_fetchOne('get_league_timezone', array($_SESSION['tabnum']));

  /* Get the value of NOW() for the league */
  $now = now_by_timezone($_SESSION['tabnum']);

  if(!$_GET['showall'])
  {
    $nf = mdb2_query('next_fixtures_7day',
                     array($_SESSION['uid'],
                           $now,
                           $_SESSION['tabnum'],
                           $now,
                           $_SESSION['tabnum']));
    if(!$nf->numRows()) {
      $nf = mdb2_query('next_fixtures',
                       array($_SESSION['uid'],
                             $now,
                             $_SESSION['tabnum']));
    }
    if(PEAR::isError($nf)) {
      return("Next set of fixtures query failed with " . $nf->getMessage() . "<br />\n");
    }
    /* If there are more fixtures beyond those selected, display a message
     * and offer a link to the full set
     */
    elseif(mdb2_fetchOne('new_fixtures_in_league',
                   array($_SESSION['tabnum'], $now))           > $nf->numRows())
    {
      $disp_str .= "
<br />
<div class=\"errmsg\">There are additional fixtures
after those listed below. Click to view all 
<a href=\"" . $_SERVER['PHP_SELF'] . "?action=nextset&tabnum=" .
              $_SESSION['tabnum']  .
              "&showall=1\">available fixtures</a>.</div><br />\n";
    }
  } else {
    $nf = mdb2_query('next_fixtures',
                     array($_SESSION['uid'],
                           $now,
                           $_SESSION['tabnum']));
    if(PEAR::isError($nf)) {
      return("Next set of fixtures query failed with " . $nf->getMessage() . "<br />\n");
    }
  }
  /* Don't build the form if there are no fixtures */
  if(!$nf->numRows()) { $nf->free(); return($disp_str); }

  /* Construct a form with the fixtures listed */
  $form = new HTML_QuickForm('predictions', 'post');

  $disp_str .= "<form action=\"?action=nextset&tabnum=" .
                                 $_SESSION['tabnum'] . "\" " .
               "method=\"post\" name=\"nextset\">\n";

  $disp_str .= "All times specified in the $tz timezone.<br />\n";

  $disp_str .= "
<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">\n
<colgroup span=\"1\" />
<colgroup span=\"1\" width=\"27%\" />
<colgroup span=\"1\" width=\"10%\" />
<colgroup span=\"1\" width=\"5%\" />
<colgroup span=\"1\" width=\"10%\" />
<colgroup span=\"1\" width=\"27%\" />
<colgroup span=\"1\" width=\"8%\" />
<colgroup span=\"1\" width=\"12%\" />
";

  $curr_date = ""; /* Trap changes in match dates */
  $endclass = "dark";    /* Alternate color of displayed rows */

  while($fixture = $nf->fetchRow())
  {

    /* Display a header showing a different date for the match */
    if( $curr_date != $fixture['match_date'])
    {

      $curr_date = $fixture['match_date'];

      $disp_str .= "<tr class=\"header\">" .
                   "<th colspan=\"7\">" . $curr_date . "</th>" .
                   "<th>no save</th>" .
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
    $aattrs = array('value' => ($_POST[$a_elem] ?
                                $_POST[$a_elem] :
                                $fixture['away']));

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

    /* Build the links to the home and away form guide */
    $hlink = "sndFormReq('home', '" . $fixture[home_team_id] . "',"  .
                            "'"    . $_SESSION[tabnum]      . "', " .
                            "'"    . $fixture[away_team_id] . "');";
    $alink = "sndFormReq('home', '" . $fixture[home_team_id] . "',"  .
                            "'"    . $_SESSION[tabnum]      . "', " .
                            "'"    . $fixture[away_team_id] . "');";

    /* Create a delete checkbox */
    $nsbox = new HTML_QuickForm_checkbox("nosave" . $fixture['fixture_id'],
                                         'no', '',
                                         array('class' => 'checkbox'));

    /* Home team : home goals : vs. : away goals : away team */
    $disp_str .= "<tr>" .
                 "<td class=\"pointless\">&nbsp;</td>" .
                 "<td class=\"$endclass\">" .
                 "<a href=\"javascript:" . $hlink . "\">" .
                          $fixture[home_team]    . "</a></td>" .
                 "<td>" . $home->toHtml()        . "</td>" .
                 "<td>" . " vs "                 . "</td>" .
                 "<td>" . $away->toHtml()        . "</td>" .

                 "<td>" .
                 "<a href=\"javascript:" . $alink . "\">" .
                          $fixture[away_team]   . "</a></td>" .
                 "<td>" . $fixture['kickoff']   . "</td>" .
                 "<td>" . $nsbox->toHtml()      . "</td>" .
                 "</tr>\n";

    $endclass = ($endclass == "dark" ? "light" :"dark");

    /* Add the formguide display functions */
    $disp_str .= "
";


  }

  /* Add submit and reset buttons */
  $sub  = new HTML_QuickForm_submit('makepredict', 'Submit', "class=button");
  $res  = new HTML_QuickForm_reset('reset',    'Clear',  "class=button");
  $sgrp = new HTML_QuickForm_group('submitreset', '',
                  array($sub, $res), '', FALSE);


  $disp_str .= "</table>\n";
  $disp_str .= $sgrp->toHtml();
  $disp_str .= "</form>\n";

  $nf->free();

  return($disp_str);
}

/*
 * Functions to store the predicted values
 * @params:
 *  none, but $_POST and $_SESSION variables accessed
 * @returns:
 *  string with list of the predictions submitted
 */
function add_predictions()
{
  global $db; /* Global database handle */

  global $query_ary; /* Allowed queries */

  $disp_str = "Processing predictions.<br />\n";

  $lge_id = $_SESSION['tabnum'];
  if(!$lge_id)
  {
    return("<span class=\"error\">No valid league specified.</span>\n");
  }

  /* loop through the home and away predicted scores */
  foreach($_POST as $postvar => $score)
  {

    /* Extract the prediction details */
    if(preg_match("/^home/", trim($postvar)))
    {

      /* Extract the id of the fixture */
      $fid = substr($postvar, 4);

      /* Get the names of the teams */
      $fixture_teams = mdb2_fetchRow('fixture_home_away',
                                   array($fid));

      /* Don't save the prediction if its 'nosave' box is checked */
      if($_POST['nosave' . $fid])
      {
        $disp_str .= "Not saving prediction for " .
                     $fixture_teams['home_team'] . " vs " .
                     $fixture_teams['away_team'] . "<br />\n";
        continue;
      } else {
      /* Remove the old prediction before inserting the new one */
        $res = mdb2_query('del_prediction',
                    array($fid, $_SESSION['uid']));
      }

      $res = mdb2_query('add_prediction',
                        array($_SESSION['uid'],
                              $fid,
                              trim($_POST['home' . $fid]),
                              trim($_POST['away' . $fid])));

      if(!PEAR::isError($res))
      {
        $disp_str .= "Saved prediction for " .
                     $fixture_teams['home_team'] . " vs " .
                     $fixture_teams['away_team'] . "<br />\n";
      }
      else
      {
        $disp_str .= error_message("ERROR:Failed to save prediction for ".
                                   $fixture_teams['home_team'] . " vs " .
                                   $fixture_teams['away_team'] . "<br />");
      }

    }
  }

  return($disp_str);
}
?>
