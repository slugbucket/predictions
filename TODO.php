<?php
/*
 * Suggested changes:
 *
 * include/leagues.php
 *  - Only allow relegation/promotion submission at the end of the season:
 *  - Fix the dropdown lists in the leagues page (?action=leagues) so that
 *    the numbers in relegate/promote display clearly.
 *
 * include/fixtures.php
 *  - Only individual fixtures to be deleted.
 *  - Rename del_fixture function to del_fixture_set. Done, jur
 *  - call fixture_set_form if ERROR: is detected from del_fixture_set(). Done.
 *  - Detect repeated teams submitted in fixture list and highlight errors - done, jur, 200707013
 *  - Apply styling to the fixture set table. done
 *  - Include a link back to the main fixtures pages after saving/deleting. done
 *
 * include/hist.php
 *
 * include/navtabs.php
 *
 * include/results.php
 *
 * include/team.php
 *
 * include/connect.php
 *
 * include/leagues.php
 *  - disable promotion/relegation until the end of a season
 *
 * include/next.php
 *  - remove the 'Found 1 fixture sets.' from the top of the page. done, jur
 *  - Add all the predictions made this season in each league as expand/collapse
 *    boxes. done, jur 21 Aug 2007
 *
 * include/seasons.php
 *  - Disable start and end date if the season has ended - done, jur:20070701
 *  - Include form and function for adding a new season
 *  - Detect date changes before throwing error when only checking archive box
 *    of an old season.
 * - Only display the archive fixtures box when the season has ended. done
 *
 * include/users.php
 *  - Detect insert/update errors and redisplay forms - done, jur, 20070717
 *
 * include/fixtures.php
 *
 * include/leftnav.php
 *
 * include/options.php
 *
 * include/results.php
 * Only display matches that have already been played. done, jur 22 Aug 2007
 *
 * include/settings.php
 *  - Prepare settings to be used to use PEAR modules on an ISP site - done,
 *   jur, 20070708
 *
 * include/functions.php
 *
 * include/login.php
 *
 * include/queries.php
 *  - In 'next_fixtures' compare kickoff against NOW() - done, jur, 20070708
 *
 * include/summary.php
 * - include a list of the fixtures where the score or result has been correctly
 *   predicted, done, jur, 20070811
 * - Use tabs for each league for a display of the correctly predicted scores
 *   and results. done, jur 21 Aug 2007
 *   Added league_result_teams to $query_ary (queries.php) to extract the
 *   correctly predicted results (not) scores for a given league by a given
 *   user.
 *
 * include/users.php
 *  - Add a delete user option and delete all predictions and user team
 *    memberships.
 *
 * include/userteams.php
 *  When setting inactive, detect if the members have that as their only
 *  user team and mark the user account inactive if so.
 *
 */

