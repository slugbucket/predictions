<?php
/*
 * show() - Main driver
 * navtabs() - Top navigation items
 * summary_table() - display the sumary details
 * list_correct_fixtures() - show correctly predicted fixtures
 */
/*
 * Script to display the users prediction performance
 */
function show()
{
  $disp_str = "User prediction results for week ending " .
              date( "D d M Y" ) . ".<br />\n";

  if($_SESSION['tabnum'])
  {
    $now = now_by_timezone($_SESSION['tabnum']);
    $notyet = mdb2_fetchOne('league_not_yet_predicted',
                   array($_SESSION['uid'],
                         $_SESSION['tabnum'],
                         $now,
                         $_SESSION['tabnum']));
    if(PEAR::isError($notyet)) { print $notyet->getMessage(); };
    $qs = "?action=nextset&tabnum=" . $_SESSION['tabnum'] . "&showall=1";
  }
  else
  {
    $lid = mdb2_fetchOne('default_subscription', array($_SESSION['uid']));
    $now = now_by_timezone($lid);
    $notyet = mdb2_fetchOne('not_yet_predicted',
                   array($_SESSION['uid'], $_SESSION['uid'], $now));

    if(PEAR::isError($notyet)) { print $notyet->getMessage(); };
    $qs = "?action=nextset";
  }

  if($notyet)
  {
    $disp_str .= "<span class=\"errmsg\">There are " .
                 "<a href=\"" . $_SERVER['PHP_SELF'] . $qs . "\">" .
                 $notyet . " fixtures</a> not yet predicted.</span>";
  }

  /* Show the correctly predicted scores and results for a league */
  if($_SESSION['tabnum'])
  {
    $disp_str .= subscribed_league_summary();

    $caption = "<br /><strong>Exact scoreline</strong>";
    $disp_str .= list_correct_fixtures("league_score_teams", $caption);

    $caption = "<br /><strong>Correctly predicted draws</strong> - actual scores in parentheses<br />";
    $disp_str .= list_correct_fixtures("league_draws_teams", $caption);

    $caption = "<br /><strong>Correctly predicted winning team</strong> - actual scores in parentheses<br />";
    $disp_str .= list_correct_fixtures("league_result_teams", $caption);

  }
  else /* Display a general summary of the previous week's predictions */
  {

    /* Proposed layout would be to have summary_table and topfive next to
     * each other with the scores and results tables beneath
    $disp_str .= "<div id=\"summary\">" .
                 "<p>bob</p>\n" .
     */
    $disp_str .= "<div id=\"summary\">" .
                 summary_table() .
                 "</div><!-- summary -->\n";

    $disp_str .= "<div style=\"clear: both;\">&nbsp</div>\n";

    $caption = "Most recent exact scores predicted in leagues.";
    $disp_str .= list_correct_fixtures("correct_score_teams", $caption);

    $caption = "<br />Most recent predicted draws for all leagues - actual scores in parentheses.";
    $disp_str .= list_correct_fixtures("correct_draws_teams", $caption);

    $caption = "<br />Most recent correct winners for all leagues - actual scores in parentheses.";
    $disp_str .= list_correct_fixtures("correct_result_teams", $caption);

  }

  return($disp_str);
}

/*
 * Script to generate the top navigation tabs with a tab for each league that
 * the logged in user predicts on as listed in the database table.
 * @param:
 * none
 * @returns:
 * string containing CSS-friendly naivgation tabs
 */
function navtabs()
{
  /* $navstr = "<ul><li class=\"selected\">Summary</li></ul>\n"; */

    $subs = mdb2_query('subscriptions', array($_SESSION['uid']));

  /* Trap common error conditions */
  if(!$subs) { return("Subscription navigation tab error.<br />\n"); }

  /* Default to the summary tab (see below) if no tab asked for in _GET */
  if(!isset($_SESSION['tabnum'])) {$_SESSION['tabnum'] = "0";}

  $tcnt = 0;
  while($tab = $subs->fetchRow())
  {
    $class = "";

    $tab_text = $tab['league_name']; /* What to display in the tab */

    /* Default to first tab if nothing specified in URL (GET) */
    if(!$tcnt && !isset($_SESSION['tabnum'])) {
      $class = " class=\"selected\"";
    }
    else
    {
      if($_SESSION['tabnum'] == $tab['league_id'])
      {
        $class = " class=\"selected\"";
      }
      else /* Display a link to subscribed league */
      {
        $tab_text = "<a href=\"?action=summary" .
                    "&tabnum=" . $tab['league_id'] .
                    "\" class=\"leaguetab\">" .
                    $tab_text . "</a>";
      }
    }
    $navstr .= "<li" . $class . ">" . $tab_text . "</li>\n";

    ++$tcnt;
  }
  $subs->free();

  /* Add the summary tab as the first tab */

  $tab_text = "Summary";
  $class = "";
  if(!$_SESSION['tabnum']) {$class = " class=\"selected\"";}
  else
  {
    $tab_text = "<a href=\"?action=summary" .
                    "&tabnum=0\" class=\"leaguetab\">" .
                    $tab_text . "</a>";
  }
  $navstr = "<li" . $class . ">" . $tab_text . "</li>\n" . $navstr;
  $navstr = "<ul>" . $navstr;

  $navstr .= "</ul>\n";

  return($navstr);
}

