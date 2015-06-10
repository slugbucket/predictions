<?php
/*
 * Clean up the global arrays so that slashes have been added to all incoming
 * requests
 */
function addslash( $val ) {
  return( is_array( $val ) ? array_map( 'Addslash', $val ) : addslashes( $val
) );
}
function addslashGpc () {
  foreach( array('POST', 'GET', 'REQUEST', 'COOKIE' ) as $gpc ) {
    $GLOBALS["_$gpc"] = array_map( 'addslash', $GLOBALS["_$gpc"] );
  }
}

function stripslash( $val ) {
  return( is_array( $val ) ? array_map( 'stripslash', $val ) : trim(stripslashes( $val)) );
}
function stripslashGpc () {
  foreach( array('POST', 'GET', 'REQUEST', 'COOKIE' ) as $gpc ) {
    $GLOBALS["_$gpc"] = array_map( 'stripslash', $GLOBALS["_$gpc"] );
  }
}

/*
 * Function to return the result set from an MDB2 prepared query
 * @params:
 *  $query: name of $query_array
 *  $args: array of prepared values to supply to the query execution
 * @returns:
 *  $rset: MDB2 result set
 */
function mdb2_query($query = "", $args = array()) {
  global $db;
  global $query_ary;

  if(!$query) {return(NULL);}
  /* if(!is_array($args)) {
    echo "DEBUG: mdb2_query: query params for $query must be an array.<br />\n";
  }
  echo "DEBUG: mdb2_query: Fetch one for query $query.<br />\n"; */

  $sth = $db->prepare($query_ary[$query]);
  /* echo "DEBUG: mdb2_query: Prepared query " . $query_ary[$query] . " with " . print_r($args) . ".<br />\n"; */
  $rset = $sth->execute($args);
  /* */
  #$rset = mdb2_query($query, $args);
  #$row = $rset->fetchRow(MDB2_FETCHMODE_ORDERED);
  #echo "DEBUG: mdb2_query: Retrieved result " . $row[0] . ".<br />\n";
  return($rset);
}

/*
 * Function to return the result set from an MDB2 prepared query
 * @params:
 *  $query: name of $query_array
 *  $args: array of prepared values to supply to the query execution
 * @returns:
 *  $rset: MDB2 result set
 */
function mdb2_fetchOne($query = "", $args = array()) {
  if(!$query) {return(NULL);}
  /* if(!is_array($args)) {
    echo "DEBUG: mdb2_fetchOne: query params must be an array.<br />\n";
  } */
  /* $db->setLimit(1, 1); */
  $rset = mdb2_query($query, $args);
  #$sth = $db->prepare($query_ary[$query]);
  #echo "DEBUG: mdb2_fetchOne: Prepared query " . $query_ary[$query] . ".<br />\n";
  #$rset = $sth->execute($args);

  $row = $rset->fetchRow(MDB2_FETCHMODE_ORDERED);
  /* echo "DEBUG: mdb2_fetchOne: Retrieved result " . $row[0] . ".<br />\n";  */
  if(PEAR::isError($rset)) {
    die("mdb2_fetchOne: MDB2 query failed: " . MDB2::errorMessage() . ".<br />\n");
  }
  return($row[0]);
}

/*
 * Function to return a row from an MDB2 prepared query
 * @params:
 *  $query: name of $query_array
 *  $args: array of prepared values to supply to the query execution
 *  $fm: rwo fetchmode (default ASSOC)
 * @returns:
 *  $row: MDB2 row
 */
function mdb2_fetchRow($query = "", $args = array(), $fm = MDB2_FETCHMODE_ASSOC) {
  global $db;
  global $query_ary;

  if(!$query) {return(NULL);}
  /* if(!is_array($args)) {
    echo "DEBUG: mdb2_fetchRow: query params must be an array.<br />\n";
  } */
  $rset = mdb2_query($query, $args);
  
  $row = $rset->fetchRow($fm);
  /* echo "DEBUG: mdb2_fetchRow: Retrieved result " . $row[0] . ".<br />\n"; */
  if(PEAR::isError($rset)) {
    die("mdb2_fetchRow: MDB2 query failed: " . MDB2::errorMessage() . ".<br />\n");
  }
  return($row);
}

/*
 * Function to return an aasociative array from a query
 * @params:
 *  $query: name of $query_array
 *  $args: array of prepared values to supply to the query execution
 * @returns:
 *  $ary: assoc array 
 */
function mdb2_fetchAssoc($query = "", $args = array()) {
  global $db;
  global $query_ary;

  if(!$query) {return(NULL);}
  if(!is_array($args)) {
    die("mdb2_fetchAssoc: query params must be an array.<br />\n");
  }
  $rset = mdb2_query($query, $args);
  if(PEAR::isError($rset)) {
    die("mdb2_fetchAssoc: MDB2 query $query failed: " . MDB2::errorMessage() . ".<br />\n");
  }
  while($row = $rset->fetchRow(MDB2_FETCHMODE_ORDERED)) {
    $key = $row[0];
    $ary[$row[0]] = $row[1];
  }
  /* echo "DEBUG: mdb2_fetchAssoc: Retrieved result " . print_r($ary) . ".<br />\n"; */
  return($ary);
}

/*
 * Function to return an ordered array from a query
 * @params:
 *  $query: name of $query_array
 *  $args: array of prepared values to supply to the query execution
 * @returns:
 *  $ary: ordered array 
 */
function mdb2_fetchOrdered($query = "", $args = array()) {
  global $db;
  global $query_ary;

  if(!$query) {return(NULL);}
  if(!is_array($args)) {
    die("mdb2_fetchAssoc: query params must be an array.<br />\n");
  }
  $rset = mdb2_query($query, $args);
  if(PEAR::isError($rset)) {
    die("mdb2_fetchAssoc: MDB2 query $query failed: " . MDB2::errorMessage() . ".<br />\n");
  }
  while($row = $rset->fetchRow(MDB2_FETCHMODE_ORDERED)) {
    $key = $row[0];
    $ary[$row[0]] = $row[1];
  }
  /* echo "DEBUG: mdb2_fetchAssoc: Retrieved result " . print_r($ary) . ".<br />\n"; */
  return($ary);
}

/*
 * Function to return the number of rows in a result set
 * @params:
 *  MDB2 result
 * @returns:
 * int
 */
function mdb2_numRows($res = NULL) {
  global $db;

  if(!$res) { return(0);}
  $i = 0;
  while($r = $res->fetchRow()) {++$i;}
  #$db->Iterator->rewind($res);

  return($i);
  
}

/* Function to extract an error message "^ERROR:" from a string and return the
 * remainder of the string as a formatted error message
 * @params:
 *  $message: string containing potential error message
 * @returns:
 *  string with "ERROR:" removed from the front and with HTML applied
 */
function error_message($message = "")
{
  if(!$message)  {return;}

  if(preg_match("/^ERROR:/", $message))
  {
    $message = "<span class=\"errmsg\">" .
               substr($message, 6) .
               "</span>\n";
  }

  return($message);
}

