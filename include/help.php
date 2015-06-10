<?php
/*
 * Help page describing the operation of the site
 * show() - Main driver
 * navtabs() - top navigation items
 */

/* Main driver for the single page help system
 * @params:
 *  none
 * @returns:
 *  string containing HTML contents of the page
 */
function show()
{
  $disp_str = "
<dl id=\"help\">
 <dt>Summary</dt>
 <dd>The summary page consists of a general summary page in addition to a tab
for each league that predictions are made for.
<br />
The summary page consists of three items:
<ol>
 <li>A table listing the number of predictions, correct match outcomes (1 point
for each) and exact scores (3 points for each) for the past 7 seven days; the
week previous to that and in the past 31 days.
<br />
The sum of the correct match outcomes and exact scores for the season is
displayed beneath this table.</li>
 <li>A table showing all the matches where the exact score was correctly
predicted (and scored 3 points).</li>
 <li>A table showing all the matches where the difference between the home
and away goals was correctly predicted (excluding draws) and scoring two
points each.</li>
 <li>A table showing all the matches where the outcome was correctly
predicted (and scored 1 point); the actual score of the match is shown
in parentheses.</li>
</ol>
There is also a tab for each league that predictions are made for showing the
matches where exact score and outcomes have been correctly predicted.
<br />
Matches that have been postponed or abandoned are not included in the
calculations.
 </dd>

 <dt>Predictions</dt>
 <dd>The predictions page contains a tab for each league where predictions are
made.
<br />
The display for each league consists of two sections:
<ol>
 <li>A table listing the upcoming fixtures where predictions can be registered.
Predictions can be made up until the match kick-off time. Matches will not be
displayed once the kick-off time has passed. Press the Submit button to save
the listed match predictions.</li>
 <li>To the right of the list of fixtures is the current league table. When
a team name is clicked (for the form guide) it (and the opposing team) will be
highlighted in the legue table.</li>
 <li>A prediction can be prevented from being saved by checking the
'<em>no save</em>' to the right of the fixture.</li>
 <li>Click on a team name to view the recent form of a the team; the number of
games won, drawn and lost, goals scored and conceeded at home for home teams
and the same for games away for away teams.
<br />
The form guide also includes the position of the team in their league as well
as results of their last five games (home and away).</li>
 <li>A list of dates on which predictions have been submitted, starting with
the most recent. If JavaScript is enabled in the browser, click on the date to
display the predictions made on the date. Another click on the date will hide
the predictions.
<br />
At the top of the date list is a dropdown list of users that are also members of
the teams you belong to. If JavaScript is enabled, select the name of a user
from the list to display a list of the predictions they have made this season.
Note that it is not possible to view the predictions other users have made for
the upcoming fixtures.</li>
</ol>
 </dd>

 <dt>Group table</dt>
 <dd>The group table page shows how well your position in the user teams that
you are a member of.
<br />
There is a tab at the top of the page for each team you are a member and the
following items are listed on each page:
<ul>
 <li>Username - the login name of the team member,</li>
 <li>Predictions - the total number of predictions made this season,</li>
 <li>Correct results - the number of correctly predicted match outcomes
(1 point for each),</li>
 <li>Correct scores, - the number of correctly predicted scores, (3 points
for each),</li>
 <li>Points - the sum of the correctly predicted match outcomes and exact
score predictions.</li>
</ul>
 </dd>

 <dt>Options</dt>
 <dd>The options page provides two forms: one to select the leagues that
predictions are made for, and, a form to enter a new password.
 </dd>

</dl>
";

  return($disp_str);
}

/* Function to create the horizontal navigation tabs
 * @params:
 *  none
 * @returns:
 *  string containing HTML list of navigation tabs
 */
function navtabs()
{
  $navstr = "<ul><li class=\"selected\">About this site</li></ul>\n";

  return($navstr);
}
?>
