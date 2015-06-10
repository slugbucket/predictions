<?php
/*
 * Section for managing fixture records
 *
 * Functions:
 * show()
 * navtabs()
 * list_fixture_sets()
 * fixture_set_details() - form to display the fixture set details
 * is_valid_fixture_set() - verify fixture set id is in the database
 * verify_fixture_set_dates() - verify the submitted start/stop date
 * fixture_list_form() - display a list of fixtures for a fixture_set
 * fixture_set_form() - update/insert fixture set
 * fixtures_validate() - validate subitted fixtures
 * set_fixture() - update/add fixture records
 * delete_fixture() - delete fixture results and predictions
 * del_fixture_set() - delete fixture set and associated fixtures (predictions?)
 */

/*
 * Driver controlling the actions of the fixture records section
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

  $disp_str = "<p>Add and update fixtures for the managed leagues.</p>\n";

  /* Figure out which actions to perform */
  if(isset($_GET['setid']))
  {
    $disp_str .= "Return to <a href=\"?action=fixtures\">fixture set</a>" .
                 " list.<br />\n";
    $disp_str .= fixture_set_details($_GET['setid']);

  } else {
	  $disp_str .= list_fixture_sets();
  }

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
  $navstr = "<ul><li class=\"selected\">Fixtures<li></ul>\n";

  return($navstr);
}

/* Function to format the header text for a month's worth of fixture sets
 * @params:
 *  $box_id: unique id for the expand/contract link
 *  $month: Text for the month
 *  $year: Text for the year
 * @returns
 *  string containing hTML for the expand/contract links
 */
function format_excol_hdr($box_id = "0", $month = "", $year = "")
{
  $disp_str = 
             "<li>" .
             "<span class=\"contract\" id=\"" . $box_id . "_c\">" .
             "<a href=\"javascript:expand_contract('" . $box_id . "', 1);\">" .
             $month . " " . $year . "</a>" .
             "</span><br />" .
             "<span class=\"expand\" id=\"" . $box_id . "_e\">" .
             "<a href=\"javascript:expand_contract('" . $box_id . "', 0);\">" .
             $month . " " . $year . "</a>" .
             "</a>\n" .
             "<table cellpadding=\"0\" cellspacing=\"0\" class=\"tableview\">" .
             "<tr>" .
             "<th>&nbsp;</th><th>Start</th><th>End</th><th>League</th><th>Count</th><th>&nbsp;</th><th>Delete</th>" .
             "</tr>\n";

  return($disp_str);
}

/*
 * Function to display the current fixture sets and prompt for a new set
 * of fixtures.
 * @params:
 *  none
 * @returns:
 *  string containing HTML to display
 */
function list_fixture_sets()
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "
<p>Update or delete a set of fixtures for a league.
<br />
Click on a date to display the fixture sets for that month.
</p>\n";

  /* List the current fixture sets */
  $f_res = $db->query($query_ary['fixture_set_list']);

  $endclass = "dark";

  $disp_str .= "
<!-- div id=\"predictions\" -->
<form action=\"" . $_SERVER['REQUEST_URI'] . "&setid=0\" method=\"post\" name=\"newfset\">
<div id=\"fixturesets\">
<ul>\n";

  $curr_month = "";
  while($f_res->fetchInto($f_row))
  {

    if($f_row['month'] != $curr_month)
    {
      /* Close the previous table if this is not the first one */
      if($curr_month)
      {
        $disp_str .= "</table>\n</span>\n</li>\n";
      }

      $curr_month = $f_row['month'];
      $disp_str .= format_excol_hdr($f_row['set_id'], $f_row['month'], $f_row['year']);
    }

    /* Make the edit link */
    $edit_link = "?action=fixtures&setid=" . $f_row['set_id'];
    /* Create a delete checkbox */
    $delbox = new HTML_QuickForm_checkbox("del".$f_row['set_id'], 'yes', '',
                                          array('class' => 'checkbox'));

    $disp_str .=
 "<tr>" .
 "<td class=\"pointless\">&nbsp;</td>" .
 "<td class=\"$endclass\">" . $f_row['start'] . "</td>" .
 "<td>" . $f_row['end'] . "</td>" .
 "<td>" . $f_row['league_name'] . "</td>" .
 "<td>" . $f_row['num_fixtures'] . "</td>" .
 "<td>" . "<a href=\"" . $edit_link . "\">Edit</a>" . "</td>" .
 "<td style=\"padding-left: 0.8em\">" . $delbox->toHtml() . "</td>" .
"</tr>\n";

    $endclass = ($endclass == "dark" ? "light" :"dark");

  }
  /* Close the previous table if this is not the first one */
  if($curr_month)
  {
    $disp_str .= "</table>\n</span>\n</li>\n";
  }
  $disp_str .= "</ul>\n</div>\n";

  $f_res->free();

  $disp_str .= "</table>\n";

  /* Add a 'new;' button */
  $disp_str .= "