/*
 * Function to indicate whether the logged in user is an admin or not
 * @params:
 *  none
 * @returns:
 *  value of isadmin session variable
 */
function isadmin()
{
  return($_SESSION['isadmin']);
}

/*
 * Function that mimics the HTML_QuickForm_date object but allows for
 * the date to be pre-selected.
 * param:
 *  $label: label for date group
 *  $elem_names: array containing day,month,year element names
 *   elem_names can use 'day', 'month', 'year' or 0, 1, 2 as the index
 *  $selected: array containing day.month,year to display as selected
 */
function date_box($label, $elem_names, $selected = array())
{
  /* Construct the month part of the date selector */
  $dname = $elem_names['day'];
  if(!$dname && $elem_names[0]) {$dname = $elem_names[0];}
  $day_select = new HTML_QuickForm_select($dname, '');
  for($i = 1; $i <=31; $i++) {
    $day = ($i>=10?$i:"0".$i);
    $d_arr[$day] = $day;
  }

  $day_select->load($d_arr);
  if($selected['day']) {$day_select->setSelected($selected['day']);}
  else { $day_select->setSelected(date("d")); }
 
  /* Construct the month part of the date selector */
  $mname = $elem_names['month'];
  if(!$mname && $elem_names[1]) { $mname = $elem_names[1]; }
  $month_list = array(
              "01" => "January", "02" => "February", "03" =>"March",
              "04" => "April",   "05" => "May",      "06" => "June",
              "07" => "July",    "08" => "August",   "09" => "September",
              "10" => "October", "11" => "November", "12" => "December");

  $month_select = new HTML_QuickForm_select($mname, '');
  $month_select->load($month_list);
  if($selected['month']) {$month_select->setSelected($selected['month']); }
  else { $month_select->setSelected(date("m")); }

  /* Construct the year part of the date selector */
  $yname = $elem_names['year'];
  if(!$yname && $elem_names[2]) { $yname = $elem_names[2]; }
  $year_select = new HTML_QuickForm_select($yname, '');
  $start_year = $selected[$yname] ?
                $selected[$yname] - 5 :
                date("Y")-5;
   for($i = $start_year; $i <= $start_year+10; $i++) { $y_arr[$i] = $i; }
  $year_select->load($y_arr);
  if($selected['year']) { $year_select->setSelected($selected['year']); }
  else { $year_select->setSelected(date("Y")); }

  $dmy = new HTML_QuickForm_group('Day Mon Year', $label, array($day_select, $month_select, $year_select), '', FALSE);

  return($dmy);
}

/*
 * Function to display a select box for the available leagues
 * @params:
 *  $chosen: (optional) id of a pre-selected league
 * @returns:
 *  string containing HTML select box
 */
function league_select($chosen = "0", $attrs = array())
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  $lge_rows = $db->getAssoc($query_ary['league_select'],
                          false,
                          null,
                          DB_FETCHMODE_ASSOC
                         );
  $lge_sel = new HTML_QuickForm_select("leagues",
                                       "Leagues",
                                       $lge_rows, $attrs);

  $lge_sel->setSelected($chosen);

  return($lge_sel->toHtml());
}

/*
 * Function to display a select box for the available teams in a league
 * @params:
 *  $name: name for the select element
 *  $lge_id: id for the league in which teams play
 *  $chosen: (optional) id of a pre-selected team
 * @returns:
 *  string containing HTML select box
 */
function league_team_select($name = "", $lge_id = "",
                            $chosen = "0", $attrs = "")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  $team_rows = $db->getAssoc($query_ary['league_team_list'],
                          false,
                          array($lge_id),
                          DB_FETCHMODE_ASSOC
                         );
  $team_sel = new HTML_QuickForm_select($name,
                                       $name,
                                       $team_rows,
                                       $attrs);

  $team_sel->setSelected($chosen);

  return($team_sel->toHtml());
}

/*
 * Function to display a select box for the teams in the playoff positions
 * in a league.
 * There are issues with this function on MySQL 4.x.
 * @params:
 *  $name: name for the select element
 *  $lge_id: id for the league in which teams play
 *  $chosen: (optional) id of a pre-selected team
 * @returns:
 *  string containing HTML select box
 */
function playoff_team_select($name = "", $lge_id = "", $chosen = "0")
{
  global $db; // Global database handle
  global $query_ary; // Database queries that can be submitted

  /* The the start position and number of teams in the playoff places */
  $pl_range = $db->getRow($query_ary['get_playoff_range'],
                            true,
                            array($lge_id),
                            DB_FETCHMODE_ORDERED
                           );

  $pl_start = $pl_range[0]-1;
  $pl_count = $pl_range[1];

  /* Need to replace strings in the query array entry because quoted limits
   * are not accepted by MySQL
   * http://dev.mysql.com/doc/refman/5.1/en/user-variables.html
   */
  $qry = str_replace("START", $pl_range[0]-1, $query_ary['playoff_team_select']);
  $qry = str_replace("RANGE", $pl_range[1], $qry);

  $team_ary = $db->getAll($qry,
                          true,
                          array($lge_id),
                          DB_FETCHMODE_ASSOC
                         );

  foreach($team_ary as $team)
  {
    $idx = $team[0];
    $team_rows[$idx] = $team[1];
  }
 
  $team_sel = new HTML_QuickForm_select($name,
                                       $name,
                                       $team_rows);

  $team_sel->setSelected($chosen);

  return($team_sel->toHtml());
}

/*
 * Function to create a list of possible dates for matches based on strings
 * containing start and end dates.
 * @params:
 *  $name: The name of the select element
 *  $start_date: string containg day, month and year values
 *  $end_date: string containg day, month and year values
 *  $chosen: the value in the list to be pre-selected
 * @returns:
 *  string containg HTML format of the possible dates
 */
function fixture_date_select($name = "",   $start_date="",
                             $end_date="", $chosen = "")
{
  if(!$start_date || !$end_date) {
    return("Missing start date or number of days.");
  }

  $fselect = new HTML_QuickForm_select($name, '', '', array("id" => $name));

  /* The nice way to do it but requires PHP 5.3.x for teh DateTime->add method
   *
  $db_date = new DateTime($start_date);
  for($i = 0; $i < $num_days; ++$i) {
    $fselect->addOption(date_format($db_date, "d M Y"), date_format($db_date, "Y-m-d") );

    if($chosen == $db_date)
    {
      $fselect->setSelected($db_date);
    }
    $db_date->add(new DateInterval("P1D"));
  }
   */
  for($i = strtotime($start_date)+3601; $i <= strtotime($end_date)+36001; $i+=86400)
  {
    $db_date = gmdate("Y-m-d", $i);
    $show_date = gmdate("d M Y", $i);
    $fselect->addOption($show_date, $db_date);

    /* Check for a pre-selected value */
    if($chosen == $db_date)
    {
      $fselect->setSelected($db_date);
    }
  }

  return($fselect->toHtml());
}

/*
 * Function to return a select list containing valid kickoff times
 * @params:
 *  $name: the name of the select element
 *  $chosen: a pre-selected value
 * @returns:
 * string containg HTML select element
 */
