<?php
/*
 * Script to display details of how well the user is doing in each of the
 * user teams that they are a member of.
 * A user can belong to more than one userteam and individual users are not
 * required to be subscribed to the same teams.
 * A userteam can have at most 11 members.
 * A user can belong to at most 4 teams.
 *
 * show() - Main driver
 * navtabs() - Top navigation items
 * graph_category() - return XML category for the graph
 */

/*
 * Include the FCKeditor
 */
include("fckeditor/fckeditor_php4.php");

/*
 * Driver function that identifies what is required from the incoming
 * request
 */
function show()
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  $disp_str = "Display the user's position in the teams they are " .
              "a member of.<br />\n";

  /* Get the default userteam if none provided in the URL */
  if($_SESSION['tabnum']) /* URL provides the userteam id */
  {
    $tabnum = $_SESSION['tabnum'];
  }
  else /* Get the userteam id from the database */
  {
    $tabnum = $db->fetchOne($query_ary['default_userteam'],
                         array($_SESSION['uid']));
  }

  /* Check for an incoming user comment and insert it */
  $comm_div = "<div id=\"comments\">\n";
  if(isset($_POST['FCKeditor1']))
  {
    $comm_res = insert_user_comment($_SESSION['uid'], $tabnum);
    if(!$comm_res) {$comm_div .= error_message($comm_res);}
  }

  /* Create a display element containing all this group's comments */
  $comm_div .= show_user_comments($tabnum) .
               "</div>\n";

  $disp_str .= "<!-- div id=\"predictions\" -->" .
               "<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">" .
               "<colgroup span=\"1\" />" .
               "<colgroup span=\"1\" width=\"15%\" />" .
               "<colgroup span=\"1\" width=\"15%\" />" .
               "<colgroup span=\"1\" width=\"15%\" />" .
               "<colgroup span=\"1\" width=\"15%\" />" .
               "<colgroup span=\"1\" width=\"15%\" />" .
               "<colgroup span=\"1\" width=\"12%\" />" .
               "<colgroup span=\"1\" width=\"18%\" />" .
               "<tr>"                                  .
               "<th>&nbsp;</th>"                       .
               "<th>Username</th><th>Predictions</th>" .
               "<th>Winners</th>"                      .
               "<th>Draws</th>"                        .
               "<th>Exact scores</th>"                       .
               "<th>Points</th>"                       .
               "<th>Rating (%)</th>"                   .
               "</tr>\n";

  $uteam_users = $db->query($query_ary['userteam_points_ordered'], $tabnum);

  $endclass = "dark";
                            
  /* Print each user's details */
  while($uteam_users->fetchInto($member))
  {

    /* Convert the total score to a success ratio */
    if($member['num_predictions'])
    {
      $rating = ($member['total_points'] / $member['num_predictions']) * 100;
    } else
    {
      $rating = 0;
    }

    $disp_str .= "<tr  align=\"center\">" .
                 "<td class=\"pointless\">&nbsp;</th>" .
                 "<td class=\"$endclass\">" . $member['username'] . "</td>" .
                 "<td>" . $member['num_predictions']              . "</td>" .
                 "<td>" . $member['correct_results']              . "</td>" .
                 "<td>" . $member['correct_diffs']                . "</td>" .
                 "<td>" . $member['exact_scores']                 . "</td>" .
                 "<td>" . $member['total_points']                 . "</td>" .
                 "<td>" . sprintf("%.2f", $rating)                . "</td>" .
                 "</tr>\n";

    $endclass = ($endclass == "dark" ? "light" :"dark");

  }

  $disp_str .= "</table>\n<!-- /div --><!-- predictions -->";

  /* Display the season in chart form */
  //include charts.php to access the InsertChart function
  // include_once "charts/charts.php";
  // $disp_str .=  InsertChart("charts/charts_library/lnno.swf", "charts/charts_library", "charts/sample.php", 400, 250 );


  /* Using FustionCharts to plan the team data */
  include("FusionChartsFree/Code/PHP/Includes/FusionCharts.php");
  $graph_str = "<SCRIPT LANGUAGE=\"Javascript\" SRC=\"FusionChartsFree/JSClass/FusionCharts.js\"></SCRIPT>";

  /* Format XML data for use with the graph */
  $predgraph = "<graph caption='Predictions made' subCaption='for each set of fixtures' xAxisName='Fixture set date' yAxisMinValue='0' yAxisName='Predictions' rotateNames='1' decimalPrecision='0' showNames='1'>";
  $resultsgraph = "<graph caption='Score rating' subCaption='for this month' xAxisName='Fixture set date' yAxisMinValue='0' yAxisName='Rating %25' rotateNames='1' decimalPrecision='0' showNames='1'>";

  /* Array of colors to use on the graphs */
  $graph_colors = array("AFD8F8", "0099FF", "008ED6",
                        "A186BE", "9D080D", "FF8E46");


  /* Get the default league so that we can calculate a value for NOW() */
  $lid = $db->fetchOne($query_ary['default_subscription'], $_SESSION['uid']);
  $now = now_by_timezone($lid);

  /* Get the users and fixtures dates for this month */
  $us_ary = $db->getAssoc($query_ary['userteam_users'],
                          false,
                          array($tabnum),
                          DB_FETCHMODE_ASSOC);

  /* Get the range of fixture sets for this month, or the past 14 days if there
   * aren't enough sets this month.
   */
  $fs_ary = $db->getAll($query_ary['fixture_set_this_month'],
                        array($now),
                        DB_FETCHMODE_ASSOC);
  if(count($fs_ary) < 3)
  {
    $fs_ary = $db->getAll($query_ary['fixture_set_this_month2'],
                          array($now, $now),
                          DB_FETCHMODE_ASSOC);
  }
  if(PEAR::isError($fs_ary)) {
    return("Error extracting monthly fixture set data.");
  }

  /* Set the FusionCharts categories for the multi-segment graphs */
  $predgraph .= "<categories>" .
             implode("", array_map("graph_category", $fs_ary)) .
             "</categories>";
  $resultsgraph .= "<categories>" .
             implode("", array_map("graph_category", $fs_ary)) .
             "</categories>";

  /* Loop through each fs_set date for each user */
  if($fs_ary)
  {
    $ucnt = 0;
    $rating = 0;
    foreach($us_ary as $name => $user_id)
    {


      /* Get the predictions made by this user */
      $gcol = $graph_colors[$ucnt++];
      $predgraph .= "<dataset seriesname='" . $name . "' color='$gcol'>";
      $resultsgraph .= "<dataset seriesname='" . $name . "' color='$gcol'>";

      foreach($fs_ary as $fset)
      {

        $edate = $db->fetchOne($query_ary['fixture_set_end'],
                             array($fset['start_date']));

        $np = $db->fetchOne($query_ary['user_predictions_bydate'],
                        array($fset['start_date'],
                              $edate,
                              $user_id));
        $nr = $db->fetchOne($query_ary['results_bydate'],
                        array($fset['start_date'],
                              $edate,
                              $user_id));
        $ns = $db->fetchOne($query_ary['scores_bydate'],
                        array($fset['start_date'],
                              $edate,
                              $user_id));
        if($np) {
          $rating = 100 * ($nr + $ns) / $np;
        }

        // echo "Setting graph color to " . $gcol . ".<br />\n";

        $predgraph .= "<set value='$np' alpha='100' />";
        $resultsgraph .= "<set value='$rating' alpha='100' />";
      }

      $predgraph .= "</dataset>";
      $resultsgraph .= "</dataset>";
    }
    $predgraph    .= "</graph>";
    $resultsgraph .= "</graph>";

    $graph_str .= "<div id=\"monthgraphs\">\n";

    $graph_str .= renderChart("FusionChartsFree/Charts/FCF_MSLine.swf",
                              "", $predgraph, "monthpreds",
                              275, 250, false, false);

    $graph_str .= renderChart("FusionChartsFree/Charts/FCF_MSLine.swf",
                              "", $resultsgraph, "monthresults",
                              275, 250, false, false);

    $graph_str .= "</div>\n";
    $disp_str  .= $graph_str;
  }


  $disp_str .= "<p>User comments (submission form at bottom of page)</p>\n" .
               $comm_div;

  $disp_str .= "Use the form below to submit a new comment - keep it clean!<br />\n";

  $f_action = "action=team";
  if($tabnum) {$f_action .= "&tabnum=$tabnum";}

  $disp_str .= "<form action=\"?" . $f_action . "\" method=\"post\">\n";

  $oFCKeditor = new FCKeditor('FCKeditor1') ;
  $oFCKeditor->BasePath = 'fckeditor/';
  $oFCKeditor->Value = 'Add your comment text here';
  $oFCKeditor->Width  = '100%' ;
  $oFCKeditor->Height = '200';
  $oFCKeditor->ToolbarSet = 'Basic';
  $disp_str .= $oFCKeditor->CreateHtml();