<input type=\"submit\" name=\"newfset\" value=\"New fixture set\" class=\"button\" />
<input type=\"submit\" name=\"delfset\" value=\"Delete selected\" class=\"button\" />
</form>
";

  $disp_str .= "<!-- /div --><!-- predictions -->\n";

  return($disp_str);
}

/*
 * Function to display the form for a new fixture set including:
 *  start date
 *  end date
 *  league_id
 * After adding
 * @params:
 *  set_id: if 0 display blank form, otherwise show details
 * @returns:
 *  string containing HTML form for new fixture set
 */
function fixture_set_details($set_id = "0")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  /* Check for form submission.
   * Display the form with the fixtures list if we receive a submit from
   * this form.
   * FIXME: Check the returned output for ^ERROR: and only return the output
   * if ERORR: is not present.
   */
  if($_POST['newfset'] == "Fixture set")
  {
#    return(fixture_set_form($set_id));
    $err = fixture_set_form($set_id);
    if(substr($err, 0, 6) == "ERROR:")
    {
       $disp_str .= error_message($err);
    } else
    {
      $disp_str .= $err;
      return($disp_str);
    }
  }
  if($_POST['addfset'] == "Save fixtures")
  {

      $err = set_fixture($set_id);
      if(substr($err, 0, 6) == "ERROR:")
      {

       $disp_str .= error_message($err);
       $err = fixture_list_form($set_id);
       if(substr($err, 0, 6) == "ERROR:") {
         $disp_str .= error_message($err);
         return($disp_str);
       }
      } else
      {
        $disp_str .= $err . list_fixture_sets();
      }
      return($disp_str);
  } elseif($_POST['fixturesdel'] == "Delete selected")
  {

    /* Hack through the POST vars that start with del and extract the
     * attached id field and remove the fixture, result and prediction
     */
    foreach($_POST as $postvar => $postval)
    {

      if(substr($postvar, 0, 3) == "del")
      {
        $fid = substr($postvar, 3);
        $err = delete_fixture($set_id, $fid);
        if(substr($err, 0, 6) == "ERROR:")
        {
          $disp_str .= error_message($err);
        } else
        {
          $disp_str .= $err . list_fixture_sets();
        }
      }
    }
    return($disp_str);
  }
  if($_POST['delfset'] == "Delete selected")
  {
      $del_fs      .= del_fixture_set();
      if(preg_match("/^ERROR:/", $del_fs))
      {
         $disp_str .= error_message($del_fs);
         $disp_str .= fixture_set_form($set_id);
      } else
      {
        $disp_str  .= list_fixture_sets();
      }
      return($disp_str);
  }

  $disp_str .= "
<p>
Select the start and end date for the upcoming fixtures for a given league.<br />
There is a maximum of 6 days between the start and end date of the fixture set
and the number of fixtures must be nor more than half the number of teams in
the league. A default maximum number of games possible for the league will be
shown if JavaScript is enabled.
<br />
Just select the start date if all fixtures are on the same day.
</p>
";

  /* Default values for form */
  $start_ary = array();
  $end_ary   = array();
  $league_id = 1;
  $num_fixt  = 10;
  $def_mt    = "league"; /* Default match type */
  $evko      = ''; /* Evening kickoff */

  if($set_id) /* Extract the details from the database before displaying */
  {
    $fs = $db->getRow($query_ary['fixtures_by_setid'],
                      array($set_id));
    if(!count($fs))
    { /* Fixture set has no fixtures */
      $fs = $db->getRow($query_ary['empty_fixture_set'],
                        array($set_id));
    }

    $sdstr = $fs['start_year'] . '-' . $fs['start_month'] . '-' . $fs['start_day'];
    $edstr = $fs['end_year'] . '-' . $fs['end_month'] . '-' . $fs['end_day'];
    $league_id = $fs['league_id'];

    $num_fixt  = $fs['num_fixtures'];

    /* Get the default match type if no match type can be determined from
     * previously submitted fixtures
     */
    $def_mt = $db->fetchOne($query_ary['get_fixture_set_match_type'], 
                          $set_id);
    if(!$def_mt)
    {
      $def_mt = $db->fetchOne($query_ary['get_default_match_type'], 
                            array($league_id));
    }

  }
  
  /* Check for form submitted values to be redisplayed */
  if($_POST['leagues'])      { $league_id = $_POST['leagues'];      }
  if($_POST['startdate'])    { $sdstr     = $_POST['startdate'];    }
  if($_POST['enddate'])      { $edstr     = $_POST['enddate'];      }
  if($_POST['num_fixtures']) { $num_fixt  = $_POST['num_fixtures']; }
  if($_POST['matchtype'])    { $def_mt    = $_POST['matchtype'];    }
  if($_POST['evening'])      { $evko      = $_POST['evening'];      }

  $disp_str .= "<fieldset><legend>Fixture set details</legend>\n" .
              "<dl>\n";

  /* Show the form for the requested fixture set */
  $disp_str .= "<form action=\"" . $_SERVER['REQUEST_URI'] . "\" " .
  "method=\"post\" name=\"fset_details\" onsubmit=\"javascript:sameday();\">\n";

   $disp_str .=<<<EOF
   <dd>