function kickoff_select_list($name = "", $chosen = "")
{
  $ko_vals = array("12:45", "13:00", "15:00", "16:00", "17:30", "19:45",
                   "20:00", "11:30", "12:00", "12:15", "12:30", "12:35",
                   "13:15", "13:30", "14:00", "14:05", "14:30", "15:30",
                   "16:10", "16:15", "16:30", "17:00", "17:05", "17:15",
                   "17:20", "17:45", "18:00", "18:30", "18:45", "19:00",
                   "19:30");

  
  $kselect = new HTML_QuickForm_select($name, '');
  foreach($ko_vals as $time)
  {
    $ktime =  $time . ":00";
    $kselect->addOption($time, $ktime);
    if($ktime == $chosen) { $kselect->setSelected($ktime); }
  }
  if(!$chosen) { $kselect->setSelected("15:00:00"); }

  return($kselect->toHtml());
}

/*
 * Function to verify that a league passed from the option list is listed
 * in the database
 * @param:
 *  $lge_id: league id passed from the form
 * @return:
 *  count of leagues matching the requested id (1 or 0)
 */
function is_valid_league($lge_id = '0')
{
  global $db; /* Global database handle */

  global $query_ary; /* List of acceptable queries */
  $db->setLimit(1,1);
  return($db->query($query_ary['is_valid_league'], array($lge_id)));
}

/*
 * Function to determine if a team is in the submitted league or not
 * @params:
 *  $lge_id: the id of the submitted league
 *  $tid: the id of the submitted team
 * @returns:
 *  team name if team is in league, otherwise empty string
 */
function is_team_in_league($lge_id = '0', $tid = "0")
{
  global $db; /* Global database handle */

  global $query_ary; /* List of acceptable queries */

  return($db->query($query_ary['is_team_in_league'], array($lge_id, $tid)));
}

/* Function to extract the valid leagues from a form post. Up to 4 leagues
 * may be selected.
 * Used by options::save_options, users::users_add, users::users_update
 * @params:
 *  none
 * @returns:
 *  array containing id's of valid leagues
 */
function get_valid_leagues()
{
  global $db; /* Global database handle */

  global $query_ary; /* List of acceptable queries */

  /* Only allow a maximum of 4 leagues */
  $new_leagues = array_slice($_POST['leagues'], 0, 4);

  $valid_leagues = array();
  foreach($new_leagues as $lge_id)
  {
    if(is_valid_league($lge_id))
    {
      $valid_leagues[] = $lge_id;
    }
  }

  return($valid_leagues);
}

/* Function to save the leagues that the user has subscribed to
 * @params:
 * $uid: id of user subscribing to leagues.
 * $lges: array containing leagues to subscribe to
 * @returns:
 *  string detailing the success (or otherwise) of the operation
 */
function subscribe_user_to_leagues($uid = "0", $lges = array())
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!$uid)
  {
    return("Inavlid user id given for league subscription.");
  }

  if(is_array($lges) && !count($lges))
  {
    return("ERROR:No leagues specified for subscription.");
  }

  /* Remove the existing league subscriptions for the user and add the
   * new ones that have been requested
   */
  $db->query($query_ary['subs_delete'], array($uid));
  foreach($lges as $l)
  {
    $db->query($query_ary['subs_insert'], array($uid, $l));
    $disp_str .= "Subscribed to " .
                 mdb2_fetchOne('league_name', array($l)) .
                 ".<br />\n";
  }

  return($disp_str);
}

/*
 * Function to verify that a userteam passed from the option list is listed
 * in the database
 * @param:
 *  $team_id: league id passed from the form
 * @return:
 *  count of leagues matching the requested id (1 or 0)

 *
 * Does not appear to be required for the options form
 *
function is_valid_userteam($team_id = '0')
{
  global $db;

  global $query_ary;

  return(mdb2_fetchOne('is_valid_userteam', array($team_id)));
}
 */

/* Function to get the valid user teams specified in a submission
 * @params:
 *  none but $_POST is referenced
 * @returns:
 *  array containing the id's of the valid user teams from the request
 */
function get_valid_user_teams()
{
  global $db; /* Global database handle */

  global $query_ary; /* List of acceptable queries */

  /* Only allow a maximum of 4 teams */
  $new_teams = array_slice($_POST['userteams'], 0, 4);

  $valid_teams = array();
  foreach($new_teams as $team_id)
  {
    if(is_valid_userteam($team_id))
    {
      $valid_teams[] = $team_id;
    }
  }

  return($valid_teams);
}

/* Function to save the userteams that the user belogs to
 * @params:
 * $uid: id of user belonging to userteams.
 * $teams: array containing leagues to subscribe to
 * @returns:
 *  string detailing the success (or otherwise) of the operation
 */
function subscribe_user_to_teams($uid = "0", $teams = array())
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!$uid)
  {
    return("Inavlid user id given for team membership.");
  }

  if(is_array($teams) && !count($teams))
  {
    return("ERROR:No teams specified for membership.");
  }

  /* Remove the existing league subscriptions for the user and add the
   * new ones that have been requested
   */
  $db->query($query_ary['uteams_delete'], array($uid));
  foreach($teams as $t)
  {
    $db->query($query_ary['uteam_insert'], array($uid, $t));
    $disp_str .= "Added to " .
                 mdb2_fetchOne('userteam_name', array($t)) .
                 ".<br />\n";
  }

  return($disp_str);
}

/* Function to save the users that are members of this userteam.
 * @params:
 * $uid: id of user belonging to userteams.
 * $teams: array containing leagues to subscribe to
 * @returns:
 *  string detailing the success (or otherwise) of the operation
 */
function userteam_members($tid = "0", $users = array())
{
  global $db; // Global database handle

  global $query_ary; // Array of acceptable queries

  if(!$tid)
  {
    return("Inavlid user team id given for user membership.");
  }

  if(is_array($users) && !count($users))
  {
    return("ERROR:No users specified for team membership.");
  }

  /* Remove the existing league subscriptions for the user and add the
   * new ones that have been requested
   */
  $db->query($query_ary['uteams_delete_byteam'], array($tid));
  foreach($users as $u)
  {
    $db->query($query_ary['uteam_insert'], array($u, $tid));
    $disp_str .= "Added " .
                 mdb2_fetchOne('user_details', array($u)) .
                 " to user team.<br />\n";
  }

  return($disp_str);
}


/*
 * Function to display a list of the possible match types to help constrain
 * the result type submitted for the match result.
 * FIXME: This should really be database derived.
 * @params:
 *  $name: The name of the select element
 *  $chosen: The pre-selected value
 *  $attrs: Additional element settings
 * @return:
 *  string containing HTML select element for result types
 */
function match_type_select($name = "", $chosen = "", $attrs = array())
{
  $mt_ary = array('league',    'playoff',     'knockout', 'friendly');

  $mtselect = new HTML_QuickForm_select($name, '', null, $attrs);
  foreach($mt_ary as $mt_val)
  {
    $mtselect->addOption($mt_val, $mt_val);
    if($mt_val == $chosen) { $mtselect->setSelected($mt_val); }
  }
  if(!$chosen) { $mtselect->setSelected("league"); }

  return($mtselect->toHtml());
}

