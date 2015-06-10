<?php
/*
 * Handler script for serving AJAX requests
 */

/* Don't allow authenticated access */
if(!$_SESSION['uid'])
{
  die("inner|Cannot invoke async actions without logging in.");
}

/*
 * Handle the request to delete a message
 */
if(isset($_REQUEST['msgid']) && isset($_REQUEST['grpid']))
{
  echo "comments|" . delete_user_comment($_REQUEST['msgid']) .
                     show_user_comments($_REQUEST['grpid']);
}

/* Handle the request for the form guide display */
if(isset($_REQUEST['where']) &&
   isset($_REQUEST['team'])  &&
   isset($_REQUEST['lge'])   &&
   isset($_REQUEST['opp']))
{
  echo "formguide|" . get_team_form($_REQUEST['team'],
                                    $_REQUEST['lge'],
                                    $_REQUEST['where'],
                                    $_REQUEST['opp']);
  echo "|leaguetable|" . show_leaguetable($_REQUEST['lge'],
                                         $_REQUEST['team'],
                                         $_REQUEST['opp']);
}

/*
 * Handle the request for user score updates
 */
if(isset($_REQUEST['scores']))
{
  echo "updatedusers|" . update_user_scores($_REQUEST['scores']);
}

/*
 * Handle the request for a team's results for the season
 */
if(isset($_REQUEST['search']))
{
  echo "team_results|" . show_team_results($_REQUEST['search']);
}

/*
 * Get the predictions made by a given user this season
 */
if(isset($_REQUEST['puid']) && is_numeric($_REQUEST['puid']))
{
  echo "season|" . season_predictions($_REQUEST['puid']);
}

/*
 * Return the number of teams in a league
 */
if(isset($_REQUEST['lgeid']) && is_numeric($_REQUEST['lgeid']))
{
  $lge_id = $_REQUEST['lgeid'];
  $m_type = $_REQUEST['type'];

  if($m_type == "playoff") {
    $tcnt = $db->getOne($query_ary['get_playoff_count'], array($lge_id)) / 2;
  } else {
    $tcnt = $db->getOne($query_ary['league_team_count'], array($lge_id)) / 2;
  }
  echo "num_fixtures|$tcnt";
}
  /*
   * General operation
  switch($_REQUEST['action']) {
    case 'foo':
      echo "foo|foo done";
      break;
  }
*/
die();
?>