<div id="cal1Container"></div>
<div id="dates">
    <p><label for="startdate">Start date:</label><input type="text" name="startdate" id="startdate" value="$sdstr"></p>
    <p><label for="enddate">End date:</label><input type="text" name="enddate" id="enddate" value="$edstr" /></p>
</div>
</dd>
EOF;

  $disp_str .= "<div style=\"clear: both\">\n";

  $attrs = array("onchange" => "javascript:sndReq()");
  $disp_str .= "<dt><label for=\"leagues\">League</label>" .
               "<dd>" . league_select($league_id, $attrs) . "</dd>\n";

  /* Display the match type dropdown */
  $disp_str .= "<dt><label for=\"matchtype\">Match type</label>" .
               "<dd>" . match_type_select("matchtype", $def_mt, $attrs) . "</dd>\n";

  $nfix = new HTML_QuickForm_text(
                'num_fixtures',
                'Number of fixtures',
                array('value'     => $num_fixt,
                      'size'      => '4',
                      'maxlength' => '4',
                      'id'        => 'num_fixtures'
                     ));

  $ko = new HTML_QuickForm_checkbox("evening", $evko);
  $ko->setChecked($evko);

  $disp_str .= "<dt><label for=\"num_fixtures\">Number of fixtures</label>" .
               "<dd>" . $nfix->toHtml() . $ko->toHtml() . "Evening kickoff</dd>\n";

  $disp_str .= "<input type=\"submit\" name=\"newfset\" value=\"Fixture set\" class=\"button\" />\n";
  $disp_str .= "<input type=\"reset\" name=\"reset\" value=\"Undo changes\" class=\"button\" />\n";
 
  $disp_str .= "</form>\n</dl>\n</fieldset>\n";
  $disp_str .= "</div>\n";

  return($disp_str);
}

/*
 * Function to verify that a requested fixture set is in the database
 * @params:
 *  $set_id: the id of the requested fixture set
 * @returns:
 *  number of fixture sets matching the requested id
 */
function is_valid_fixture_set($set_id = '0')
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  return($db->fetchOne($query_ary['valid_fixture_set'], array($set_id)));
}

/*
 * Function to verify that the start_date and end_date are valid (no more
 * than 6 days between; containin 10 characters and return an array
 * containing a set of valid dates that can be used in the fixture list
 * selection form
 * @params:
 *  $start_date: array with year, month and day values for date of first match
 *  $end_date: array with year, month and day values for date of last match
 * @returns:
 *  if valid, empty string
 *  not valid, string containing appropriate error message
 */
function verify_fixture_set_dates($sdary = array(), $edary = array())
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!is_array($sdary) || !is_array($edary)) {
		  return("ERROR: Missing start or end date.");
  }
  $sdstr = implode('-', $sdary);
  $edstr = implode('-', $edary);
   
  if(!checkdate($sdary[1], $sdary[2], $sdary[0])) {
    return("ERROR:Incorrectly formatted start date: " .  $sdstr);
  }
  if(!checkdate($edary[1], $edary[2], $edary[0]))
  {
    return("ERROR:Incorrectly formatted end date: " . $edstr);
  }

  /* Check for start_date later than end_date */
  if($sdstr > $edstr)
  { return("ERROR:start date must be before end date."); }

  /* Check for excessive gap between start and end date */
  $max_end = mktime(0, 0, 0, $sdary[1],
                             $sdary[2]+6,
                             $sdary[0]);
  if(strtotime( $edstr) > $max_end)
  {
    return("ERROR:End date cannot be more than 6 days after the start.");
  }

  /* Check that start and end date fall with the season limits for the league */
  if(!$db->fetchOne($query_ary['fixture_set_season'],
                  array( $sdstr, $edstr)))
  {
    return("ERROR:Requested start and end date are outside a season boundary.");
  }

  return("");
}