/*
 * Function to display a list of the possible result types for use when
 * submitting results
 * @params:
 *  $name: The name of the select element
 *  $chosen: The pre-selected value
 * @return:
 *  string containing HTML select element for result types
 */
function result_type_select($name = "", $chosen = "", $match_type = "league")
{
  $rt_ary = array('normal', 'abandoned', 'postponed'); 

  /* Add extra options for playoff and knockout and set default result type */
  if($match_type == "playoff" || $match_type == "knockout")
  {
    array_push($rt_ary, 'playoff', 'extra', 'penalties');
    if(!$chosen) { $chosen = $match_type; }
  }

  $rtselect = new HTML_QuickForm_select($name, '');
  foreach($rt_ary as $rt_val)
  {
    $rtselect->addOption($rt_val, $rt_val);
    if($rt_val == $chosen) { $rtselect->setSelected($rt_val); }
  }
  if(!$chosen) { $rtselect->setSelected("normal"); }

  return($rtselect->toHtml());
}

/* Function to get the start of a season
 * @params:
 *  $sid: id of season to be checked
 * @returns:
 *  YYYY-MM-DD of season or null if season not found
 */
function start_of_season($sid = "")
{
  if(!$sid) {return(null);}

  return(mdb2_fetchOne('get_season_start', array($sid)));
}

/* Function to get the end of a season
 * @params:
 *  $sid: id of season to be checked
 * @returns:
 *  YYYY-MM-DD of season or null if season not found
 */
function end_of_season($sid = "")
{
  if(!$sid) {return(null);}

  return(mdb2_fetchOne('get_season_end', array($sid)));
}

/* Function to return a string either containing the date as text or a
 * 'datebox with day, month and year select form elements
 * @params:
 *   $sid: season id
 *   array containing day, month and year elements
 *   array containing elements for names of form elements where needed
 * @returns:
 * string with plain text date (if array date is before today), or
 *   HTML for three select elements for day, month and year
 */
function make_season_date($sid = "0",     $name = "",
                          $dmy = array(), $elems = array())
{
  global $db; // Global database handle

  global $query_ary; // Database queries that can be submitted

  if(!isset($sid)) {return(null);}

  $m_ary = array("01" => 'January', "02" => 'February', "03" => 'March',
                 "04" => 'April',   "05" => 'May',      "06" => 'June',
                 "07" => 'July',    "08" => 'August',   "09" => 'September',
                 "10" => 'October', "11" => 'November', "12" => 'December');

  $ss = start_of_season($sid);

  if(($ss  && date("Y-m-d") <= $ss) ||
    ($_POST['newseason']))
  {
    $dbox       = date_box($name, $dmy, $elems);
    $sdate      = $dbox->toHtml();
  }
  else
  {
    $mnth        = $elems['month'];
    $sdate       = $elems['day'] . " " .
                   $m_ary[$mnth] . " " .
                   $elems['year'];
  }

  return($sdate);
}

/* Function to return a select form element showing the leagues that the user
 * has subscribed to.
 * @params:
 *  $uid: id of user to show team membership
 * @returns:
 *  string containing HTML select form element
 */
function user_subscribed_select($uid = "0")
{
  global $db; // Global database handle

  global $query_ary; // Database queries that can be submitted

  if(!$uid) {echo "No such user $uid.<br />\n";return(null);}

  $lge_rows = $db->getAssoc($query_ary['league_select'],
                            false,
                            null,
                            DB_FETCHMODE_ASSOC
                           );

  $lge_sel = new HTML_QuickForm_select("leagues",
                                       "Leagues",
                                       $lge_rows,
                                       array('multiple' => 'yes',
                                             'size'     => '4')
                                      );

  if($_POST['leagues'])
  {
    $chosen = get_valid_leagues();
  }
  else
  {
    $chosen = $db->getAssoc($query_ary['league_dropdown'],
                            false,
                            array($uid),
                            DB_FETCHMODE_ASSOC
                           );
  }

  $lge_sel->setSelected($chosen);

  return($lge_sel->toHtml());
}

/* Function to return a select form element showing the teams that the user
 * belongs to.
 * @params:
 *  $uid: id of user to show team membership
 * @returns:
 *  string containing HTML select form element
 */
function user_team_select($uid = "0")
{
  global $db; // Global database handle

  global $query_ary; // Database queries that can be submitted

  if(!$uid) {echo "No such user $uid.<br />\n";return(null);}

  $uteam_rows = $db->getAssoc($query_ary['user_team_select'],
                            false,
                            null,
                            DB_FETCHMODE_ASSOC
                           );

  $uteam_sel = new HTML_QuickForm_select("userteams",
                                       "",
                                       $uteam_rows,
                                       array('multiple' => 'yes',
                                             'size'     => '4')
                                      );
  if($_POST['userteams'])
  {
    $chosen = get_valid_user_teams();
  }
  else
  {
    $chosen = $db->getAssoc($query_ary['user_teamlist'],
                          false,
                          array($uid),
                          DB_FETCHMODE_ASSOC
                         );
  }
  $uteam_sel->setSelected($chosen);

  return($uteam_sel->toHtml());
}

/* Function to return a select form element showing the timezone that the user
 * is in
 * @params:
 *  $uid: id of user to show timezone
 * @returns:
 *  string containing HTML select form element
 */
function user_timezone_select($uid = "0")
{
  if(!$uid) {echo "No such user $uid.<br />\n";return(null);}

  $currtz = mdb2_fetchOne('get_user_timezone', $_SESSION['uid']);

  $tz_ary = array( "Europe/London"    => "Europe/London",
		   "Europe/Paris"     => "Europe/Paris",
                   "Europe/Madrid"    => "Europe/Madrid",
                   "Europe/Rome"      => "Europe/Rome",
                   "Europe/Berlin"    => "Europe/Berlin",
                   "Europe/Lisbon"    => "Europe/Lisbon",
                   "Europe/Stockholm" => "Europe/Stockholm",
                   "Europe/Oslo"      => "Europe/Oslo");

  $disp_str = "<select name=\"timezone\">\n";
  foreach($tz_ary as $tz => $tzval)
  {
    $sel = "";
    if($tzval == $currtz) { $sel = " selected=\"selected\""; }
    $disp_str .= "<option value=\"$tzval\"$sel>$tzval</option>\n";
  }
  $disp_str .= "</select>\n";
  return($disp_str);
}

/* Function to determine whether a user submitted timezone value should be
 * allowed or not.
 * is in
 * @params:
 *  $tz: the timezone submitted by the user
 * @returns:
 *  "" if successful, error message otherwise
 */
