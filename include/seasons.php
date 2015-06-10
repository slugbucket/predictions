<?php
/*
 * show() - Main driver
 * navtabs() - Top navigation items
 * season_details_form() - display season details form
 * add_new_season_form() - save a new season
 * can_season_be_archived() - check date before archiving
 * seasons_insert() - Insert details of a new season
 * seasons_update() - update details of a season
 * archive_season() - archive a season's fixtures, results and predictions
 */
/*
 * Driver controlling the actions of the seasons
 * @params:
 *  none
 * @returns:
 *  string containing HTML to display on the page
 */
function show()
{
  if(!isadmin()) { return("ERROR:This is an admin-only operation."); }

  $disp_str = "Define new seasons and archive the previous season's data.<br />";

  /* Check for new season request */
  if($_POST['newseason'] == "New season")
  {
    $disp_str .= add_new_season_form('0');
  }

  /* Check for new season submission */
  elseif($_POST['season'] == "Save season")
  {
    $disp_str .= seasons_insert();
  }

  /* Check for season update */
  elseif($_POST['season'] == "Update season")
  {
    $disp_str .= seasons_update();
  }
  else
  {
    $disp_str .= season_details_form($_SESSION['tabnum']);
  }

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
  $ssns = mdb2_query('season_list');

  if(!$ssns) {return("<ul><li class=\"selected\">Seasons</li></ul>\n");}

  if($_POST['newseason'] == "New season")
  {
    return("<ul><li class=\"selected\">New season</li></ul>\n");
  }

  $navstr = "<ul>";
  $tcnt = 0;
  while($tab = $ssns->fetchRow())
  {
    $class = "";

    $tab_text = $tab['season_name']; /* What to display in the tab */
    /* Default to first tab if nothing specified in URL (GET) */
    if(!$tcnt && !$_SESSION['tabnum']) {
      $class = " class=\"selected\"";
      $_SESSION['tabnum'] = $tab['season_id'];
    }
    else
    {
      if($_SESSION['tabnum'] == $tab['season_id'])
    {
        $class = " class=\"selected\"";
      }
      else /* Display a link to subscribed league */
      {
        $tab_text = "<a href=\"?action=seasons" .
                    "&tabnum=" . $tab['season_id'] . "\">" .
                    $tab_text . "</a>";
      }
    }
    $navstr .= "<li" . $class . ">" . $tab_text . "</li>\n";

    ++$tcnt;
  }
  $ssns->free();

  $navstr .= "</ul>\n";

  return($navstr);
}

/*
 * Function to show the basic season details and offer to archive the previous
 * season's predictions, results and fixtures if we're past the season's end.
 * @parama:
 *  none but _SESSION and _POST arrays are accessed
 * @returns:
 *  string containing HTML for display
 */