/*
 * Function to display a list of fixtures. If submitted form data does not
 * contain 'home' and 'away' elements, fixture id's are drawn from the
 * fixtures sequence; if they are present, the form values are drawn from
 * the submitted data.
 * @params:
 * $set_id: the id of the fixture set
 *  $fixlist: optional array of fixtures that are already in the database
 * @returns:
 *  string containing HTML form
 */
function fixture_list_form($set_id="0", $fix_list = "")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  /* Get the details of the fixture set */
  $fset_res = $db->query($query_ary['fixtureset_details'], array($set_id));

  $fset_res->fetchInto($fset_dtls);

  $num_fixt   = $fset_dtls['num_fixtures'];
  $lge_id     = $fset_dtls['league_id'];
  $sdate_str  = $fset_dtls['start_day'] . '-' .
                 $fset_dtls['start_month'] . '-' .
		 $fset_dtls['start_year'];
  $edate_str  = $fset_dtls['end_day'] . '-' .
                 $fset_dtls['end_month'] . '-' .
		 $fset_dtls['end_year'];
  $fdate_str  = $fset_dtls['full_date'];
  /* Create default values for the blank fixtures including kickoof
   * for evening fixtures and a default date of a saturday when the set
   * starts (but doesn't end) on a Friday.
   */
  $def_date   = $db->fetchOne($query_ary['default_match_date'], array($set_id));
  if(PEAR::isError($def_date))
  {
    $disp_str .= "<span class=\"errmsg\">Default match date error for fixture set, $set_id: " . $def_date->getMessage() . ".</span><br />\n";
    $def_date = $sdate_str;
  }
   
  /*
   * Check the number of requested fixtures is not greater than the number
   * teams in the league
   */
  $lge_tm_cnt = $db->fetchOne($query_ary['league_team_count'], array($lge_id));

  if(2 * $num_fixt > $lge_tm_cnt)
  {
    $disp_str .= "Warning: Not enough teams for requested fixture count.";
    $num_fixt  = $lge_tm_cnt / 2;
  }
  if($_POST['matchtype'] == "playoff")
  {

    /* Check that all the fixtures in the league have been played */
    $mcnt = $db->fetchOne($query_ary['all_matches_played'],
                        array($lge_id, $lge_id));
    if(PEAR::isError($mcnt)) {
     return("ERROR:all matches played before playoff query failed:\n" .
            $mcnt->getMessage());
    }                  
    if(!$mcnt)
    {
      return("ERROR:Cannot select playoff fixtures before all matches for the season have been played.");
    }

    /* The the start position and number of teams in the playoff places */
    $pl_count = $db->fetchOne($query_ary['get_playoff_count'], array($lge_id));
    if($num_fixt > ($pl_count / 2))
    {

      /* Update the number of fixtures recorded in the fixture set */
      $num_fixt = $pl_count / 2;
      $db->query($query_ary['update_fset_num_fixtures'],
                 array($num_fixt, $set_id));
    }

  }

  /* try and get a list of fixtures from submitted form data (checking that
   * the requested teams are in the suggested league).
   * Alternatively, get a list of fixtures from the database and if they don't
   * exist, generate a set of fixtures from the fixtures sequence.
   */
  if(count($fix_list) > $num_fixt) { $num_fixt = count($fix_list); }
  $fixture_ary = array();

  /* Check the submitted form data. This is for re-display after an error
   * thrown by fixtures_validate()
   */
  if($_POST['addfset'] == "Save fixtures")
  {

    $fix_cnt = 0;
    foreach($_POST as $postvar => $postval)
    {

      /* Home team */
      if(substr($postvar, 0, 5) == "hteam") {
      {
         $fid = substr($postvar, 5);}
         $fixture_ary[$fix_cnt]['fid']        = $fid;
         $fixture_ary[$fix_cnt]['home_team']  = $_POST['hteam'.$fid];
         $fixture_ary[$fix_cnt]['away_team']  = $_POST['ateam'.$fid];
         $fixture_ary[$fix_cnt]['match_date'] = $_POST['fixture_date'.$fid];
         $fixture_ary[$fix_cnt]['kickoff']    = $_POST['kickoff'.$fid];
  
         ++$fix_cnt;
      }
    }
  }
  elseif(count($fix_list))
  {

    /* Check that the number of fixtures in the list matches the number
     * in the fixture set definition. Flag a warning if there is a difference
     */
    if(count($fix_list) != $num_fixt)
    {
      $disp_str .= "<span class=\"errmsg\">Warning: the number of available fixtures for this set does not match the fixture set value.</span><br />\n";
    }

    /* Hack through each of the requested fixtures and if a database record
     * is found, then copy the row into the fixture array. If not, then assign
     * a fixture id for display in the form from the fixtures sequence
     */
    for($i=0; $i<$num_fixt; $i++)
    {
      if($fix_list[$i])
      {
        $fixture_ary[$i] = $fix_list[$i];
      } else {
        $fixture_ary[$i]['fid'] = $db->nextId('fixtures');
      }
    }
    /* Not sure why this is here - jur/Jan 2010
    $num_fixt = count($fix_list);
     */

  }
  else
  {
    for($i=0; $i<$num_fixt; $i++)
    {
       $fixture_ary[$i]['fid'] = $db->nextId('fixtures');
       $fixture_ary[$i]['match_date'] = $def_date;
       if($_POST['evening'])
       {
         $fixture_ary[$i]['kickoff'] = "19:45:00";
       }
    }
  }

  /* Prepare the form for display */
  $disp_str .= "<form action=\"?action=fixtures&setid=" . $set_id . "\" " .
               "method=\"post\" name=\"fset_fixtures\">\n";

  /* Add the league_id as a hidden field */
  $hlge = new HTML_QuickForm_hidden('league_id', $lge_id);
  $disp_str .= $hlge->toHtml() . "\n";

  /* Match type - hidden so that it can be applied to submitted matches */
  $h_m_type   = new HTML_QuickForm_hidden('matchtype', $_POST['matchtype']);
  $disp_str .= $h_m_type->toHtml() . "\n";

  if($num_fixt)
  {
    $disp_str .= "<table id=\"predictions\" cellpadding=\"0\" cellspacing=\"0\">\n";

    $taggle = 0;    /* Alternate color of displayed rows */

    $disp_str .= "<tr class=\"header\">" .
                 "<td colspan=\"5\">" .
                 "<strong>" .
                   $db->fetchOne($query_ary['league_name'],
                               array($lge_id)) .
                 "</strong>" .
                 "</td>" .
                 "</tr>\n";

    /* Display a header showing what data is being requested */
    $disp_str .= "<tr class=\"greyrow\">" .
                 "<td>Date</td>" .
                 "<td>Home team</td>" .
                 "<td>Away team</td>" .
                 "<td>Kick-off</td>" .
                 "<td>Delete</td>" .
                 "</tr>\n";

    for($i=0; $i<$num_fixt; ++$i)
    {
      $home_id = "hteam";
      $away_id = "ateam";
      if($fixture_ary[$i]['fid']) {
        $home_id .= $fixture_ary[$i]['fid'];
        $hsel     = $fixture_ary[$i]['home_team'];
        $away_id .= $fixture_ary[$i]['fid'];
        $asel     = $fixture_ary[$i]['away_team'];
        $mdate    = $fixture_ary[$i]['match_date'];
        $kickoff  = $fixture_ary[$i]['kickoff'];
      } else {
        $disp_str .= "<tr><td colspan=\"4\">Error:MIssing fixture id</td></tr>\n";
        continue;
      }

      /* Extract the submitted team details if they exist */
      if($_POST[$home_id]) {
        $hsel = $_POST[$home_id];
      }
      if($_POST[$away_id]) {
        $asel = $_POST[$away_id];
      }

      /* Get the fixture date for display. Fix the match date to use a
       * specific time so that autumn daylight saving time doesn't prevent
       * the same date being shown over a DST changeover
       */
      /* If the start and end date are the same just display the date
       * in a disabled text box and don't include the up-down date
       * selector images to the right and left.
       */
      $f = $fixture_ary[$i]['fid'];
      if( $sdate_str != $edate_str) {
        $fixt_dates = fixture_date_select("fixture_date".$fixture_ary[$i]['fid'],
                                          $sdate_str,
                                          $edate_str,
                                          $mdate);
        $date_field = button_submit($f, "l_arrow.png", "chng_date('m', '$f')") .
                      $fixt_dates .
                      button_submit($f, "r_arrow.png", "chng_date('p', '$f')");
      } else {
        $hdte = new HTML_QuickForm_hidden("fixture_date".$fixture_ary[$i]['fid'], $def_date);
        $date_field = $hdte->toHtml() . "\n";
        $date_field .= "<span class=\"static_date\">" . $fdate_str . "</span>";
      }

      $ko_times = kickoff_select_list("kickoff".$fixture_ary[$i]['fid'],
                                      $kickoff);

      /* Check whether a playoff team select box should be displayed rather
       * than a full team list
       */
      $home_list = league_team_select($home_id, $lge_id, $hsel, array("class" => "teamlist"));
      $away_list = league_team_select($away_id, $lge_id, $asel, array("class" => "teamlist"));

      /* Get the fixture type to trap playoff matches */
      $mt = $db->fetchOne($query_ary['get_fixture_match_type'],
                        array($fixture_ary[$i]['fid']));

      /* If this is a playoff then get the teams in the playoff positions
       * and also limit the number of matches to be played
       */
      if($mt == "playoff" || $_POST['matchtype'] == "playoff")
      {

         $home_list = playoff_team_select($home_id, $lge_id, $hsel);
         $away_list = playoff_team_select($away_id, $lge_id, $asel);
      }

      /* Add a fixture delete checkbox but only include it in the form
       * if the fixture already exists
       */
      $dbox = new HTML_QuickForm_checkbox("del".$fixture_ary[$i]['fid'], '');
      if($db->fetchOne($query_ary['does_fixture_exist'],
                     array($fixture_ary[$i]['fid'])))
      {
        $dbh = $dbox->toHtml();
      } else {
        $dbh = "&nbsp;&nbsp;---";
      }

      $toggle = 0;
      /* Check that the selected teams are from the requested league */
      $disp_str .= "<tr>" .
                   "<td>" .
                   $date_field .
                   "</td>" .
                   "<td>" . $home_list  . "</td>" .
                   "<td>" . $away_list  . "</td>" .
                   "<td>" . $ko_times   . "</td>" .
                   "<td>" . $dbh        . "</td>" .
                   "</tr>\n";
    }

    $disp_str .= "</table>\n";
  }


  $disp_str .= "<input type=\"submit\" name=\"addfset\" value=\"Save fixtures\" class=\"button\" />";
  $disp_str .= "<input type=\"reset\" name=\"reset\" value=\"Undo changes\" class=\"button\" />";
  $disp_str .= "<input type=\"submit\" name=\"fixturesdel\" value=\"Delete selected\" class=\"button\" />";
  $disp_str .= "</form>\n";

  return($disp_str);
}