function user_timezone_validate($tz = "")
{
  $tz_ary = array( "Europe/London"    => "Europe/London",
		   "Europe/Paris"     => "Europe/Paris",
                   "Europe/Madrid"    => "Europe/Madrid",
                   "Europe/Rome"      => "Europe/Rome",
                   "Europe/Berlin"    => "Europe/Berlin",
                   "Europe/Lisbon"    => "Europe/Lisbon",
                   "Europe/Stockholm" => "Europe/Stockholm",
                   "Europe/Oslo"      => "Europe/Oslo");

  foreach($tz_ary as $tz => $tzval)
  {
    if($tz == $tzval) { return(""); }
  }
  return("ERROR:Invalid timezone, $tz");
}

/* Function to update the user's timezone
 * is in
 * @params:
 *  $uid: id of user to show timezone
 *  $tz: Timezone string of th user
 * @returns:
 *  "" if successful, database error otherwise
 */
function user_timezone_update($tz = "", $uid = "0")
{
  global $db; // Global database handle

  global $query_ary; // Database queries that can be submitted

  if(!$uid) {echo "Cannot update missing $uid.<br />\n";return(null);}
  if(!$tz) {echo "Hey dude, where's your timezone.<br />\n";return(null);}

  $res = $db->query($query_ary['save_user_timezone'],
                       array($tz, $_SESSION['uid']));

  if(PEAR::isError($res)) {
    return("Error updating timezone: " . $res->getMessage() . "<br />\n");
  }
  return("");
}

/* Function to get the valid users specified in a submission
 * @params:
 *  none but $_POST is referenced
 * @returns:
 *  array containing the id's of the valid users from the request
 */
function get_valid_users()
{
  global $db; /* Global database handle */

  global $query_ary; /* List of acceptable queries */

  /* Only allow a maximum of 10 users per team */
  $new_users = array();
  if($_POST['userteam_users'])
  {
    $new_users = array_slice($_POST['userteam_users'], 0, 10);
  }

  $valid_users = array();
  foreach($new_users as $user_id)
  {
    if(is_valid_user($user_id))
    {
      $valid_users[] = $user_id;
    }
  }

  return($valid_users);
}

/* Function to return a select form element showing the users in a given
 * userteam.
 * @params:
 *  $tid: id of userteam to show team membership
 * @returns:
 *  string containing HTML select form element
 */
function userteam_users_select($tid = "0")
{
  global $db; // Global database handle

  global $query_ary; // Database queries that can be submitted

  if(!$tid) {echo "No such user team $tid.<br />\n";return(null);}

  $user_rows = mdb2_fetchAssoc('user_select');

  $uteam_sel = new HTML_QuickForm_select("userteam_users",
                                       "",
                                       $user_rows,
                                       array('multiple' => 'yes',
                                             'size'     => '4')
                                      );
  /* Only choose valid users from the posted form */
  if($_POST['userteam_users'])
  {
    $chosen = get_valid_users();
  }
  else
  {
    $chosen = mdb2_fetchAssoc('userteam_users', array($tid));
  }
  $uteam_sel->setSelected($chosen);

  return($uteam_sel->toHtml());
}

/* Check that the requested user is regeistered in the database.
 * @params:
 *  $uid: id of user to check
 * @returns:
 *  true if user id in database, false otherwise
 */
function is_valid_user($uid = "0")
{
  if(!$uid) {return(false);}

  return(mdb2_fetchOne('users_password', array($uid)));
}

/* Check that the requested user team is regeistered in the database.
 * @params:
 *  $tid: id of user team to check
 * @returns:
 *  true if user team id in database, false otherwise
 */
function is_valid_userteam($tid = "0")
{
  if(!$tid) {return(false);}

  return(mdb2_fetchOne('userteam_details', array($tid)));
}

/* Function to generate an HTML link to call the JavaScript expand_contract
 * function using stylesheets to display or hide a section of text.
 * @params:
 *  $fid: element id
 *  $text: string to display
 * @returns:
 *  string containing HTML code for expand/contact link
 */
function expand_contract_js($fid = "0", $header = "", $table = "")
{
  $disp_str = "
  <span class=\"contract\" id=\"" . $fid . "_c\">" .
    "<a href=\"javascript:expand_contract('" . $fid . "', 1);\">" .
    "<!-- img src=\"images/more.gif\" border=\"0\" alt=\"Click to display details\"" .
    " width=\"9\" height=\"11\" -->" . $header . "</a>" .
    "  </span><br>\n";
  $disp_str .= "  <span class=\"expand\" id=\"" . $fid . "_e\">" .
    "<a href=\"javascript:expand_contract('" . $fid . "', 0);\">" .
    "<!-- img src=\"images/less.gif\" border=\"0\" alt=\"Click to hide details\"" .
    " width=\"9\" height=\"11\" -->" . $header . "</a>\n";

  /* Add the data to be displayed */
  $disp_str .= $table . "\n" . "</span>\n";

  /* Display the table if JavaScript not available */
  $disp_str .= "<noscript>" . $header .
               "<br />" . $table .
               "</noscript>\n";

  return($disp_str);
}

/* Function to create a list of all the users that are members of the
 * looged-in user's teams.
 * @params:
 *  $uid: user id of the person to display the predictions for. The is must
 *  belong to one of the logged in users' team groups.
 * @returns:
 *  String with list of HTML tables with predictions for the season
 */
function display_all_userteam_users($uid = "0")
{
  /* Choose either the supplied uid of the session uid */
  $user = ($uid ? $uid : $_SESSION['uid']);

  /* Add a form listing all available users for the user's userteams and
   * call and AJAX function when a new user is selected
   */
  $teamusers = mdb2_query('logged_in_user_team_members',
                      array($_SESSION['uid']));

  $user_rows = mdb2_fetchAssoc('logged_in_user_team_members', array($_SESSION['uid']));

  $attrs = array("onchange" => "javascript:sndReq()");

  $pred_str = "<form method=\"post\" name=\"userlist\" action=\"#\">\n";
  
  $user_select = new HTML_QuickForm_select("predict_users", "Predict users", $user_rows, $attrs);
  /* $user_select->load($userlist); */
  $user_select->setSelected($user);
  $pred_str .= $user_select->toHtml();
  $pred_str .= "</form>\n";

  $disp_str .= $pred_str;

  return($disp_str);
}

/* Function to display the predictions so far this season by the logged in user
 * for the specified league.
 * This function can also be used to display the predictions made by another
 * user in a team group by specifying the id of that user.
 * @params:
 *  $uid: user id of the person to display the predictions for. The is must
 *  belong to one of the logged in users' team groups.
 * @returns:
 *  String with list of HTML tables with predictions for the season
 */