/* Function to display the performance of the user for the subscribed league.
 * @params:
 *  none
 * @returns:
 *  tables displaying the season's prediction performance
 */
function subscribed_league_summary()
{
  if(!$_SESSION['tabnum']) {return("");}

  /* Get the number of predictions
   */
  $np = mdb2_fetchOne('num_league_predictions',
                    array($_SESSION['uid'],
                          $_SESSION['tabnum']));
  $ns = mdb2_fetchOne('summary_league_scores',
                    array($_SESSION['uid'],
                          $_SESSION['tabnum']));
  $nr = mdb2_fetchOne('summary_league_outcomes',
                    array($_SESSION['uid'],
                          $_SESSION['tabnum']));
  $nd = mdb2_fetchOne('summary_league_draws',
                    array($_SESSION['uid'],
                          $_SESSION['tabnum']));
  $pc = ($np ? 100*($nr+$ns+$nd)/$np : 0);

  /* Display a table showing progress so far */
  $disp_str = "<!-- div id=\"myseason\" -->
<table cellspacing=\"0\" class=\"tableview\">
<caption>Season performance for league</caption>
<colgroup span=\"1\" />
<colgroup span=\"1\" width=\"28%\" />
<colgroup span=\"1\" width=\"16%\" />
<colgroup span=\"1\" width=\"16%\" />
<colgroup span=\"1\" width=\"16%\" />
<colgroup span=\"1\" width=\"24%\" />
<tr class=\"header\">
 <th>&nbsp;</th><th>Predictions</th><th>Winner</th><th>Scores</th><th>Draws</th><th>Points</th><th>Rating</th>
</tr>";

  /* Display the stats */
  $disp_str .=
"<tr>" .
  "<td class=\"pointless\">&nbsp;</td><td class=\"dark\">$np</td><td>$nr</td><td>$ns</td><td>$nd</td><td>" . ($nr+$ns+$nd) . "</td><td>" . sprintf("%.5s", $pc) . "</td>" .
"</tr>\n";

  $disp_str .= "</table>\n";

  $disp_str .= "<!-- /div --><!-- myseason -->\n";

  return($disp_str);
}

/* Function to show a simple table showing summary stats for the predictions
 * made for all leagues this season
 * @params:
 *  none (but tabnum and uid _SESSION variables are referenced
 * @returns:
 *  HTML table listing the summary of predicted scores and results
 */
function summary_table()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  /* Get the number of predictions
   */
  $mp = mdb2_fetchOne('num_predictions', array($_SESSION['uid']));

  /* Get the number of correctly predicted results (but wrong score) */
  $mt = mdb2_fetchOne('match_results', array($_SESSION['uid']));

  /* Get the number of wrong scores but correct score differential */
  $md = mdb2_fetchOne('summary_season_draws', array($_SESSION['uid']));

  /* Get the number of correctly predicted scores */
  $cs = mdb2_fetchOne('scores', array($_SESSION['uid']));

  /* Get the historical stats so far */
  $now = get_user_now($_SESSION['uid']);
  $ptw = mdb2_fetchOne('predictions_this_week',
                     array($now, $now,  $_SESSION['uid']));
  $plw = mdb2_fetchOne('predictions_last_week',
                     array($now, $now,  $_SESSION['uid']));
  $plm = mdb2_fetchOne('predictions_last_month', 
                     array($now, $now,  $_SESSION['uid']));

  /* Correctly predicted scores */
  $stw = mdb2_fetchOne('scores_this_week',  array($_SESSION['uid']));
  $slw = mdb2_fetchOne('scores_last_week',  array($_SESSION['uid']));
  $slm = mdb2_fetchOne('scores_last_month', array($_SESSION['uid']));

  /* Correctly predicted results */
  $rtw = mdb2_fetchOne('results_this_week',  array($_SESSION['uid']));
  $rlw = mdb2_fetchOne('results_last_week',  array($_SESSION['uid']));
  $rlm = mdb2_fetchOne('results_last_month', array($_SESSION['uid']));

  /* Correctly predicted drawn matches */
  $dtw = mdb2_fetchOne('correct_draw_this_week',  array($_SESSION['uid']));
  if(PEAR::isError($dtw)) {
    return("Matches drawn this week query failed with " . $dtw->getMessage() . "<br />\n");
  }
  $dlw = mdb2_fetchOne('correct_draw_last_week', array($_SESSION['uid']));
  $dlm = mdb2_fetchOne('correct_draw_last_month', array($_SESSION['uid']));

  /* Display a table showing progress so far */
  $disp_str = "<!-- div id=\"myseason\" -->