/*
 * Function to update an existing or save a new fixture set record giving
 * the start and stop date for a group of matches in a given league.
 * Also displays a form requesting the times and dates of the kick-off of
 * each match to be included in this set.
 * @params:
 *  $set_id: 0 for a new set, >0 for an existing set
 * @returns:
 *  string containg HTML representation of the form
 */
function fixture_set_form($set_id = "0")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "Set the start and end date for a set of league fixtures.<br />\n";

  /*
   * Extract fixture set details from the posted form
   *  $set_id: 0 for a new set, >0 for an existing set
   *  $num_fixtures: the number of fixture detials to list
   *  $league_id: the league from which team names should be displayed
   *  $start_day, $start_month, $start_year: default date for matches
   *  $end_day, $end_month, $end_year: end date for set of fixtures
   */
  $num_fixt   = $_POST['num_fixtures'];
  $league_id  = $_POST['leagues'];
  
  $sdate_str  = $_POST['startdate']; /* In the form 'YYYY-MM-DD' */
  $edate_str  = $_POST['enddate'];   /* In the form 'YYYY-MM-DD' */
 
  $start_date = array(substr($sdate_str, 0, 4),
                      substr($sdate_str, 5, 2),
                      substr($sdate_str, 8, 2));

  $end_date = array(substr($edate_str, 0, 4),
                    substr($edate_str, 5, 2),
                    substr($edate_str, 8, 2));

  /* Check validity of dates
   */
  $dres = verify_fixture_set_dates($start_date, $end_date);
  if($dres) { return($dres); }

  /* Trap overlapping fixture sets */
  if($db->fetchOne($query_ary['overlapping_fsets'],
                 array($sdate_str, $sdate_str, $edate_str, $edate_str,
                       $sdate_str, $edate_str, $sdate_str, $edate_str,
                       $league_id, $set_id)) )
  {
      return("ERROR:Start or end date conflicts with existing fixture set.");
  }
  /* Make sure all the make sure all the fixtures have been played
  
  /* If set_id posted, check that the id is valid and then update the
   * existing record
   */
  $fixture_ary = array();
  if($set_id)
  {
    if(!is_valid_fixture_set($set_id))
    {
      return("ERROR:Invalid fixture set requested.");
    }

    /* Update the existing fixture set */
    $db->query($query_ary['update_fixture_set'],
               array($sdate_str,
                     $edate_str,
                     $league_id,
                     $num_fixt,
                     $set_id));

    /* Get the fixtures that have been previously been saved for this set */
    $flist = $db->query($query_ary['fixture_set_match_list'],
                        array($sdate_str, $edate_str, $league_id));

    /* Hack through each of the requested fixtures and if a database record
     * is found, then copy the row into the fixture array. If not, then assign
     * a fixture id for display in the form from the fixtures sequence
     */

    for($i=0; $i<$flist->numRows(); $i++)
    {
      if($flist->fetchInto($frow)) { $fixture_ary[$i] = $frow; }
    }

  } else /* Insert a new fixture set entry */
  {

    /* Get a set_id from the fixture_set sequence */
    $set_id = $db->nextId('fixture_set');
    $db->query($query_ary['insert_fixture_set'],
               array($set_id,
                     $sdate_str,
                     $edate_str,
                     $league_id,
                     $num_fixt));

  }

  $disp_str .= fixture_list_form($set_id, $fixture_ary);

  return($disp_str);
}