function season_predictions($uid = "0")
{
  /* Choose either the supplied uid of the session uid */
  $user = ($uid ? $uid : $_SESSION['uid']);

  /* Some rule checking to prevent cheating */
  /* If $uid has a value but is different to the session uid then check
   * that the user exists in one of the users team groups.
   */
  if(($user != $_SESSION['uid']) &&
    !mdb2_fetchOne('is_user_in_same_team',
                 array($_SESSION['uid'], $uid)))
  {
    return("Requested user does not belong to logged in user's teams.<br />\n");
  }

  $now = now_by_timezone($_SESSION['tabnum']);
  $preds = mdb2_query('user_predictions_season',
                      array($now, $user, $_SESSION['tabnum']));

  $disp_str = "The list below shows all the dates for which match " .
              "predictions have been submitted this season. Click on the " .
              "dates to display a list of predictions on that date.<br />\n" .
              "Row highlighting indicates the following:<br />" .
              "<span class=\"outcome\">correct winner</span>, " .
              "<span class=\"diffs\">correct draws</span>, " .
              "<span class=\"exactscore\">exact score</span>.\n<br />\n";

  if(!$preds->numRows())
  {
    return($disp_str .
           "No predictions have been found for this league for this user." .
           "</div>");
  }

  $disp_str .= "<ul>\n";

  $pred_table = "";
  $curr_date = ""; /* Trap changes in match dates */
  $toggle = 0;
  $table_id = 0;
  while($prediction = $preds->fetchRow())
  {

    /* If a new date is found then a table that has previously been built
     * is formatted as a expand/contract element.
     * Then start a new table with the date as the header.
     */
    if($curr_date != $prediction['match_date'])
    {
      if($pred_table)
      {
        $pred_table .= "</table>\n";
        $disp_str .= "<li>" .
                     expand_contract_js($table_id,
                                        $curr_date,
                                        $pred_table) .
                     "</li>\n";
      }

      $curr_date = $prediction['match_date'];

      /* Start a new table */
      ++$table_id;
      $toggle = 0;
      $pred_table = "
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n
<colgroup span=\"1\" width=\"40%\" />
<colgroup span=\"1\" width=\"10%\" />
<colgroup span=\"1\" width=\"40%\" />
<colgroup span=\"1\" width=\"10%\" />
\n";
    }

    /* Set the style for the display row */
    switch($prediction['win_score_draw']) {
      case 'S': $class = " class=\"exactscore\"";
        break;
      case 'D': $class = " class=\"diffs\"";
        break;
      case 'W': $class = " class=\"outcome\"";
        break;
      default: $class = ($toggle) ? "" : " class=\"greyrow\"";
        $toggle = ($toggle ? 0 : 1);
        break;
    }

    /* Add the prediction details to the table */
    $pred_table .= "<tr$class>" .
                    "<td>" . $prediction['home_team'] . "</td>" .
                    "<td class=\"score\">" . $prediction['home_goals'] . "</td>" .
                    "<td>" . $prediction['away_team'] . "</td>" .
                    "<td class=\"score\">" . $prediction['away_goals'] . "</td>" .
                    "</tr>\n";

  }

  /* Close the final table and add it to the bottom of the page */
  $pred_table .= "</table>\n";
  $disp_str .= "<li>" .
               expand_contract_js($table_id,
                                  $curr_date,
                                  $pred_table) .
               "</li>\n";

  $disp_str .= "</ul>\n";

  return($disp_str);
}

/* Function to add a button to add a form with one button to update the user
 * scores table. The button activates an RPC call to remove any existing
 * entries and add new entries with the latest scores.
 * @params:
 *  $inital: Optional char specifying the starting letter of the users to update
 *  If 0, all users are updated.
 * @returns:
 *  string containing HTML representation of the form
 */
function update_user_scores_form($initial = "")
{
  $disp_str = "
<div id=\"updatedusers\">
If JavaScript is enabled, press the button below to calculate the scores for
the users listed on this page. If no users are listed, all user records will be
updated.
<form action=\"" . $_SERVER[PHP_SELF] . "\" method=\"post\" name=\"updatescores\">
<input type=\"button\" name=\"update\" value=\"Update scores\" onclick=\"javascript:sndReq('$initial');\" class=\"button\" />
</form>
</div>
";

  return($disp_str);
}

/* Function to create predict_user_scores entries for each user whose name
 * begins with $initial, or all users if no initial is given.
 * @params:
 *  $inital: first character of user's names to be updated.
 * @returns:
 *  string indicating which users have been updated.
 */
function update_user_scores($initial = "")
{
  $usrch = $initial . "%";

  $disp_str = "<p>Calculating user scores.<br />\n";

  /* Loop through all the users that start with initial */
  $res = mdb2_query('list_users_byname', $usrch);

  if(!$res->numRows())
  {
    return("<p>No matching users for score updates.</p>\n");
  }

  while($res->fetchInto($uary))
  {
    $user_id = $uary['id'];

    /* Predictions made this season */
    $mprd = mdb2_fetchOne('predicted_results', array($user_id));

    /* Correctly predicted match outcomes */
    $mres = mdb2_fetchOne('match_results', array($user_id));

    /* Correctly predicted match scores */
    $mscr = mdb2_fetchOne('scores', array($user_id));

    /* Correctly predicted drawn matches */
    $mdif = mdb2_fetchOne('summary_season_draws', array($user_id));

    /* Points for the season is the matches + exact scores */
    $tot = $mres + $mscr + $mdif;

    /* Update the database */
    $now = get_user_now($_SESSION['uid']);
    if(!$now || fnmatch("User timezone error:*", $now)) {
      return("Error with timezone for user " . $_SESSION['uid'] . ".<br />\n");
    }
    $db->query($query_ary['user_score_delete'], $user_id);
    $r1 = mdb2_query('user_score_insert',
                      array($user_id, $mprd, $mres, $mdif, $mscr, $tot, $now));

    /* Update the page display */
    if(PEAR::isError($r1))
    {
      $disp_str .= "Error updating score for " . $uary['uname'] . ".<br />\n";
    } else
    {
      $disp_str .= "Updated score for " . $uary['uname'] .
                   " to $tot points.<br />\n";
    }

  }
  $res->free();

  return($disp_str);
}

/* Function to output the top five users by score
 * @params:
 *  none
 * @returns:
 *  string containing HTML table with the top 5 users
 */
function top_five_users()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  $disp_str = "
<div id=\"topfive\">
<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\">\n
<colgroup span=\"1\" width=\"72%\" />
<colgroup span=\"1\" width=\"28%\" />
<tr>
 <th colspan=\"2\">Top 6 users</th>
</tr>
\n";

  /* Get the top 5 users */
  $top5 = mdb2_query('top_five_users');
  if(PEAR::isError($top5))
  {
    return("Cannot locate users for top 5 display.");
  }

  /* MDB2 while($top5->fetchInto($user)) */
  while($user = $top5->fetchRow())
  {
    $disp_str .= "<tr>" .
                 " <td>" . $user['username'] . "</td>" .
                 " <td>" . $user['rating'] . "</td>" .
                 "</tr>";
  }

  $disp_str .= "</table></div><!-- topfive -->\n";

  return($disp_str);
}

/* Function to recalculate the points and goals scored/conceded for a
 * given league.
 * @params:
 *  $lge_id: id of the league to be updated
 * @returns:
 *  string indicating the success or otherwise of the operation
 */
function update_league_table($lge_id = "")
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  if(!$lge_id) {return("Must specify a league to update.");}

  $db->query($query_ary['delete_league_table'], $lge_id);
  $res = $db->query($query_ary['insert_league_table'],
                    array_fill(0, 10, $lge_id));

  if(PEAR::isError($res))
  {
    $disp_str .= error_message("ERROR:Failed to update league table because: " .
                               $res->getMessage() . ".\n");
  } else {
    $disp_str .= "Uploaded submitted results into league table.<br />";
  }

  return($disp_str);
}