<table cellspacing=\"0\" class=\"tableview\">
<colgroup span=\"1\" />
<colgroup span=\"1\" width=\"22%\" />
<colgroup span=\"1\" width=\"18%\" />
<colgroup span=\"1\" width=\"20%\" />
<colgroup span=\"1\" width=\"18%\" />
<colgroup span=\"1\" width=\"20%\" />
<tr class=\"header\">
 <th>&nbsp;</th><th>&nbsp;</th><th>This week</th><th>Previous week</th><th>Last month</th><th>Total</th>
</tr>";

  /* Display the stats */
  $disp_str .=
"<tr>" .
  "<td class=\"pointless\">&nbsp;</td><td class=\"dark\">Predictions</td><td>$ptw</td><td>$plw</td><td>$plm</td><td>$mp</td>" .
"</tr>\n" .
"<tr>" .
  "<td class=\"pointless\">&nbsp;</td><td class=\"light\">Correct winner</td><td>$rtw</td><td>$rlw</td><td>$rlm</td><td>$mt</td>" .
"</tr>\n" .
"<tr>" .
  "<td class=\"pointless\">&nbsp;</td><td class=\"light\">Correct draws (x2)</td><td>$dtw</td><td>$dlw</td><td>$dlm</td><td>$md</td>" .
"</tr>\n" .
"<tr>" .
  "<td class=\"pointless\">&nbsp;</td><td class=\"dark\">Exact scores (x3)</td><td>$stw</td><td>$slw</td><td>$slm</td><td>$cs</td>" .
"</tr>\n";

  $disp_str .= "</table>\n";

  $disp_str .= "<p><strong>Points total : " . ($mt+$cs+$md) . "</strong></p>\n";

  $disp_str .= "<!-- /div --><!-- myseason -->\n";

  return($disp_str);
}

/* Function to return a table listing how the correctly predicted scores or
 * results for a particular league or just a summary of the most recent
 * predictions
 * @params:
 *  $tab_type: string: the name of the query to get the display data
 *  $caption: string: What to display in the table with the data
 *  tabnum and uid _SESSION variables are referenced
 * @returns:
 *  HTML table listing the teams and scores of predicted fixtures
 */
function list_correct_fixtures($tab_type = "", $caption = "Correct scores")
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  if(!$query_ary[$tab_type])
  {
    return("<p>Invalid format request for summary table.</p>");
  }

  /* Now display a table showing the correctly predicted scores and results */
  $qary = array();
  if($_SESSION['tabnum']) { $qary[] = $_SESSION['tabnum']; }
  $qary[] = $_SESSION['uid'];
  $correct_scores = mdb2_query($tab_type, $qary);

  if(PEAR::isError($correct_scores)) {
    return("$tab_type query failed with " . $correct_scores->getMessage() . "<br />\n");
  }
  if($correct_scores->numRows())
  {
    $disp_str .= "<!-- div id=\"goodscores\" -->
<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">
<caption>$caption</caption>\n
<colgroup span=\"1\" width=\"1%\" />
<colgroup span=\"1\" width=\"20%\" />
<colgroup span=\"1\" width=\"25%\" />
<colgroup span=\"1\" width=\"5%\" />
<colgroup span=\"1\" width=\"25%\" />
<colgroup span=\"1\" width=\"5%\" />
";
    $disp_str .= "<tr class=\"header\">" .
                 "<th>&nbsp;</th>" .
                 "<th>Date</th>" .
                 "<th>Home team</th>" .
                 "<th>&nbsp;</th>" .
                 "<th>Away team</th>" .
                 "<th>&nbsp;</th>" .
                 "</tr>\n";

    $endclass = "dark";

    while($good_score = $correct_scores->fetchRow())
    {
  
      /* Set the style for the display row */
      /* Home team : home goals : vs. : away goals : away team */
      $disp_str .= "<tr><td class=\"pointless\">&nbsp;</td>" .
                   "<td class=\"$endclass\">" . $good_score['match_date'] . "</td>" .
                   "<td>" . $good_score[home_team] . "</td>" .
                   "<td class=\"score\">" . $good_score['predict_home'];

      if(is_numeric($good_score['actual_home'])) {
        $disp_str .= " (" . $good_score['actual_home'] . ")";
      }
      $disp_str .= "</td>";

      $disp_str .= 
                   "<td>" . $good_score['away_team'] . "</td>" .
                   "<td class=\"score\">" . $good_score['predict_away'];

      if(is_numeric($good_score['actual_away'])) {
        $disp_str .= " (" . $good_score['actual_away'] . ")";
      }
      $disp_str .= "</td>";

                   "</tr>\n";
      $endclass = ($endclass == "dark" ? "light" :"dark");
  
    }
    $disp_str .= "</table>\n<!-- /div --><!-- goodscores -->\n";
  }
  else
  {
    if($tab_type == "league_score_teams"  ||
       $tab_type == "correct_score_teams") {
      return("<p>No correctly predicted exact scores.</p>\n");
    } else {
      return("<p>No match results correctly predicted.</p>\n");
    }
  }

  return($disp_str);
}
?>