function season_details_form($sid)
{
  if($sid) /* Get and existing season */
  {
    $res = mdb2_query('season_details', array($sid));
  } else {
    $res = mdb2_query('new_season_details');
  }
  $ssns = $res->fetchRow();

  if(!is_array($ssns)) {return("ERROR:Invalid season reference.");}

  $disp_str = "
<p>
Update the season details using the form below.
<br />
If the season has ended, there is the option to archive any predictions,
fixtures and results from the previous season.
</p>\n";

  /* Create the individual form elements */

  /* Season name box */
  $sname = new HTML_QuickForm_text('season_name',
                                   '',
                                   array('value'     => $ssns['season_name'],
                                         'size'      => '32',
                                         'maxlength' => '64'));

  /* Start date */
  $start_names = array('day'   => 'start_day',
                       'month' => 'start_month',
                       'year'  => 'start_year');
  $start_ary   = array('day'   => $ssns['start_day'],
                       'month' => $ssns['start_month'],
                       'year'  => $ssns['start_year']);

  $sdate = make_season_date($sid, 'start',
                            $start_names,        $start_ary);

  /* End date */
  $end_names = array('day'   => 'end_day',
                     'month' => 'end_month',
                     'year'  => 'end_year');
  $end_ary   = array('day'   => $ssns['end_day'],
                     'month' => $ssns['end_month'],
                     'year'  => $ssns['end_year']);

  $edate = make_season_date($sid, 'end',
                            $end_names,          $end_ary);

  /* Archive data checkbox */
  $arcbox = new HTML_QuickForm_checkbox('archive', 'yes');

  /* Form submission buttons */
  $sub_but = new HTML_QuickForm_submit('season', 'Update season',
                                       array('class' => 'button'));
  $new_but = new HTML_QuickForm_submit('newseason', 'New season',
                                       array('class' => 'button'));
  $res_but = new HTML_QuickForm_reset('reset', 'Reset form',
                                       array('class' => 'button'));

  $disp_str .= "<form action=\"" . $_SERVER['REQUEST_URI'] . "\" " .
               "method=\"post\" name=\"season_details\">\n";
  $disp_str .= "<fieldset><legend>" .
               $ssns['season_name'] .
               " details</legend>" .
               "<dl>";

  /* Standard form entries for every season */
  $disp_str .= "<dt>Season name</dt>" .
               "<dd>" . $sname->toHtml() . "</dd>\n" .

               "<dt>Start date</dt>" .
               "<dd>" . $sdate . "</dd>" .

               "<dt>Finish date</dt>" .
               "<dd>" . $edate . "</dd>";

  /* Check to see if an archive checkbox is appropriate */
  $lid = mdb2_fetchOne('default_subscription', $_SESSION['uid']);
  $now = now_by_timezone($lid);
  $fxcnt = mdb2_fetchOne('fixtures_in_season', array($now, $sid));
  if($fxcnt)
  {
    $disp_str .= "<dt>Archive " . $fxcnt . " fixtures</dt>" .
                 "<dd>" . $arcbox->toHtml() . "</dd>";

  } else { $disp_str .= "<br />"; }

  /* Add the buttons */
  $disp_str .= $sub_but->toHtml() . $res_but->toHtml() . "<br />" .
               $new_but->toHtml() . "\n";

  $disp_str .= "</dl>\n</fieldset>\n";
  $disp_str .= "</form>\n";

  return($disp_str);
}

/* Function to request form data for a new season: name, start and end
 * @params:
 *  none
 * @returns:
 *  string containg HTNL form for display on page
 */
function add_new_season_form($sid = '0')
{
  $disp_str .= "<p>Use this form to add a new season.</p>\n";

  $ssns = mdb2_fetchRow('new_season_details');

  /* Season name box */
  $sname = new HTML_QuickForm_text('season_name',
                                   '',
                                   array('value'     => $ssns['season_name'],
                                         'size'      => '32',
                                         'maxlength' => '64'));

  /* Start date */
  $start_names = array('day'   => 'start_day',
                       'month' => 'start_month',
                       'year'  => 'start_year');
  $start_ary   = array('day'   => $ssns['start_day'],
                       'month' => $ssns['start_month'],
                       'year'  => $ssns['start_year']);

  $sdate = make_season_date($sid, 'start',
                            $start_names,        $start_ary);

  /* End date */
  $end_names = array('day'   => 'end_day',
                     'month' => 'end_month',
                     'year'  => 'end_year');
  $end_ary   = array('day'   => $ssns['end_day'],
                     'month' => $ssns['end_month'],
                     'year'  => $ssns['end_year']);

  $edate = make_season_date($sid, 'end',
                            $end_names,          $end_ary);

  /* Form submission buttons */
  $sub_but = new HTML_QuickForm_submit('season', 'Save season',
                                       array('class' => 'button'));
  $new_but = new HTML_QuickForm_submit('newseason', 'New season',
                                       array('class' => 'button'));
  $res_but = new HTML_QuickForm_reset('reset', 'Reset form',
                                       array('class' => 'button'));

  $disp_str .= "<form action=\"" . $_SERVER['REQUEST_URI'] . "\" " .
               "method=\"post\" name=\"season\">\n";
  $disp_str .= "<fieldset><legend>" .
               $ssns['season_name'] .
               " details</legend>" .
               "<dl>";

  /* Standard form entries for every season */
  $disp_str .= "<dt>Season name</dt>" .
               "<dd>" . $sname->toHtml() . "</dd>\n" .

               "<dt>Start date</dt>" .
               "<dd>" . $sdate . "</dd>" .

               "<dt>Finish date</dt>" .
               "<dd>" . $edate . "</dd>";

  /* Add the buttons */
  $disp_str .= $sub_but->toHtml() . $res_but->toHtml() . "<br />\n";

  $disp_str .= "</dl>\n</fieldset>\n";
  $disp_str .= "</form>\n";

  return($disp_str);
}