/*  *** U P D A T E S ***
 * 2007-07-07 - Initial ISP upload
 *
 * 2007-07-08
 * include/queries.php:
 * Changed order by for fixture/prediction list display
 * Only future fixtures are displayed rather than after a fixed date (for testing)
 *
 * include/userteams.php
 * New script for managing userteams
 *
 * include/functions.php
 *  Added userteam_users_select() for users in a user team.
 *  Added get_valid_users() to extract valid user ids from posted data
 *
 * include/leftnav.php
 * Include link for Userteams in admin section
 *
 * index.php
 * Support ?action=userteams
 * Removed 'Main content' text.
 *
 * include/next.php
 *  removed verification debug output
 * include/options.php
 * Display list of leagues rather than userteams
 *
 * Database
 *  ALTER TABLE user_leagues ADD COLUMN created DATETIME DEFAULT '2007-07-01 08:00:00' AFTER name;
 *
 * Uploaded: 2007-7-09
 * -------------------
 *
 * 2007-07-09
 *
 * include/next.php
 *  - Display the kickoff time in the prediction list
 *
 * include/queries.php
 *  - modified next_set query to select kickoff as hours:minutes
 *
 * Uploaded: jur, 2007-07-09
 * -------------------------
 *
 * Database:
 * ALTER TABLE fixtures ADD COLUMN date_created DATETIME NOT NULL AFTER away_team_id;
 * ALTER TABLE fixtures ADD COLUMN last_updated DATETIME NOT NULL AFTER date_created;
 *
 * include/queries.php
 *  - Added query to remove fixtures from a given fixture_set
 *  - update_fixture query includes the last_updated column
 *  - add_new_fixture query includes the date_created and last_updated columns
 *
 * include/fixtures.php
 *  - del_fixture_set() now removes the fixtures as well as the fixture set, but
 *    only if the fixture_set is in the future
 *
 * Uploaded: jur, 2007-07-10
 * -------------------------
 *
 * 11 July 2007
 * ------------
 *
 * Database
 *  ALTER TABLE fixture_set ADD COLUMN num_fixtures TINYINT DEFAULT '0' AFTER league_id;
 * This is to allow the list of fixtures display form to be used again
 *
 * include/fixtures.php
 *  - removed fixed FIXME
 *  - added isadmin checks
 *  - re-written fixture set processing to use a new function fixture_list_form
 *    that handles the display of the fixture list and can be called after
 *    submitting fixture_set details or finding an error in a list submit.
 *
 * include/seasons.php
 *  - added isadmin checks
 *
 * include/users.php
 *  - added isadmin checks to show()
 *
 * include/results.php
 *  - added isadmin checks
 *
 * include/next.php
 *  - Changed form action to ?action= from /?action=
 *
 * Uploaded: jur, 2007-07-13
 * -------------------------
 *
 * include/login.php
 *  - renamed the login form to 'login'
 *
 * include/logout.php
 *  - Displays logout message
 *
 * include/leftnav.php
 *  - removed leading slash on logout link
 *
 * index.php
 *  - change setFocus login test to login.username.
 *  - added footer text
 *  - Added support for site logout
 *
 * predictions.css
 *  - Added styling for footer
 *
 * Uploaded: jur, 2007-07-13
 * -------------------------
 *
 * 17 July 2007
 *
 * include/users.php
 *  - modified users_add() to correctly detect an insert error and flag it
 *  - modified users_update() to correctly detect an update error and flag it.
 *    If passwd submitted by form md5 the supplied password before updating.
 *    Update using the calculated password rather than just the form version.
 *  - Traps errors from add/update and re-displays the user form.
 *
 * include/queries.php
 *  - Updated users_add/update queries to match the supplied positional params
 *
 * Uploaded: jur, 2007-07-17
 * -------------------------
 *
 * 18 July 2007
 * ------------
 *
 * include/summary.php
 *  - replaced current scoring with a tabular display of recent stats
 *
 * include/queries.php
 *  - Added correct result queries for last week and month for the user
 *
 * Uploaded: jur, 2007-07-18
 * -------------------------
 * 
 * 18 July 2007
 * ------------
 *
 * include/users.php
 *  - In users_update() pass $_SESSION['uid'] when retriving password from
 *    database
 *
 * Uploaded: jur, 2007-07-19
 * -------------------------
 *
 * 11 August 2007
 * --------------
 *
 * include/queries.php
 * - Changed 'today' to be <= NOW() in the predictions_this_* queries.
 * - Added queries to get the match teams that were correctly predicted.
 *
 * include/results.php
 * - Removed leading / from form actions that stopped result updates from
 *   being posted.
 *
 * include/summary.php
 * - Included the correctly predicted scores and results in tables
 *
 * Uploaded: jur, 2007-08-11
 * -------------------------
 *
 * 12 August 2007
 * --------------
 *
 * include/queries.php
 * - Changed the 'correct_result_teams' query to order by fixture_date and
 *   restrict the select to the past 7 days.
 * - Changed the 'correct_score_teams' query to order by fixture_date and
 *   restrict the select to the past 7 days.
 * - Added 'correct_result_teams' query to get the correct results for a league.
 * - Added 'correct_score_teams' query to get the correct results for a league.
 *
 * include/summary.php
 * - Summary information spread across several tabs.
 * - Only display the table if there are correct predictions.
 * - Expanded navtabs function to include tabs for the leagues
 * - Added summary_table() to format the summary table for display.
 * - Added list_correct_fixtures() to format a table that displays the correctly
 *   predicted scores or results.
 * 
 * Uploaded: jur, 2007-08-12
 * -------------------------
 *
 * 12 August 2007
 * --------------
 *
 * include/queries.php
 * - Added constraint to fixtures_in_season to only return fixture count when
 *   season has ended.
 * - General tidy-up and indicated which queries were used in which modules and
 *   functions where it had not already been specified.
 *   
 * 12 August 2007
 * --------------
 *
 * include/summary.php
 * - Changed the wording in the season summary table for results and scores.
 *
 * Uploaded: jur, 2007-08-13
 * -------------------------
 *   
 * 14 August 2007
 * --------------
 *
 * include/queries.php
 * - Update correct_score_teams, correct_result_teams, league_score_teams and
 *   league_result_teams to only include results (and scores) up to and
 *   including today.
 *
 * include/fixtures.php
 * - Made the messages displayed at the top of the page a bit more
 *   meaningful.
 *
 * Uploaded: jur, 2007-08-14
 * -------------------------
 *   
 * 15 August 2007
 * --------------
 *
 * include/queries.php
 * - Updated results_fixtures query to select only fixtures at least 90 minutes
 *   after the match has kicked off
 * - Added check_end_of_fixture query to prevent submission of results for
 *   matches before they have been completed.
 * - Changed fixture_set_list to select descending fixture_set details so that
 *   the most recent fixture set is shown at the top.
 * - Changed correct_result_teams, correct_score_teams, league_result_teams and
 *   league_score_teams to display the scores
 *   and results starting with the most recent.
 *
 * include/results.php
 * - Updated add_results to check that submitted results are not for matches
 *   that have not completed yet.
 * - Added a message to show_fixture_sets() to indicate that highlighted
 *   fixture sets have already had results submitted.
 *
 * include/functions.php
 * - Added stripslash and stripslashGpc functions to remove gpc slashes
 *
 * include/userteams.php
 * - Updated userteams_add to strip slashes from the team name.
 *
 * Uploaded: jur, 2007-08-15
 * -------------------------
 *   
 * 17 August 2007
 * --------------
 *   
 * include/functions.php
 * - Added function expand_contract_js() to allow expand/contract sections to
 *   display the seasons previous predictions for the league.
 * - Added function display_league_predictions() to display all the seasons
 *   predictions so far using expand/contract div elements. The functions
 *   is called from show() when displaying a league summary.
 *
 * expand.js
 * - The expand/contract JavaScript functions
 *
 * index.php
 * - Load the expand/contract JavaScript functions in expand.js.
 *
 * include/summary.php
 * - Added call to display_league_predictions on each league tab
 *
 * include/next.php
 * - Added call to display_league_predictions on each league tab
 *
 * include/queries.php
 * - Added query user_predictions_season to retrieve all the predictions made
 *   in the season by the logged in user.
 *
 * predictions.css
 * - Added Styles for expand/contract display elements: contract, expand,
 *   season (with anchor element actions)
 * - Added styles for summary and season prediction tables
 *
 * images/more.gif
 * - image for display in the expand/contract box
 *
 * images/less.gif
 * - image for display in the expand/contract box
 *
 * Uploaded: jur, 2007-08-18
 * -------------------------
 *   
 * 17 August 2007
 * --------------
 *   
 * include/summary.php
 * - added styling to table created by list_correct_fixtures()
 *
 * include/next.php
 * - Used colgroups and dedicated styles for improved formatting in next_form()
 *
 * predictions.css
 * - Added nextset, goodscores and summary styles
 *
 * Uploaded: jur, 2007-08-18
 * -------------------------
 *
 * 17 August 2007
 * --------------
 *   
 * On the results page, future fixture sets are displayed in the list but
 * show no fixtures when submitted.
 * Error when changing the date of a fixture after the set has already been
 * saved.
 * This appears to be caused by a fixture getting an id of 0 which is not
 * caught by the check for whether the fixture already exists or not.
 * - include/fixtures.php
 *   Corrected set_fixture() wth if(isset($isfixt)) for fixture existence test
 *
 * On the results page, future fixture sets are displayed in the list but
 * show no fixtures when submitted.
 * - include/queries.php
 *   Changed results_fixture_sets query to only return fixture sets where
 *   fixture_set.start_date <= NOW()
 * - include/results.php
 *   Added list of functions to the top of the script.
 *
 * After deleting a fixture set, display the list of fixture sets and request
 * a new set.
 * include/fixtures.php
 * - Changed fixture_set_details() to add output from list_fixture_sets()
 *   after a fixture set has been deleted. Note that the site does not allow
 *   fixture sets dated in the past to be removed.
 * - Included check for error from fixture set deletion and redisplayed the
 *   fixture set form in addition to the error message.
 * Include a back to fixture set list on the fixture set entry form.
 * - include/fixtures.php
 *   Added a link back to the fixture set list in show() before displaying
 *   the output from fixture_set_details().
 *
 * When an away team is entered more than once, it is the home team that is
 * identified as the duplicate.
 * Enter a fixture with the away team duplicated.
 * include/fixtures.php
 * - Fixed fixtures_validate() which returned the home team name in the error
 *   message when an away team was duplicated.
 *
 * Center the table elements on the group table (action=team) page.
 * predictions.css
 * - Added grouptable class to centre text
 * include/team.php
 * - Added grouptable class to tr display in show()
 *
 * Uploaded: jur, 2007-08-21
 * -------------------------
 *
 * 21 August 2007
 * --------------
 *   
 * Replace database query success checks with PEAR::isError rather than just
 * whether the result set exists or not (if($res)).
 *
 * include/leagues.php
 * - Updated leagues_save() to use the PEAR check
 * - Update save_new_team() to use the PEAR check
 *
 * include/next.php
 * - Updated add_predictions() to use the PEAR check
 *
 * include/seasons.php
 * - Updated archive_season() and seasons_update() to use the PEAR check
 *
 * Uploaded: jur, 2007-08-21
 * -------------------------
 *
 * 22 August 2007
 * --------------
 *
 * predictions.css
 * - Added style for select elements so that text is not hidden
 *   
 * include/leagues.php
 * - Added list of functions to the top of the script
 *
 * include/queries.php
 * - added functions where season queries are used
 * - Added new_season_details query to return default new season details
 *
 * include/seasons.php
 * - Added form in add_new_season_form() to request details for new seaon
 * - navtabs returns 'New season' as the tab when requesting a new season
 *
 *
 * include/functions.php
 * - make_season_date() checks for whether $sid is set rather than has a value
 *
 * Uploaded: jur, 2007-08-22
 * -------------------------
 *
 * 25 August 2007
 * --------------
 *
 * include/queries.php
 * - Excluded abandoned and postponed matches from result and score count
 *
 * Uploaded: jur, 2007-08-25
 * -------------------------
 *
 * 26 August 2007
 * --------------
 *
 * The update to fixtures.php on 17 August 2007 actually prevents fixtures from
 * being added even though no error is generated.
 * Modified set_fixture() to check for the number of affected rows when doing
 * an update or insert as well as backing out the isset($isfixt) test which
 * returns true even when 0 columns are found and so attempts an update
 * - include/fixtures.php
 *
 * include/queries.php
 * - Updated function names where some queries are used.
 *
 * Uploaded: jur, 2007-08-26
 * -------------------------
 *
 * 27 August 2007
 * --------------
 *
 * Implemented method to include AJAX scripts into the pages for actions that
 * require them. If the file, ajax/$action.php, exists a line is added into the
 * page to include it as a JavaScript source.
 * The script should contain the following:
 * createRequestObject()
 * sndReq()
 * handleResponse()
 * And these extract the relevant form attributes, submit and handle the
 * submission to a PHP script to return the relevant details
 *
 * index.php
 * - Includes code to include ajax/$action.js if it exists
 *
 * ajax/fixtures.js
 * - Updates the num_fxtures text box with the number of teams in the league
 *   selected in the 'leagues' dropdown select element.
 *
 * include/functions.php
 * - Updated league_select() to accept an attributes array that can be used to
 *   set the onchange to be an AJAX submit fuction.
 *
 * include/fixtures.php
 * - Updated fixture_set_details() to set an id attribute on the num_fixtures
 *   text box and the call to league_select() sends an onchange event with a
 *   call to an AJAX sndReq() function to update the num_fixtures box.
 *
 * rpc.php
 * - Receives the AJAX requests from form elements and returns the requested
 *   data.
 *
 * Uploaded: jur, 2007-08-27
 * -------------------------
 * 
 * 28 August 2007
 * --------------
 *
 * Allow the display of another user's predictions using AJAX.
 * It is important to note that the function called by rpc.php to update a named
 * element doesn't include the element definition itself.
 *
 * index.php
 * - include rpc.php if $_GET['do'] == 'rpc'
 *
 * rpc.php
 * - List operations within conditionals of expected inputs
 * - Checks that a user login token is available before continuing
 *
 * predictions.css
 * - Alter season elements to allow redisplay on the predictions page
 *
 * ajax/next.php
 * - Handle prediction update requests
 *
 * include/queries.php
 * - Added is_user_in_same_team and logged_in_user_team_members to verify the
 *   the list of users in the logged-in user's team groups
 * - Updated user_predictions_season query so that it doesn't return
 *   anything for fixtures not predicted for.
 * 
 * include/next.php
 * - normal display consists of a call to display_league_predictions()
 *   followed by season_predictions() which now just includes the season's
 *   predictions so far.
 * - Added call to new function display_all_userteam_users() to display a list
 *   of userteam users.
 *
 * include/functions.php
 * - split the display of the entire season's predictions into a new function,
 *   season_predictions(), so that the rpc call can just update the season
 *   element id.
 * - Changed name of display_league_predictions() to reflect the change
 *   that all it does it display a list of all userteam users. It is now
 *   called display_all_userteam_users().
 *
 * include/summary.php
 * - Removed call to display_league_predictions()
 *
 * Uploaded: jur, 2007-08-28
 * -------------------------
 * 
 * 28 August 2007
 * --------------
 *
 * Introduced a help page with general documentation for each section.
 *
 * index.php
 * - Added help section to the action driver
 *
 * predictions.css
 * - Added style for help content.
 *
 * include/help.php
 * - Created show() and navtabs() functions for the help section.
 *
 * include/leftnav.php
 * - Added Help link to left-hand navigation column
 *
 * include/summary.php
 * - The correct scores row now indicates a x3 multiplier for the figures.
 *
 * include/queries.php
 * - Updated correct_result_teams query to only list correct results from the
 *   past week for the summary page.
 *
 * Uploaded: jur, 2007-08-28
 * -------------------------
 * 
 * 30 August 2007
 * --------------
 *
 * Implement a score table to record the scores of individual users so that a
 * 'Top 5' table can be displayed and the user list in the group table can be
 * ordered with the highest scorer at the top.
 * The 'Top 5' list will be shown on the summary page.
 * The user scores will be implemented as a 'Update scores' rpc link on the 
 * users page.
 *
 * Database:
DROP TABLE predict_user_scores;
CREATE TABLE predict_user_scores (
  user_id INT(10) NOT NULL UNIQUE,
  num_predictions INT(10) NOT NULL DEFAULT '0',
  correct_results INT(10) NOT NULL DEFAULT '0',
  correct_scores INT(10) NOT NULL DEFAULT '0',
  points INT(10) NOT NULL DEFAULT '0',
  last_updated DATETIME NOT NULL DEFAULT '2007-08-11 12:00:00'
) COMMENT='Table with the sum of the user correct score and exact result predictions';
 *
 * index.php
 * - Hide login focus javascript if user is logged in.
 *
 * predictions.css
 * - corrected invalid width, height and font-weight style entries.
 * - Added styles for top 5 users.
 * - Added updatedusers style when calculating user scores.
 *
 * rpc.php
 * - Add hendler to call the update_user_scores() function to apply the points
 * for users.
 *
 * ajax/users.js
 * - JavaScript to send request and apply the updated page content in a div
 *
 * ajax/results.js
 * - JavaScript to send request and apply the updated page content in a div
 *
 * include/functions.php
 * - Added update_user_scores_form() and update_user_scores() functions to
 *   generate the update users form and update the points for users.
 *
 * include/users.php
 * - Include the update users form at the bottom of the page.
 *
 * include/queries.php
 * - Added queries: user_score_delete and user_score_insert to allow for
 * user scores to be recorded.
 * - Added userteam_points_ordered to extract the details for members of a
 *   userteam.
 * - Added top_five_users query to get the 5 best scoring users.
 *
 * include/results.php
 * - Included the update_user_scores_form() after results have been saved
 *   for a league. The update is performed by clicking the 'Update scores'
 *   button and an RPC call, but perhaps this page should do the update
 *   anyway without the button.
 *
 * include/summary.php
 * - Updated summary_table() to use a myseason div aroud the basic season
 *   summary table.
 * - Included a call to top_five_users on the general summary tab.
 * - Added a season style around the summary table and 'top 5' users.
 *
 * include/team.php
 * - Replaced user scores and points with fields extracted from the
 *   predict_user_scores table so that they can be ordered.
 * 
 * Uploaded: jur, 2007-08-31
 * -------------------------
 * 
 * 01 September 2007
 * -----------------
 *
 * include/functions.php
 * - swapped true and false for 1 and 0 in expand_contract_js.
 *
 * predictions.css
 * - corrected parts of topfive styles to ensure they work in IE7.
 *
 * 
 * Uploaded: jur, 2007-09-01
 * -------------------------
 * 
 * 02 September 2007
 * -----------------
 *
 * Fixed expand/contract display in IE.
 * expand.js
 * - Incorrect IE document object referenced for style property in the
 *   setIdProperty() and getIdProperty() function. The style property is found
 *   in: document.all.item(id).style[property]
 *
 * Uploaded: jur, 2007-09-02
 * -------------------------
 *
 * 02 September 2007
 * -----------------
 *
 * Highlight historic predictions that were a correct result or exact score.
 *
 * predictions.css
 * - Added bluerow and greenrow styles (similar to greyrow) to highlight whether
 *   a previosuly predicted score was the right result or exact score.
 *
 * include/functions.php
 * - Updated season_predictions() to set a row class of 'bluerow' or 'greenrow'
 *   when a fixture can be identified as either a correct result or an exact
 *   score. Also included a brief message telling what the coloured rows
 *   indicate.
 *
 * include/queries.php
 * - Added is_correct_outcome and is_exact_score queries to return how accurate
 *   an individual query by a user was.
 *
 * Uploaded: jur, 2007-09-02
 * -------------------------
 *
 * 02 September 2007
 * -----------------
 *
 * Add a simple messaging system to the group table pages to allow users to
 * post comments visible to other group members.
 * FCKeditor will be used to add the comments.
 *
 * Database
DROP TABLE IF EXISTS predict_comments;
CREATE TABLE predict_comments (
  id INT(10) NOT NULL UNIQUE PRIMARY KEY,
  user_id INT(10) NOT NULL,
  group_id INT(10) NOT NULL,
  title CHAR(64) NOT NULL DEFAULT 'Message title',
  message TEXT,
  posted DATETIME NOT NULL
) COMMENT='Table for group comments posted by users';

CREATE TABLE predict_comments_seq( id INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT) COMMENT='Sequence for ids for user comments';

INSERT INTO predict_comments_seq VALUES('0');
 *
 * predictions.css
 * - Added #comments styles to display user-submitted comments.
 *
 * rpc.php
 * - Handler for incoming comment removal equest.
 *
 * ajax/team.js
 * - JavaScript functions to eanble removal of comments by an admin
 *
 * include/functions.php
 * - Added function insert_user_comment to insert a user comment.
 * - Added show_user_comments to display comments
 * - Added function delete_user_comment to remove an existing user comment.
 * - Updated show() to process incoming submission and display comments posted
 *   so far.
 *
 * include/queries.php
 * - Added insert_user_comment to insert a new user comment
 * - Added show_user_comments to extract comments for a user team
 *
 * include/team.php
 * - Added FCKeditor setup and display code in show()
 * - Added code to show() to trap comment submission and element to display the
 *   comments posted so far.
 *
 * Uploaded: jur, 2007-09-05
 * -------------------------
 *
 * 02 September 2007
 * -----------------
 * Change the summary display so that only the most recent 5 correct scores and
 * results are shown on the summary page rather than for the past week. Also
 * fix the query so that the matches are listed in correct date order.
 *
 * include/queries.php
 * - Changed correct_score_teams to SELECT the formatted fixture_date AS
 *   match_date so that ORDER BY fixture_date is on the actual date rather than
 *   the formatted string. Also LIMITed results to a max of 5.
 * - Changed correct_result_teams to SELECT the formatted fixture_date AS
 *   match_date so that ORDER BY fixture_date is on the actual date rather than
 *   the formatted string. Also LIMITed results to a max of 5.
 * - Changed league_result_teams and league_score_teams to SELECT the
 *   formatted fixture_date AS match_date.
 *
 * include/summary.php
 * - Modified list_correct_fixtures() to use 'match_date' from the tab_type
 *   query because fixture_date is used for ORDER BY.
 *
 * Uploaded: jur, 2007-09-05
 * -------------------------
 *
 * 05 September 2007
 * -----------------
 * Reskin the site to fit in better with the main YJFC site.
 *
 * index.php
 * - Add a styled 'showuser' banner under the top image to display the
 *   login message
 *
 * predictions.css
 * - Many styling changes.
 * - Rationalise the following styles:
 *   predictions
 *   myseason
 *   leagues
 *   usertable
 *   goodscores
 *   nextset
 * - Added #login style to clear any background effects for the login box
 *
 * include/fixtures.php
 * - Updated list_fixture_sets() to use tr.header and tr.body styles for
 *   predictions div and created style item for checkboxes
 *
 * include/functions.php
 * - Updated season_predictions() and expand_contract_js() for the new,
 *   improved season predictions list
 *
 * include/leagues.php
 * - Updated show_league_teams() to use tr.header and tr.body styles for
 *   leagues div and created style item for radio buttons
 *
 * include/leftnav.php
 * - Current selection is now displayed as a link to simplify CSS formatting
 *
 * include/login.php
 * - Displayed the login form within it's own div.
 *
 * include/next.php
 * - Changed <td> to <th> in next_form
 *
 * include/results.php
 * - Updated results_form() to use tr.header and tr.body styles for
 *   predictions div
 *
 * include/summary.php
 * - Updated summary_table() to use tr.header and tr.body styles for myseason
 * - Updated list_correct_fixtures() to use tr.header and tr.body styles for
 *   myseason div
 *
 * include/team.php
 * - Updated show() to use tr.header and tr.body styles for predictions tab.
 *
 * include/users.php
 * - Updated list_all_users() to use tr.header and tr.body styles for
 *   leagues div
 *
 * include/userteams.php
 * - Updated list_all_users() to use tr.header and tr.body styles for
 *   leagues div
 *
 * Uploaded: jur, 2007-09-07
 * -------------------------
 *
 * 05 September 2007
 * -----------------
 * Move the top 5 user list to be under the left menu, even with the login form
 *
 *
 * index.php
 * - Included call to top_five_users() after displaying left nav or login box
 *
 * predictions.css
 * - Changed topfive and login styles to use the grey background image
 *
 * include/summary.php
 * - Removed call to top_five_users() from show()
 *
 * Uploaded: jur, 2007-09-08
 * -------------------------
 *
 * 05 September 2007
 * -----------------
 * Line break required in summary table for this week, last week , last month.
 *
 * include/summary.php
 * - Changed colgroup width percentages in summary_table()
 *
 * Uploaded: 2007-09-09
 * -------------------------
 *
 * 05 September 2007
 * -----------------
 * Implement a form system for the predictions page to display how well the 
 * selected team is doing. Show the following
 *  o Number of games/lost/won won at home/away
 *  o Goals scored/conceded at home/away
 * Test the display with:
 * http://predictions/index.php?do=rpc&where=home&team=10&lge=1
 *
 * predictions.css
 * - Changed styles for tableview: reduced width and increased font size.
 * - Added formguide id for the on-screen form guide with absolute positioning
 * - Fixed problem in IE for expand/contract style appearing white on white
 *
 * rpc.php
 * - Added trap for formguide submission
 *
 * ajax/next.js
 * - Added functions to handle formguide update request
 *
 * include/functions.php
 * - Add a function get_team_form() to show the team's form in the formguide div
 * - Added form_guide_row() to format the form table row to display
 *
 * include/next.php
 * - Changed colgroup width ratios in next_form()
 * - Added display code for formguide div element
 *
 * include/queries.php
 * - Added the following queries:
 *   home_games_played
 *   home_goals_for
 *   home_goals_against
 *   home_games_won
 *   home_games_drawn
 *   home_games_lost
 *   away_games_played
 *   away_goals_for
 *   away_goals_against
 *   away_games_won
 *   away_games_drawn
 *   away_games_lost
 *   Added select for home and away team id to next_fixtures
 *
 * Uploaded: jur, 2007-09-16
 * -------------------------
 *
 * 12 September 2007
 * -----------------
 *
 * predictions.css
 * - Added spacing to topfive id and changed table background color to #fff
 *
 * Uploaded: jur, 2007-09-12
 * -------------------------
 *
 * 12 September 2007
 * -----------------
 * All comments get posted to the first userteam.
 *
 * include/team.php
 * - Form for adding a comment in show() amended to include the team number
 * ($_GET[tabnum]) in the action.
 *
 * Uploaded: jur, 2007-09-13
 * -------------------------
 *
 * 15 September 2007
 * -----------------
 * Updating a set of predictions resulted in a page that didn't indicate that
 * any updates had been registered.
 *
 * include/queries.php
 * - The comment before 'del_prediction' was not terminated and was thus not
 *   being performed. Terminating the comment enabled the query.
 *
 * include/next.php
 * - The add_predictions() function only displayed a message if the insert
 *   (after a delete if running an update) succeeded. Added a trap to fire
 *   an error message if the update fails.
 *
 * Uploaded: jur, 2007-09-15
 * -------------------------
 *
 * 25 September 2007
 * -----------------
 * Weight the leader board display according to the best score-predictions
 * ratio.
 *
 * preamble.html
 * - Added details of the recently added features.
 *
 * predictions.css
 * - tweaked topfive styles to accomodate the percentage rating.
 *
 * include/functions.php
 * - Changed top_five_users() to display the percentage rating instead of the
 *   total number of points
 *
 * include/queries.php
 * - Changed userteam_points_ordered and top_five_users to order by the number
 *   of points divided by the number of predictions.
 * - top_five_users select the pecentage rating for the top five users.
 *
 * include/team.php
 * - Added an extra column showing the percentage accuracy for the users.
 *
 * Uploaded: jur, 2007-09-25
 * -------------------------
 *
 * 26 September 2007
 * -----------------
 * Adding users on the userteam page does not update the users in the team or
 * present the new team on the group table page.
 *
 * include/users.php
 * - Changed users_add() to create an blank entry in the predict_user_scores
 *   table so that the newly added user will appear on the group table page.
 *
 * include/userteams.php
 * - Corrected errors in userteams_add() and userteams_update() that passed
 *   the wrong value of the team id to functions::userteam_members()
 *
 * Uploaded: jur, 2007-09-26
 * -------------------------
 *
 * 29 September 2007
 * -----------------
 * A team can be entered into two fixtures in a fixture set if it is specified
 * as the home and away team
 *
 * include/fixtures.php
 * - Corrected fixtures_validate() to report a duplication error when validating
 *   the home team if that team's id has been passed as an away team field.
 *
 * Uploaded: jur, 2007-09-29
 * -------------------------
 *
 * 29 September 2007
 * -----------------
 * Use FusionCharts to display graphs of the current month's performance
 *
 * predictions.css
 * - Added monthgraphs, monthpredsDiv, monthresultsDiv to control the display
 *   of the graphs on the page.
 *
 * FusionChartsFree/Charts/*.swf
 * - Charts used by the graphing system
 *
 * FusionChartsFree/Code/PHP/Includes/FusionCharts.php
 * - PHP script for the FusionCharts renderer
 * - Removed 'align="center' from chart div definition
 *
 * FusionChartsFree/JSClass/FusionCharts.js
 * - JavaScript classes for the Fusion Chart graphs
 *
 * include/queries.php
 * - Added results_bydate, user_predictions_bydate, scores_bydate,
 *   fixture_set_this_month to extract monthly data for a given user id
 *
 * include/team.php
 * - Added Multi-segment line graphs for the previous month's form
 *
 * Uploaded: jur, 2007-09-30
 * -------------------------
 *
 * 01 October 2007
 * ---------------
 * Errors displayed on the group table page at the start of the month.
 *
 * predictions.css
 * - removed border from monthgraphs div
 *
 * include/queries.php
 * - fixture_set_this_month2 was missing a '>' that prevented selecting a set
 *   of fixtured from 14 days ago. fixture_set_this_month query only selects
 *   from the current month after day 6
 *
 * include/team.php
 * - Only display the graphs if there are fixture sets available
 *
 * Uploaded: jur, 2007-10-01
 * -------------------------
 *
 * 05 October 2007
 * ---------------
 * User cannot login after changing their own password.
 *
 * include/queries.php
 * - stopped using md5 in the SQL queries
 *
 * include/options.php
 * - save_password() uses md5 to encode the submitted password.
 *
 * Uploaded: jur, 2007-10-05
 * -------------------------
 *
 * 18 October 2007
 * ---------------
 * Support tournaments such as FA Cup, European Championship, etc,.
 * The tournament is added by setting a flag on a normal league with a flag
 * for use in a tournament setting.
 *
 * Database:
 * ALTER TABLE leagues ADD COLUMN tournament BOOL NOT NULL DEFAULT '0';
 *
 * ajax/leagues.js
 * - function add_to_league() to remove the selected teams from the
 *   available list and add them to the league member list
 * - function remove_from_league() to remove the selected teams from the
 *   league member list and add them to the available list
 * 
 * include/leagues.php
 * The league section should be broken down into two sections:
 *  1. Manage use in tournament and promotion/relegation.
 *  2. Team membership.
 * - Modified show() to trap new league form and submission
 * - add_new_league_form() for submitting a new league including one to be used
 *   for a tournament.
 * - a league cannot be updated for use in a tournament if fixtures have
 *   already be set for the league.
 * - added leagues_insert() to save the details of a new league.
 * - Added league_members_save() to update the league membership
 * - Updated show_league_teams() to display the league membership form
 * - Moved add team button to the membership form.
 *
 * include/leftnav.php
 * - Include a menu item for tournaments
 *
 * include/queries.php
 * - Added 'league_name_exist' to check whether a league already exists.
 * - Added 'league_has_fixtures' to indicate whether a league can be changed to
 *   support a tournament.
 * - Added 'league_is_tournament' to get the current tournament status flag
 * - Added 'teams_in_league' to get the teams in a league
 * - Added 'teams_not_in_league' to get the teams not in the league
 * - Added 'is_valid_team' to verify that a submitted team id exists.
 * 
 * include/tournaments.php
 * Module for arranging tournaments
 * - show() - main driver
 * - navtabs() - display tournament being edited.
 * - tournaments_preload() - dummy function
 * - tournaments_list() - dummy function
 * - tournaments_insert() - dummy function
 * - tournaments_update() - dummy function
 * - tournaments_delete() - dummy function
 *
 * index.php
 * - support the 'tournaments' action.
 *
 * predictions.css
 * - Added addremlge id for add remove buttons
 *
 * Uploaded: 23 November 2007
 * --------------------------
 *
 * 20 October 2007
 * ---------------
 * Limit the top five users to those that have made predictions in the past
 * 14 days
 *
 * include/queries.php
 * - Changed top_five_users to only select scores from those users that have
 *   predicted in the last 14 days.
 *
 * Uploaded: jur, 2007-10-20
 * -------------------------
 *
 * 29 October 2007
 * ---------------
 * Fixture date selection dropdown is affected by daylight saving time.
 * In include/functions.php, fixture_date_select() converts the start and end
 * date to a time in seconds using strtotime() and adds 86400 to each value
 * formats the display and value using date().
 *
 * include/fixtures.php
 * - Tweaked fixture_list_form() so that the $mdate passed to
 *   fixture_date_select() uses "02:00:00" to prevent daylight saving weirdness
 *
 * Backed out
 *
 * Uploaded: jur, 2007-10-29
 * -------------------------
 *
 * 02 November 2007
 * ---------------
 * Display the number of predictions that a user has not made.
 *
 * include/queries.php
 * - Added 'not_yet_predicted' to count the number of predictions that a user
 *   has not yet made.
 *
 * include/summary.php
 * - in show(), if there are outstanding predictions, display how many.
 *
 * Uploaded: jur, 2007-11-06
 * -------------------------
 *
 * 04 November 2007
 * ---------------
 * Put the list of users at the top of the list of the season's predictions
 *
 * include/next.php
 * - Modifed show() to call display_all_userteam_users() before
 *   season_predictions()
 * - Discovered that show() was using an old, hard-coded date that broke the
 *   display of the season prediction list when changed to be the current date.
 *   This was because the switch($set_qry->numRows()) for the 'next_set' query
 *   only displayed a 'no predictions' messages without displaying the seaon
 *   record.
 *   Commented out sections of the switch so that the season prediction list
 *   is displayed regardless of the number of predictions the user has for
 *   display.
 *
 * Uploaded: jur, 2007-11-06
 * -------------------------
 *
 * 05 November 2007
 * ----------------
 * Add the outcome of the last five matches to the form guide
 *
 * include/queries.php
 * - Add last_five_results to extract the most recent outome starting with the
 *   most recent.
 *
 * include/functions.php
 * - Update get_team_form() to include the outcome of the most recent matches.
 * - Added last_five_results() to get the match outcomes.
 *
 * Uploaded: jur, 2007-11-06
 * -------------------------
 *
 * 05 November 2007
 * ----------------
 * save_new_team() uses the wrong sequence table to get new team ids.
 * Database:
 * CREATE TABLE predict_teams_seq ( id INT(10) NOT NULL PRIMARY KEY)
COMMENT='sequence table for predtictable teams in a league';
 * INSERT INTO predict_teams_seq(id) VALUES('105');
 *
 * Uploaded: 23 November 2007
 * --------------------------
 *
 * 05 November 2007
 * ----------------
 * Database corrections:
 * Incorrect name for predict team, 101,
 * UPDATE predict_teams SET name = 'Rushden & Diamonds' WHERE id = '101';
 *
 * Move Morecombe from Blue Square Premier to league 2,
 * UPDATE league_teams SET league_id = '4' where team_id = '105';
 *
 * Remove Boston Utd from the Blue Square Premier
 * UPDATE league_teams SET league_id = '0' where team_id = '71';
 *
 * Add missing teams from Blue Square Premier
 * Stevenage, Cambridge United, Burton Albion, Forest Green Rovers, Histon,
 * Kidderminster, Grays Athletic, Crawley Town. Weymouth, York City,
 * Stafford Rangers, Droylesden, Northwich, Ebbsfleet United
 *
 * Uploaded: 23 November 2007
 * --------------------------
 *
 * 24 November 2007
 * ----------------
 * When new leagues are submitted, they are not displayed in the top nvabar
 * until another link has been clicked.
 *
 * index.php
 * - navtabs() is now called after show() so that any changes that apply will
 *   be reflected in the navigation menus.
 *
 * Uploaded: 24 November 2007
 * --------------------------
 *
 * 25 November 2007
 * ----------------
 * Show the top 6 users
 *
 * include/functions.php
 * - Updated display in top_five_users() for 'Top 6 users'
 *
 * include/queries.php
 * - Change LIMIT in top_five_users to 6
 *
 * Uploaded: 25 November 2007
 * --------------------------
 *
 * 03 December 2007
 * ----------------
 * Form guide to show league position of the team
 * Add a new table for the league tables to be updated when results are
 * submitted.
 * Database:
DROP TABLE IF EXISTS league_table;
CREATE TABLE league_table (
  team_id INT(10) NOT NULL REFERENCES predict_teams,
  league_id INT(10) NOT NULL REFERENCES leagues,
  home_wins SMALLINT NOT NULL DEFAULT '0',
  home_draws SMALLINT NOT NULL DEFAULT '0',
  home_losses SMALLINT NOT NULL DEFAULT '0',
  home_goals_for SMALLINT NOT NULL DEFAULT '0',
  home_goals_against SMALLINT NOT NULL DEFAULT '0',
  away_wins SMALLINT NOT NULL DEFAULT '0',
  away_draws SMALLINT NOT NULL DEFAULT '0',
  away_losses SMALLINT NOT NULL DEFAULT '0',
  away_goals_for SMALLINT NOT NULL DEFAULT '0',
  away_goals_against SMALLINT NOT NULL DEFAULT '0',
  points SMALLINT NOT NULL DEFAULT '0',
  PRIMARY KEY(team_id,league_id)
) COMMENT='Table with team positions for the leagues';
 *
 * A new table is not required as the fixtures and fixture_results tables
 * can be queried to dynamically create the league table.
 *
 * include/functions.php
 * - Added get_league_position() to query the database for the league table and
 *   then identify a team's position within the returned table
 * - Added get_league_pos_row() to format the result from get_league_position()
 *   as a row for the form guide, or return "" if team position not found
 * - Updated get_team_form() to display team's league position
 *
 * include/queries.php
 * - Add league_table query to create a league table ordered by points gained
 *   for wins and draws followed by goal difference
 *
 * Uploaded: 06 December 2007
 * --------------------------
 *
 * 03 December 2007
 * ----------------
 * Season list of predictions to be presented in two columns.
 *
 * Uploaded: 
 * --------------------------
 *
 * 03 December 2007
 * ----------------
 * Test server gives error when selecting a fixture set to enter results for
 *
 * include/queries.php
 * - Changed 'results_fixtures' so that the fixtures table is joined directly
 *   with the fixture_results table.
 *
 * Uploaded: 06 December 2007
 * --------------------------
 *
 * 03 December 2007
 * ----------------
 * Data cleanup:
 * DELETE FROM fixtures WHERE fixture_date='2007-11-01';
 *
 * Uploaded: 05 December 2007
 * --------------------------
 *
 * 11 December 2007
 * ----------------
 * Implement storing of the league table because the creation of the table can
 * be done as a single query.
 * Form guide to show league position of the team
 * Add a new table for the league tables to be updated when results are
 * submitted. This will allow for a more efficient query of the position of
 * team in the league.
 *
 * Database:
DROP TABLE IF EXISTS league_table;
CREATE TABLE league_table (
  team_id INT(10) NOT NULL REFERENCES predict_teams,
  league_id INT(10) NOT NULL REFERENCES leagues,
  home_wins SMALLINT NOT NULL DEFAULT '0',
  home_draws SMALLINT NOT NULL DEFAULT '0',
  home_losses SMALLINT NOT NULL DEFAULT '0',
  home_goals_for SMALLINT NOT NULL DEFAULT '0',
  home_goals_against SMALLINT NOT NULL DEFAULT '0',
  away_wins SMALLINT NOT NULL DEFAULT '0',
  away_draws SMALLINT NOT NULL DEFAULT '0',
  away_losses SMALLINT NOT NULL DEFAULT '0',
  away_goals_for SMALLINT NOT NULL DEFAULT '0',
  away_goals_against SMALLINT NOT NULL DEFAULT '0',
  points SMALLINT NOT NULL DEFAULT '0',
  PRIMARY KEY(team_id,league_id)
) COMMENT='Table with team positions for the leagues';
 *
 * include/functions.php
 * - Changed get_league_position() to use the get_league_position query to
 *   extract the league position.
 *
 * include/queries.php
 * - Added delete_league_table query to remove current league table so that a
 *   new one can be inserted.
 * - Added insert_league_table query to create the table for a given league
 * - Added get_league_position query to extract the position of a team in
 *   the league
 *
 * include/results.php
 * - Updated add_results() to use delete_league_table and insert_league_table
 *   queries to update the league table with the submitted results.
 *
 * Uploaded: 12 December 2007
 * --------------------------
 *
 * 11 December 2007
 * ----------------
 * When submitting fixtures remove any orphan fixtures and fixture sets.
 *
 * include/fixtures.php
 * - Extended set_fixture() to remove orphan fixture sets
 * - Added section to del_fixture_set() to delete the fixture_set passed to
 *   the function (from set_fixture()). Also added section to remove orphan
 *   fixtures.
 *
 * include/queries.php
 * - Added get_orphaned_fsets to retrieve fixture sets with not matching
 *   fixtures
 * - Added get_orphaned_fixtures query to get orphaned fixtures
 * - Added delete_fixture query to delete a given fixture
 *
 * Uploaded: 16 December 2007
 * --------------------------
 *
 * 12 December 2007
 * ----------------
 * Re-displayed previously submitted results does not show if the match was
 * postponed or abandoned and could be erroneously re-submitted.
 *
 * include/queries.php
 * - Added result_type to the SELECTed columns in results_fixtures query.
 *
 * include/results.php
 * - Changed results_form() to retrieve the result_type for the result so that
 *   the dropdown box uses the previously submitted value.
 *
 * Uploaded: 12 December 2007
 * --------------------------
 *
 * 15 December 2007
 * ----------------
 * Code tidy-up. Removed unused functions and queries and add list of functions
 * to top of each component.
 * 
 * include/fixtures.php
 * - Added a list of functions at the top
 *
 * include/functions.php
 * - Removed unused league_list_select() function
 * 
 * include/help.php
 * - Updated predictions() section to include form guide.
 * 
 * include/index.php
 * - Swapped calls to navtabs() and show()
 * 
 * include/leagues.php
 * - Added a list of functions at the top
 *
 * include/navtabs.php
 * - Removed unused script
 *
 * include/next.php
 * - Removed show_fixture_sets() function
 * - Commented each function and listed active functions at top of script
 * 
 * include/options.php
 * - Added a list of functions at the top
 *
 * include/queries.php
 * - Removed already_predicted query that was only used by show_fixture_sets()
 *   in next.php
 * - Removed unused query fixture_exists
 * - Removed unused query results_by_userteam
 * - Removed unused query subs_backup_tmp
 * - Removed unused query subs_backup_delete
 * - Removed unused query user_list
 * 
 * include/results.php
 * - Added a list of functions at the top
 * 
 * include/summary.php
 * - Added a list of functions at the top
 *
 * include/team.php
 * - Added a list of functions at the top
 *
 * include/users.php
 * - Added a list of functions at the top
 *
 * include/userteams.php
 * - Changed name of list_all_users() to userteams_list()
 *
 * Uploaded: 15 December 2007
 * --------------------------
 *
 * 15 December 2007
 * ----------------
 * When showing fixture sets, only highlights previously submitted sets for
 * the league being updated
 *
 * include/queries.php
 * - Updated 'already_resulted' with the league_id as a constraint
 *
 * include/results.php
 * - Changed show_fixture_sets() to submit the league id when requesting a
 *   list of fixture sets.
 *
 * Uploaded: 16 December 2007
 * --------------------------
 *
 * 11 December 2007
 * ----------------
 * When an existing fixture set is edited and has no fixtures, the form shows
 * the current date rather than the date orignally submitted.
 *
 * include/fixtures.php
 * - Added check for empty fixture set and select basic details if empty
 *
 * include/queries.php
 * - Added empty_fixture_set query for just the fixture set details
 * - Removed surplus columns from fixtures_by_setid query.
 *
 * Uploaded: 16 December 2007
 * --------------------------
 *
 * 17 December 2007
 * ----------------
 * Arrange the fixture sets by month with expand/contract links to reduce the
 * amount of page space consumed
 *
 * include/fixtures.php
 * - Changed list_fixture_sets() to format show a fixture set table for each
 * month.
 *
 * include/queries.php
 * - Added year and month selectors to fixture_set_list query
 *
 * predictions.css
 * - Added fixturesets id styles as mostly the same as #season, but using a
 *   width of 90%
 *
 * Uploaded: 18 December 2007
 * --------------------------
 *
 * 17 December 2007
 * ----------------
 * Add 'do not predict' boxes to inhibit saving of predictions when the form
 * is submitted.
 *
 * include/help.php
 * - Updated predictions section include the 'no save' checkbox.
 *
 * include/next.php
 * - Modified next_form() to include a 'nosave' checkbox next to each fixture
 * - Added a test to add_predictions() to skip insertion of a prediction if
 *   the corresponding 'nosave' box was checked.
 *
 * Uploaded: 18 December 2007
 * --------------------------
 *
 * 24 November 2007
 * ----------------
 * Implement tournament functionality.
 * For the predictions display this will require a change so that the stage
 * details (e.g., group name or round) are displayed next to the date.
 *
 *
 * Uploaded: 
 * --------------------------
 *
 * 27 February 2008
 * ----------------
 * If a fixture set is extended to include additional matches, the form
 * displaying the fixtures only includes fixtures that have already been
 * added. Attempts at saving the additional fixtures fail.
 *
 * include/fixtures.php
 * - In the fixture_list_form() function added a check that a relevant fixture
 * has been pulled from the database and assign a new id if not.
 * - The form element is only shown if a valid fixture id is available.
 *
 * Uploaded: 28 February 2008
 * --------------------------
 *
 * 25 April 2008
 * -------------
 * Add a scheme where the league winners can be predicted as well as those teams
 * that will be relegated. Allow this to be updated throughout the season,
 * four times per month max, but previous attempts will be recorded.
 *
 * Uploaded: 
 * --------------------------
 *
 * 25 April 2008
 * -------------
 * Include arbitrary points deduction table for teams that have gone into
 * administration or breached other league rules
 * Database
DROP TABLE IF EXISTS predict_team_deduct;
CREATE TABLE predict_team_deduct (
  team_id INT(4) NOT NULL UNIQUE,
  deduction INT(4) NOT NULL DEFAULT '10'
) COMMENT='Table listing teams that have had points deducted';
DROP TABLE IF EXISTS season_predict_team_deduct;
CREATE TABLE season_predict_team_deduct (
  season_id INT(4) NOT NULL UNIQUE,
  team_id INT(4) NOT NULL UNIQUE,
  deduction INT(4) NOT NULL DEFAULT '10'
) COMMENT='Historical record of teams that have had points deducted';
 *
 * include/queries.php
 * - Added the following UNION to the 'league_table_insert' query
UNION
SELECT predict_teams.id AS team_id,
       '0' AS hwins,
       '0' AS hdraws,
       '0' AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       '0' AS awins,
       '0' AS adraws,
       '0' AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       IFNULL(deduction, '0') AS points_won
FROM predict_teams LEFT JOIN  predict_team_deduct ON id = team_id
WHERE predict_teams.id IN
  (SELECT team_id FROM league_teams WHERE league_id = ?)
 *
 * include/results.php
 * - Changed add_results() to pass an array with 10 copies of the league_id
 *   to the insert_league_table query rather than 9 because of the extra
 *   UNION select to include the deductions.
 *
 * Uploaded: 30 April 2008
 * -----------------------
 *
 * 28 April 2008
 * -------------
 * Add support for end-of-season league playoffs. This is probably best
 * achieved by added a new result type: 'playoff'. The league_insert query
 * and perhaps others that check whether games have been abandoned or postponed
 * should just check that the result type is 'normal'; league matches don't go
 * to extra-time or penalties.
 * The league definition should include a default match type, but this can be
 * over-ridden by a match type selector on the fixture set definition. The
 * match type is then included in the fixture record in the database. When the
 * result is submitted, result_type dropdown is shown as normal, but the
 * actual record added to the database is modified as follows:
 * match_type: league
 *  'normal'    => 'normal';
 *  'postponed' => 'postponed'
 *  'abandoned' => 'abandoned'
 *   penalties and extra are not permitted and will be changed to 'normal'.
 * match_type: playoff
 *  'normal'    => 'playoff';
 *  'extra'     => 'extra'
 *  'penalties' => 'penalties'
 *  'postponed' => 'postponed'
 *  'abandoned' => 'abandoned'
 *
 * Database:
ALTER TABLE fixture_results MODIFY COLUMN result_type enum('normal','extra',
'penalties','abandoned','postponed','playoff') NOT NULL DEFAULT 'normal';
ALTER TABLE leagues ADD COLUMN default_match_type CHAR(16) NOT NULL DEFAULT
'league' AFTER name;
ALTER TABLE fixtures ADD COLUMN match_type ENUM('league', 'playoff', 'knockout', 'friendly') NOT NULL DEFAULT 'league' AFTER away_team_id;
 *
 * Define the start and end position for teams in the playoff region
ALTER TABLE leagues ADD COLUMN playoff_start TINYINT NOT NULL DEFAULT '3'
 AFTER relegated_to;
ALTER TABLE leagues ADD COLUMN playoff_cnt TINYINT NOT NULL DEFAULT '4'
 AFTER playoff_start;
UPDATE leagues SET playoff_start = '3' WHERE id = '2';
UPDATE leagues SET playoff_start = '3' WHERE id = '3';
UPDATE leagues SET playoff_start = '4' WHERE id = '4';
UPDATE leagues SET playoff_cnt = '4' WHERE id = '2';
UPDATE leagues SET playoff_cnt = '4' WHERE id = '3';
UPDATE leagues SET playoff_cnt = '4' WHERE id = '4';
 *
 * ajax/fixtures/js
 * - Modify sndReq() to extract the value of the matchtype select list
 *   and pass it to the RPC function so that selecting a matchtype of 'playoff'
 *   will select the league's playoff range as the number of fixtures.
 *
 * include/fixtures.php
 * - Modified fixture_set_details() to include a select list for the match
 *   type passing the default match type for the league as $chosen to
 *   result_type_select().
 *   The current match_type setting needs to be pre-selected.  NOT YET DONE
 * - Modified fixture_list_form() to include a hidden form element set to the
 *   value of the match_type selected when defining the fixture set.
 * - Modified set_fixture() to extract the match type and include it in the
 *   fixture database update/insert.
 * - Modified fixture_set_details() to include the matchtype select list.
 * - If the match type is playoff, only the teams in the playoff places should
 *   appear in the team list for fixtures.
 *   In fixture_list_form() check $_POST['matchtype'] == 'playoff' and
 *   call playoff_team_select() (in functions.php) which uses
 *   $query_ary['playoff_team_select'] to get the list of playoff teams.
 *
 * include/functions.php
 * - result_type_select() needs to be passed the match_type as an argument
 *   so the list of appropriate result_types can be displayed.
 * - Added match_type_select() to display a list of match types for display
 *   on the fixture_set definition form. $chosen is passed as the default
 *   match type for the league.
 *
 * include/leagues.php
 * - Modify show_league_teams() to include a section which placed team and
 *   how many are in the playoffs
 *   See separate update.
 *
 * include/queries.php
 * - Changed result_type contraint checks from abandoned/postponed to normal
 *   in league_table_insert.
 * - Add query 'get_default_match_type' to extract the default match type for
 *   the league as used as the chosen value passed to match_type_select() in
 *   fixture_set_details().
 * - Modified add_new_fixture to include an additional (match_type) parameter.
 * - Modified update_fixture to include the match_type parameter.
 * - Added get_fixture_match_type to extract the match_type of a given fixture
 * - Added get_playoff_range to get the start position of the playoff and the
 *   number of playoff teams.
 * - Added playoff_team_select to select the names and ids of the playoff
 *   teams in a league.
 * - Added update_fset_num_fixtures to update the number of fixtures in a
 *   fixture set to the number of teams in the playoffs.
 *
 * include/results.php
 * - Modified results_form() to get the match_type and pass it to
 *   result_type_select() to limit the available result types.
 * - Modify add_results() to ensure that matches that are not playoff or
 *   league cannot have a result type of extra or penalties, enforcing a
 *   result of normal instead.
 *
 * rpc.php
 * - Modified the section the updates the number of fixtures for the league
 *   to look out for a match type of playoff and then return the number of
 *   teams in the playoff sopt for the league.
 *
 * Uploaded: 5 May 2008
 * --------------------
 *
 * 28 April 2008
 * -------------
 * Don't highlight fixture set in red until all results have been submitted for
 * the fixture set.
 *
 * include/queries.php
 * - Added 'not_submitted' query to retrieve the number of matches in a fixture
 *   set that have not yet been submitted. This is used instead of the 
 *   'already_resulted' query.
 *
 * include/results.php
 * - Modified show_fixture_sets() to use the 'not_submitted' query to identify
 *   fixture sets where not all the results have been submitted.
 *
 * Uploaded: 29 April 2008
 * -----------------------
 *
 * 29 April 2008
 * -------------
 * Form guide displays incorrect number of matches played.
 *
 * include/queries.php
 * - The queries to extract form details from the fixture_results table do not
 *   take abandoned or postponed games into account.
 *   Updated home_games_played, home_goals_for, home_goals_against,
 *   home_games_won, home_games_drawn, home_games_lost, away_games_played,
 *   away_goals_for, away_goals_against, away_games_won, away_games_drawn and
 *   away_games_lost queries to exclude fixtures that were either abandoned
 *   or postponed.
 *
 * Uploaded: 29 April 2008
 * -----------------------
 *
 * 1 May 2008
 * ----------
 * Run some input data cleansing before processing.
 *
 * include/login.php
 * - In check_login() restrict length of submitted username and password to the
 *   maximum length of the respective database field.
 *
 * Uploaded: 5 May 2008
 * --------------------
 *
 * 4 May 2008
 * ----------
 * When editing a fixture set that has not previously had any fixtures saved
 * produces a PHP error.
 *
 * include/queries.php
 * - Removed a surplus comma in the SELECT list for empty_fixture_set to stop
 *   a database error occurring (separate error) when selecting a fixture set
 *   that has no fixtures.
 *
 * Uploaded: 5 May 2008
 * --------------------
 *
 * 4 May 2008
 * ----------
 * When submitting a fixture result for a playoff, check that the submitted
 * teams are in the playoff region for the league.
 *
 *
 * Uploaded: 
 * -----------------
 *
 * 4 May 2008
 * ----------
 * Disable selection (and submission) of playoff fixtures before all the games
 * in the league have been played.
 * Also disable selection of playoff fixture type if the number of playoff
 * teams in the league is zero.
 * Also need to ensure that when a playoff fixture set is submitted with a
 * playoff fixture type, that all matches have been played.
 *
 * include/fixtures.php
 * - Updated fixture_list_form() to check the all_matches_played query
 *   value for greater than 0, returning an error if 0.
 *
 * include/queries.php
 * - Added all_matches_played query to identify whether all the season's
 *   matches for a league have been played. Yields 0 if not all the matches
 *   have been played, 1 when all matches have been played.
 * 
 * Uploaded: 04 August 2008
 * ------------------------
 *
 * 5 May 2008
 * ----------
 * Add the forms to manage the league playoff places and points dedcutions.
 *
 * include/leagues.php
 * - Update show_league_teams() to display forms for managing the league playoff
 *   places, number of playoff teams and any points deductions.
 * - Added function update_playoff_places() to update the league's playoff
 *   settings.
 * - Added update_deductions() to remove exisitng deductions and add new
 *   deductions.
 *
 * include/queries.php
 * - Added query get_points_deducted to extract the points deducted for a
 *   particular team.
 * - Added get_all_points_deducted query to get the points deducted for each
 *   team in a league.
 * - Added no_points_deducted query to get the teams in a league that have not
 *   had any points deducted.
 * - Added update_playoff_places query to save the league's playoff settings.
 * - Added delete_deduction to remove a points deduction for a team.
 * - Added insert_deduction to add a points deduction for a team.
 * 
 * Uploaded: 10 May 2008
 * ---------------------
 *
 * 15 May 2008
 * -----------
 * League playoff match results are included in the league_table because there
 * is no playoff result type in the results form.
 *
 * include/functions.php
 * - Modified result_type_select() to include playoff in the resullt type list
 * 
 * Uploaded: 16 May 2008
 * ---------------------
 *
 * 5 May 2008
 * ----------
 * When archiving a season, include the deducted points table. Update the
 * season_fixtures table to include the match_type column. Update the
 * season_results table to include 'playoff' as a result_type.
 * Database
ALTER TABLE season_fixtures ADD COLUMN match_type ENUM('league','playoff','knockout','friendly') NOT NULL DEFAULT 'league' AFTER away_team_id;
ALTER TABLE season_results MODIFY result_type ENUM('normal','extra','penalties','abandoned','postponed','playoff') NOT NULL DEFAULT 'normal';
 *
 * include/queries.php
 * - Modified season_fixtures_copy query to SELECT match_type from the fixtures
 *   table.
 * 
 * Uploaded: 16 May 2008
 * ---------------------
 *
 * 17 May 2008
 * -----------
 * Allow the setting of the default match type for a league.
 * 
 * include/leagues.php
 * - Updated show_league_teams() to add a selector for the default match type.
 * - Added default match type selector to add_new_league_form().
 * - Include defaultmatchtype in the values submitted.
 * - Changed leagues_update() to submit the default match type.
 *
 * include/queries.php
 * - Updated insert_new_league query to include default_match_type.
 * - Updated save_league_details query to include default_match_type.
 * 
 * Uploaded: 20 May 2008
 * ---------------------
 *
 * 17 May 2008
 * -----------
 * On the summary page, for each league show the points rating for the season.
 * 
 * include/queries.php
 * - Added summary_league_outcomes query to get the correct outcomes for the
 *   user in the league.
 * - Added summary_league_scores query to get the correct scores for the
 *   user in the league.
 * - Added num_league_predictions query to get the number of predictions made
 *   by the user in the league.
 *
 * include/summary.php
 * - Added subscribed_league_summary() function to extract the correct results
 *   and scores and user points for the league.
 * - Modify show() to display a summary of the correct results, scores, points
 *   and percentage rating for the league.
 *
 * Uploaded: 14 June 2008
 * ----------------------
 *
 * 17 May 2008
 * -----------
 * Show the saved fixture set match type when editing a previous submission.
 * This will involve getting the match type of a fixture in the set.
 *
 * include/fixtures.php
 * - Updated fixture_set_details() to extract the match type for matches in the
 *   fixture_set and use the league default match_type if none are found.
 *
 * include/queries.php
 * - Added query, get_fixture_set_match_type, to identify the first match_type
 *   value for a match in the fixture set.
 * 
 * Uploaded: 20 May 2008
 * ---------------------
 *
 * 19 May 2008
 * -----------
 * When saving a playoff final fixture_set, the number of fixtures in the
 * fixture_set table is saved as '2' rather than 1.
 * In include/fixtures.php, the fixture_list_form() function forces the number
 * of fixtures to be half the number of teams in the playoff places rather
 * than checking that the number of requested fixtures is greater than half
 * the number of teams in the playoff places; an RPC call sets the fixture
 * count when the plyoff option is chosen.
 *
 * include/fixtures.php
 * - Updated fixture_list_form() to only force the number of fixtures for a
 *   playoff situation when the number of requested fixtures is greater than
 *   the maximum number of possible playoff matches (playoff_cnt / 2).
 * 
 * Uploaded: 20 May 2008
 * ---------------------
 *
 * 21 May 2008
 * -----------
 * Attempt to use YUI to enable the placement of the form box over the
 * link for the team.
 *
 * ajax/next.js
 * - Slight tweak to showFormGuide() to update the content of the formguide
 *   div before making it visible.
 *
 * include/next.php
 * - Modified next_form() to change the way that the team are displayed and
 *   include the yui event handlers to display the form guide when a click is
 *   made over the team name without the need for name to be a link.
 *   When the form guide is not visible, the first click on the team name
 *   pops up the form guide in the last place it was displayed; subsequent
 *   clicks on the where when it is visible will move it to where the pointer
 *   is.
 *
 * index.php
 * - Added script tags to include the YUI JavaScript libs.
 *
 * yui/build/dom/dom-min.js
 * yui/build/element/element-beta-min.js
 * yui/build/event/event-min.js
 * yui/build/yahoo/yahoo-min.js
 * - The YUI library.
 * 
 * Uploaded: 27 May 2008
 * ---------------------
 *
 * 27 May 2008
 * -----------
 * Enable the prediction of the winners of each subscribed league, and perhaps
 * the teams that will be relegated.
 * Bonus points will be awarded if the predicted winners are correct, but the
 * earlier in the season the correct prediction is made (and not changed), the
 * more points are awarded.
 * 
 * Uploaded: 
 * ---------------------
 *
 * 14 June 2008
 * ------------
 * Errors when archiving the previous season:
 * 'ERROR: Cannot modify start date after season has started'.
 * 'Warning: Division by zero in /var/www/predictions/include/summary.php on
 * line 181' on summary page after archiving season data.
 * 'No correctly predicted scores' on summary league tab displayed twice
 * when season has been archived.
 *
 * include/seasons.php
 * - Updated seasons_update() to only check for invalid start and end (with the
 *   resultant error) if they have been submitted: they're not when the season
 *   has finished.
 *
 * include/summary.php
 * - Modified subscribed_league_summary() to only calculate the percentage if
 *   there are predictions made. When that last season has been archived, 0 will
 *   be displayed.
 * - Modified list_correct_fixtures() to use the queried table to suggest what
 *   the displayed message should be.
 * 
 * Uploaded: 04 August 2008
 * ------------------------
 *
 * 04 August 2008
 * --------------
 * Missing the function to save a new season. The season_update() function
 * cannot detect when a new season is submitted.
 *
 * Duplicate of issue raised on 20 July 2009.
 *
 * Uploaded: 22 July 2009
 * ----------------------
 *
 * 04 August 2008
 * --------------
 * Promote/relegate section of Leagues page does not submit.
 * 
 * include/leagues.php
 * - Updated show_league_teams() to include a form line for the form.
 *
 * Uploaded: 04 August 2008
 * ------------------------
 *
 * 04 August 2008
 * --------------
 * When promoting/relegating teams in the leagues section, teams are removed
 * from the league but are not placed in their mew league.
 *
 * include/queries.php
 * - Unterminated comment in front of add_team_to_league query.
 *
 * Uploaded: 04 August 2008
 * ------------------------
 *
 * 04 August 2008
 * --------------
 * selecting teams to remove in the League membership' sections are not removed
 * when 'save teams' button pressed.
 *
 * include/queries.php
 * - Unterminated comment in front of remove_all_teams_from_league query.
 *
 * Uploaded: 04 August 2008
 * ------------------------
 *
 * 04 August 2008
 * --------------
 * Archiving an old season should clear any points deductions for teams.
 * It should also clear the league_table table so that the table is shown
 * correctly before the first prediciton has been made.
 * Will require a new table to identify which leagues are active during the
 * season; there is the possibility of multiple active seasons. The
 * predict_team_deduct table also needs a leagueid column.
 *
 * Database: Create a season deductions table;
CREATE TABLE season_deductions (
  season_id SMALLINT NOT NULL,
  team_id SMALLINT NOT NULL,
  league_id SMALLINT NOT NULL,
  deduction SMALLINT NOT NULL,
  PRIMARY KEY(season_id, team_id, league_id)
) COMMENT='Archive record of points deductions for teams';
ALTER TABLE predict_team_deduct ADD COLUMN league_id SMALLINT NOT NULL AFTER team_id;
 *
 * include/leagues.php
 * - Updated update_deductions() to pass the league_id to the
 *   insert_deduction and delete_deduction queries.
 *
 * include/queries.php
 * - Added query season_deductions_copy to archive the season's deductions
 * - Added query season_deductions_delete to remove the season's deductions
 * - Updated the insert_deduction query to include the league_id.
 * - Updated the delete_deduction query to include the league_id.
 *
 * include/seasons.php
 * - Added season_deductions_copy and season_deductions_delete to list of
 *   queries to run in archive_season().
 *
 * league_table reset applied on 26 July 2010.
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 10 August 2008
 * --------------
 * Check for whether a fixture was played last season and display a line in
 * the form guide: 'Last meeting: h - a'
 *
 * ajax/next.js
 * - Modified sndFormReq() to accept oppid and pass it to get_team_form().
 * 
 * include/functions.php
 * - Added get_last_meeting() to retrieve the result of the last meeting
 *   Not yet correctly formatted.
 * - modify get_team_form() to accept $oppid as the id of the
 *   opponents and call get_last_meeting() having used $hora to ensure that
 *   the home and away teams are passed correctly.
 *
 * include/next.php
 * - Modify next_form() to include the id of the opposition in
 *   the call to the sndFormReq() JavaScript function.
 *
 * include/queries.php
 * - Added score_when_last_played query to extract the score when the teams
 *   last met.
 *
 * rpc.php
 * - Tweak the form guide section to call get_team_form() with the id of
 *   the oppoents.
 *
 * Uploaded: 13 August 2008
 * ------------------------
 *
 * 10 August 2008
 * --------------
 * Archiving a season (when pressing the 'Save season' button sets the
 * season_start and season_end columns to zero which means that the
 * season_fixtures_copy query to retrieve 0 columns for insertion into the
 * season_fixtures table.
 *
 * Database:
 *  - Upload previous season's fixture data in season_fixtures-2007-2008.sql.
 *
 * include/queries.php
 * - Updated season_fixtures_copy query to only select from the seasons and
 *   fixtures tables.
 *
 * include/seasons.php
 * - Updated seasons_update() to only update the season's details if the name
 *   start and end dates are passed.
 * - Fixed typo in $op_ary for 'season_fixtures_copy'.
 *
 * Uploaded: 10 August 2008
 * ------------------------
 *
 * 13 September 2008
 * -----------------
 * Display a league table on the predictions page showing the games played
 * and the points won, with a link to display the home/away form
 *
 * include/functions.php
 * - Added function show_leaguetable() to produce the league table.
 *
 * include/next.php
 * - Added call to show_leaguetable() next to the list of predictions.
 *
 * include/queries.php
 * - Added query show_league_table to extract the league table.
 *
 * predictions.css
 * - Added leaguetable styles for the leaguetable table
 *
 * Uploaded: 24 October 2008
 * -------------------------
 *
 * 24 October 2008
 * ---------------
 * Replace 'no save' checkbox with a hidden form element with a default value
 * of '1', that is set to '0' by a JavaScript function when a matching goals
 * text box is left.
 *
 *
 * Uploaded: 
 * -------------------------
 *
 * 24 October 2008
 * ---------------
 * Display the form guide in a box under the league table.
 *
 * ajax/next.js
 * - Removed the vars and functions to close the popup.
 *
 * include/functions.php
 * - Updated get_team_form() to output paragraph tag for spacing.
 *
 * include/next.php
 * - show() will include calls to display the form guide under the league
 *   table.
 * - In next_form(), removed the YUI code output and added a link to JavaScript
 *   sndFormReq() over the team name.
 *
 * index.php
 * - Removed the form guide div from under the left nav panel.
 *
 * predictions.css
 * - Revised formguide styles for the new location of the form guide.
 *
 * Uploaded: 25 October 2008
 * -------------------------
 *
 * 26 October 2008
 * ---------------
 * After uploading the code for the form guide, when selecting a user from
 * the dropdown to display their previous predictions, the redisplayed
 * content overwrites the dropdown list.
 *
 * include/next.php
 * - Updated show() to re-order the display the dropdown in its own div.
 *
 * predctions.css
 * - Added userlist styles.
 *
 * Uploaded: 27 October 2008
 * -------------------------
 *
 * 09 November 2008
 * ----------------
 * When selecting a team in the form guide, highlight the team in the
 * league table.
 *
 * ajax/next.js
 * - Modified showFormGuide() to update the leaguetable div with the content
 *   received from the rpc call.
 *
 * include/functions.php
 * - Removed leaguetable div tags from show_leaguetable() to enable the
 *   function to be called via an RPC call to re-display the table with
 *   colour highlights for the home and away teams.
 *
 * include/next.php
 * - Surounded call to show_leaguetable() with the leaguetable div tag.
 *
 * include/queries.php
 * - Updated show_league_table query to extract the team id so that it can be
 *   compared to the requested home and away team ids in show_leaguetable().
 *
 * predictions.css
 * - Added 'tr.home' and 'tr.away' styles to '#leaguetable table'
 *
 * rpc.php
 * - Updated the formguide section to call show_leaguetable() with the home
 *   and away team ids to include them in the table.
 *
 * Uploaded: 12 November 2008
 * --------------------------
 *
 * 15 November 2008
 * ----------------
 * Tidy up display of the league table and form guide when there are no
 * fixtures: if there are no fixtures then don't show the form guide. Keep
 * the league table on the right-hand side.
 *
 * include/next.php
 * - In show() the secton of code to determine whether to display the next
 *   set of fixtures, the use of the next_set query and subsequent switch()
 *   seems redundant. If the next_fixtures query is used, a zero value can
 *   trigger the display of the 'no fixtures' message and inhibit the display
 *   of the form guide.
 * - In next_form(), remove the check for no found fixtures: this is dealt
 *   with by the calling function.
 *
 * include/queries.php
 * - Removed the redundant 'next_set' query.
 *
 * predictions.css
 * - Need to alter styles to properly align the form guide, league table and
 *   season list. NOT YET DONE.
 *
 * Uploaded: 28 November 2008
 * --------------------------
 *
 * 15 November 2008
 * ----------------
 * Modify the scoring by including two points for a correct score differential.
 *
 * Database:
 * - Alter db schema to include the correct score differentials
 ALTER TABLE predict_user_scores ADD COLUMN correct_diffs INT(10) NOT NULL DEFAULT '0' AFTER correct_results;
 *
 * include/functions.php
 * - Modify update_user_scores() to include  points for each
 *   match where the correct score differential was predicted and to
 *   exclude those results from the correct result list.
 * - Updated season_predictions() to include the test for correct diffs
 *
 * include/next.php
 * - Need to include correct differentials in season's predictions list.
 *
 * include/queries.php
 * - Added correct_diff_season, correct_diff_last_week, correct_diff_this_week,
 *   correct_diffs_teams, summary_league_diffs and correct_diff_last_month
 *   queries. These include the fixture_date constraint described below.
 * - Modified league_result_teams to exclude results where the correct
 *   difference between home and away goals was predicted.
 * - Modified correct_result_teams to exclude results where the correct
 *   difference between home and away goals was predicted.
 * - Modified summary_league_outcomes to exclude results where the correct
 *   difference between home and away goals was predicted.
 * - Modified match_results to exclude results where the correct
 *   difference between home and away goals was predicted.
 * - Modified user_score_insert query to include an extra column for the
 *   correct score differentials.
 * - Modified userteam_points_ordered query to extract the correct score
 *   differentials.
 * - Modified is_correct_outcome query to exclude correct score differentials
 * - Added is_correct_diff query to identify whether a predicted score had the
 *   correct differential.
 *
 * include/summary.php
 * - Expanded summary_table() to include extraction and display the correct
 *   score differentials. Displays Previous rather han Last week.
 * - Expanded subscribed_league_summary() to include extraction and display the
 *   correct score differentials.
 * - Expanded show() to include extraction and display the correct score
 *   differentials.
 * - Corrected typo in list_correct_fixtures().
 *
 * include/team.php
 * - Modified show() to include the correct diffs in the table.
 *
 * predictions.css
 * - Renamed .exactscore class to .diffs and added a new .exactscore class.
 *
 * Uploaded: 29 November 2008
 * --------------------------
 *
 * 26 November 2008
 * ----------------
 * When calculaing the user scores for the past week and month start from the
 * latest date when matches were played rather than the current date. This
 * should give a consistent score ratng between fixture sets.
 * This can be done for the previous 7 days by adding the fixture_date
 * constraint as,
  AND fixture_date <=
      (SELECT MAX(fixture_date)
         FROM fixtures, fixture_results
        WHERE fixtures.id = fixture_results.fixture_id)
  AND fixture_date >=
      (SELECT DATE_SUB(MAX(fixture_date), INTERVAL 7 DAY)
         FROM fixtures, fixture_results
        WHERE fixtures.id = fixture_results.fixture_id)
 * for the previous month, this would be:
  AND fixture_date <=
      (SELECT MAX(fixture_date) FROM fixtures, fixture_results
        WHERE fixtures.id = fixture_results.fixture_id)
  AND fixture_date >=
      (SELECT DATE_SUB(MAX(fixture_date), INTERVAL 1 MONTH)
         FROM fixtures, fixture_results
        WHERE fixtures.id = fixture_results.fixture_id)
 * 
 * include/queries.php
 * - Modified predictions_this_week to start from when the last set of
 *   results where submitted.
 * - Modified predictions_last_month to start from when the last set of
 *   results where submitted.
 * - Modified correct_score_teams to start from when the last set of
 *   results where submitted.
 * - Modified scores_this_week to start from when the last set of
 *   results where submitted.
 * - Modified scores_last_week to start from when the last set of
 *   results where submitted.
 * - Modified scores_last_month to start from when the last set of
 *   results where submitted.
 * - Modified results_this_week to start from when the last set of
 *   results where submitted.
 * - Modified results_last_week to start from when the last set of
 *   results where submitted.
 * - Modified results_last_month to start from when the last set of
 *   results where submitted.
 *
 * Uploaded: 29 November 2008
 * --------------------------
 *
 * 10 December 2008
 * ----------------
 * Display the league table to the right of the submitted scores on the
 * results page. This will help verify that the correct results have been
 * submitted.
 *
 * include/functions.php
 * - Minor display format changes in update_user_scores_form().
 *
 * include/results.php
 * - Added <div> elements to separate the output from the form submission
 *   and the league table in show().
 *
 * predictions.css
 * - Added resultspage and resultlist styles. fixturesets floats to the left.
 *
 * Uploaded: 30 December 2008
 * --------------------------
 *
 * 30 December 2008
 * ----------------
 * Add a search form to the results page, before the fixture set list, to
 * allow retrieval of all the results for a team in the league so that the
 * submitted results can be verified. Include postponed and abandoned games.
 *
 * include/functions.php
 * - Modified league_team_select() to accept a submit action so that the
 *   rpc function can be called.
 *
 * include/leftnav.php
 * - Added link to search page
 *
 * include/search.php
 * - New driver for menu option
 *
 * include/queries.php
 * - Added results_byleague_team to get all the results for a team.
 *
 * index.php
 * - Added admin driver link
 * - Added include for HTML_QuickForm_static
 *
 * predictions.css
 * - Added matchlist styles
 *
 * rpc.php
 * - Added trap for a team's matches and display the results
 *
 * Uploaded: 26 January 2010
 * -------------------------
 *
 * 30 December 2008
 * ----------------
 * Update the help page with the changes to the form guide.
 *
 * include/help.php
 * Updated instructions.
 *
 * Uploaded: 30 December 2008
 * --------------------------
 *
 * 12 January 2009
 * ---------------
 * Show the number of fixtures in the set list on the fixtures page.
 *
 * include/fixtures.php
 * - Modified format_excol_hdr() to include a fixture count column.
 * - Modified list_fixture_sets() to include the number of fixtures in the set.
 *
 * include/queries.php
 * - modified fixture_set_list to SELECT num_fixtures from the table.
 *
 * Uploaded: 12 January 2009
 * -------------------------
 *
 * 12 January 2009
 * ---------------
 * Exclude postponed and abandoned matches from the list of predicted
 * fixtures.
 *
 * include/queries.php
 * - Exclude postponed and abandoned matches from user_predictions_season
 *   query.
 *
 * Uploaded: 12 January 2009
 * -------------------------
 *
 * 12 January 2009
 * ---------------
 * When a fixture list is re-displayed due to an error all the dates revert
 * to the fixture set start date, not the originally selected date.
 *
 * include/fixtures.php
 * - When checking the submitted fixture data in fixture_list_form(),
 *   $_POST['match_date'.$fid] was incorrectly saved rather than
 *   $_POST['fixture_date'.$fid];
 *
 * Uploaded: 12 January 2009
 * -------------------------
 *
 * 12 January 2009
 * ---------------
 * In the league table, highlight the teams in the promotion, playoff and
 * relegation places.
 *
 * include/functions.php
 * - Updated show_leaguetable() to determine whether to apply different
 *   styling to promotion, playoff and relegation teams
 * include/queries.php
 * - Added playoff_relegation_zone query to determine the playoff and
 *   relegation places
 * predictions.css
 * - Added leaguetable table tr styles for promotion, playoff and relegation
 *
 * Uploaded: 16 December 2009
 * --------------------------
 *
 * 27 February 2009
 * ----------------
 * Indicate on the predicions page which teams have had points deducted.
 *
 * include/functions.php
 * - Modified show_leaguetable() to get the points deducted for the teams and
 *   display the deductions in brackets.
 *
 * include/next.php
 * - Included help text at the top of the page.
 *
 * include/queries.php
 * - Tweaked query get_points_deducted to return 0 if null
 *
 * Uploaded: 27 February 2009
 * --------------------------
 *
 * 27 February 2009
 * ----------------
 * recalculate league table when points are deducted from a team during
 * the season.
 *
 * include/functions.php
 * - Added update_league_table() to perform the league update.
 *
 * include/leagues.php
 * - Added call to update_league_table() in update_deductions()
 *
 * include/queries.php
 * -  Updated comment for delete_/insert_league_table
 *
 * include/results.php
 * - Modified add_results() to call update_league_table() instead of running
 *   the queries inline.
 *
 * Uploaded: 18 March 2009
 * -----------------------
 *
 * 07 April 2009
 * --------------
 * Non-numeric prediction values are not highlighted
 *
 * Uploaded: 
 * -----------------------
 *
 * 07 April 2009
 * --------------
 * League and form table not redisplayed when prediction error detected.
 *
 * Uploaded: 
 * -----------------------
 *
 * 13 may 2009
 * -----------
 * Should create a separate table to indicate matches (playoffs) which have
 * settled by penalty shootout.
 * The aggregate score should be calculated after submission of playoff
 * matches and prompted for the penalty shootout result.
 *
 * Uploaded: 
 * -----------------------
 *
 * 13 may 2009
 * -----------
 * Result type for playoff matches should default to 'playoff'.
 *
 * include/functions.php
 * - Modified result_type_select() to set the default value to the match_type
 *   if the match is either knowut or playoff and there is no pre-selected 
 *   value.
 *
 * Uploaded: 15 May 2010
 * ---------------------
 *
 * 20 July 2009
 * ------------
 * Missing the code to insert a new season
 *
 * include/queries.php
 * - Added seasons_insert to add a new season
 *
 * include/seasons.php
 * - Added seasons_insert to add a new season
 *
 * Uploaded: 22 July 2009
 * ----------------------
 *
 * 22 July 2009
 * ------------
 * Archive the league table at the end of a season
 *
 * Database:
DROP TABLE IF EXISTS `season_league_table`;
CREATE TABLE `season_league_table` (
  `season_id` int(10) NOT NULL default '0',
  `team_id` int(10) NOT NULL default '0',
  `league_id` int(10) NOT NULL default '0',
  `home_wins` smallint(6) NOT NULL default '0',
  `home_draws` smallint(6) NOT NULL default '0',
  `home_losses` smallint(6) NOT NULL default '0',
  `home_goals_for` smallint(6) NOT NULL default '0',
  `home_goals_against` smallint(6) NOT NULL default '0',
  `away_wins` smallint(6) NOT NULL default '0',
  `away_draws` smallint(6) NOT NULL default '0',
  `away_losses` smallint(6) NOT NULL default '0',
  `away_goals_for` smallint(6) NOT NULL default '0',
  `away_goals_against` smallint(6) NOT NULL default '0',
  `points` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`team_id`,`league_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Historial record of previous seasons league table';
INSERT INTO season_league_table SELECT
'3', team_id, league_id,
home_wins, home_draws, home_losses, home_goals_for, home_goals_against,
away_wins, away_draws, away_losses, away_goals_for, away_goals_against,
points
FROM league_table;
DELETE FROM league_table;
INSERT INTO league_table
SELECT team_id, league_id, '0', '0', '0', '0', '0',
'0', '0', '0', '0', '0', '0'
FROM league_teams;
ALTER TABLE season_league_table DROP PRIMARY KEY;
ALTER TABLE season_league_table ADD PRIMARY KEY(`season_id`,`team_id`,`league_id`);
 *
 * NEED TO INCLUDE THIS IN SEASON ARCHIVE FUNCTION
 *
 * Uploaded: 22 July 2009
 * ----------------------
 *
 * 23 July 2009
 * ------------
 * Cannot deactivate a usergroup
 *
 * include/userteams.php
 * - SQL update in userteams_update() used $uid instead of $tid
 *
 * Uploaded: 23 July 2009
 * ----------------------
 *
 * 23 July 2009
 * ------------
 * Inactive userteam still displayed on the group table page
 *
 * include/queries.php
 * - Added user_teamlist_active query to only return active userteams
 *
 * include/team.php
 * - navtabs() shows teams using the user_teamlist_active query
 *
 * Uploaded: 23 July 2009
 * ----------------------
 *
 * 23 July 2009
 * ------------
 * Need to archive user prediction scores and user comments
 *
 * Database:
DROP TABLE IF EXISTS `season_predict_user_scores`;
CREATE TABLE `season_predict_user_scores` (
  `season_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0',
  `num_predictions` int(10) NOT NULL default '0',
  `correct_results` int(10) NOT NULL default '0',
  `correct_diffs` int(10) NOT NULL default '0',
  `correct_scores` int(10) NOT NULL default '0',
  `points` int(10) NOT NULL default '0',
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Historical record with the sum of the user correct score and exact results';
INSERT INTO season_predict_user_scores
SELECT '3', user_id, num_predictions, correct_results, correct_diffs,
correct_scores, points
FROM predict_user_scores;
DELETE FROM predict_user_scores;
DROP TABLE IF EXISTS `season_predict_comments`;
CREATE TABLE `season_predict_comments` (
  `season_id` int(10) NOT NULL default '0',
  `comment_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '0',
  `group_id` int(10) NOT NULL default '0',
  `title` varchar(64) NOT NULL default 'Message title',
  `message` text,
  `posted` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`comment_id`, `season_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table for group comments posted by users';
INSERT INTO season_predict_comments
SELECT '3', id, user_id, group_id, title, message, posted
FROM predict_comments;
DELETE FROM predict_comments;
 *
 * Uploaded: 23 July 2009
 * ----------------------
 *
 * 16 December 2009
 * ----------------
 * Link to the predictions section on the summary page, particularly when
 * there are prdictions not yet made.
 *
 * Uploaded: 16 December 2009
 * --------------------------
 *
 * 16 December 2009
 * ----------------
 * Implement a weighting mechanism so that predicting the harder leagues
 * is more rewarding.
 *
 * Uploaded: 
 * ----------------------
 *
 * 16 December 2009
 * ----------------
 * Adjust the score to take into account the proportion of predictions made
 * against the total for the league.
 *
 * Uploaded: 
 * -----------------------
 *
 * 16 December 2009
 * ----------------
 * On the summary tab for each league, show, and link to, the number of
 * predictions outstanding for that league.
 *
 * include/queries.php
 * - Added league_not_yet_predicted
 *
 * include/summary.php
 * - Modified show() to distinguish between leagues
 *
 * predictions.css
 * - Added errmsg class link styles
 *
 * Uploaded: 16 December 2009
 * --------------------------
 *
 * 16 December 2009
 * ----------------
 * If the nextset tabum is not explicit referenced in the link, or is out
 * of the ranger of subscribed leagues, default to the first one in the
 * user's subscriptions that has predictions to be made.
 *
 * include/queries.php
 * - Added get_league_from_subs
 *
 * index.php
 * - Added trap to ensure equested league tab is from a user's subscription
 *
 * Uploaded: 16 December 2009
 * --------------------------
 *
 * 16 December 2009
 * ----------------
 * When submitting a fixture set, check whether it conflicts with an existing
 * fixture set for the league.
 *
 * include/fixtures.php
 * - Added error checking to fixture_set_details() so that the fixture
 *   set form is redisplayed if an overlap is found. Updated fixture_set_form()
 *   to run the overlapping_fsets query and return an error if any found.
 *
 * include/queries.php
 * - Added overlapping_fsets query to trap overlapping fixture sets
 *
 * Uploaded: 20 January 2010
 * -------------------------
 *
 * 26 January 2010
 * ---------------
 * When showing a list of fixtures in a fixture set, check that the number
 * returned is the same as the number specified in the fixture set defintion.
 *
 * include/fixtures.php
 * - Modified fixture_list_form() to display all the fixtures passed from
 *   fixture_set_form() and warn if there is a mismach between the passed
 *   fixtures and the number specified for the fixture set.
 *   Updated fixture_set_form() to build the fixture array with
 *   $flist->numRows() rather than count,
 *
 * Uploaded: 20 January 2010
 * -------------------------
 *
 * 26 January 2010
 * ---------------
 * Add a delete button next to each fixture in the list; ensure that any
 * predictions for the fixture are also removed along with any results.
 *
 * include/fixtures.php
 * - Added delete_fixture() to delete individual fixtures.
 *   Updated fixture_set_details() to extract fixtures to be deleted from the
 *   posted form.
 *   Updated fixture_list_form() to include a delete fixtures button.
 *
 * include/queries.php
 * - Added fixture_bounded_by_set, delete_results_by_fixture,
 *   delete_predctions_by_fixture and delete_fixture queries.
 *
 * Uploaded: 20 January 2010
 * -------------------------
 *
 * 26 January 2010
 * ---------------
 * Selecting a result date that is shown as having results that need to be
 * submitted will display a 'no fixtures' error if this action is done
 * before kickoff; the date should not be shown until at least one
 * match has completed.
 * 4 September 2010
 * This is because the results_fixture_sets query only checks for the start
 * date in the fixture set rather than considering the kickoff times of the
 * fixtures in the set.
 *
 * Uploaded: 
 * --------------------------
 *
 * 26 January 2010
 * ---------------
 * Show both the home and away team's form in the form guide box under the
 * league table and make it expand-contract.
 * 
 * include/fixtures.php
 * - Modified next_form() to use the same home/away team id in the sndReq
 *   links
 *
 * include/functions.php
 * - Updated get_team_form() to include the home and away teams. Makeing the
 *   box expand/contract is very problematic and doesn't look good.
 *
 * Uploaded: 29 January 2010
 * --------------------------
 *
 * 26 January 2010
 * ---------------
 * Add a general 'stat attack' box under the home and away form guides.
 *
 * Uploaded: 
 * --------------------------
 *
 * 26 January 2010
 * ---------------
 * Add a default kick-off selector to the fixture set definition so that
 * evening matches can be pre-selectd.
 *
 * include/fixtures.php
 * - Modified fixture_list_form() to capture when the evening kickoff box has
 * been checked. Modified fixture_set_details() to include a checkbox to
 * indicate evening kickoffs.
 *
 * Uploaded: 20 January 2010
 * -------------------------
 *
 * 27 January 2010
 * ---------------
 * Only display the fixture delete box if the fixture already exists in
 * the database.
 *
 * include/fixtures.php
 * - Updated fixture_list_form() to do a lookup using does_fixture_exist
 *   query an include the delete box if the fixture does exist.
 *
 * include/queries.php
 * - Updated notes for does_fixture_exist query to include
 *   fixtures::fixture_list_form in the list of using functions.
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 29 January 2010
 * ---------------
 * Search results should indicate the result type: normal, postponed, etc, and
 * should be in ascending order with the first matches at the top..
 *
 * include/queries.php
 * - Modified results_byleague_team to 'ORDER BY fixture_date'.
 *
 * include/search.php
 * - Modified show_team_results() to display the result type.
 *
 * Uploaded: 29 January 2010
 * --------------------------
 *
 * 29 January 2010
 * ---------------
 * A fixture list with fixtures already added cannot be expanded to include
 * new fixtures.
 *
 * include/fixtures.php
 * - Tweaked the logic in fixture_list_form() to accept extra fixtures if the
 *   fixture set requests them.
 *
 * Uploaded: 29 January 2010
 * --------------------------
 *
 * 9 February 2010
 * ---------------
 * The search link on the left nav should link to the league if a league
 * page is being displayed.
 *
 * include/leftnav.php
 * - Reworked the leftnav() function to use an ltabs array that determines
 *   the links on which pages should include the league id.
 *
 * Uploaded: 13 February 2010
 * --------------------------
 *
 * 13 February 2010
 * ----------------
 * Only list the next 7 days worth of fixtures but include a link to indicate
 * that more fixtures are available and reloads the page with all the fixtures.
 *
 * inlcude/next.php
 * - Modified next_form() to use the next_fixtures_7day query get the minimum
 *   number of fixtures to display.
 *   Also uses the $_GET['ahowall'] value to determine whether a message
 *   regarding additional fixtures needs to be displayed. The message contains
 *   a link to display the full amount of fixtures.
 *
 * include/queries.php
 * - Added next_fixtures_7day query to only extract fixtures from the next
 *   fixture_set rehardless of how many have been added.
 * - Added new_fixtures_in_league query to identify the number of all future 
 *   fixtures for the league.
 * 
 * Uploaded: 13 February 2010
 * --------------------------
 *
 * 13 February 2010
 * ----------------
 * The Championship fixture set for 20 Feb 2010 when displayed on 20 Feb lists
 * no fixtures, but says that there are additional fixtures available and
 * displays the submit and reset buttons. Clicking the 'display all' link
 * provides an additional fixture from the current set, to be played on 21 Feb.
 * 8 August 2010
 * This i smost likely an artefact of the introduction of the 7day fixtures
 * query and has been resolved by other fixture set updates.
 * 
 * Uploaded: not required
 * ----------------------
 *
 * 5 April 2010
 * ------------
 * Prediction and result input processing functions should remove all but the
 * first 4 numeric characters and error if nothing else is encountered.
 *
 * Uploaded: 
 * --------------------------
 *
 * 15 May 2010
 * -----------
 * Only the first fixture in a set of playoffs is marked as a playoff; the
 * other fixtures are listed as 'league'.
 * This affects the results page because the default result type is given as
 * normal rather than playoff.
 *
 * include/queries.php
 * - Modified the results_fixtures query to use the fxtures.match_type column
 *   value if there is no result (and type) already submitted.
 *
 * Uploaded: 16 May 2010
 * ---------------------
 *
 * 16 May 2010
 * -----------
 * Some fixtures displaying the first date in the fixture set rather the date
 * actually in the database.
 *
 * include/functions.php
 * - Modified fixture_date_select() to compare the formatted date with the
 *   database value to decide which date to mark as selected.
 *
 * Uploaded: 16 May 2010
 * ---------------------
 *
 * 30 June 2010
 * ------------
 * Error displayed when adding a team to a league even though the team is added
 * ERROR:Failed to add Newport Town to Blue square premier. 
 *
 * include/leagues.php
 * - save_new_team() database query error check needs to use PEAR::isError($res)
 *   rather than just a check for $res. This should have been fixed on 21
 *   August 2007.
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 26 July 2010
 * ------------
 * When a fixture set is added outside a season boundary, the re-displayed form
 * displays default values rather than what was submitted; the error message
 * should also show the seaon boundaries.
 *
 * include/fixtures/php
 * - Updated fixture_set_details() to include checks for submitted form items
 *   and to override the database or default values for display in the form.
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 26 July 2010
 * ------------
 * Additional kickoff times needed for the new season
 *
 * include/fixtures.php
 * - Added 12:15 and 12:35 to $ko_vals array in kickoff_select_list()
 *
 * Uploaded: 26 July 2010
 * ----------------------
 *
 * 26 July 2010
 * ------------
 * Fixture set creation form should use UI calendar code to select the start
 * and end dates. The current and net months should be displayed by default.
 *
 * ajax/fixtures.js
 * - Included YUI calendar code and additional functions for updating the
 *   start and end date boxes.
 *
 * css
 * - Created directory to host stylesheets
 *
 * css/fixtures.css
 * - Stylesheet for the fixture set calendar
 *
 * include/fixtures.php
 * - Heavy modifications to fixture_set_details() to use the calendar.
 * - fixture_set_details() includes a div to display the calendar and
 *   input boxes
 * - verify_fixture_set_dates() now takes two arrays with year, month and
 *   day to run the tests against
 * - fixture_set_form() needs to rework how the start and end date are presented
 *   in the form.
 *
 * index.php
 * - Included stylesheet and YUI JavaScript files for the calendar
 * - Changed path to predictions stylesheet to be in the css directory
 * - Included code to load in external stylesheet for the action if it is
 *   available.
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 26 July 2010
 * ------------
 * League table not reset when the season is archived. We need to be careful
 * when creating new league table because the league are not necessarily
 * formalised when the season is defined. It is best to populate a new
 * league table when it's display is requested and there is nothing there.
 *
 * include/functons.php
 * - Updated show_leaguetable() so that if no league table results are returned
 *   a new league table for the league is created.
 *
 * include/queries.php
 * - Added new_league_table, season_del_league_table and
 *   season_league_table_copy for the league_table operations.
 *
 * include/seasons.php
 * - Updated archive_season() to include league_table operations in $op_ary.
 * - Added season_del_league_table and season_league_table_copy queries
 * 
 * Uploaded: 1 August 2010
 * -----------------------
 *
 * 26 July 2010
 * ------------
 * Prediction list should display next fixture date's worth of fixtures even
 * if they are more than a week ahead.
 *
 * include/queries.php
 * - Modify the next_fixtures_7day query to select just the most recent end
 *   date from the fixtures-fixture_set join using LIMIT 1 and use
 *   DATE_FORMAT(NOW(), '%Y-%m-%d') in new_fixtures_in_league query.
 * 
 * Uploaded: 7 August 2010
 * -----------------------
 *
 * 1 August 2010
 * -------------
 * When a blank league_table is created the form guide shows all teams in the
 * league with the same colour and they are listed by team_id rather than name.
 *
 * include/functions.php
 * - Modified show_leaguetable() to include a check for submitted fixture
 *   results to determine whether the promotion, playoff and relegation
 *   zones should be displayed.
 *
 * include/queries.php
 * - Tweaked show_league_table query to order by team name in ascending order
 *   when points and goal difference are the same.
 * - Added num_league_results query to identify leagues with no results
 *   submitted.
 * 
 * Uploaded: 1 August 2010
 * -----------------------
 *
 * 1 August 2010
 * -------------
 * Prediction table left border images don't show up on the page. This is
 * probably caused by the relocation of the css files into a subdirectory
 *
 * css/predictions.css
 * - Updated the td.dark, td.light, #topfive, #leftnav, #leftnav ul li,
 *   #leftnav ul li a:hover and table.tableview styles to get the images
 *   from ../images
 * 
 * Uploaded: 1 August 2010
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * Implement a virtual promotion for teams in the 'champions league' and
 * 'Europa league' places in the premier division.
 * 
 * Uploaded: 
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * Add a description field to the points deduction table and include it in the
 * season archive.
 *
 * Database:
ALTER TABLE `predictions`.`predict_team_deduct` ADD COLUMN `description` TEXT  DEFAULT NULL COMMENT 'Describe the reason for the deduction' AFTER `deduction`;
 *
 * include/queries.php
 * 
 * 
 * Uploaded: 
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * On the search page include a popup that allows the score to be corrected.
 * 
 * Uploaded: 
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * Adding the league_id column to the predict_team_deduct table means that the
 * deduction display in the league table needs to take account of the league.
 *
 * include/functions.php
 * - Updated show_leaguetable() to include $league_id when querying for the
 *   points deduction for a team.
 *
 * include/queries.php
 * - Update get_points_deducted query to include the league_id
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * recalculate league table when a points deduction is removed from a team
 *
 * include/leagues.php
 * - Added call to update_league_table() in update_deductions() when removing
 *   a deduction from a team.
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * When no matches have been played in a season, the form guide shows a blank
 * for goals for and against rather than 0.
 *
 * include/queries.php
 * - Updated the {home,away}_goals_{for,against} queries to use IFNULL around
 *   the SUM() to return '0' instead. The queries would actually be better
 *   off selecting from the league_table table which is guaranteed to have
 *   values for the goals even at the start of the season
 *
 * Uploaded: 5 August 2010
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * The user group page shows the performance ratings for the previous season
 * at the start of the new season when all values should be 0.
 * And once a user has made an initial set of predictions at the start of the
 * new season, the top five guide shows that rating from last season.
 * This is because the predict_user_scores table is not archived when the
 * season is completed.
 *
 * Database: Include the last_updated column in the archive
ALTER TABLE season_predict_user_scores ADD COLUMN last_updated DATETIME AFTER points;
ALTER TABLE season_predict_user_scores DROP KEY user_id;
ALTER TABLE season_predict_user_scores ADD PRIMARY KEY (season_id, user_id);
 *
 * include/queries.php
 * - Added season_user_scores_copy and season_user_scores_del queries to
 *   archive this season's user records and cleab the user scores table ready
 *   for the new season.
 *
 * include/seasons.php
 * - Modified archive_season() to include the season_user_scores_copy and
 *   season_user_scores_del queries to clear out the old user records.
 *
 * Uploaded: 7 August 2010
 * -----------------------
 *
 * 5 August 2010
 * -------------
 * Update the {home,away}_goals_{for,against} queries to select from the
 * league_table table which will be more efficient and meaningful than using
 * the fixture results, particularly before any matches have been played.
 * Updated: 26 August 2010. Better to use the league_table_view for selecting
 * the data.
 *
 * include/queries.php
 * - Updated the following queries to retrive their data from the league_table
 *   table:
 *     home_games_played
 *     home_goals_for
 *     home_goals_against
 *     home_games_won
 *     home_games_drawn 
 *     home_games_lost
 *     away_games_played
 *     away_goals_for
 *     away_goals_against
 *     away_games_won
 *     away_games_drawn
 *     away_games_lost
 *
 * Uploaded: 26 August 2010
 * ------------------------
 *
 * 7 August 2010
 * -------------
 * Predictions list for today's fixtures not displayed.
 *
 * include/queries.php
 * - next_fixtures_7day query returns a NULL result with the
 *   start_date >= NOW() constraint. Changing the fixture set sub query
 *   to just retrieve the next fixture set end_date after today produces
 *   the required list.
 *
 * Uploaded: 7 August 2010
 * -----------------------
 *
 * 7 August 2010
 * -------------
 * The additional fixtures link is displayed when the fixture_set start date
 * is before today and the end date is after.
 *
 * include/queries.php
 * - The next_fixtures_7day was checking for start_date > NOW() when returns
 *   a NULL result set when predictions are made after the set has started.
 *
 * Uploaded: 7 August 2010
 * -----------------------
 *
 * 7 August 2010
 * -------------
 * get_league_position query only orders by points without taking goal
 * difference into account.
 *
 * See discussion from 24 August below.
 *
 * Uploaded: 26 August 2010
 * ------------------------
 *
 * 7 August 2010
 * -------------
 * During the dates covered by an existing fixture set, any new fixture are
 * not automatically listed and only available via the 'view all' link.
 * Perhaps the end_date constraint in the next_fixtures_7day query should
 * take into account the kickoff of the last match in the fixture set to decide
 * which fixtures to list.
 * 
 * queries.php
 * - Updated the next_fixtures_7day and new_fixtures_in_league to consider the
 *   time as well as the date when retrieving the count of fixtures to come.
 *
 * Uploaded: 11 September 2010
 * ---------------------------
 *
 * 7 August 2010
 * -------------
 * Don't display the no-save box if a fixture has already been predicted.
 *
 * Uploaded: 
 * -----------------------
 *
 * 16 August 2010
 * --------------
 * Displaying predictions while a match on the last day of a fixture set is in
 * progress shows the dreaded 'more fixtures' link rather than the fixtures
 * for the next set.
 *
 * include/next.php
 * - Updated next_form() to check for no new fixtures before getting the full
 *   set and also to return before building the form if there are no new
 *   fixtures.
 *
 * Uploaded: 10 December 2011
 * --------------------------
 *
 * 24 August 2010
 * --------------
 * Display showing teams in the playoff and relegation spots is a bit erratic,
 * especially at the start of a new season: five teams may be shown in the
 * playoff spots and perhaps just one for relegation.
 * This is because the get_league_position query cannot distinguish between
 * teams on the same points and goal difference and goals for.
 * We need to rework the query to use a view that just stores the goals for and
 * against as well as points and ordered by ascending name.
 *.
 * Database:
CREATE VIEW league_table_view AS
SELECT team_id,
       league_id,
       name                                        AS team_name,
       home_wins + home_draws + home_losses+
       away_wins + away_draws + away_losses        AS played,
       home_goals_for+away_goals_for               AS goals_for,
       home_goals_against+away_goals_against       AS goals_against,
       home_goals_for+away_goals_for-home_goals_against-away_goals_against AS goal_diff,
       points
FROM league_table JOIN predict_teams on team_id = predict_teams.id
ORDER BY league_id ASC, points DESC, goal_diff DESC, goals_for DESC, name ASC;
 *
 * include/functions.php
 * - Removed old commented-out lines from get_league_position() that used the
 *   old league_table query to get the position.
 *
 * include/queries.php
 * - Update the get_league_position query to calculate the team's position in
 * the league.
 *
 * Uploaded: 26 August 2010
 * ------------------------
 *
 * 24 August 2010
 * --------------
 * League table needs to order by goals scored before ordering by name.
 *
 * include/queries.php
 * - Update show_league_table query constraint to be
 * ORDER BY points DESC,
 *       (home_goals_for+away_goals_for-
 *       home_goals_against-away_goals_against) DESC,
 *       home_goals_for+away_goals_for DESC,
 *       team_name ASC
 *
 * Uploaded: 24 August 2010
 * ------------------------
 *
 * 26 August 2010
 * --------------
 * Rewrite form and league_table based queries to use the league_table_view.
 * Of particular attention are: league_table, show_league_table,
 * mysql50_playoff_team_select, playoff_team_select.
 *
 * include/functions.php
 * - Removed the static playoff_team_select query from playoff_team_select()
 *   and removed the comments regarding implementing this with MySQL 4.x
 *
 * include/queries.php
 * - The playoff_team_select query can be made much simpler because the view
 *   is already ordered.
 * - Removed the mysql50_playoff_team_select query as it is not used.
 * - Updated show_league_table query to use the league_table_view view.
 * - Removed the league_table query as it is not used.
 *
 * Uploaded: 26 August 2010
 * ------------------------
 *
 * 26 August 2010
 * --------------
 * Migrate to a standalone database using only the predictions tables.
 *
 * Database:
CREATE DATABASE predictions;
GRANT SELECT, INSERT, UPDATE, DELETE ON predictions.* to predict_user@'localhost' IDENTIFIED BY 'predict_pass';
FLUSH PRIVILEGES;
 *
 * Dump the required databases tables with the command:
table_list:mysqldump --opt -u root -p predictions_dev fixture_results fixture_set fixture_set_seq fixtures fixtures_seq league league_table league_teams leagues leagues_seq predict_comments predict_comments_seq predict_team_deduct predict_teams predict_teams_seq predict_user_scores predict_users predict_users_seq predictions season_deductions season_fixture_sets season_fixtures season_league_table season_predict_comments season_predict_team_deduct season_predict_user_scores season_predictions season_results season_teams_leagues seasons seasons_seq user_league_members user_leagues user_leagues_seq user_subscriptions > predictions_innodb.sql
 *
 * Import the database:
mysql -u root -p predictions < predictions_innodb.sql
 * include/settings.php
 * - Update the $dsn variable to be:
 *   $dsn = 'mysql://predict_user:predict_pass@localhost/predictions';
 *
 * Uploaded: 26 August 2010
 * ------------------------
 *
 * 26 August 2010
 * --------------
 * Remove duplicate playoff_team_select query: either at 1679 or 2393.
 *
 * In actual fact, neither query is used because of the problem with the DB
 * driver not supporting parameterisation for LIMIT and RANGE values.
 *
 * include/queries.php
 * - Deleted the superfluous playoff_team_select query
 *
 * Uploaded: 26 August 2010
 * ------------------------
 *
 * 26 August 2010
 * --------------
 * Add a known name column to the predict_teams table so that an abbreviated
 * team name can be used in the league table.
 *
 * Database:
 * ALTER TABLE predict_teams ADD COLUMN known_name CHAR(32) DEFAULT NULL;
 * Recreate the league_table_view to include the known name column:
DROP view league_table_view;
CREATE VIEW league_table_view AS
SELECT team_id,
       league_id,
       name                                        AS team_name,
       known_name,
       home_wins + home_draws + home_losses+
       away_wins + away_draws + away_losses        AS played,
       home_goals_for+away_goals_for               AS goals_for,
       home_goals_against+away_goals_against       AS goals_against,
       home_goals_for+away_goals_for-home_goals_against-away_goals_against AS goal_diff,
       points
FROM league_table JOIN predict_teams on team_id = predict_teams.id
ORDER BY league_id ASC, points DESC, goal_diff DESC, goals_for DESC, name ASC;
 *
 * include/queries.php
 * - Update the show_league_table query to select the full team name if the
 *   known name is null, but use the known name if it exists.
 *
 * Uploaded: 26 August 2010
 * ------------------------
 *
 * 26 August 2010
 * --------------
 * Update the leagues section to include an 'edit team' button in the 'league
 * membership' section to display a box where the details (name, known name) of
 * the first selected team can be modified.
 *
 *
 * Uploaded: 
 * ------------------------
 *
 * 26 August 2010
 * --------------
 * Replace the {home,away}_games_{played,won,lost,drawn} queries with a single
 * query from the database and update the form_guide_row() function to take
 * a column value and format it.
 *
 * include/functions.php
 * - form_guide_row() runs either the home_form or away_form query and uses
 *   the column name as the text to display.
 * - get_team_form() makes only one call to form_guide_row() each for home
 *   and away with either the away_form or home_form query.
 *
 * include/queries.php
 * - Added home_form query to extract all the home form data from league_table
 * - Added away_form query to extract all the home form data from league_table
 * - Removed home_goals_played, home_games_won, home_goals_for, etc queries.
 *
 * Uploaded: 6 September 2010
 * --------------------------
 *
 * 27 August 2010
 * --------------
 * Login form text and password boxes stretch beyond the left navigation panel.
 *
 * css/predictions.css
 * - Added font-size: 0.75em element to the #login and added padding-left: 7px
 *
 * include/login.php
 * - Changed the size of the login form input boxes.
 *
 * Uploaded: 27 August 2010
 * ------------------------
 *
 * 30 August 2010
 * --------------
 * Because the live website is hosted in Chicago (HostGator), the timezone for
 * the database is set to 'US/Central' which means that prediction fixtures are
 * available for many hours after the match has completed and that results can
 * not be submitted until well after the match has completed.
 * This should mean adding timezone support to the system so that MySQL
 * queries that use 'NOW()' are replaced with a parameterised query variable
 * representing the date and time in the time zone for that league.
 *
 * This will also involve adding a timezone dropdown selector to the league
 * forms.
 *
 * The PHP date equivalent of MySQL NOW() is: date("Y-m-d H:i:s")
 *
 * Database: Add timezone value to the league table
ALTER TABLE leagues ADD COLUMN timezone CHAR(64) NOT NULL DEFAULT 'Europe/London' AFTER name;
 * 
 * include/fixtures.php
 * - Updated set_fixture() to pass the timezoned times as a parameter
 *   to the update_fixture and add_new_fixture queries.
 * - Updated del_fixture_set() to retrieve the league id for the set and use
 *   this to retrieve the value of NOW() to use when deleting fixtures.
 * 
 * include/functions.php
 * - Added now_by_timezone() to get the value of NOW() for a league's
 *   timezone.
 * - Updated season_predictions() to pass the timezoned times as a parameter
 *   to the user_predictions_season query.
 *   Check for what happens when an invalid timezone is used.
 * - Updated update_user_scores() to include a call to get_user_now() to use
 *   as the last_updated column with the user_score_insert query
 * - Updated insert_user_comment() to include a call to get_user_now() to use
 *   as the last_updated column with the user_score_insert query
 *
 * include/login.php
 * - Updated check_login() to pass a PHP-derived date time to be used to
 *   update the last login column of the predict_users table.
 *
 * include/next.php
 * - Updated next_form() to display the timezone above the predictions form.
 * - Updated next_form() to pass the league's value of NOW() and pass it to
 *   the next_fixtures_7day query.
 * - Updated show() to retrieve the league's value of NOW() and pass it to
 *   the next_fixtures query.
 * - Updated next_form() to retrieve the league's value of NOW() and pass it to
 *   the new_fixtures_in_league query.
 * - Update verify_next() to retrieve the league's value of NOW() and pass it to
 *   the check_ko query. Also included an error check for the check_ko query.
 *
 * incude/queries.php
 * - Added the get_league_timezone query for the now_by_timezone() function
 * - Updated the update_last_login query to take a parameter instead of NOW()
 * - Updated the get_first_league query to take a parameter instead of NOW()
 * - Modified the not_yet_predicted query to use a parameter instead of NOW()
 * - Modified the league_not_yet_predicted query to use a parameter instead
 *   of NOW()
 * - Modified the user_predictions_season query to use a parameter instead
 *   of NOW()
 * - Modified the correct_result_teams query to not use NOW()
 * - Modified the league_score_teams query to use a parameter instead of NOW()
 * - Modified the league_result_teams query to use a parameter instead of NOW()
 * - Modified the fixture_set_this_month query to use a parameter instead
 *   of NOW()
 * - Modified the fixture_set_this_month2 query to use a parameter instead
 *   of NOW()
 * - Modified the next_fixtures query to use a parameter instead of NOW()
 * - Modified the next_fixtures_7day query to use a parameter instead of NOW()
 * - Modified the new_fixtures_in_league query to use a parameter instead
 *   of NOW() and also use >= for the date check.
 * - Modified the check_ko query to use a parameter instead of NOW()
 * - Modified the add_new_fixture query to use a parameter instead of NOW()
 * - Modified the update_fixture query to use a parameter instead of NOW()
 * - Added fixture_set_league_id query so that the timezone applied to a
 *   fixture set may be determined.
 * - See 4 Sepetember entry below for details of update to num_predictions
 *   query which requires the fixture_results_view VIEW.
 * - Rewritten num_predictions query so that it does not require NOW() and
 *   excludes matches that have been abandoned or postponed.
 * - Rewritten num_league_predictions so that it does not require NOW() and
 *   excludes matches that have been abandoned or postponed.
 * - Modified the results_fixture_sets query to use a parameter instead
 *   of NOW()
 * - Modified check_end_of_fixture query to use a parameter instead of NOW()
 * - Modified results_fixtures query to use a parameter instead of NOW()
 * - The new_season_details query does not need to be changed because it is
 *   only retrieving the current year.
 * - Modified fixtures_in_season query to use a parameter instead of NOW()
 * - userteams_insert query is not dependent on any league and so requires
 *   a separate method for determining the time (c.f. user timezone column
 *   below).
 * - Modified user_score_insert query to use a parameter instead of NOW()
 * - Modified top_five_users to use the fixtures_results_view and only use
 *   NOW() to exclude users that have not predicted in the last 14 days.
 * - Modified insert_user_comment query to use a parameter instead of NOW()
 *
 * include/results.php
 * - Added call to now_by_timezone() to get date for results_fixture_sets query.
 *   in show().
 * - Added call to now_by_timezone() to get date for check_end_of_fixture query.
 *   in add_results().
 * - Added call to now_by_timezone() to get date for results_fixtures query.
 *   in results_form().
 *
 * include/seasons.php
 * - Added call to now_by_timezone() to get date for fixtures_in_season query.
 *   in can_season_be_archived().
 * - Added call to now_by_timezone() to get date for fixtures_in_season query.
 *   in season_details_form().
 *
 * include/summary.php
 * - Added call to now_by_timezone() to get date for league_not_yet_predicted
 *   and not_yet_predicted queries in show()
 * - Added call to now_by_timezone() to get NOW() in list_correct_fixtures()
 *
 * include/team.php
 * - Update show() to pass a value for NOW() (derived from the timezone for the
 *   default subscription).
 *
 * index.php
 * - Update the nextset action code to pass the current date to the
 *   get_first_league query
 *
 * Uploaded: 5 September 2010
 * --------------------------
 *
 * 4 September 2010
 * ----------------
 * Create a view for the fixtures and fixture_results that can be used when
 * selecting fixture data for display.
 *
 * Database:
DROP VIEW IF EXISTS fixture_results_view;
CREATE VIEW fixture_results_view AS
SELECT id AS fixture_id, league_id,
       CONCAT(fixture_date, ' ', kickoff) AS kickoff,
       home_team_id AS home, away_team_id AS away, match_type,
       result_type, home_goals, away_goals
  FROM fixtures, fixture_results
 WHERE fixtures.id = fixture_results.fixture_id;
 *
 * Uploaded: 5 September 2010
 * --------------------------
 *
 * 4 September 2010
 * ----------------
 * Replace the num_predictions query with something that takes into account
 * fixtures that have not been abaondoned or postponed.
 *
 * include/queries.php
 * - Rewrote the num_predictions query so that it excludes abandoned and
 *   postponed fixtures, uses the fixture_results_view VIEW and is not bound
 *   by the current time.
 *
 * Uploaded: 5 September 2010
 * --------------------------
 *
 * 4 September 2010
 * ----------------
 * Create a new query that is used to count the number of predictions made for
 * matches have had the result submitted and where the match was not 
 * postponed or abandoned. This is to replace the use of num_predictions
 * in functions::update_user_scores().
 *
 * include/functions.php
 * - Updated update_user_scores() to use the predicted_results query for the
 * number of matches predicted.
 *
 * include/queries.php
 * - Added predicted_results query that selects the number of predictions made
 *   for matches where the result has been submitted and the match was not
 *   abandoned or postponed.
 *
 * Uploaded: 5 September 2010
 * --------------------------
 *
 * 4 September 2010
 * ----------------
 * Add a locale/timezone column to the predict_users table so that queries
 * that require the client timezone (i.e. need to know NOW() but are not
 * attached to a league) can be performed.
 * Database:
ALTER TABLE predict_users ADD COLUMN timezone CHAR(64) NOT NULL DEFAULT 'Europe/London' AFTER password;
 *
 * include/functions.php
 * - Added get_user_now funtion to retrieve NOW() in the user's timezome.
 *
 * include/queries.php
 * - Added get_user_timezone query to retrieve the timezone for a user.
 * - Updated the userteams_insert query to use a paramater instead of NOW().
 * - Updated the users_add query to use a paramater instead of NOW().
 *
 * include/users.php
 * - Updated users_add() to call get_user_now() to use as the value of the time
 *   that a new user was created.
 *
 * include/userteams.php
 * - Updates userteams_add() to select the user's timezone when calculating
 *   the time that the userteam was created.
 *
 * Uploaded: 5 September 2010
 * --------------------------
 *
 * 4 September 2010
 * ----------------
 * Add timezone details to user form.
 *
 * include/queries.php
 * - Updated user_details query to include the timezone column
 * - Updated list_users_byname query to include the timezone column
 * - Updated users_update query to include the timezone column
 * - Updated users_add query to include the timezone column
 *
 * include/users.php
 * - Updated users_form() to include the timezone.
 * - Updated users_update() to include the timezone.
 * - Updated users_add() to include the timezone.
 *
 * Uploaded: 6 September 2010
 * --------------------------
 *
 * 4 September 2010
 * ----------------
 * The last meeting line in the form guide shows the result of the first
 * recorded meeting rather than the last.
 *
 * include/queries.php
 * - Updated score_when_last_played query to use the season_results and
 *   season_fixtures table because the subquery results are passed up in
 *   reverse order and the fixtures need to be retrived in date order.
 *
 * Uploaded: 5 September 2010
 * --------------------------
 *
 * 4 September 2010
 * ----------------
 * Live site reports the following error on index.php?action=team
Warning: array_map() [function.array-map]: Argument #2 should be an array in /home/spleen/public_html/predict/include/team.php on line 150

Warning: implode() [function.implode]: Invalid arguments passed in /home/spleen/public_html/predict/include/team.php on line 150

Warning: array_map() [function.array-map]: Argument #2 should be an array in /home/spleen/public_html/predict/include/team.php on line 153

Warning: implode() [function.implode]: Invalid arguments passed in /home/spleen/public_html/predict/include/team.php on line 153
 *
 * include/team.php
 * - Not uploaded when migrating to timezoned queries.
 *
 * Uploaded: 7 September 2010
 * --------------------------
 *
 * 7 September 2010
 * ----------------
 * Add timezone details to league form.
 *
 * include/leagues.php
 * - Updated show_league_teams() to get and display the timezone for the league.
 * - Removed save_new_league() function as it is not used
 * - Update leagues_insert() to include the submitted timezone.
 * - Updated add_new_league_form() to include a timezone box.
 * - Update leagues_update() to include the submitted timezone.
 *
 * include/queries.php
 * - Updated insert_new_league query to use the posted timezone.
 * - Removed insert_new_league query as it is not used.
 * - Updated leagues_insert to include the timezone
 * - Updated save_league_details to include the timezone
 *
 *
 * Uploaded: 11 September 2010
 * ---------------------------
 *
 * 7 September 2010
 * ----------------
 * Review the tables that do a join across the fixtures and fixture_results
 * tables and re-write the fixture_results_view VIEW so that it only includes
 * fixtures whose result is not 'postponed' or 'abandoned'.
 * Likely affected queries: predictions_this_week, predictions_last_month,
 * predictions_last_month, correct_score_teams, correct_diffs_teams,
 * correct_diffs_teams, scores_bydate, scores_this_week, scores_last_week,
 * scores_last_month, results_bydate, results_this_week, results_last_week,
 * results_last_month, correct_diff_this_week, correct_diff_last_week,
 * correct_diff_last_month, correct_result_teams, league_score_teams,
 * league_diffs_teams, league_result_teams, match_results,
 * results_byleague_team, result_byid_with_teams, summary_season_diffs,
 * summary_league_outcomes, summary_league_diffs, summary_league_scores,
 * is_exact_score, is_correct_diff, is_correct_outcome, last_five_results,
 * insert_league_table, scores, num_predictions, predicted_results,
 * num_league_predictions, all_matches_played, num_league_results,
 * already_resulted, not_submitted, season_results_copy, season_results_delete
 * top_five_users
 *
 * Database:
DROP VIEW IF EXISTS fixture_results_view;
CREATE VIEW fixture_results_view AS
SELECT id AS fixture_id, league_id,
       CONCAT(fixture_date, ' ', kickoff) AS kickoff,
       home_team_id AS home, away_team_id AS away, match_type,
       result_type, home_goals, away_goals
  FROM fixtures, fixture_results
 WHERE fixtures.id = fixture_results.fixture_id
  AND result_type <> 'abandoned'
    AND result_type <> 'postponed';
 * 
 * include/queries.php
 * - Updated the following queries:
 *   predictions_this_week - Replaced subquery with NOW() parameters
 *   predictions_last_week - Replaced subquery with NOW() parameters
 *   predictions_last_month - Replaced subquery with NOW() parameters
 *   correct_score_teams - Use fixture_results_view for fixture selection 
 *   correct_diffs_teams - Use fixture_results_view for fixture selection
 *   correct_result_teams - Use fixture_results_view for fixture selection
 *   scores_bydate - Use fixture_results_view for fixture selection
 *   scores_this_week - Use fixture_results_view for fixture selection
 *   scores_last_week - Use fixture_results_view for fixture selection
 *   scores_last_month - Use fixture_results_view for fixture selection
 *   results_bydate - Use fixture_results_view for fixture selection
 *   results_this_week - Use fixture_results_view for fixture selection
 *   results_last_week - Use fixture_results_view for fixture selection
 *   results_last_month - Use fixture_results_view for fixture selection
 *   correct_diff_this_week - Use fixture_results_view for fixture selection
 *   correct_diff_last_week - Use fixture_results_view for fixture selection
 *   correct_diff_last_month - Use fixture_results_view for fixture selection
 *   correct_result_teams - Use fixture_results_view for fixture selection
 *   league_score_teams - replaced timezoned NO() parameter with a the MAX date
 *   that a results has been submitted for the league. Use fixture_results_view
 *   for fixture selection
 *   league_diffs_teams - replaced timezoned NO() parameter with a the MAX date
 *   that a results has been submitted for the league. Use fixture_results_view
 *   for fixture selection
 *   league_result_teams - replaced timezoned NO() parameter with a the MAX date
 *   that a results has been submitted for the league. Use fixture_results_view
 *   for fixture selection
 *   match_results - Use fixture_results_view for fixture selection
 *   results_byleague_team - Use fixture_results_view for fixture selection
 *   result_byid_with_teams - Query not used, deleted.
 *   summary_season_diffs - Use fixture_results_view for fixture selection
 *   summary_league_outcomes - Use fixture_results_view for fixture selection
 *   summary_league_diffs - Use fixture_results_view for fixture selection
 *   summary_league_scores - Use fixture_results_view for fixture selection
 *   is_exact_score - Use fixture_results_view for fixture selection
 *   is_correct_diff - Use fixture_results_view for fixture selection
 *   is_correct_outcome - Use fixture_results_view for fixture selection
 *   last_five_results - Use fixture_results_view for fixture selection
 *   insert_league_table - Use fixture_results_view for fixture selection
 *   scores -Use fixture_results_view for fixture selection
 *   num_predictions - Removed the result_type constraints
 *   predicted_results - Removed the result_type constraints
 *   num_league_predictions - Removed the result_type constraints
 *   all_matches_played - Use fixture_results_view for fixture selection
 *   num_league_results - Use fixture_results_view for fixture selection
 *   already_resulted - Use fixture_results_view for fixture selection
 *   not_submitted - Use fixture_results_view for fixture selection
 *   season_results_copy - Use fixture_results_view for fixture selection
 *   season_results_delete - Use fixture_results_view for fixture selection
 *   top_five_users - Removed the result_type constraints
 *
 * include/sumamry.php
 * - Modified summary_table() to pass the user NOW() paramters to the
 *   summary queries.
 * - Moified list_correct_fixtures() to add use the league id to the query
 *   array instead of the timezoned now.
 *
 * Uploaded: 18 September 2010
 * ---------------------------
 *
 * 12 September 2010
 * ----------------
 * Add timezone details to options form, in it's own panel.
 *
 * include/functions.php
 * - Added user_timezone_select() to display a list of timezones.
 * - Added user_timezone_validate() to verify that a valid timezone has been
 *   submitted
 * - Added user_timezone_update() to allow the user to update their own timezone
 *
 * include/options.php
 * - Updated show() to check for a valid timezone submission
 * - Updated options_form() to include the timezone in the settings form
 *
 * include/queries.php
 * - Added get_user_timezone query for the user's timezone
 * - Added save_user_timezone query to set the user's timezone
 *
 * Uploaded: 29 September 2010
 * ---------------------------
 *
 * 17 September 2010
 * ----------------
 * Promotion, playoff and relegation positions not highlighted.
 *
 * include/queries.php
 * - Incorrect table name in where clause.
 *
 * Uploaded: 18 September 2010
 * ---------------------------
 *
 * 18 September 2010
 * ----------------
 * Updating the scores after submitting results causes the league table to be
 * filled with zero for each column for each team. Suspect an error in the
 * insert_league_table query.
 *
 * include/functions.php
 * - Updated update_league_table() to display a better error message when the
 *   league table update failed.
 *
 * include/queries.php
 * - The insert_league_table query was constrained on the fixtures table instead
 *   of fixture_results_view and was also using a fixture_id constraint instead
 *   of league_id
 *
 * Uploaded: 18 September 2010
 * ---------------------------
 *
 * 18 September 2010
 * ----------------
 * Fixture sets where postponed or abandoned matches have been submitted show
 * up as incomplete on the results page.
 *
 * include/queries.php
 * - Updated not_submitted query to not select from fixture_results_view because
 *   the view excludes postponed and abandoned matches.
 *
 * include/results.php
 * - Updated show_fixture_sets() to pass changed arguments to the not_submitted
 *   query.
 *
 * Uploaded: 18 September 2010
 * ---------------------------
 *
 * 18 September 2010
 * ----------------
 * The displayed number of predictions made by a user is incorrect.
 *
 * include/queries.php
 * - The num_predictions and num_league_predictions were excluding the fixtures
 *   for which results for the completed match have been submitted rather than
 *   counting them.
 *
 * Uploaded: 18 September 2010
 * ---------------------------
 *
 * 18 September 2010
 * ----------------
 * User scores are completely off kilter, almost exactly the same as the number
 * of predictions. This appears to be because of duplicate counting with the
 * summary_season_diffs, match_results and scores queries.
 *
 * include/queries.php
 * - Updated the summary_season_diffs query to exclude fixtures where the
 *   home goals has been correctly predicted.
 *
 * Uploaded: 28 September 2010
 * ---------------------------
 *
 * 10 March 2011
 * -------------
 * Matches not available for prediction on the day they are played even though
 * the kickoff is several hours away.
 * This is likely because of the timezone of the DB server.
 * Not a problem with the timezone of the DB server, but rather doing a date
 * compare using DATE_FORMAT including the time.
 *
 * include/queries.php
 * - Corrected new_fixtures_in_league query to use '%Y-%m-%d' for the date
 *   comparison and also changed comparison to >= from >.
 * - Updated next_fixtures_7day query to  use '>=' for the comparison between
 *   fixture_date+kickoff and current time (by timezone) and changed DATE_FORMAT
 *   to '%Y-%m-%d' in comparison with end_date.
 *
 * Uploaded: 25 November 2011
 * --------------------------
 *
 * 23 July 2011
 * ------------
 * Attempt to archive a season results in the following errors:
 *
Define new seasons and archive the previous season's data.
Store predictions
Delete predictions
Store results
Delete resultsfailed
Store fixtures
Delete fixtures
Copy fixture sets
Delete fixture sets
Copy deductions
Delete deductionsfailed
Archive user scores
Delete user scoresfailed
Copy league table
Delete league tablefailed
New league tablefailed
2010/2011 Season updated.
 *
 * Uploaded: 
 * ---------------------------
 *
 * 24 October 2011
 * ---------------
 * Unselecting all user groups for an inactive user results in the following
 * warning being displayed along with the 'User must belong to at least one 
 * user team' error:
 *
 * Warning: array_slice() expects parameter 1 to be array, null given in /home/spleen/public_html/predict/include/users.php on line 371
 *
 *
 * Uploaded: 
 * ---------------------------
 *
 * 26 November 2011
 * ----------------
 * The test for checking whether a prediction has been submitted after kickoff
 * only checks the hours, minutes and seconds of the submission and not the
 * actual date.
 *
 * include/queries.php
 * - Updated check_ko query to convert submission and catenated fixture date and
 *   kickoff to timestamps and return a count of the number of times that the
 *   submission timestamp is lower.
 *
 * Uploaded: 26 November 2011
 * --------------------------
 *
 * 10 December 2011
 * ----------------
 * Reorder the list of kickoff times so that the most commonly selected are at
 * the top of the list before the rest in an ordered list.
 *
 * include/functions.php
 * - Rearranged the $ko_vals array in kickoff_select_list() so the most
 *   frequently selected values are displayed first.
 *
 * Uploaded: 10 December 2011
 * --------------------------
 *
 * 10 December 2011
 * ----------------
 * Error updating user scores. The user score is deleted but the new score cannot
 * be calculated.
 *
 * include/functions.php
 * - Last two lines of get_user_now() were commented out meaning that no time
 *   value was returned.
 * - Also updated update_user_scores() to check for a null value of $now and
 *   return an error.
 *
 * Uploaded: 10 December 2011
 * --------------------------
 *
 * 20 December 2011
 * ----------------
 * Fix the top 5 user display two decimal places for the user scores.
 * 
 * include/queries.php
 * - Updated top_five_users query to use the TRUNCATE function to limit the
 *   retrieved score to two digits
 *
 * Uploaded: 20 December 2011
 * --------------------------
 *
 * 06 January 2012
 * ---------------
 * The link to unpredicted fixtures on the summary page for a league should
 * include '&showall=1' to ensure unpredicted fixtures are displayed.
 *
 * include/summary.php
 * - Modified the line setting $qs to include "&showall=1" in show().
 *
 * Uploaded: 09 March 2012
 * -----------------------
 *
 * 14 January 2012
 * ---------------
 * When all fixtures are to be played on the same day, the fixtures form should
 * not display a select box for the date. Plain text or a disabled text box.
 *
 *
 * Uploaded: 
 * --------------------------
 *
 * 09 March 2012
 * -------------
 * The predications page league table should include goal difference.
 *
 * include/queries.php
 * - Added goal_diff to the columns selected in the show_league_table query.
 *
 * include/functions.php
 * - Added goal difference column to the league table in show_leaguetable().
 *
 * Uploaded: 09 March 2012
 * -----------------------
 *
 * 09 March 2012
 * -------------
 * Remove the YJFC branding from the site banner and footer.
 *
 * index.php
 * - Replace the yjfc image with some text and update the footer message
 *
 * css/predictions.css
 * - Change the width and height elements in the body and banner styles
 *
 * Uploaded: 09 March 2012
 * -----------------------
 *
 * 09 March 2012
 * -------------
 * Include the ability (via AJAX) to edit team known-name on the leagues page.
 *
 * Uploaded: 
 * --------------------------
 *
 * 12 March 2012
 * -------------
 * Order the fixtures in the results page by the kick-off time.
 *
 * include/queries.php
 * - Changed the ORDER BY clause in results_fixtures query to include kickoff.
 *
 * Uploaded: 12 March 2012
 * -----------------------
 *
 * 19 March 2012
 * -------------
 * Daylight saving switchover still problematic.
 *
 * include/functions.php
 * - Changed the for loop terminating value to be 10 hours into the day to get
 *   past the daylight saving traumas.
 *
 * Uploaded: 21 March 2012
 * -----------------------
 *
 * 1 June 2012
 * -------------
 * Ugly top 5 error displayed at the end of the season.
 * Error caused by installing site on new system, creating a new database from a restore
 * file that does not contain the VIEW defintions.
 *
 * include/functions.php
 * - Added query error trap when top_five_users query fails in top_five_users().
 *
 * Uploaded: 1 June 2012
 * -----------------------
 *
 * The test for checking whether a prediction has been submitted after kickoff
 * ---------------------------------
 * Files to upload - 29 January 2010
 * ---------------------------------
 *
 * include/functions.php
 * - Updated get_team_form() to include the home and away teams. Makeing the
 *   box expand/contract is very problematic and doesn't look good.
 * include/fixtures.php
 * - allows for an evening kickoff checkbox
 * - Tweaked the logic in fixture_list_form() to accept extra fixtures if the
 *   fixture set requests them.
 * include/queries.php
 * - Modified results_byleague_team to 'ORDER BY fixture_date'.
 * include/search.php
 * - Modified show_team_results() to display the result type.
 * ---------------------------------
 * 13 February 2010
 * ---------------------------------
 * include/leftnav.php
 * - Reworked leftnav() function
 * include/next.php
 * - Only include the next 6 days worth of fixtures, unless more requested
 * include/queries.php
 * - Added restricted fixture selection queries
 *
 * ---------------------------------
 * 5 September 2010
 * ---------------------------------
 Database:
ALTER TABLE leagues ADD COLUMN timezone CHAR(64) NOT NULL DEFAULT 'Europe/London' AFTER name;
OP VIEW IF EXISTS fixture_results_view;
CREATE VIEW fixture_results_view AS
SELECT id AS fixture_id, league_id,
       CONCAT(fixture_date, ' ', kickoff) AS kickoff,
       home_team_id AS home, away_team_id AS away, match_type,
       result_type, home_goals, away_goals
  FROM fixtures, fixture_results
 WHERE fixtures.id = fixture_results.fixture_id;
ALTER TABLE predict_users ADD COLUMN timezone CHAR(64) NOT NULL DEFAULT 'Europe/London' AFTER password;
 *
 * include/fixtures.php
 * - Updated set_fixture()
 * - Updated del_fixture_set()
 *
 * include/queries.php
 * - Rewrote the num_predictions query
 * - Added predicted_results query
 * - Added get_user_timezone query
 * - Updated the userteams_insert query
 * - Updated the users_add query
 * - Numerous query updates for timezone support
 *
 * include/results.php
 * - Updated show(), add_results() and results_form()
 *
 * include/seasons.php
 * - Updated can_season_be_archived() and season_details_form()
 *
 * include/summary.php
 * - Updated show() and list_correct_fixtures()
 * include/team.php
 * - Updated show()
 * index.php
 * - Update the nextset action
 *
 * include/functions.php
 * - Updated update_user_scores()
 * - Added get_user_now funtion
 * - Added now_by_timezone()
 * - Updated season_predictions()
 * - Updated update_user_scores()
 * - Updated insert_user_comment()
 *
 * include/login.php
 * - Updated check_login()
 *
 * include/next.php
 * - Updated next_form()
 * - Updated show()
 * - Update verify_next()
 *
 * include/team.php
 * - Updated show()
 *
 * include/users.php
 * - Updated users_add()
 *
 * include/userteams.php
 * - Updates userteams_add()
 */
?>