/* Function to save a comment posted from FCKeditor
 * @params:
 *   $uid: id of user posting comment
 *   $grp_id: id of the user team the comment is posted to
 * @returns:
 *   any error resulting from the insert attempt
 */
function insert_user_comment($uid = '0', $grp_id = '0')
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  $disp_str = "";

  $sValue = stripslashes($_POST['FCKeditor1']);

  if(!$sValue) {return("ERROR:Blank comments cannot be posted.");}

  $comm_id = $db->nextId('predict_comments');

  /* 90th minute goal for Blackpool denied us three points tonight. */
  $now = get_user_now($_SESSION['uid']);
  $comm_ary = array($comm_id,
                     $uid,
                     $grp_id,
                     "User-submitted message",
                     $sValue,
                     $now);

  $res = $db->query($query_ary['insert_user_comment'], $comm_ary);

  return($disp_str);
}

/* Function to delete a comment posted by a user. Only an admin can delete
 * messages.
 * @params:
 *   $msgid: id of message to be be removed
 * @returns:
 *   any error resulting from the insert attempt
 */
function delete_user_comment($mid = '0')
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  if(!isadmin())
  {
    $disp_str = error_message("ERROR:Only an administrator can delete comments.");
  }

  /* Bomb if no message specified */
  if(!$mid) {return(error_message("ERROR:Missing comment id."));}

  /* Delete the comment */
  $res = $db->query($query_ary['delete_user_comment'], $mid);

  if(PEAR::isError($res))
  {
    return(error_message("ERROR:Could not delete comment id, $mid."));
  }

  return("<p>Deleted user comment.</p>");
}

/* Function to format the comments posted by users
 * @params:
 * $grp_id: id of the user team comments have been posted to
 * @returns:
 *   string containing formatted user-submitted comments
 */
function show_user_comments($grp_id = '0')
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  $disp_str = "";

  $comm_qry = mdb2_query('show_user_comments', $grp_id);

  /* Post something meaningful if there are no queries */
  if(!$comm_qry->numRows())
  {
     return("No comments posted by members of this team.");
  }

  /* MDB2 while($comm_qry->fetchInto($comment)) */
  while($comment = $comm_qry->fetchRow())
  {
    $disp_str .= "<span class=\"header\">" .
                   "Posted by " . $comment['username'] .
                   " on " . $comment['posted_date'] .
                 "</span>\n";

    /* Only an admin gets the option to remove comments */
    if(isadmin())
    {
      $disp_str .= "<span>&nbsp;&nbsp;<a href=\"javascript:sndReq('" .
                   $comment['comment_id'] . "','" .$grp_id . "');\"" .
                   " title=\"Delete comment\">X</a></span>";
    }

    $disp_str .= "<div class=\"message\">" . $comment['user_comment'] .
                 "</div>\n";
  }

  return($disp_str);
}

/* Function to get the outcome of the last $nm matches for a team, both home
 * and away.
 * @params:
 *  $teamid: id of the teams whose form is being checked
 *  $nm: the number of matches to extract the outcome of
 * @returns:
 *  string showing last five matches, of the form, 'wwdlw'
 */
function last_five_results($teamid = 0, $nm = 5)
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  $disp_str = "<tr><td>Last $nm matches</td><td>";

  if(!$teamid) {return($disp_str . str_repeat('-', $nm) . "</td></tr>\n");}

  /* Because we can't pass $nm as a prepared value, we need to modify the
   * query with the number of fixtures to extract in the LIMIT
   */
  $qry = str_replace("NUM_RESULTS", $nm, $query_ary['last_five_results']);
  $outcomes = $db->fetchCol($qry, 0,
                            array($teamid, $teamid, $teamid,
                                  $teamid, $teamid, $teamid)
                            );

#  if($outcomes) {
    $disp_str .= str_pad(strrev(implode($outcomes)), $nm, "-", STR_PAD_RIGHT);
#  } else {
#    $disp_str .= str_repeat('-', $nm);
#  }

  $disp_str .= "</td></tr>\n";

  return($disp_str);
}

/* Function to get the position of a team in the league based on the points
 * and goal difference
 * @params:
 *  $team_id: id of the team whose position is to be found
 *  $lgeid: id of the league to search
 * @returns:
 *  integer for the team's position in the league, 0 if teamid not found
 */
function get_league_position($teamid = "0", $lgeid = "0")
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  if(!$teamid || !$lgeid ) {return(0);}

  return(mdb2_fetchOne('get_league_position',
                      array($teamid, $lgeid)));
}

/* Function to format the league position as a row for the form guide
 *  $team_id: id of the team whose position is to be found
 *  $lgeid: id of the league to search
 * @returns:
 *  string containing HTML formatted row, "" if team position not found
 */
function get_league_pos_row($teamid, $lgeid)
{
  $lgepos = get_league_position($teamid, $lgeid);

  if($lgepos)
  {
    return("<tr><td>League position</td><td>" . $lgepos . "</td></tr>\n");
  } else {
    return("");
  }
}

/* Function to retrieve the score when (and if) the last least met
 * regardless of fixture type.
 * @params:
 *  $query: name of query to get the home or away form
 *  $teamid: The id of the team to pass to the query
 *  $lgeid: The id of the league the team plays in
 * @returns:
 *  $disp_str: HTML table with team's home or away form
 */
function form_guide_row($query = "", $teamid = "", $lgeid = "")
{
  $form_guide_row = mdb2_fetchRow($query,
                               array($teamid, $lgeid));
  if(PEAR::isError($form_guide_row)) {
    return("");
  }
  foreach($form_guide_row as $k => $v) {
    $disp_str .= "<tr><td>$k</td><td>$v</td></tr>\n";
  }

  return($disp_str);
}

/* Function to retrieve the score when (and if) the last least met
 * regardless of fixture type.
 * @params:
 *  $home_team_id: id of home team
 *  $away_team_id: id of away team
 * @returns:
 *  String representing result of last recorded match or a space
 */
function get_last_meeting($home_team_id = "0", $away_team_id = "0")
{
  /* Get the result of the last time this match was played */
  $ls = mdb2_fetchOne('score_when_last_played',
                    array($home_team_id, $away_team_id));

  if(count($ls)) {
    return("<tr><td>Last meeting</td><td>" . $ls . "</td></tr>\n");
  }
  return("");
}

/* Function to extract the results form for a team and show how many games
 * they've won, lost, drawn and how many goals have been scored and conceded
 * The form shown is drawn from the home fixtures if the team is at home in the
 * match to be predicted, away otherwise.
 * @params:
 *  $teamid: the id of the team whose form is to be displayed
 *  $lgeid: the id of the league the team is in
 *  $hora: Whether the team is 'home' or 'away'.
 *  $oppid: the id of the opposition team; used for the last meeting detail
 * @returns
 *  string containing HTML div with the requested teams form
 */