/* Function to check that season has data that can be archived
 * @params:
 *  none but $_POST and $_SESSION are referenced
 * @returns:
 *  true if archivable, false if not
 */
function can_season_be_archived()
{
  /* Check for suitable date */
  if(date("Y-m-d") <= mdb2_fetchOne('get_season_end',
                                  array($_SESSION['tabnum'])))
  {
    return(false);
  }

  /* Check that there are fixtures to archive */
  $lid = mdb2_fetchOne('default_subscription', $_SESSION['uid']);
  $now = now_by_timezone($lid);
  if(!mdb2_fetchOne('fixtures_in_season',
                  array($now, $_SESSION['tabnum'])))
  {
    return(false);
  }

  return(true);
}

/* Function to insert the details of a new season
 * FIXME: refuse start and end change if they are earlier than today
 * @params:
 *  none but $_POST is referenced
 * @returns:
 *  string containing HTML regarding the success (or otherwise) of the operation
 */
function seasons_insert()
{
  /* Format the season update */
  if($_POST['start_year'] && $_POST['start_month'] && $_POST['start_day']) {
    $start = $_POST['start_year']  . "-" .
             $_POST['start_month'] . "-" .
             $_POST['start_day'];
  }
  if($_POST['end_year'] && $_POST['end_month'] && $_POST['end_day']) {
    $end   = $_POST['end_year']  . "-" .
             $_POST['end_month'] . "-" .
             $_POST['end_day'];
  }
  if($start && !strlen($start) == 10) {
    return("ERROR:Bad start date. Season not saved.");
  }
  if($end && !strlen($end) == 10) {
    return("ERROR:Bad end date. Season not saved.");
  }
  $sid = mdb2_fetchOne('new_season_id');
  $res = mdb2_query('seasons_insert',
                    array($sid, $_POST['season_name'], $start, $end));

  if(PEAR::isError($res))
  {
    $disp_str .= $_POST['season_name'] . " insert failed.<br />\n";
  }
  else
  {
    $disp_str .= $_POST['season_name'] . " created.<br />\n";
  }
  return($disp_str);
}

/* Funtion to update the data for an existing season, including data archive
 * FIXME: refuse start and end change if they are earlier than today
 * @params:
 *  none but $_SESSION and $_POST are referenced
 * @returns:
 *  string containing HTML regarding the success (or otherwise) of the operation
 */
function seasons_update()
{
  global $db; // Global database handle

  global $query_ary; // Database queries that can be submitted

  /* Check that today is after end_date and there are fixtures in the season */
  if($_POST['archive'])
  {
    if(can_season_be_archived())
    {
      $disp_str .= archive_season();
    }
    else
    {
      $disp_str .= "ERROR:Either no fixtures or season not finished. Not archived.";
    }
  }

  /* Format the season update */
  if($_POST['start_year'] && $_POST['start_month'] && $_POST['start_day']) {
    $start = $_POST['start_year']  . "-" .
             $_POST['start_month'] . "-" .
             $_POST['start_day'];
  }
  if($_POST['end_year'] && $_POST['end_month'] && $_POST['end_day']) {
    $end   = $_POST['end_year']  . "-" .
             $_POST['end_month'] . "-" .
             $_POST['end_day'];
  }
  if($start && !strlen($start) == 10) {
    return("ERROR:Bad start date. No update saved.");
  }
  if($end && !strlen($end) == 10) {
    return("ERROR:Bad end date. No update saved.");
  }

  /* Check for post-date start/end changes */
  $db_start = mdb2_fetchOne('get_season_start',
                          array($_SESSION['tabnum']));

  if($start && $start != $db_start && date("Y-m-d") >= $db_start)
  {
    return("ERROR:Cannot change start date after start of season.");
  }

  $db_end   = mdb2_fetchOne('get_season_end',
                          array($_SESSION['tabnum']));
  if($end && $end   != $db_end   && date("Y-m-d") >= $db_end)
  {
    return("ERROR:Cannot change end date after end of season.");
  }

  /* Get the season name */
  $sname = $_POST['season_name'];
  if(!$sname) {return("ERROR:Season must have a name.");}


  /* Update the season record */
  if($sname && $start && $end)  {
    $res = mdb2_query('seasons_update',
                      array($sname,
                            $start,
                            $end,
                            $_SESSION['tabnum']));
  }

  if(PEAR::isError($res))
  {
    $disp_str .= $sname . " update failed.<br />\n";
  }
  else
  {
    $disp_str .= $sname . " updated.<br />\n";
  }

  return($disp_str);
}

