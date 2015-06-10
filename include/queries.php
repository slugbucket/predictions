<?php
/*
 * Script to define the possible queries in use on the site with placeholders
 * for positional values
 */

$query_ary = array(

/* Check for a valid username and password
 * Used by login::check_login()
 */
'check_login' =>
"
SELECT id,
       fullname,
       isadmin
  FROM predict_users
 WHERE uname = ?
   AND password = md5(?)
   AND active ='1' LIMIT 1
",

/* Update the last_login field after a user has logged in
 * Used by login::check_login()
 */
'update_last_login' =>
"
UPDATE predict_users
   SET last_login = ?
 WHERE id = ?
",

/* Get the timezone for a league.
 * Used by functions::now_by_timezone(), leagues::show_league_teams()
 */
'get_league_timezone' =>
"
SELECT timezone
  FROM leagues
 WHERE id = ?
",

/* Get the timezone for a user.
 * Used by functions::user_timezone_select(), userteams::userteams_add()
 */
'get_user_timezone' =>
"
SELECT timezone
  FROM predict_users
 WHERE id = ?
",

/* Get the first league that a user is subscribed to and has outstanding
 * predictions, if a specific league has been requested.
 * Used in index.php when a link on the summary page is for the total
 * number of predictions yet to be made.
 * Used by index
 */
'get_first_league' =>
"
SELECT league_id
  FROM user_subscriptions
 WHERE user_id = ?
   AND league_id IN
   ( SELECT league_id
       FROM fixtures
      WHERE fixture_date > ? )
 LIMIT 1;
",

/* Find whether the requested league is in a user's subscriptions */
'get_league_from_subs' =>
"
SELECT COUNT(user_id)
  FROM user_subscriptions
 WHERE user_id = ?
   AND league_id = ?
",

/* Get a count of the  predictions that a user has not made
 * Used by summary::show()
 */
'not_yet_predicted' =>
"
SELECT COUNT(id)
  FROM fixtures
 WHERE league_id IN (
   SELECT league_id
     FROM user_subscriptions
    WHERE user_subscriptions.user_id = ?)
   AND fixtures.id NOT IN (
    SELECT DISTINCT fixture_id
      FROM predictions, user_subscriptions
     WHERE predictions.user_id = user_subscriptions.user_id
      AND predictions.user_id = ?)
  AND CONCAT(fixture_date, \" \", kickoff) > ?
",

/* Get a count of the predictions that a user has not made in this league
 * Used by summary::show()
 */
'league_not_yet_predicted' =>
"
SELECT COUNT(id)
  FROM fixtures
 WHERE fixtures.id NOT IN (
  SELECT DISTINCT fixture_id
  FROM predictions, user_subscriptions
  WHERE predictions.user_id = user_subscriptions.user_id
    AND predictions.user_id = ?
    AND user_subscriptions.league_id = ?)
  AND CONCAT(fixture_date, \" \", kickoff) > ?
  AND league_id = ?
",

/* Get all the predictions made between a given start and end date
 * Used by team::show()
 */
'user_predictions_bydate' =>
"
SELECT COUNT(fixtures.id) AS num_preds
  FROM fixtures, predict_teams AS t1 JOIN predict_teams AS t2, predictions
 WHERE home_team_id = t1.id
   AND away_team_id = t2.id
   AND fixture_date >= ?
   AND fixture_date <= ?
   AND predictions.user_id = ?
   AND predictions.fixture_id = fixtures.id
",

/* Get all the predictions made this season for a given league
 * Used by functions::season_predictions()
 */
'user_predictions_season' =>
"
SELECT p1.fixture_id                        AS fixture_id,
  team_name(frv1.home)                      AS home_team,
  team_name(frv1.away)                      AS away_team,
  DATE_FORMAT(frv1.kickoff, '%D %M %Y')     AS match_date,
  IFNULL(p1.home_goals, '0')                AS home_goals,
  IFNULL(p1.away_goals, '0')                AS away_goals,
  DATE_FORMAT(frv1.kickoff, '%H:%i')        AS kickoff,
  win_draw_score(frv1.home_goals,
                 frv1.away_goals,
                 p1.home_goals,
                 p1.away_goals)             AS win_score_draw
  FROM predictions AS p1
  INNER JOIN
  fixture_results_view AS frv1
    ON p1.fixture_id = frv1.fixture_id
 WHERE frv1.kickoff < ?
   AND p1.user_id = ?
   AND frv1.league_id = ?
 ORDER BY frv1.kickoff DESC, home_team
",

/* Get the number of predictions made in the last week by a user
 * Used by summary::summary_table()
 */
'predictions_this_week' =>
"
SELECT COUNT(predictions.fixture_id)
  FROM fixtures, predictions, predict_users
 WHERE predictions.fixture_id = fixtures.id
   AND fixture_date <= ?
   AND fixture_date >= DATE_SUB(?, INTERVAL 7 DAY)
   AND predictions.user_id = predict_users.id
   AND predict_users.id = ?
",

/* Get the number of predictions made in the last week by a user 
 * Used by summary::summary_table()
 */
'predictions_last_week' =>
"
SELECT COUNT(predictions.fixture_id)
  FROM fixtures, predictions, predict_users
 WHERE predictions.fixture_id = fixtures.id
   AND fixture_date < DATE_SUB(?, INTERVAL 7 DAY)
   AND fixture_date >= DATE_SUB(?, INTERVAL 14 DAY)
   AND predictions.user_id = predict_users.id
   AND predict_users.id = ?
",

/* Get the number of predictions made in the last month by a user 
 * Used by summary::summary_table()
 */
'predictions_last_month' =>
"
SELECT COUNT(predictions.fixture_id)
  FROM fixtures, predictions, predict_users
 WHERE predictions.fixture_id = fixtures.id
   AND fixture_date <= ?
   AND fixture_date >= DATE_SUB(?, INTERVAL 1 MONTH)
   AND predictions.user_id = predict_users.id
   AND predict_users.id = ?
",

/* Get the names of the teams of a fixture where the goals were correctly
 * predicted for a certain user id.
 * Used by summary::show()
 */
'correct_score_teams' =>
"
SELECT t1.name                               AS home_team,
       t2.name                               AS away_team,
       DATE_FORMAT(kickoff, '%D %M %Y')      AS match_date,
       p1.home_goals                         AS predict_home,
       p1.away_goals                         AS predict_away,
       pu1.fullname
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id,
       predict_teams AS t1 JOIN predict_teams AS t2
 WHERE frv1.away_goals = p1.away_goals
   AND frv1.home_goals = p1.home_goals
   AND home = t1.id
   AND away = t2.id
   AND pu1.id = ?
 ORDER BY DATE_FORMAT(kickoff, '%Y-%m-%d') DESC, league_id, home_team
 LIMIT 5
",

/* Get the names of the teams of a fixture where the goal difference for
 * fixtures was correctly predicted for a certain user id.
 * Used by summary::show()
 */
'correct_draws_teams' =>
"
SELECT t1.name                          AS home_team,
       t2.name                          AS away_team,
       DATE_FORMAT(kickoff, '%D %M %Y') AS match_date,
       p1.home_goals           AS predict_home,
       p1.away_goals           AS predict_away,
       frv1.home_goals  AS actual_home,
       frv1.away_goals  AS actual_away,
       pu1.fullname
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id,
       predict_teams AS t1 JOIN predict_teams AS t2,
       leagues
 WHERE frv1.away_goals = frv1.home_goals
   AND p1.away_goals = p1.home_goals
   AND frv1.away_goals <> p1.away_goals
   AND home= t1.id
   AND away= t2.id
   AND frv1.league_id = leagues.id
   AND pu1.id = ?
 ORDER BY DATE_FORMAT(kickoff, '%Y-%m-%d') DESC, league_id, home_team
 LIMIT 5
",
/* Get the fixtures in the past week where the home and away goals were
 * correctly predicted.
 * Used by team::show()
 */
'scores_bydate' =>
"
SELECT COUNT(frv1.fixture_id)*3
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.away_goals = p1.away_goals
   AND frv1.home_goals = p1.home_goals
   AND kickoff >= ?
   AND kickoff <= ?
   AND pu1.id = ?
",

/* Get the fixtures in the past week where the home and away goals were
 * correctly predicted.
 * Used by summary::summary_table()
 */
'scores_this_week' =>
"
SELECT COUNT(frv1.fixture_id)*3
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.away_goals = p1.away_goals
   AND frv1.home_goals = p1.home_goals
   AND kickoff <= frv_datesub(0)
   AND kickoff >= frv_datesub(7)
   AND pu1.id = ?
",

/* Get the fixtures last week where the home and away goals were
 * correctly predicted.
 * Used by summary::summary_table()
 */
'scores_last_week' =>
"
SELECT COUNT(frv1.fixture_id)*3
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.away_goals = p1.away_goals
   AND frv1.home_goals = p1.home_goals
   AND kickoff <= frv_datesub(7)
   AND kickoff >= frv_datesub(14)
   AND pu1.id = ?
",

/* Get the fixtures in the past week where the home and away goals were
 * correctly predicted.
 * Used by summary::summary_table()
 */
'scores_last_month' =>
"
SELECT COUNT(frv1.fixture_id)*3
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.away_goals = p1.away_goals
   AND frv1.home_goals = p1.home_goals
   AND kickoff <= frv_datesub(0)
   AND kickoff >= frv_datesub(30)
   AND pu1.id = ?
",

/* Get the number of correctly predicted results for a given user this month
 * Used by team::show()
 */
'results_bydate' => "
SELECT COUNT(frv1.kickoff)
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE
     ((frv1.home_goals < frv1.away_goals
     AND
     p1.home_goals < p1.away_goals)
     OR
     (frv1.away_goals > frv1.home_goals
     AND
     p1.away_goals > p1.home_goals)
     OR
     (frv1.away_goals = frv1.home_goals
        AND
      p1.home_goals = p1.away_goals
     )
   )
   AND (p1.away_goals <> frv1.away_goals
     OR p1.home_goals <> frv1.home_goals)
   AND kickoff >= ?
   AND kickoff <= ?
   AND pu1.id = ?
",

/* Get the number of correctly predicted results for a given user this week
 * Used by summary::summary_table()
 */
'results_this_week' => "
SELECT COUNT(frv1.kickoff)
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE
     ((frv1.away_goals < frv1.home_goals
     AND
     p1.away_goals < p1.home_goals)
     OR
     (frv1.away_goals > frv1.home_goals
     AND
     p1.away_goals > p1.home_goals)
   )
   AND (p1.home_goals <> frv1.home_goals
       OR frv1.away_goals <> p1.away_goals)
   AND kickoff <= frv_datesub(0)
   AND kickoff >= frv_datesub(7)
   AND pu1.id = ?
",

/* Get the number of correctly predicted results for a given user last week
 * Used by summary::summary_table()
 */
'results_last_week' => "
SELECT COUNT(frv1.kickoff)
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE
     ((frv1.away_goals < frv1.home_goals
     AND
     p1.away_goals < p1.home_goals)
     OR
     (frv1.away_goals > frv1.home_goals
     AND
     p1.away_goals > p1.home_goals)
   )
   AND (p1.home_goals <> frv1.home_goals
       OR frv1.away_goals <> p1.away_goals)
   AND kickoff <= frv_datesub(7)
   AND kickoff >= frv_datesub(14)
   AND pu1.id = ?
",

/* Get the number of correctly predicted results for a given user last month
 * Used by summary::summary_table()
 */
'results_last_month' => "
SELECT COUNT(frv1.kickoff)
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE
     ((frv1.away_goals < frv1.home_goals
     AND
     p1.away_goals < p1.home_goals)
     OR
     (frv1.away_goals > frv1.home_goals
     AND
     p1.away_goals > p1.home_goals)
   )
   AND (p1.home_goals <> frv1.home_goals
       OR frv1.away_goals <> p1.away_goals)
   AND kickoff <= frv_datesub(0)
   AND kickoff >= frv_datesub(30)
   AND pu1.id = ?
",

/* Get the number of matches with the correct difference between home and away
 * goals (excluding draws) in the past 7 days.
 * Used by summary::show()
 */
'correct_draw_this_week' =>
"
SELECT COUNT(frv1.fixture_id)*2
FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
WHERE frv1.home_goals = frv1.away_goals
  AND p1.home_goals   = p1.away_goals
  AND frv1.home_goals <> p1.home_goals
  AND pu1.id = ?
  AND kickoff <= frv_datesub(0)
  AND kickoff >= frv_datesub(7)
",

/* Get the number of matches with the correct difference between home and away
 * goals (excluding draws) in the past 7 days.
 * Used by summary::show()
 */
'correct_draw_last_week' =>
"
SELECT COUNT(frv1.fixture_id)*2
FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
WHERE frv1.home_goals = frv1.away_goals
  AND p1.home_goals   = p1.away_goals
  AND frv1.home_goals <> p1.home_goals
  AND pu1.id = ?
  AND kickoff <= frv_datesub(7)
  AND kickoff >= frv_datesub(14)
",

/* Get the number of matches with the correct difference between home and away
 * goals (excluding draws) in the past 7 days.
 * Used by summary::show()
 */
'correct_draw_last_month' =>
"
SELECT COUNT(frv1.fixture_id)*2
FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
WHERE frv1.home_goals = frv1.away_goals
  AND p1.home_goals   = p1.away_goals
  AND frv1.home_goals <> p1.home_goals
  AND pu1.id = ?
  AND kickoff <= frv_datesub(0)
  AND kickoff >= frv_datesub(30)
",

/* Get the teams and scores of matches where the result was correctly predicted.
 * Used by summary::show()
 */
'correct_result_teams' =>
"
SELECT DATE_FORMAT(kickoff, '%Y-%m-%d') AS date,
       t1.name                          AS home_team,
       t2.name                          AS away_team,
       DATE_FORMAT(kickoff, '%D %M %Y') AS match_date,
       p1.home_goals           AS predict_home,
       p1.away_goals           AS predict_away,
       frv1.home_goals  AS actual_home,
       frv1.away_goals  AS actual_away,
       pu1.fullname
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id,
       predict_teams AS t1 JOIN predict_teams AS t2
 WHERE
     ((frv1.away_goals < frv1.home_goals
     AND
     p1.away_goals < p1.home_goals)
     OR
     (frv1.away_goals > frv1.home_goals
     AND
     p1.away_goals > p1.home_goals)
   )
   AND (p1.home_goals <> frv1.home_goals
     OR p1.away_goals <> frv1.away_goals)
   AND home = t1.id
   AND away = t2.id
   AND pu1.id = ?
 ORDER BY DATE_FORMAT(kickoff, '%Y-%m-%d') DESC, league_id, home_team
 LIMIT 5
",

/* Get the names and goals scored by teams in a given league where the score
 * was correctly predicted by a user
 * Used by summary::show()
 */
'league_score_teams' =>
"
SELECT t1.name                          AS home_team,
       t2.name                          AS away_team,
       DATE_FORMAT(kickoff, '%D %M %Y') AS match_date,
       p1.home_goals                    AS predict_home,
       p1.away_goals                    AS predict_away
FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id,
     predict_teams AS t1 JOIN predict_teams AS t2
WHERE frv1.away_goals = p1.away_goals
  AND frv1.home_goals = p1.home_goals
  AND home            = t1.id
  AND away            = t2.id
  AND frv1.league_id  = ?
  AND pu1.id          = ?
ORDER BY kickoff DESC, frv1.fixture_id
",

/* Get the teams and scores of matches where the score difference was
 * correctly predicted.
 * Used by summary::show()
 */
'league_draws_teams' =>
"
SELECT t1.name                          AS home_team,
       t2.name                          AS away_team,
       DATE_FORMAT(kickoff, '%D %M %Y') AS match_date,
       p1.home_goals                    AS predict_home,
       p1.away_goals                    AS predict_away,
       frv1.home_goals                  AS actual_home,
       frv1.away_goals                  AS actual_away
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id,
       predict_teams AS t1 JOIN predict_teams AS t2
 WHERE frv1.away_goals = frv1.home_goals
   AND p1.away_goals = p1.home_goals
   AND frv1.home_goals <> p1.home_goals
   AND home = t1.id
   AND away = t2.id
   AND frv1.league_id = ?
   AND pu1.id = ?
 ORDER BY kickoff DESC, league_id, home
",

/* Get the names and goals scored by teams in a given league where the result
 * was correctly predicted by a user
 * Used by summary::show()
 */
'league_result_teams' =>
"
SELECT t1.name                          AS home_team,
       t2.name                          AS away_team,
       DATE_FORMAT(kickoff, '%D %M %Y') AS match_date,
       p1.home_goals                    AS predict_home,
       p1.away_goals                    AS predict_away,
       frv1.home_goals                  AS actual_home,
       frv1.away_goals                  AS actual_away
FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id,
     predict_teams AS t1 JOIN predict_teams AS t2
WHERE
    ((frv1.away_goals < frv1.home_goals
      AND
      p1.away_goals < p1.home_goals)
      OR
      (frv1.away_goals > frv1.home_goals
      AND
      p1.away_goals > p1.home_goals)
  )
  AND (p1.away_goals <> frv1.away_goals
    OR p1.home_goals <> frv1.home_goals)
  AND home = t1.id
  AND away = t2.id
  AND frv1.league_id = ?
  AND pu1.id = ?
ORDER BY kickoff DESC, frv1.fixture_id
",

/* Get the number of correctly predicted results for a given user
 * Used by summary::summary_table()
 */
'match_results' =>
"
SELECT COUNT(kickoff)
FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
WHERE
    ((frv1.away_goals < frv1.home_goals
     AND
     p1.away_goals       < p1.home_goals)
     OR
     (frv1.away_goals  > frv1.home_goals
     AND
     p1.away_goals       > p1.home_goals)
   )
  AND (p1.away_goals   <> frv1.away_goals
     OR p1.home_goals  <> frv1.home_goals)
  AND pu1.id = ?
",

/* Get the results of all matches played a team
 * Used by search::show_team_results
 */
'results_byleague_team' =>
"
SELECT fixture_results_view.fixture_id,
       DATE_FORMAT(kickoff, '%D %M %Y') AS match_date,
       p1.name AS home_team,
       home_goals,
       p2.name AS away_team,
       away_goals,
       result_type
  FROM fixture_results_view,
       predict_teams AS p1,
       predict_teams AS p2
 WHERE league_id = ?
   AND (home = ? OR away = ?)
   AND home = p1.id
   AND away = p2.id
 ORDER BY kickoff DESC
",

/* Get the number of matches with the correct difference between home and away
 * goals (excluding draws).
 * Used by summary::show(), functions::update_user_scores
 */
'summary_season_draws' =>
"
SELECT COUNT(frv1.fixture_id)*2
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.home_goals = frv1.away_goals
   AND p1.home_goals   = p1.away_goals
   AND frv1.home_goals <> p1.home_goals
   AND pu1.id = ?
",

/* Find the fixtures where the outcome (result, but not score) was correctly
 * predicted only including the subscribed-to leagues
 * Used by summary::subscribed_league_summary()
 */
'summary_league_outcomes' =>
"
SELECT COUNT(frv1.fixture_id) AS correct_results
  FROM fixture_results_view frv1
       INNER JOIN
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN 
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE
     ((frv1.away_goals < frv1.home_goals
     AND
     p1.away_goals       < p1.home_goals)
     OR
     (frv1.away_goals  > frv1.home_goals
     AND
     p1.away_goals       > p1.home_goals)
   )
   AND (p1.away_goals    <> frv1.away_goals
     OR p1.home_goals    <> frv1.home_goals)
   AND pu1.id           = ?
   AND frv1.league_id = ?
",

/* Get the number of matches with the correct difference between home and away
 * goals (excluding draws).
 * Used by summary::show()
 */
'summary_league_draws' =>
"
SELECT COUNT(frv1.fixture_id)*2
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.away_goals = frv1.home_goals
   AND p1.away_goals   = p1.home_goals
   AND frv1.home_goals <> p1.home_goals
   AND pu1.id          = ?
   AND frv1.league_id  = ?
",

/* Get the fixtures in the past week where the home and away goals were
 * correctly predicted.
 * Used by summary::subscribed_league_summary()
 */
'summary_league_scores' =>
"
SELECT COUNT(frv1.fixture_id)*3
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.away_goals  = p1.away_goals
   AND frv1.home_goals  = p1.home_goals
   AND pu1.id           = ?
   AND frv1.league_id   = ?
 ORDER BY kickoff DESC, frv1.fixture_id
",

/*
 * Home and away form guide queries. The column name is used as the text to
 * display on the page.
 * Used by functions::form_guide_row()
 */
'home_form' =>
"
SELECT home_wins+home_draws+home_losses AS Played,
       home_wins                        AS Won,
       home_draws                       AS Drawn,
       home_losses                      AS Lost,
       home_goals_for                   AS 'Goals For',
       home_goals_against               AS Against
  FROM league_table
 WHERE team_id   = ?
   AND league_id = ?
",
'away_form' =>
"
SELECT away_wins+away_draws+away_losses AS Played,
       away_wins                        AS Won,
       away_draws                       AS Drawn,
       away_losses                      AS Lost,
       away_goals_for                   AS 'Goals For',
       away_goals_against               AS Against
  FROM league_table
 WHERE team_id   = ?
   AND league_id = ?
",

/* Get the outcome of the last five matches
 * Used by: functions::get_team_form()
 */
'last_five_results' =>
"
SELECT outcome FROM
(
  SELECT kickoff AS fixture_date,
         IF(home_goals > away_goals,'W',NULL) AS outcome
  FROM fixture_results_view
  WHERE  home = ?
UNION
  SELECT kickoff, IF(home_goals < away_goals,'L',NULL) AS outcome
  FROM fixture_results_view
  WHERE  home = ?
UNION
  SELECT kickoff, IF(home_goals = away_goals,'D',NULL) AS outcome
  FROM fixture_results_view
  WHERE  home = ?
UNION
  SELECT kickoff, IF(home_goals > away_goals,'L',NULL) AS outcome
  FROM fixture_results_view
  WHERE  away = ?
UNION
  SELECT kickoff, IF(home_goals < away_goals,'W',NULL) AS outcome
  FROM fixture_results_view
  WHERE  away = ?
UNION
  SELECT kickoff, IF(home_goals = away_goals,'D',NULL) AS outcome
  FROM fixture_results_view
  WHERE  away = ?
) AS t1
WHERE t1.outcome IS NOT NULL
ORDER BY fixture_date DESC
LIMIT NUM_RESULTS
",

/* Create a league table based on the results of the fixtures in a league
 * Used by results::add_results(),functions::update_league_table()
 */
"delete_league_table" =>
'
DELETE FROM league_table WHERE league_id = ?
',

"insert_league_table" =>
"
INSERT INTO league_table
SELECT ltable.team_id,
       ?,
       SUM(hwins),
       SUM(hdraws),
       SUM(hlosses),
       SUM(hgoalsfor),
       SUM(hgoalsagainst),
       SUM(awins),
       SUM(adraws),
       SUM(alosses),
       SUM(agoalsfor),
       SUM(agoalsagainst),
       SUM(points_won)
FROM (
-- home draws
SELECT pt1.id AS team_id,
       '0' AS hwins,
       COUNT(frv1.fixture_id) AS hdraws,
       '0' AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       '0' AS awins,
       '0' AS adraws,
       '0' AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       COUNT(frv1.fixture_id) AS points_won
FROM predict_teams AS pt1
      INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.home
WHERE frv1.home_goals = frv1.away_goals
  AND result_type = 'normal'
  AND frv1.league_id = ?
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
       '0' AS hwins,
       '0' AS hdraws,
       '0' AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       '0' AS awins,
       COUNT(frv1.fixture_id) AS adraws,
       '0' AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       COUNT(frv1.fixture_id) AS points_won
FROM predict_teams AS pt1
     INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.away
WHERE frv1.away_goals = frv1.home_goals
  AND result_type = 'normal'
  AND frv1.league_id = ?
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
       COUNT(frv1.fixture_id) AS hwins,
       '0' AS hdraws,
       '0' AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       '0' AS awins,
       '0' AS adraws,
       '0' AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       COUNT(frv1.fixture_id)*3 AS points_won
FROM predict_teams AS pt1
     INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.home
WHERE frv1.home_goals > frv1.away_goals
  AND result_type = 'normal'
  AND frv1.league_id = ?
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
       '0' AS hwins,
       '0' AS hdraws,
       '0' AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       COUNT(frv1.fixture_id) AS awins,
       '0' AS adraws,
       '0' AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       COUNT(frv1.fixture_id)*3 AS points_won
FROM predict_teams AS pt1
     INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.away
WHERE frv1.home_goals < frv1.away_goals
  AND result_type = 'normal'
  AND frv1.league_id = ?
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
       '0' AS hwins,
       '0' AS hdraws,
       COUNT(frv1.fixture_id) AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       '0' AS awins,
       '0' AS adraws,
       '0' AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       '0' AS points_won
FROM predict_teams AS pt1
     INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.home
WHERE frv1.home_goals < frv1.away_goals
  AND result_type = 'normal'
  AND frv1.league_id = ?
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
       '0' AS hwins,
       '0' AS hdraws,
       '0' AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       '0' AS awins,
       '0' AS adraws,
       COUNT(frv1.fixture_id) AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       '0' AS points_won
FROM predict_teams AS pt1
     INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.away
WHERE frv1.home_goals > frv1.away_goals
  AND result_type = 'normal'
  AND frv1.league_id = ?
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
       '0' AS hwins,
       '0' AS hdraws,
       '0' AS hlosses,
       SUM(frv1.home_goals) AS hgoalsfor,
       SUM(frv1.away_goals) AS hgoalsagainst,
       '0' AS awins,
       '0' AS adraws,
       '0' AS alosses,
       '0' AS agoalsfor,
       '0' AS agoalsagainst,
       '0' AS points_won
FROM predict_teams AS pt1
     INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.home
WHERE result_type = 'normal'
  AND home IN
(SELECT team_id FROM league_teams WHERE league_id = ?)
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
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
FROM predict_teams AS pt1
     LEFT JOIN predict_team_deduct ON id = team_id
WHERE pt1.id IN
  (SELECT team_id FROM league_teams WHERE league_id = ?)
GROUP BY pt1.id
UNION
SELECT pt1.id AS team_id,
       '0' AS hwins,
       '0' AS hdraws,
       '0' AS hlosses,
       '0' AS hgoalsfor,
       '0' AS hgoalsagainst,
       '0' AS awins,
       '0' AS adraws,
       '0' AS alosses,
       SUM(frv1.away_goals) AS agoalsfor,
       SUM(frv1.home_goals) AS agoalsagainst,
       '0' AS points_won
FROM predict_teams AS pt1
     INNER JOIN fixture_results_view AS frv1 ON pt1.id = frv1.away
WHERE result_type = 'normal'
  AND away IN
(SELECT team_id FROM league_teams WHERE league_id = ?)
GROUP BY pt1.id
) AS ltable
GROUP BY team_id
",

/* Get the number of results for the league.
 * Used by functions::show_leaguetable()
 */
"league_results_count" =>
'
SELECT COUNT(team_id)
FROM league_table_view
WHERE league_id = ?
',

/* Get the teams, the nmuber of games played and points for the teams.
 * Used by functions::show_leaguetable()
 */
"show_league_table" =>
'
SELECT team_id,
       IFNULL(known_name, team_name) AS team_name,
       played,
       goal_diff,
       points
FROM league_table_view
WHERE league_id = ?
',

/* Get the position of a team in the league. This requires a view of the
 * league table that uses goal difference, for and against columns.
 * Used by functions::get_league_position(),functions::show_leaguetable()
 */
"get_league_position" =>
'
SELECT COUNT(lt1.team_id)+1 AS league_position
FROM league_table_view AS lt1 INNER JOIN
     league_table_view AS lt2 ON lt1.league_id = lt2.league_id
WHERE lt2.team_id   = ?
  AND lt1.league_id = ?
  AND (lt1.points   > lt2.points
      OR (lt1.points       = lt2.points
         AND lt1.goal_diff > lt2.goal_diff
         )
      OR ( lt1.points      = lt2.points
         AND lt1.goal_diff = lt2.goal_diff
         AND lt1.goals_for > lt2.goals_for
         )
      OR ( lt1.points      = lt2.points
         AND lt1.goal_diff = lt2.goal_diff
         AND lt1.goals_for = lt2.goals_for
         AND lt1.team_name < lt2.team_name
         )
      )
',

/* Find the playoff and relegation zone positions for a league
 * Used by functions::show_leaguetable()
 */
'playoff_relegation_zone' =>
"
SELECT playoff_start,
       IF(playoff_start, playoff_start+playoff_cnt-1, 0) AS playoff_end,
       COUNT(team_id)-num_relegated+1 AS relegate
FROM leagues, league_teams
WHERE league_teams.league_id = ?
  AND leagues.id = league_teams.league_id
GROUP BY id
",

/* Get the fixture sets for the current month
 * Used by team::show() for the flash graphs
 */
'fixture_set_this_month' =>
"
SELECT DISTINCT start_date,
       DATE_FORMAT(start_date, '%d %m') AS fs_start
FROM  fixture_set
WHERE start_date LIKE DATE_FORMAT(?, '%Y-%m-%%%%')
  AND DATE_FORMAT(start_date, '%d') > '06'
ORDER by start_date
",

/* Be a bit more generous in deciding what constitutes a month when selecting
 * fixture sets so that the first few days of the month don't return a blank
 * set.
 * Used by team::show()
 */
'fixture_set_this_month2' =>
"
SELECT DISTINCT start_date
FROM  fixture_set
WHERE start_date < ?
  AND start_date > DATE_SUB(?, INTERVAL 14 DAY)
ORDER by start_date
",

/* Get the end_date where the start_date of a league fixture_set is known
 * Used by team::show()
 */
'fixture_set_end' =>
"
SELECT end_date
FROM fixture_set
WHERE start_date = ?
",

/* Extract the next set of fixtures to submit predictions for
 * Used by next::next_form()
 */
'next_fixtures' => "
SELECT fixtures.id                       AS fixture_id,
  t1.id                                  AS home_team_id,
  t1.name                                AS home_team,
  t2.id                                  AS away_team_id,
  t2.name                                AS away_team,
  DATE_FORMAT( fixture_date, '%D %M %Y') AS match_date,
  IFNULL(predictions.home_goals, '0')    AS home,
  IFNULL(predictions.away_goals, '0')    AS away,
  DATE_FORMAT(fixtures.kickoff, '%H:%i') AS kickoff
FROM predict_teams AS t1 JOIN predict_teams AS t2, fixtures
  LEFT JOIN predictions ON predictions.fixture_id = fixtures.id
    AND predictions.user_id = ?
WHERE home_team_id = t1.id
  AND away_team_id = t2.id
  AND CONCAT(fixture_date, ' ', kickoff) > ?
  AND fixtures.league_id = ?
ORDER BY fixtures.fixture_date, kickoff, home_team
",

/* Extract the next set of fixtures to submit predictions for
 * Used by next::next_form()
 */
'next_fixtures_7day' => "
SELECT fixtures.id                       AS fixture_id,
  t1.id                                  AS home_team_id,
  t1.name                                AS home_team,
  t2.id                                  AS away_team_id,
  t2.name                                AS away_team,
  DATE_FORMAT( fixture_date, '%D %M %Y') AS match_date,
  IFNULL(predictions.home_goals, '0')    AS home,
  IFNULL(predictions.away_goals, '0')    AS away,
  DATE_FORMAT(fixtures.kickoff, '%H:%i') AS kickoff
FROM predict_teams AS t1 JOIN predict_teams AS t2, fixtures
  LEFT JOIN predictions ON predictions.fixture_id = fixtures.id
    AND predictions.user_id = ?
WHERE home_team_id = t1.id
  AND away_team_id = t2.id
  AND CONCAT(fixture_date, ' ', kickoff) >= ?
  AND fixtures.league_id = ?
  AND fixture_date <= (
        SELECT end_date
          FROM fixture_set
         WHERE end_date >= DATE_FORMAT(?, '%Y-%m-%d')
           AND league_id = ?
         LIMIT 1
      )
ORDER BY fixtures.fixture_date, kickoff, home_team
",

/* Get the total number of future fixtures for a league that have been added
 * Used by next::next_form()
 */
'new_fixtures_in_league' => "
SELECT COUNT(fixtures.id)
FROM fixtures
WHERE fixtures.league_id = ?
  AND fixtures.fixture_date > DATE_FORMAT(?, '%Y-%m-%d')
",

/* Get the score of the last time (if) this fixture was played
 * Used by next::next_form()
 */
'score_when_last_played' => "
SELECT CONCAT(home_goals, ' - ', away_goals)
  FROM season_results, season_fixtures
 WHERE season_results.fixture_id = season_fixtures.fixture_id
   AND season_results.fixture_id IN
   ( SELECT fixture_id
       FROM season_fixtures
      WHERE home_team_id = ?
        AND away_team_id = ?
    )
 ORDER BY fixture_date DESC
 LIMIT 1
",

/* Get the teams in the playoff positions for a league
 * Used by fixtures::fixture_list_form()
 * This version works with MySQL 4.x
 */
'playoff_team_select' => 
"
SELECT team_id, team_name
FROM league_table_view
WHERE league_id = ?
LIMIT START, RANGE
",

/* Get the number of correctly predicted scores
 * FIXME: need a subquery to only include thos leagues that the user
 * is currently subscribed to
 * Used by functions::update_user_scores() summary::summary_table()
 */
'scores' =>
"
SELECT COUNT(frv1.fixture_id)*3 AS score_points
  FROM fixture_results_view frv1
       INNER JOIN 
       predictions p1 ON frv1.fixture_id = p1.fixture_id
       INNER JOIN
       predict_users pu1 ON p1.user_id = pu1.id
 WHERE frv1.home_goals = p1.home_goals
   AND frv1.away_goals = p1.away_goals
   AND pu1.id = ?
 ORDER BY frv1.fixture_id
",

/* Get the number of predictions subitted by a user
 * Used by functions::update_user_scores() summary::summary_table()
 */
'num_predictions' => "
SELECT COUNT(predictions.fixture_id)
  FROM predictions, fixtures
 WHERE predictions.fixture_id = fixtures.id
   AND fixtures.id IN (
       SELECT fixture_id
         FROM fixture_results_view
   )
   AND user_id = ?
",

/* Get the number of predictions made by a user for which results have been
 * submitted. This is used for calculating the rating and avoids the skew
 * that occurs when using the num_predictions query when the user's score
 * drops significantly after the first league's esults have been posted and
 * there has been more than one league predicted.
 */
'predicted_results' =>
"
SELECT COUNT(predictions.fixture_id)
FROM predictions, fixture_results_view
WHERE fixture_results_view.fixture_id = predictions.fixture_id
  AND user_id = ?
",

/* Get the number of predictions subitted by a user
 * Used by  summary::subscribed_league_summary()
 */
'num_league_predictions' => "
SELECT COUNT(fixture_id)
FROM predictions, fixtures
WHERE predictions.fixture_id = fixtures.id
  AND fixture_id IN (
      SELECT fixture_id
         FROM fixture_results_view
  )
  AND user_id               = ?
  AND fixtures.league_id    = ?
",

/* Get the first league that a user has subscribed to
 * Used by leftnav::leftnav(), summary::show(), team::show()
 */
'default_subscription' =>
"
SELECT league_id
FROM user_subscriptions
WHERE user_id = ?
LIMIT 1
",

/* List of all leagues whether active or not.
 * Used in leagues::navtabs() results::navtabs()
 */
'league_list' =>
"
SELECT id, name
FROM leagues
",

/* Save the details of a new league. If saving for a tournament, the promoted
 * and relegated columns should all be zero.
 * Used by leagues::leagues_save()
 */
'leagues_insert' =>
"
INSERT INTO leagues (id, name, timezone, num_promoted, num_relegated,
                     promoted_to, relegated_to, active, tournament)
VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)
",

/* Used by save_options in options.php to display the name of a subscribed
 * league
 * Used by leagues::show_league_teams() leagues::league_members_save()
 *         leagues::save_new_team() leagues::save_new_team()
 *         fixtures::fixture_list_form() functions::subscribe_user_to_leagues()
 *         functions::show_leaguetable()
 */
'league_name' =>
"
SELECT league_name(?) AS name
",

/* Get whether a league is used for a tournament.
 * Used by: leagues::show_league_teams()
 */
'league_is_tournament' =>
"
SELECT tournament
FROM leagues
WHERE id = ?
",

/* Check whether a league has had fixtures submitted for it so that if it has,
 * the tournament checkbox can be removed, or an update error generated.
 * Used by leagues::show_league_teams() leagues::leagues_save()
 */
'league_has_fixtures' => 
"
SELECT COUNT(id)
FROM fixtures
WHERE league_id = ?
",

/* List of only active leagues
 * Used by functions::user_subscribed_select() functions::league_select
 * Used by leagues::show_league_teams(), seasons::archive_season()
 */
'league_select' =>
"
SELECT id, name
FROM leagues
WHERE active = '1'
  AND id < '99'
ORDER BY name
",

/* List of only active leagues
 * Used by functions::league_select{}
 */
'league_select_all' =>
"
SELECT id, name
FROM leagues
ORDER BY name
",

/* The name of a team
 * Used by fixtures::fixtures_validate() fixtures::set_fixture()
 * Used by leagues::league_members_save()
 */
'team_name' =>
"
SELECT name
FROM predict_teams
WHERE id = ?
ORDER BY name
",

/* Identify whether a team id is active
 * Used by leagues::league_members_save()
 */
'is_valid_team' =>
"
SELECT COUNT(name)
FROM predict_teams
WHERE id = ?
",

/* How many teams are in a given league.
 * Used by fixtures.php::fixture_set_form()
 */
'league_team_count' =>
"
SELECT COUNT(team_id)
FROM league_teams, leagues
WHERE league_id = ?
  AND league_teams.league_id = leagues.id
  AND leagues.active = '1'
",

/* Get the teams that are in a given league
 * Used by leagues::show_league_teams()
 */
'teams_in_league' =>
"
SELECT predict_teams.id, predict_teams.name
FROM leagues, league_teams, predict_teams
WHERE league_teams.league_id = ?
  AND league_teams.league_id = leagues.id
  AND league_teams.team_id = predict_teams.id
ORDER BY predict_teams.name
",

/* Get the teams that are not in a given league.
 * Used by leagues::show_league_teams()
 */
'teams_not_in_league' =>
"
SELECT predict_teams.id, predict_teams.name
FROM predict_teams LEFT JOIN league_teams
     ON predict_teams.id = league_teams.team_id
WHERE predict_teams.id NOT IN
  (SELECT team_id FROM league_teams WHERE league_id = ?)
ORDER BY predict_teams.name
",

/* Get the subsribed leagues for a user
 * Used by next::navtabs() summary::navtabs()
 */
'subscriptions' => "
SELECT l1.id   AS league_id,
       l1.name AS league_name
FROM user_subscriptions AS us1
     INNER JOIN
     leagues AS l1 ON us1.league_id = l1.id
WHERE us1.user_id = ?
  AND l1.active = '1'
LIMIT 4
",

/* Get whether a user is subscribed to a league or not
 * Used by next::verify_next()
 */
'is_subscribed' =>
"
SELECT COUNT(fixtures.id) AS is_subscribed
FROM user_subscriptions, fixtures
WHERE fixtures.id = ?
  AND user_subscriptions.league_id = fixtures.league_id
  AND user_subscriptions.user_id = ?
  AND user_subscriptions.league_id = ?
",

/*
 * Verify that a requested fixture set exists.
 * Used by fixtures::is_valid_fixture_set
 */
'valid_fixture_set' =>
"
SELECT COUNT(set_id)
FROM fixture_set
WHERE set_id = ?
",

/* Find potentially overlapping fixture sets
 * Used by fixtures::
 */
'overlapping_fsets' => "
SELECT COUNT(set_id)
FROM fixture_set
 WHERE ((start_date <= ?  AND end_date >= ?)
        OR
       (start_date <= ?  AND end_date >= ?)
        OR
       (start_date <= ?  AND end_date >= ?)
        OR
       (start_date >= ?  AND end_date <= ?))
  AND league_id = ?
  AND set_id <> ?
",

/* Remove a dangling fixture set
 * Used by fixtures::del_fixture_set()
 */
'del_fixture_set' =>
"
DELETE FROM fixture_set
WHERE set_id = ?
",

/* Get the names of the home and away teams
 * Used by next::add_predictions, results::add_results
 */
'fixture_home_away' =>
"
SELECT t1.name AS home_team,
       t2.name AS away_team
FROM predict_teams AS t1, fixtures JOIN predict_teams AS t2
WHERE fixtures.id = ?
  AND fixtures.home_team_id = t1.id
  AND fixtures.away_team_id = t2.id
",

/*
 * Get the teams and match details for a given fixture set
 * Used by fixtures::fixture_set_form()
 */
'fixture_set_match_list' =>
"
SELECT DISTINCT fixtures.id AS fid,
       t1.id                AS home_team,
       t2.id                AS away_team,
       fixture_date         AS match_date,
       kickoff
FROM fixtures,
     predict_teams AS t1 JOIN predict_teams AS t2
WHERE home_team_id = t1.id
  AND away_team_id = t2.id
  AND fixture_date >= ? 
  AND fixture_date <= ?
  AND fixtures.league_id = ?
ORDER BY fixture_date, kickoff, t1.name ASC
",

/* Get the id of fixture sets that have matching fixtures
 * Used by fixtures::set_fixture()
 */
'get_orphaned_fsets' =>
"
SELECT set_id AS orphaned_fset
  FROM fixture_set
 WHERE league_id = ?
   AND set_id NOT IN 
(SELECT set_id FROM fixtures LEFT JOIN fixture_set
   ON fixture_set.league_id = fixtures.league_id
WHERE fixture_date >= start_date AND fixture_date <= end_date
  AND fixture_set.league_id = fixtures.league_id
  AND fixtures.league_id = ?
)
",

/* Find the fixtures whose date does not fall between the start and end
 * date of a fixture_set
 * Used by fixtures::set_fixture()
 */
'get_orphaned_fixtures' =>
"
SELECT id
FROM fixtures
WHERE league_id = ?
  AND id NOT IN
(SELECT id
FROM fixtures, fixture_set
WHERE fixtures.league_id = ?
  AND fixtures.league_id = fixture_set.league_id
  AND fixtures.fixture_date >= fixture_set.start_date
  AND fixtures.fixture_date <= fixture_set.end_date
)
",

/* Determine whether a fixture to be predicted has kicked off yet
 * Used by next::verify_next
 */
'check_ko' =>
"
SELECT COUNT(kickoff) AS valid_ko
FROM fixtures
WHERE id = ?
  AND TIMESTAMP(CONCAT(fixture_date, ' ', fixtures.kickoff)) > TIMESTAMP(?)
",

/* Remove any existing prediction for the fixture
 * Used by next::add_predictions()
 */
'del_prediction' =>
"
DELETE FROM predictions
WHERE fixture_id = ?
AND user_id = ?
",

/* Add a new prediction to the database
 * Used by next::add_predictions
 */
'add_prediction' =>
"
INSERT INTO predictions VALUES(?, ?, ?, ?)
",

/* List all the user teams
 * Used by functions::user_team_select()
 */
'user_team_select' =>
"
SELECT id, name
FROM user_leagues
WHERE active = '1'
",

/* List all the user teams including any marked inactive
 * Used by userteams::userteams_display()
 */
'user_team_list' =>
"
SELECT id,
       name,
       DATE_FORMAT(created, '%D %M %Y %H:%i:%s') AS created,
       active
FROM user_leagues
",

/* Verify that a user team name is unique
 * used by userteams::userteams_validate
 */
'userteams_unique_uname' =>
"
SELECT COUNT(id)
FROM user_leagues
WHERE name = ?
",

/* List the details of a specific userteam
 * Used by userteams::userteams_form
 */
'userteam_details' =>
"
SELECT id, name, active
FROM user_leagues
WHERE id = ?
",

/* Get the teams that a user belongs to
 * Used by functions::user_team_select()
 */
'user_teamlist' =>
"
SELECT user_leagues.name AS user_league_name,
       user_leagues.id   AS user_league_id
FROM predict_users, user_leagues, user_league_members
WHERE user_league_members.user_id = predict_users.id
  AND user_league_members.user_league_id = user_leagues.id
  AND predict_users.id = ?
",

/* Get the teams that a user belongs to
 * Used by  team::navtabs()
 */
'user_teamlist_active' =>
"
SELECT user_leagues.name AS user_league_name,
       user_leagues.id   AS user_league_id
FROM predict_users, user_leagues, user_league_members
WHERE user_league_members.user_id = predict_users.id
  AND user_league_members.user_league_id = user_leagues.id
  AND predict_users.id = ?
  AND user_leagues.active = '1'
",

/* Verify that a requested user id is in the same team group as the logged-in
 * user.
 * Used by functions::season_predictions
 */
'is_user_in_same_team' =>
"
SELECT COUNT(user_league_id)
FROM user_league_members
WHERE user_id = ?
  AND user_league_id IN
(SELECT user_league_id
 FROM user_league_members
 WHERE user_id = ?)
",

/* Get a list of users that are also un the same userteams as the logged-in
 * user
 * Used by functions::display_all_userteam_users()
 */
'logged_in_user_team_members' =>
"
SELECT DISTINCT pu.id, pu.uname
FROM   predict_users pu
       INNER JOIN user_league_members ulm
       ON pu.id = ulm.user_id 
   AND ulm.user_league_id IN
(SELECT user_league_id
 FROM user_league_members 
 WHERE user_league_members.user_id = ?)
",

/* Get the number of entries for the requested userteam id
 * Used by functions::is_valid_userteam
 */
'is_valid_userteam' =>
"
SELECT COUNT(name)
FROM user_leagues
WHERE id = ?
  AND active = '1'
",

/* Get the first userteam for a user
 * Used by team::show()
 */
'default_userteam' =>
"
SELECT user_league_id
FROM user_league_members
WHERE user_id = ?
LIMIT 1
",

/* Find the users that belong to a given userteam
 * Used by functions::get_valid_users() functions::userteam_users_select
 * Used by team::show()
 */
'userteam_users' =>
"
SELECT predict_users.uname AS user_name,
       predict_users.id    AS user_id
FROM predict_users, user_leagues, user_league_members
WHERE predict_users.id  = user_league_members.user_id
  AND user_league_members.user_league_id = user_leagues.id
  AND user_leagues.id = ?
",

/* List of subscribed leagues suitable for use in HTML_QuickForm
 * Used by fucntions::user_subscribed_select()
 */
'league_dropdown' => "
SELECT name AS league_name,
       id   AS league_id
FROM leagues, user_subscriptions
WHERE leagues.id = user_subscriptions.league_id
  AND user_subscriptions.user_id = ?
  AND leagues.active = '1'
LIMIT 4
",

/* Queries used by the saveoptions function in include/options.php to process
 * and generate the options forms.
 * Check to see if a requested league exists
 * Used by functions::is_valid_league()
 */
'is_valid_league' =>
"
SELECT COUNT(id)
FROM leagues
WHERE id = ?
  AND active = '1'
",

/* Check whether a league name already exists. Because we need to use extra
 * characters as part of the WHERE clause, placeholders can't be used with
 * actual value being substituted by the application.
 * Used by leagues::leagues_insert()
 */
'league_name_exist' =>
"
SELECT COUNT(id)
FROM leagues
WHERE name REGEXP '^LEAGUE_NAME'
",

/* Simple query to retrieve the next id for a new league
 * Used by leagues::leagues_insert()
 */
'leagues_nextid' =>
"
SELECT MAX(id)+1 FROM leagues
",
/* Simple query to retrieve the next id for a new league
 * Used by leagues::leagues_insert()
 */
'predict_teams_nextid' =>
"
SELECT MAX(id)+1 FROM predict_teams
",

/* Remove existing user subscriptions
 * Used by functions::subscribe_user_to_leagues()
 */
'subs_delete' =>
"
DELETE FROM user_subscriptions
WHERE user_id = ?
",

/* Save user subscriptions
 * Used by functions::subscribe_user_to_leagues()
 */
'subs_insert' =>
"
INSERT INTO user_subscriptions VALUES(?, ?)
",

/* Select the name of a userteam
 * Used by functions::subscribe_user_to_teams
 */
'userteam_name' =>
"
SELECT name
FROM user_leagues
WHERE id = ?
",

/* Delete any existing userteam entries (prior to re-insertion, perhaps)
 * Used by functions::subscribe_user_to_teams
 */
'uteams_delete' =>
"
DELETE FROM user_league_members
WHERE user_id = ?
",

/* Delete any existing userteam entries (prior to re-insertion, perhaps)
 * Used by functions::userteam_members
 */
'uteams_delete_byteam' =>
"
DELETE FROM user_league_members
WHERE user_league_id = ?
",

/* Insert user team membership records
 * used by functions::subscribe_user_to_teams
 */
'uteam_insert' =>
"
INSERT INTO user_league_members VALUES(?, ?)
",

/* Save the udpdated password
 * Used by options::save_password()
 */
'save_password' =>
"
UPDATE predict_users
SET password = ?
WHERE id = ?
",

/* Save the udpdated password
 * Used by options::save_options()
 */
'save_user_timezone' =>
"
UPDATE predict_users
SET timezone = ?
WHERE id = ?
",

/* Get the details of a fixture set
 * Used by fixtures::fixture_set_form()
 */
'fixtureset_details' =>
"
SELECT DATE_FORMAT(start_date, '%d')       AS start_day,
       DATE_FORMAT(start_date, '%m')       AS start_month,
       DATE_FORMAT(start_date, '%Y')       AS start_year,
       DATE_FORMAT(end_date,   '%d')       AS end_day,
       DATE_FORMAT(end_date,   '%m')       AS end_month,
       DATE_FORMAT(end_date,   '%Y')       AS end_year,
       DATE_FORMAT(start_date, '%d %b %Y') AS full_date,
       league_id,
       num_fixtures
FROM fixture_set
WHERE set_id = ?
",

/* If the fixture settarts on friday and ends at least a day later, use the
 * saturday as the default choice on the fixture form. Requires a user
 * function default_match_date to determine whether a saturday should be the
 * defult for the fixture set.
 * Used by fixtures::fixture_set_form()
 */
'default_match_date' =>
"
SELECT default_match_date(?) AS default_match_date
",

/* Determine the number of fixtures a team in a league should play in a
 * season: (teams_in_league - 1) * 2
 * Used by fixtures::fixture_list_form()
 */
'all_matches_played' =>
"
SELECT FLOOR(SUM(league_matches_played) /
            (SUM(teams_in_league) * (SUM(teams_in_league)-1)))
  FROM (
    SELECT count(fixture_id) AS league_matches_played, 0 AS teams_in_league
      FROM fixture_results_view
     WHERE league_id = ?
  UNION
    SELECT 0 AS league_matches_played, COUNT(team_id) AS teams_in_league
      FROM league_teams
     WHERE league_id = ?
  ) AS t1
",

/*
 * Get a list of fixture sets
 * Used by: fixtures.php::list_fixture_sets()
 */
'fixture_set_list' =>
"
SELECT set_id,
       leagues.name                          AS league_name,
       DATE_FORMAT(start_date, \"%Y\")       AS year,
       DATE_FORMAT(start_date, \"%M\")       AS month,
       DATE_FORMAT(start_date, \"%d %M %Y\") AS start,
       DATE_FORMAT(end_date, \"%d %M %Y\")   AS end,
       num_fixtures
FROM fixture_set
     INNER JOIN
     leagues ON leagues.id = fixture_set.league_id
ORDER BY start_date DESC
",

/*
 * Verify that a date exists with a current season
 * Used by fixtures::verify_fixture_set_dates()
 */
'fixture_set_season' =>
"
SELECT count(season_id)
FROM seasons
WHERE ? >= season_start
  AND ? <= season_end
",

/* Add a new fixture set record.
 * Used by fixtures::fixture_set_form()
 */
'insert_fixture_set' =>
"
INSERT INTO fixture_set
VALUES(?, ?, ?, ?, ?)
",

/* Update an existing fixture set record.
 * Used by fixtures::fixture_set_form()
 */
'update_fixture_set' =>
"
UPDATE fixture_set
SET start_date = ?, end_date = ?, league_id = ?, num_fixtures = ?
WHERE set_id = ?
",

/* The list of teams in a given league.
 * Used by functions::league_team_select()
 */
'league_team_list' =>
"
SELECT id, name
FROM predict_teams, league_teams
WHERE predict_teams.id = league_teams.team_id
  AND league_teams.league_id = ?
ORDER BY name
",

/* Get the  number of teams in the playoff places in a league.
 * Used by fixtures::fixture_list_form
 */
'get_playoff_count' =>
"
SELECT playoff_cnt
  FROM leagues
 WHERE id = ?
",

/* Get the start position and number of teams in the playoff places in a
 * league.
 * Used by functions::playoff_team_select(), leagues::show_league_teams()
 */
'get_playoff_range' =>
"
SELECT playoff_start, playoff_cnt
  FROM leagues
 WHERE id = ?
",

/* Update the playoff team position and count
 * Used by legaues::update_playoff_places()
 */
'update_playoff_places' =>
"
UPDATE leagues
SET playoff_start = ?, playoff_cnt = ?
WHERE id = ?
",

/* Get the number of points deducted from a team in a league
 * Used by leagues::leagues::show_league_teams(), functions::show_leaguetable()
 */
'get_points_deducted' =>
"
SELECT IFNULL(deduction, '0') AS deduction
FROM predict_team_deduct
WHERE team_id   = ?
  AND league_id = ?
",

/* Get the number of points deducted for each team in a league
 * Used by leagues::show_league_teams()
 */
'get_all_points_deducted' =>
"
SELECT predict_teams.id, ABS(IFNULL(deduction, '0')) AS deducted
FROM predict_teams LEFT JOIN  predict_team_deduct
  ON id = team_id
WHERE predict_teams.id IN
  (SELECT team_id
     FROM league_teams
    WHERE league_id = ?
  )
",

/* Get the teams in a league that have not had any points deducted
 * Used by leagues::show_league_teams()
 */
'no_points_deducted' =>
"
SELECT predict_teams.id, predict_teams.name
FROM predict_teams LEFT JOIN predict_team_deduct
  ON id = team_id
WHERE predict_team_deduct.deduction IS NULL
  AND predict_teams.id IN
  (SELECT team_id
     FROM league_teams
    WHERE league_id = ?
  )
ORDER BY predict_teams.name
",

/* Remove the points deduction for a team
 * Used by leagues::update_deductions()
 */
'delete_deduction' =>
"
DELETE FROM predict_team_deduct
WHERE team_id = ?
  AND league_id = ?
",

/* Insert a record for a points deduction for a team
 * Used by leagues::update_deductions()
 */
'insert_deduction' =>
"
INSERT INTO predict_team_deduct(team_id, league_id, deduction)
VALUES(?, ?, ?)
",

/*
 * FInd whether a team is in the requested league or not.
 * Used by functions::is_team_in_league()
 */
'is_team_in_league' =>
"
SELECT name
FROM league_teams, predict_teams
WHERE league_teams.league_id = ?
  AND league_teams.team_id = predict_teams.id
  AND predict_teams.id = ?
",

/* Get the default match type for a league */
'get_default_match_type' =>
"
SELECT default_match_type
  FROM leagues
 WHERE id = ?
",

/* Get the match_type of a particular fixture
 * Used by results::results_form(), results::add_results()
 */
'get_fixture_match_type' =>
"
SELECT match_type
  FROM fixtures
 WHERE id = ?
",

/* Get the match_type of fixtures ina given fixture set
 * Used by fixtures::fixture_set_details()
 */
'get_fixture_set_match_type' =>
"
SELECT DISTINCT match_type
FROM fixtures, fixture_set
WHERE fixtures.league_id = fixture_set.league_id
  AND fixtures.fixture_date >= fixture_set.start_date
  AND fixtures.fixture_date <= fixture_set.end_date
  AND fixture_set.set_id = ?
LIMIT 1
",

/* Get the number of fixtures and the league name for a given set
 * Used by: fixtures::fixture_set_details()
 */
'fixtures_by_setid' => "
SELECT fs1.num_fixtures,
       fs1.league_id                     AS league_id,
       league_name(fs1.league_id)        AS league_name,
       DATE_FORMAT(fs1.start_date, '%d') AS start_day,
       DATE_FORMAT(fs1.start_date, '%m') AS start_month,
       DATE_FORMAT(fs1.start_date, '%Y') AS start_year,
       DATE_FORMAT(fs1.end_date,   '%d') AS end_day,
       DATE_FORMAT(fs1.end_date,   '%m') AS end_month,
       DATE_FORMAT(fs1.end_date,   '%Y') AS end_year
FROM fixtures
  LEFT JOIN fixture_set AS fs1 ON fixtures.fixture_date >= fs1.start_date
WHERE fs1.league_id = fixtures.league_id
  AND fixtures.fixture_date >= fs1.start_date
  AND fixtures.fixture_date <= fs1.end_date
  AND fs1.set_id = ?
GROUP BY fs1.set_id
",

/* Update the number of fixtures in a set. Used when a match type 0f playoff
 * is selected for a league.
 * Used by fixtures::fixture_list_form()
 */
'update_fset_num_fixtures' =>
"
UPDATE fixture_set
   SET num_fixtures = ?
 WHERE set_id = ?
",

/* Just select the basic details from a fixture set with no fixtures
 * Used by fixtures::fixture_set_details()
 */
'empty_fixture_set' =>
"
SELECT fixture_set.num_fixtures,
       fixture_set.league_id                     AS league_id,
       fixture_set.num_fixtures                  AS num_fixtures,
       DATE_FORMAT(fixture_set.start_date, '%d') AS start_day,
       DATE_FORMAT(fixture_set.start_date, '%m') AS start_month,
       DATE_FORMAT(fixture_set.start_date, '%Y') AS start_year,
       DATE_FORMAT(fixture_set.end_date,   '%d') AS end_day,
       DATE_FORMAT(fixture_set.end_date,   '%m') AS end_month,
       DATE_FORMAT(fixture_set.end_date,   '%Y') AS end_year
FROM fixture_set
WHERE fixture_set.set_id = ?
",

/* Check whether a fixture exists or not. This is used to decide whether to use
 * the insert or update query for the fixture.
 * Used by fixtures::set_fixture(), fixtures::fixture_list_form()
 */
'does_fixture_exist' =>
"
SELECT COUNT(fixture_date)
FROM fixtures
WHERE id = ?
",

/* Insert a new fixture
 * Used by fixtures::set_fixture()
 */
'add_new_fixture' =>
"
INSERT INTO fixtures
VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)
",

/* Update a existing fixture
 * Used by fixtures::set_fixture()
 */
'update_fixture' =>
"
UPDATE fixtures
SET fixture_date = ?,
    kickoff      = ?,
    match_type   = ?,
    home_team_id = ?,
    away_team_id = ?,
    last_updated = ?
WHERE id = ?
",

/* Delete a fixture
 * Used by fixtures::set_fixture(), fixtures::delete_fixture()
 */
'delete_fixture' =>
"
DELETE FROM fixtures WHERE id = ?
",

/* Delete predictions matching a given fixture id
 * Used by fixtures::delete_fixture()
 */
'delete_predictions_by_fixture' =>
"
DELETE FROM predictions WHERE fixture_id = ?
",

/* Delete any submitted results for a given fxiture id
 * Used by fixtures::delete_fixture()
 */
'delete_results_by_fixture' =>
"
DELETE FROM fixture_results WHERE fixture_id = ?
",

/* Get the league id used for a specified fixture set
 * Used by fixtures::del_fixture_set()
 */
'fixture_set_league_id' =>
"
SELECT league_id
  FROM fixture_set
 WHERE set_id = ?
",

/* Delete the fixtures for a given a fixture_set. Needed when removing a
 * fixture_set.
 * Used by fixtures::del_fixture_set()
 */
'fixture_set_fixtures_delete' =>
"
DELETE FROM fixtures
USING fixture_set, fixtures
WHERE fixtures.league_id = fixture_set.league_id
  AND fixture_set.start_date > ?
  AND fixtures.fixture_date >= fixture_set.start_date
  AND fixtures.fixture_date <= fixture_set.end_date
  AND fixture_set.set_id = ?
",

/* Verify that a requested fixture is bounded by a fixture set
 * Used by fixtures::del_fixture()
 */
'fixture_bounded_by_set' =>
"
SELECT kickoff
  FROM fixtures, fixture_set
 WHERE fixtures.league_id = fixture_set.league_id
   AND fixture_date >= start_date
   AND fixture_date <= end_date
   AND fixtures.id = ?
   AND fixture_set.set_id = ?
",
/* Identify the submitted fixtures that have not had the results posted yet
 * Used by results::show() with setid passed to results::
 */
'results_fixture_sets' => "
SELECT fs1.set_id AS set_id,
       fs1.num_fixtures,
       league_name(fs1.league_id) AS league_name,
       DATE_FORMAT(MIN(CONCAT(f1.fixture_date, ' ', kickoff)), '%D %M %Y') AS first_date,
       MIN(CONCAT(f1.fixture_date, ' ', kickoff)) AS earliest,
       MAX(CONCAT(f1.fixture_date, ' ', kickoff)) AS latest
FROM fixtures f1 INNER JOIN fixture_set fs1 ON f1.league_id = fs1.league_id
WHERE f1.fixture_date >= fs1.start_date
  AND f1.fixture_date <= fs1.end_date
  AND fs1.league_id = ?
  AND DATE_ADD(?, INTERVAL 2 HOUR) > CONCAT(f1.fixture_date, ' ', kickoff)
  GROUP BY fs1.set_id
  ORDER BY earliest DESC
",

/* Return the number of fixtures with a certain id that kicked off at least more
 * than 90 minutes ago. This is to prevent results being submitted for matches
 * before they have been played.
 * Used by results::add_results()
 */
'check_end_of_fixture' =>
"
SELECT COUNT(kickoff) AS valid_ko
FROM fixtures
WHERE fixtures.id = ?
  AND DATE_ADD(CONCAT(fixture_date, ' ', kickoff), INTERVAL 110 MINUTE) < ?
",

/* Update the details of a particular league.
 * Used by: fixtures::leagues_update()
 */
'save_league_details' =>
"
UPDATE leagues SET name               = ?,
                   timezone           = ?,
                   num_promoted       = ?,
                   num_relegated      = ?,
                   promoted_to        = ?,
                   relegated_to       = ?,
                   default_match_type = ?,
                   active             = ?,
                   tournament         = ?
WHERE id = ?
",

/* Identify the league into which teams will be promoted and the number of
 * teams that make the transition
 * Used by leagues::show_league_teams() leagues::leagues_promote_relegate()
 */
'league_promoted' =>
"
SELECT l1.name                      AS league_name,
       l2.name                      AS up_name,
       l1.active                    AS active,
       IFNULL(l2.id, '0')           AS leagueid,
       l2.name                      AS up_to,
       IFNULL(l1.num_promoted, '0') AS num_promoted
FROM leagues AS l1 LEFT JOIN leagues AS l2
  ON l1.promoted_to = l2.id
WHERE l1.id = ?
",

/* Identify the league into which teams will be relegated and the number of
 * teams that make the transition
 * Used by leagues::show_league_teams() leagues::leagues_promote_relegate()
 */
'league_relegated' =>
"
SELECT l1.name                       AS league_name,
       l2.name                       AS down_name,
       l1.active                     AS active,
       IFNULL(l2.id, '0')            AS leagueid,
       l2.name                       AS down_to,
       IFNULL(l1.num_relegated, '0') AS num_relegated
FROM leagues AS l1 LEFT JOIN leagues AS l2
  ON l1.relegated_to = l2.id
WHERE l1.id = ?
",

/* remove a relegated or promoted team from the current league
 * Used by leagues::leagues_promote_relegate() leagues::save_new_team()
 */
'remove_team_from_league' =>
"
DELETE FROM league_teams
WHERE league_id = ?
  AND team_id = ?
",

/* Remove all teams from a league
 * Used by leagues::league_members_save()
 */
'remove_all_teams_from_league' =>
"
DELETE FROM league_teams
WHERE league_id = ?
",

/* Add a team to a league
 * Used by leagues::league_members_save() leagues::save_new_team()
 * Used by leagues::leagues_promote_relegate() leagues::save_new_league()
 */
'add_team_to_league' =>
"
INSERT INTO league_teams(league_id, team_id)
VALUES(?, ?)
",

/* Idenitfy the number of submitted fixture results for a league. This is so
 * we can determine whether to disable the display of the promotion, playoff
 * and relegeation zones.
 * Used by functions::show_leaguetable()
 */
'num_league_results' =>
"
SELECT COUNT(fixture_id) AS num_results
  FROM fixture_results_view
 WHERE league_id = ?
",

/* Get the next set of fixtures to submit results for based on the start date
 * of the fixture set.
 * Don't include fixtures that occur in the future and add 90 minutes to the
 * kick-off of today matches.
 * Used by results::results_form()
 */
'results_fixtures' =>
"
SELECT fixtures.id AS fixture_id,
  t1.name                                         AS home_team,
  t2.name                                         AS away_team,
  DATE_FORMAT( fixture_date, '%D %M %Y')          AS match_date,
  IFNULL(fixture_results.home_goals, '0')         AS home,
  IFNULL(fixture_results.away_goals, '0')         AS away,
  IFNULL(fixture_results.result_type, match_type) AS result_type
FROM fixture_set, predict_teams AS t1 JOIN predict_teams AS t2, fixtures
  LEFT JOIN fixture_results ON fixture_results.fixture_id = fixtures.id
WHERE home_team_id = t1.id AND away_team_id = t2.id
  AND fixtures.league_id = ?
  AND fixture_set.set_id = ?
  AND fixture_date >= fixture_set.start_date
  AND fixture_date <= fixture_set.end_date
  AND DATE_ADD(CONCAT(fixture_date, ' ', kickoff), INTERVAL 110 MINUTE) <= ?
ORDER BY CONCAT(fixture_date, ' ', kickoff), home_team
",

/* Retrieve the difference between the number of fixtures in a league between
 * given dates and the number of results submitted. If all results for
 * submitted fixtures have been posted, the result will be zero, otherwise it
 * will be the number of fixtures for which no result has been
 * submitted. This is used to highlight fixture sets that still have results
 * to be submitted and replaces 'already_resulted'.
 * Used by next::show_fixture_sets()
 */
'not_submitted' => "
SELECT fs1.set_id AS set_id,
       fs1.num_fixtures AS num_fixtures
FROM fixtures f1 INNER JOIN fixture_set fs1 ON f1.league_id = fs1.league_id
WHERE f1.fixture_date >= fs1.start_date
   AND f1.fixture_date <= fs1.end_date
   AND f1.id NOT IN (SELECT fixture_id FROM fixture_results)
GROUP BY fs1.set_id
",

/* Remove any existing result for the fixture
 * Used by next::add_predictions()
 */
'del_result' =>
"
DELETE FROM fixture_results
WHERE fixture_id = ?
",

/* Add a new result to the database
 * fixture_id, result_type, home_goals, away_goals
 * Used by next::add_predictions()
 */
'add_result' =>
"
INSERT INTO fixture_results VALUES(?, ?, ?, ?)
",

/* Add a new team
 * Used by leagues::save_new_team()
 */
'insert_new_team' =>
"
INSERT INTO predict_teams(id, name, known_name) VALUES(?, ?, ?)
",

/* Get the new season id
 * Used by seasons::seasons_insert
 */
'new_season_id' =>
"
SELECT MAX(season_id)+1 FROM seasons
",

/* Add a new season
 * Used by seasons::seasons_insert()
 */
'seasons_insert' =>
"
INSERT INTO seasons(season_id, season_name, season_start, season_end)
VALUES(?, ?, ?, ?)
",

/*
 * Season data archive section
 */

/* Copy the season's predictions into the history table
 * Used by seasons::archive_season()
 */
'season_predictions_copy' =>
"
INSERT INTO season_predictions
  SELECT season_id, user_id, fixture_id, home_goals, away_goals
  FROM predictions, fixtures, seasons
  WHERE predictions.fixture_id = fixtures.id
    AND fixtures.fixture_date >= seasons.season_start
    AND fixtures.fixture_date <= seasons.season_end
    AND season_id = ?
",

/* Remove the existing predictions for the last season
 * Used by seasons::archive_season()
 */
'season_predictions_delete' =>
"
DELETE FROM predictions
 USING fixtures, predictions, seasons
 WHERE fixture_date >= season_start 
   AND fixture_date <= season_end
   AND fixtures.id = predictions.fixture_id
   AND season_id = ?
",

/* Promote and relegate teams in the active leagues
 * Used by seasons::archive_season()
 */
'league_promote_relegate' =>
"
CALL promote_relegate()
",

/* Copy the season's results into the history table
 * Used by seasons::archive_season()
 */
'season_results_copy' =>
"
INSERT INTO season_results
  SELECT season_id, fixture_id, result_type, home_goals, away_goals
  FROM fixture_results_view, seasons
  WHERE kickoff >= seasons.season_start
    AND kickoff <= seasons.season_end
    AND season_id = ?
",

/* Remove the existing results for the last season
 * Used by seasons::archive_season()
 */
'season_results_delete' =>
"
DELETE FROM fixture_results
 USING fixture_results, fixtures, seasons
 WHERE fixture_results.fixture_id = fixtures.id
   AND fixture_date >= season_start 
   AND fixture_date <= season_end
   AND season_id = ?
",

/* Copy the season's results into the history table
 * Used by seasons::archive_season()
 */
'season_fixtures_copy' =>
"
INSERT INTO season_fixtures
  SELECT season_id,
         id,
         league_id,
         fixture_date,
         kickoff,
         home_team_id,
         away_team_id,
         match_type
  FROM fixtures, seasons
  WHERE fixtures.fixture_date >= seasons.season_start
    AND fixtures.fixture_date <= seasons.season_end
    AND season_id = ?
",

/* Remove the existing results for the last season
 * Used by seasons::archive_season()
 */
'season_fixtures_delete' =>
"
DELETE FROM fixtures
USING fixtures, seasons
WHERE fixture_date >= season_start 
  AND fixture_date <= season_end
  AND season_id = ?
",

/* Copy the season's fixture sets into the history table
 * Used by seasons::archive_season()
 */
'season_fixture_set_copy' =>
"
INSERT INTO season_fixture_sets
  SELECT season_id, set_id, start_date, end_date, league_id
  FROM seasons, fixture_set
  WHERE start_date >= season_start
    AND end_date <= season_end
    AND season_id = ?
",

/* Remove the existing fixture sets for the last season
 * Used by seasons::archive_season()
 */
'season_fixture_set_delete' =>
"
DELETE FROM fixture_set
USING fixture_set, seasons
WHERE start_date >= season_start 
  AND end_date <= season_end
  AND season_id = ?
",

/* Copy the season's points deductions into the history table
 * Used by seasons::archive_season()
 */
'season_deductions_copy' =>
"
INSERT INTO season_deductions
  SELECT season_id, league_id, team_id, deduction
  FROM predict_team_deduct, seasons
  WHERE season_id = ?
",

/* Remove all existing points deduction records. The WHERE clause is so
 * the query will work when passed a parameter by the script.
 * Used by seasons::archive_season()
 */
'season_deductions_delete' =>
"
DELETE FROM predict_team_deduct WHERE ?
",

/* Archive the predict_user_scores table
 * Used by seasons::archive_season()
 */
'season_user_scores_copy' =>
"
INSERT INTO season_predict_user_scores
  SELECT season_id,
         user_id,
         num_predictions,
         correct_results,
         correct_diffs,
         correct_scores,
         points,
         last_updated
  FROM predict_user_scores, seasons
  WHERE season_id = ?
",

/* Remove the predict_user_scores table to prevent the top 5 list showing
 * bogus information when the new season starts. The WHERE clause is so
 * the query will work when passed a parameter by the script.
 * Used by seasons::archive_season()
 */
'season_user_scores_del' =>
"
DELETE FROM predict_user_scores WHERE ?
",

/* Get the seasons for display in the section navtabs
 * Used by seasons::navtabs()
 */
'season_details' =>
"
SELECT season_name,
       season_end,
       DATE_FORMAT(season_start, \"%d\") AS start_day,
       DATE_FORMAT(season_start, \"%m\") AS start_month,
       DATE_FORMAT(season_start, \"%Y\") AS start_year,
       DATE_FORMAT(season_end, \"%d\")   AS end_day,
       DATE_FORMAT(season_end, \"%m\")   AS end_month,
       DATE_FORMAT(season_end, \"%Y\")   AS end_year
FROM seasons
WHERE season_id = ?
LIMIT 1
",

/* Select default values for a season when adding a new season
 * Used by seasons::add_new_season_form()
 */
'new_season_details' =>
"
SELECT 'New season'                                        AS season_name,
       '01'                                                AS start_day,
       '08'                                                AS start_month,
       DATE_FORMAT(NOW(), '%Y')                            AS start_year,
       '31'                                                AS end_day,
       '05'                                                AS end_month,
       DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 YEAR), '%Y') AS end_year
",

/* Get the start date of a season
 * Used by seasons::seasons_update()
 */
'get_season_start' =>
"
SELECT season_start
FROM seasons
WHERE season_id = ?
",

/* Get the end date of a season
 * Used by seasons::can_season_be_archived() seasons::seasons_update()
 */
'get_season_end' =>
"
SELECT season_end
FROM seasons
WHERE season_id = ?
",

/* Get the deails for a specific season
 * Used by seasons::season_details_form()
 */
'season_list' =>
"
SELECT season_id, season_name, season_start, season_end
FROM seasons
ORDER BY season_start DESC
",

/* Find out many fixtures, predictions and results are in a given season
 * Used by seasons::season_details_form(), seasons::can_season_be_archived()
 */
'fixtures_in_season' =>
"
SELECT COUNT(fixtures.id) AS season_fixtures
FROM fixtures, seasons
WHERE fixture_date >= season_start
  AND fixture_date <= season_end
  AND season_end < ?
  AND season_id  = ?
",

/* Update an existing season with new details
 * Used by seasons::seasons_update()
 */
'seasons_update' =>
"
UPDATE seasons
SET season_name = ?, season_start = ?, season_end = ?
WHERE season_id = ?
",

/* Copy the league table for the season into he archive table
 * Used by seasons::archive_season()
 */
'season_league_table_copy' =>
"
INSERT INTO season_league_table
SELECT ?, team_id, league_id,
home_wins, home_draws, home_losses, home_goals_for, home_goals_against,
away_wins, away_draws, away_losses, away_goals_for, away_goals_against,
points
FROM league_table;
",

/* Remove all entries from the league_table. The WHERE clause is so
 * the query will work when passed a parameter by the script.
 * used by seasons::archive_season()
 */
'season_del_league_table' =>
"
DELETE FROM league_table WHERE ?
",

/* Create a blank league table for the selected league
 * used by */
'new_league_table' =>
"
INSERT INTO league_table(team_id, league_id,
home_wins, home_draws, home_losses, home_goals_for, home_goals_against,
away_wins, away_draws, away_losses, away_goals_for, away_goals_against,
points)
SELECT team_id, league_id, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
FROM league_teams
WHERE league_id = ?
",

/*
 * U S E R   A D M I N I S T R A T I O N
 */
/* Get a list of active users
 * Used by functions::userteam_users_select()
 */
'user_select' =>
"
SELECT id, uname
FROM predict_users
WHERE active = '1'
",

/* Insert a new userteam
 * Used by userteams::userteams_add()
 */
'userteams_insert' =>
"
INSERT INTO user_leagues
VALUES(?, ?, ?, ?)
",

/* Update an existing user
 * Used by userteams::users_update()
 */
'userteams_update' =>
"
UPDATE user_leagues
SET active = ?
WHERE id = ?
",

/* Get the users whse name matches
 * Used by functions::update_user_scores()
 */
'list_users_byname' =>
"
SELECT id,
       uname,
       fullname,
       timezone,
       DATE_FORMAT(joined, '%D %M %Y') AS joined,
       active,
       isadmin,
       DATE_FORMAT(last_login, '%D %M %Y %H:%i:%s') AS last_login
FROM predict_users
WHERE uname LIKE ?
",

/* Get details of a particular user
 * Used by functions::userteam_members() users::users_form()
 */
'user_details' =>
"
SELECT uname,
       fullname,
       timezone,
       DATE_FORMAT(joined, \"%D %M %Y\") AS joined,
       active,
       isadmin,
       DATE_FORMAT(last_login, \"%D %M %Y %H:%i:%s\") AS last_login
FROM predict_users
WHERE id = ?
",

/* User password for using when updating a user and no password given
 * Used by users:users_update()
 */
'users_password' =>
"
SELECT password
FROM predict_users
WHERE id = ?
",

/* Verify that a username is unique
 * Used by users::users_validate()
 */
'users_unique_uname' =>
"
SELECT COUNT(id)
FROM predict_users
WHERE uname = ?
",

/* Add a new user
 * Used by users::users_add()
 */
'users_add' =>
"
INSERT INTO predict_users(id,
                          uname,
                          fullname,
                          timezone,
                          password,
                          joined,
                          active,
                          isadmin)
VALUES(?, ?, ?, md5(?), ?, ?, ?)
",

/* Update an existing user
 * Used by users::users_update()
 */
'users_update' =>
"
UPDATE predict_users
SET fullname = ?, timezone = ?, password = ?, active = ?, isadmin = ?
WHERE id = ?
",

/* Delete a user's score entry ready for re-insertion
 * Used by functions::update_user_scores()
 */
'user_score_delete' =>
"
DELETE FROM predict_user_scores
WHERE user_id = ?
",

/* Insert an updated score record for a user
 * Used by functions::update_user_scores()
 */
'user_score_insert' =>
"
INSERT INTO predict_user_scores
VALUES(?, ?, ?, ?, ?, ?, ?)
",

/* Get the predictions, results, scores and points for the members of a userteam
 * Used by team::show()
 */
'userteam_points_ordered' =>
"
SELECT predict_users.id                    AS user_id,
       predict_users.uname                 AS username,
       predict_user_scores.num_predictions AS num_predictions,
       predict_user_scores.correct_results AS correct_results,
       predict_user_scores.correct_diffs   AS correct_diffs,
       predict_user_scores.correct_scores  AS exact_scores,
       predict_user_scores.points          AS total_points
FROM predict_users, predict_user_scores, user_league_members
WHERE predict_users.id = predict_user_scores.user_id
  AND user_league_members.user_id = predict_users.id
  AND user_league_members.user_league_id = ?
ORDER BY points / num_predictions DESC
",

/* Get the 5 top scoring users that have made predictions in the past 14 days
 * Used by functions::top_five_users
 */
'top_five_users' =>
"
SELECT predict_users.uname                         AS username,
       predict_user_scores.points                  AS total_points,
       TRUNCATE(points / num_predictions * 100, 2) AS rating
  FROM predict_users, predict_user_scores
 WHERE predict_users.id = predict_user_scores.user_id
   AND predict_users.id IN
(SELECT DISTINCT predictions.user_id
   FROM fixture_results_view, predictions
  WHERE fixture_results_view.fixture_id = predictions.fixture_id
    AND fixture_results_view.kickoff >= SUBDATE(NOW(), INTERVAL 14 DAY)
)
 ORDER BY points / num_predictions DESC
 LIMIT 6
",

/* Query to insert a new comment
 * Used by functions::insert_user_comment()
 */
'insert_user_comment' =>
"
INSERT INTO predict_comments
VALUES(?, ?, ?, ?, ?, ?)
",

/* Query to delete an existing comment
 * Used by functions::delete_user_comment()
 */
'delete_user_comment' =>
"
DELETE FROM predict_comments
WHERE id = ?
",

/* Extract the comments posted by users to the team page
 * Used by functions::show_user_comments()
 */
'show_user_comments' =>
"
SELECT predict_comments.id AS comment_id,
       predict_users.uname AS username,
       DATE_FORMAT(predict_comments.posted, '%D %M %Y %H:%i') AS posted_date,
       predict_comments.message AS user_comment
FROM predict_comments, predict_users
WHERE predict_comments.group_id = ?
  AND predict_comments.user_id = predict_users.id
ORDER BY posted DESC
",
);
?>