function get_team_form($teamid = "0", $lgeid = "0", $hora = "", $oppid = "0")
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  if(!$teamid || !$lgeid || !$hora) {return($disp_str .
                                     "Invalid form request.</div>\n");}

  $team_name = mdb2_fetchOne('team_name', array($teamid));
  $oppo_name = mdb2_fetchOne('team_name', array($oppid));

  if(!$team_name) {return($disp_str . "Inavlid team id.</div>\n");}

  $disp_str .= "<p>" .
               "<table cellspacing=\"0\">" .
               "<tr>" .
               "<th colspan=\"2\">" . $team_name . "&nbsp;&nbsp;</th>" .
               "</tr>\n";

  if($hora != "home" && $hora != "away") {
    return($disp_str . "Must specify home or away.</div>\n");
  }

  /* Run the queries for the form guide  and display the results */
  $disp_str .= form_guide_row("home_form",  $teamid, $lgeid);
  $disp_str .= get_league_pos_row($teamid, $lgeid);
  $disp_str .= last_five_results($teamid, 5);

  /* Away team form */
  $disp_str .= "<tr>" .
               "<th colspan=\"2\">" . $oppo_name . "&nbsp;&nbsp;</th>" .
               "</tr>\n";
  $disp_str .= form_guide_row("away_form",  $oppid, $lgeid);
  $disp_str .= get_league_pos_row($oppid, $lgeid);
  $disp_str .= last_five_results($oppid, 5);

  /* If both teams' form is shown, $hora is always home */
  if($hora == "home") {
    $disp_str .= get_last_meeting($teamid, $oppid);
  } else {
    $disp_str .= get_last_meeting($oppid, $teamid);
  }

  $disp_str .= "</span>\n"; /* End of expand box */

  $disp_str .= "</table>\n</p>\n";

  return($disp_str);
}

/**
 * Function to add a form button with image and call to JavaScript
 * submission function.
 * @params:
 * $name: name of button
 * $img: name of image (relative to the images directory)
 * $action: name of JavaScript submission function
 * button_submit("11414", "l_arrow.png", "chng_date('m', '11414')");
 */
 function button_submit($name = "", $img = "", $action = "") {
   $disp_str = "<a href=\"#\" onclick=\"javascript:$action; return true;\">" .
                "<img src=\"images/$img\" alt=\"$img\"/>" .
                "</a>\n";
   return($disp_str);
}
/**
 * Function to display a simple league table with games played and points
 * for each team.
 * @params:
 *  $league_id: id of the league to dipslay
 *  $hid: optional home id for highlighting their record
 *  $aid: optional away id for highlighting their record
 * @returns:
 *  string containing HTML for the table
 */
function show_leaguetable($league_id = "0", $hid = "0", $aid = "0")
{
  $disp_str = "Form guide";

  if(!$league_id) {return($disp_str);}

  /* If no results found, create a blank table and relegation zones in the table
   */
  $nr = mdb2_fetchOne('league_results_count', array($league_id));
  echo "DEBUG: show_leaguetable: Found " . $nr . " rows for league $league_id.<br />\n";
  if( ! $nr) {
    echo "DEBUG: show_leaguetable: Empty league table for league $league_id; creating empty table.<br />\n";
    mdb2_query('new_league_table', array($league_id));
    $nr = mdb2_fetchOne('league_results_count', array($league_id));
  }
  $res = mdb2_query('show_league_table', array($league_id));
  if(PEAR::isError($res)) {
    return("Error: Cannot query league table.<br />\n");
  }
$tbl = $res->fetchAssoc();
print_r($res);

  /* If no results found, disable promotion, playoff
   * and relegation zones in the table
   */
  $no_results = (mdb2_fetchOne('num_league_results', array($league_id)) < $nr) ? 0 : 1;

  /* Get the playoff and relegation zones */
  $przres = mdb2_query('playoff_relegation_zone', $league_id);
  $przone = $przres->fetchRow();

  $lge_name = mdb2_fetchOne('league_name', array($league_id));
  $disp_str = "<table cellspacing=\"0\">\n" .
              "<tr><th>" . $lge_name . "</th><th>Pld</th><th>GD</th><th>Pts</th></tr>\n";

  $toggle = 1; /* row colour switcher */
  echo "DEBUG: show_leaguetable: Preparing table of " . $nr . " rows for $lge_name.<br />\n";
  
  while($tbl = $res->fetchRow()) {
    print_r($tbl);
    echo "$tbl";
    /* Get the position of the team in the league */
    $tpos = mdb2_fetchOne('get_league_position',
                        array($tbl['team_id'], $league_id));

    /* Show if the team has had points deducted */
    $tded = mdb2_fetchOne('get_points_deducted',
                        array($tbl['team_id'], $league_id));

    $tname = $tbl['team_name'] . ($tded ? " ($tded)" : "");

    $class = $toggle ? " class=\"pale\"" : "";
    if($no_results) {
      if($tpos < $przone['playoff_start']) { $class = " class=\"promotion\""; }
      if($tpos >= $przone['playoff_start'] &&
         $tpos <= $przone['playoff_end'])
      {
        $class = " class=\"playoff\"";
      }
      if($tpos >= $przone['relegate']) { $class = " class=\"relegation\""; }
    }
    if($tbl['team_id'] == $hid) { $class = " class=\"home\""; }
    if($tbl['team_id'] == $aid) { $class = " class=\"away\""; }
    
    $disp_str .= "<tr$class>\n" .
                 " <td>" . $tname            . "</td>" .
                 " <td>" . $tbl['played']    . "</td>" .
                 " <td>" . $tbl['goal_diff'] . "</td>" .
                 " <td>" . $tbl['points']    . "</td>" .
                 "</tr>\n";

    $toggle ^= 1;
  }
  $disp_str .= "</table>\n";

  return($disp_str);
}

/* Function to determine the MySQL equivalent on 'NOW()' based on the timezone
 * specified for a league
 *
 * params:
 *  $lge_id: id of the league whose timezone should be used to calulate NOW()
 * returnsL
 *  $now: string in the form "YYYY-MM-DD hh:mm"
 */
function now_by_timezone($lge_id = "")
{
  if(!$lge_id) {return("now_by_timezone: Missing league id.<br />\n");}

  $ltz = mdb2_fetchOne('get_league_timezone', array($lge_id));

  if(!$ltz) {return("Missing league timezone.<br />");}

  $now = new DateTime(null, new DateTimeZone($ltz));
  return($now->format('Y-m-d H:i'));
}

/* Get the user's timezone
 * params:
 *  $uid: integer id for the user.
 * @returns:
 *  $now: String containing datetime in the format "Y-m-d H:i:s"
 */
function get_user_now($uid = "") {
  if(!$uid) { return("Invalid userid."); }

  $tz = mdb2_fetchOne('get_user_timezone', array($uid));
  if(PEAR::isError($tz)) { return("User timezone error: " . $tz->getMessage() . "<br />\n"); }
  $date = new DateTime(null, new DateTimeZone($tz));
  return($date->format('Y-m-d H:i:s'));
}
?>