/*
 * Function to validate a submitted fixture
 * This needs to verify the following:
 *  - a team can only be selected once
 *  - that each team belongs to the requested league
 * @params:
 *  $fid: fixture id
 *  $hid: id of home team
 *  $aid: id of away team
 *  $lid: id of league for the fixture
 * @returns:
 *  null if fixture is valid, string detailing error if not valid
 */
function fixtures_validate($fid="0", $hid="0", $aid="0", $lid="0")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  /* Are the requested teams in the submitted league */
  if(!is_team_in_league($lid, $hid))
  { return("ERROR:Home team is not in the requested league.");}

  if(!$db->fetchOne($query_ary['is_team_in_league'], array($lid, $aid)))
  { return("ERROR:Away team is not in the requested league.");}

  /* Check if home or away teams have been submitted more than once */
  $hcnt = 0;
  $acnt = 0;
  foreach($_POST as $postvar => $postval)
  {

    if(substr($postvar, 0, 5) == "hteam" && $postval == $hid) {$hcnt++;}
    if(substr($postvar, 0, 5) == "ateam" && $postval == $aid) {$acnt++;}

    /* Detect a team being entered as both home and away */
    if(substr($postvar, 0, 5) == "ateam" && $postval == $hid)
    {
      return("ERROR:Duplicate team found in " .
             $db->fetchOne($query_ary['team_name'], array($hid)) . " vs. " .
             $db->fetchOne($query_ary['team_name'], array($aid)) . ".");
    }
 
  }

  if($hcnt > 1)
  {
    return("ERROR:Home team " .
           $db->fetchOne($query_ary['team_name'], array($hid)) .
           " entered in more than one fixture.<br />\n");
  }
  if($acnt > 1)
  {
    return("ERROR:Away team " .
           $db->fetchOne($query_ary['team_name'], array($aid)) .
           " entered in more than one fixture.");
  }

  return(null);
}