/* Function to archive away old season data.
 * Needs to verify that the requested season completed before today and
 * that there are fixtures, predictions and results to archive
 * @params:
 *  none
 * @returns:
 * string containing HTML verifying the success (or otherwise) of the operation
 */
function archive_season()
{
  global $db;
  global $query_ary;

  /* List of the archive operations to perform */
  $op_ary = array('season_predictions_copy'   => 'Store predictions',
                  'season_predictions_delete' => 'Delete predictions',
                  'season_results_copy'       => 'Store results',
                  'season_results_delete'     => 'Delete results',
                  'season_fixtures_copy'      => 'Store fixtures',
                  'season_fixtures_delete'    => 'Delete fixtures',
                  'season_fixture_set_copy'   => 'Copy fixture sets',
                  'season_fixture_set_delete' => 'Delete fixture sets',
                  'season_deductions_copy'    => 'Copy deductions',
                  'season_deductions_delete'  => 'Delete deductions',
                  'season_user_scores_copy'   => 'Archive user scores',
                  'season_user_scores_del'    => 'Delete user scores',
                  'season_league_table_copy'  => 'Copy league table',
                  'season_del_league_table'   => 'Delete league table');
/*
 */
 echo "DEBUG: season_archive: uncomment this loop.<br />\n";
  foreach($op_ary as $arch_op => $op_show)
  {
    /* $res = mdb2_query($arch_op, array($_SESSION['tabnum']));
echo "DEBUG: archive_season: Archiving with query $arch_op.<br />\n";
    if(!PEAR::isError($res)) { $disp_str .= $op_show . "<br />\n"; }
    else {
     $disp_str .= $op_show . " failed: " . error_message($res) . "<br />\n";
     return($disp_str);
    }
    */
  }

  /* Promote and relegate the teams */
  /*
   * The call to the store procedure appears not to work from PHP.
   * jur: 20120619
   */
  echo "DEBUG: archive_season: Archived fixtures, predictions and results. Now promoting and relegating.<br />\n";
  $disp_str .= "Archived fixtures, predictions and results. Now promoting and relegating.<br />\n";

  /* $res = $db->queryAll($query_ary['league_promote_relegate']); */
  $res = $db->queryAll('CALL promote_relegate');
  print_r($res);
/*  
  if ($res) {
                
                 while ($row = $res->fetchRow()) {echo $row[0];}
                 $res->free();
  }
  
  
  print_r($res);
  if(PEAR::isError($res)) {
    return($disp_str . "Promote relegate failed: " . error_message($res) . "<br />\n");
  }
  */
  while($dt = $res->fetchRow(MDB2_FETCHMODE_ORDERED)) {
  //$res->nextResult();
  
  echo "Promote: " . $dt[0] . ".<br />\n";
    $disp_str .= $dt[0] . "<br />\n";
    echo "archive_season: checking next section.<br />\n";
    
  }
  echo "archive_season: freeing promote_relegate result.<br />\n";
print_r($res);
$res->free();  




return($disp_str);




  /* Create blank league tables for the new season */
  echo "DEBUG: archive_season: Create blank league tables for new season/<br />\n";
  /* $res = mdb2_query('league_select'); */
  $ls = $db->query($query_ary['league_select']);

  #$res = $sth->execute();
  print_r($ls);
  if(PEAR::isError($ls)) {
    return($disp_str . "Query for leagues to create new table failed: " . error_message($ls) . "<br />\n");
  }
  while($dt = $ls->fetchRow()) {
    $disp_str .= "Creating blank league table for " . $dt['name'] . "<br />\n";
    echo "Creating blank league table for " . $dt['name'] . "<br />\n";
    mdb2_query('new_league_table', array($dt[id]));
  }
  return($disp_str);
}
?>