//  $oFCKeditor->Create() ;

  $disp_str .= "
      <br>
      <input type=\"submit\" value=\"Add comment\" class=\"button\" />
    </form>
";

  /* Display the posted comments */
  $disp_str .= "
<div id=\"comments\">\n
";

  $disp_str .= "</div> <!-- comments -->\n";

  return($disp_str);
}

function graph_category($category)
{
  return("<category name='" . $category['start_date'] . "' />");
}

/*
 * Function to display the navigation tabs for each team that the user is
 * a member of.
 */
function navtabs()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  /* Get the user's teams */
  $uteams = $db->query($query_ary['user_teamlist_active'],
                       array($_SESSION['uid']));

  /* Trap common error conditions */
  if(!$uteams) { return("Teams navigation tab error.<br />\n"); }

  $navstr = "<ul>";
  $tcnt = 0;
  while($uteams->fetchInto($tab))
  {
    $class = "";

    $tab_text = $tab['user_league_name']; /* What to display in the tab */

    /* Default to first tab if nothing specified in URL (GET) */
    if(!$tcnt && !$_SESSION['tabnum']) {
      $class = " class=\"selected\"";
    }
    else
    {
      if($_SESSION['tabnum'] == $tab['user_league_id'])
      {
        $class = " class=\"selected\"";
      }
      else /* Display a link to subscribed league */
      {
        $tab_text = "<a href=\"?action=team" .
                    "&tabnum=" . $tab['user_league_id'] .
                    "\" class=\"leaguetab\">" .
                    $tab_text . "</a>";
      }
    }
    $navstr .= "<li" . $class . ">" . $tab_text . "</li>\n";

    ++$tcnt;
  }
  $uteams->free();

  $navstr .= "</ul>\n";

  return($navstr);
}
?>