/*
 * Function to add new or update existing fixtures for the prediction list.
 * This needs to verify the following:
 *  - that the fixture set is valid
 *  - that the requested fixture date is with fixture set bounds
 * @params:
 *  $set_id: the id of the fixture_set
 * @returns:
 *  String indicating the success (or otherwise of the insert)
 */
function set_fixture($set_id = "0")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "Saving fixture set.<br />\n";

  /* Check through the posted variables looking for fixture details  */
  foreach($_POST as $postvar => $postval)
  {
    if(substr($postvar, 0, 5) == "hteam") /* Extract the relevant match id */
    {
      $fid     = substr($postvar, 5);
      $hteam   = $_POST['hteam'.$fid];
      $hname   = $db->fetchOne($query_ary['team_name'], array($hteam));
      $ateam   = $_POST['ateam'.$fid];
      $aname   = $db->fetchOne($query_ary['team_name'], array($ateam));
      $kickoff = $_POST['kickoff'.$fid];
      $fdate   = $_POST['fixture_date'.$fid];
      $lge_id  = $_POST['league_id'];
      $mtype   = $_POST['matchtype'];

      /* Bomb out if the fixture is not valid */
      $valid_fixt = fixtures_validate($fid, $hteam, $ateam, $lge_id);
      if($valid_fixt) {return($valid_fixt);}

      /* If fixture exists run an update query */
      $isfixt = $db->fetchOne($query_ary['does_fixture_exist'], array($fid));

      $now = now_by_timezone($lge_id);
      if($isfixt) /* The value may be zero */
      {
        $f_qry = $query_ary['update_fixture'];
        $f_ary = array($fdate, $kickoff, $mtype, $hteam, $ateam, $now, $fid);
      } else /* Insert a new fixture */
      {
        $f_qry = $query_ary['add_new_fixture'];
        $f_ary = array($fid, $lge_id, $fdate, $kickoff,
                       $hteam, $ateam, $mtype, $now, $now);
      }

      $res = $db->query($f_qry, $f_ary);
      if(PEAR::isError($res))
      {
        $disp_str .= "ERROR:Failed to add $hname vs. $aname to the database.<br />\n" . $res->getMessage() . ".<br />\n";
      } else
      {
        $disp_str .= "Successfully added $mtype $hname vs. $aname to the database.<br />\n";
      }
      
    }
  }

  /* Check for orphaned fixture sets and fixtures */
  $of = $db->query($query_ary['get_orphaned_fsets'], array($lge_id, $lge_id));
  if($of->numRows())
  {
    $disp_str .= "Need to remove fixture sets without matching fixtures.<br />";
    while($of->fetchInto($orphan))
    {
      $disp_str .= del_fixture_set($orphan['orphaned_fset']);
    }
    $of->free;
  }
  /* Check for orphaned fixtures */
  $of = $db->query($query_ary['get_orphaned_fixtures'],
                   array($lge_id, $lge_id));
  if($of->numRows())
  {
    $disp_str .= "Need to remove fixtures without matching fixture sets.<br />";
    while($of->fetchInto($orphan))
    {
      $db->query($query_ary['delete_fixture'], $orphan['id']);
    }
    $of->free;
  }

  return($disp_str);
}

/* Function to remove an individual fixture. This will result in the removal
 * of predictions and results for the particular fixture. This operation
 * should not reduce the fixture_set.num_fixtures columns because that should
 * be controlled when defining the fixture set.
 * @params:
 *  $set_id: id the id of the set that the fixture belongs to
 *  $fid: id of fixture to be removed
 * @returns:
 *  string indicating the success or otherwise of the operation
 */
function delete_fixture($set_id, $fid)
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if(!$set_id)   { return("ERROR:Missing fixture set id."); }
  if(!$fid)      { return("ERROR:Missing id for fixture."); }

  /* Verify that the fixture is covered by the fixture set */
  if($db->fetchOne($query_ary['fixture_bounded_by_set'], array($fid, $set_id)))
  {
    $disp_str .= "Deleting results for fixture $fid.<br />\n";
    $db->query($query_ary['delete_results_by_fixture'], $fid);
    $disp_str .= "Deleting predictions for fixture $fid.<br />\n";
    $db->query($query_ary['delete_predictions_by_fixture'], $fid);
    $disp_str .= "Deleting fixture $fid.<br />\n";
    $db->query($query_ary['delete_fixture'], $fid);
  } else {
    $disp_str = "ERROR:Fixture $fid does not have a matching fset.";
  }
  return($disp_str);
}

/* Function to delete a fixture set entry and all fixtures from the associated
 * league. This can help with 'dangling' fixture sets that don't have fixtures
 * added when the fixture set was created.
 * @params:
 *  $set_id: id of fixture set (identified as an orphan) to delete. If not
 *  given, POST data examined for requested sets.
 * @returns:
 *  string indicating the success or otherwise of the operation
 */
function del_fixture_set($set_id = "0")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  if($set_id) {
    $db->query($query_ary['del_fixture_set'], $set_id);
    return("Deleting orphan fixture set $set_id.<br />\n");
  }

  /* Check through the posted variables looking for deleted fixture sets  */
  foreach($_POST as $postvar => $postval)
  {
    if(substr($postvar, 0, 3) == "del") /* Extract the relevant fset id */
    {
      $fset_id = substr($postvar, 3);

      $lid = $db->fetchOne($query_ary['fixture_set_league_id'], $fset_id);
      $now = now_by_timezone($lid);
      /* Delete the fixtures and fixture_set */
      if(is_numeric($fset_id) &&
         $postval             &&
         is_valid_fixture_set($fset_id))
      {

        $db->query($query_ary['fixture_set_fixtures_delete'],
                   array($fset_id, $now));
        $db->query($query_ary['del_fixture_set'], array($fset_id));

        $disp_str .= "Deleted fixture set $fset_id.<br />\n";

      }
    }
  }

  return($disp_str);
}
?>
