-- phpMyAdmin SQL Dump
-- version 4.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 12, 2015 at 12:36 PM
-- Server version: 5.5.42-37.1
-- PHP Version: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dbowner_predictions`
--
CREATE DATABASE IF NOT EXISTS `dbowner_predictions` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `dbowner_predictions`;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `league_promote`$$
CREATE DEFINER=`dbowner`@`localhost` PROCEDURE `league_promote`(lge_id INT)
lepro: BEGIN
  DECLARE prom_end INT;      
  DECLARE prom_lge CHAR(32); 
  DECLARE prom_into INT;     
  DECLARE p_off_champ INT;   
  DECLARE CONTINUE HANDLER FOR SQLSTATE '23000'
  BEGIN
    DELETE FROM league_teams WHERE league_id = lge_id;
    SELECT "Cancelling promotion for league due to error" AS display_text;
  END;

  SELECT num_promoted INTO prom_end FROM leagues WHERE id = lge_id;
  IF prom_end = 0
  THEN
    LEAVE lepro;
  END IF;
  SELECT promoted_to INTO prom_into FROM leagues WHERE id = lge_id;
  SELECT league_name(prom_into) INTO prom_lge;
  SELECT playoff_winner(lge_id) INTO p_off_champ;
 
  
  IF p_off_champ > 0
  THEN
    SET prom_end = prom_end - 1;
    SELECT CONCAT("Promoting playoff winner, ", team_name(p_off_champ), " into ", prom_lge) AS display_text;
    DELETE FROM league_teams WHERE league_id = lge_id AND team_id = p_off_champ;
    INSERT INTO league_teams(league_id, team_id) VALUES(prom_into, p_off_champ);
  END IF;
    
  p1: REPEAT
    CALL nth_team_in_league(lge_id, prom_end);
    IF @nth = 0 THEN LEAVE p1; END IF;
    SELECT CONCAT("Promoting ", prom_end , " team, ", team_name(@nth), " into  ", prom_lge) AS display_text;
    DELETE FROM league_teams WHERE league_id = lge_id AND team_id = @nth;
    INSERT INTO league_teams(league_id, team_id) VALUES(prom_into, @nth);
    SET prom_end = prom_end - 1;
  UNTIL prom_end = 0 END REPEAT;
END lepro$$

DROP PROCEDURE IF EXISTS `league_relegate`$$
CREATE DEFINER=`dbowner`@`localhost` PROCEDURE `league_relegate`(lge_id INT)
lerel: BEGIN
  DECLARE releg_start INT;
  DECLARE releg_end INT;
  DECLARE releg_into INT;
  DECLARE releg_lge CHAR(32);
  DECLARE CONTINUE HANDLER FOR SQLSTATE '23000'
  BEGIN
    DELETE FROM league_teams WHERE league_id = lge_id;
    SELECT "Cancelling relegation for league due to error" AS display_text;
  END;

  SELECT num_in_league(lge_id) INTO releg_end;
  IF releg_end = 0
  THEN
    LEAVE lerel;
  END IF;
  SELECT releg_end - num_relegated + 1 INTO releg_start FROM leagues WHERE id = lge_id; 
  SELECT relegated_to INTO releg_into FROM leagues WHERE id = lge_id;
  SELECT league_name(releg_into) INTO releg_lge;

  SELECT CONCAT("Relegating from team ", releg_start, " to ", releg_end, " into ", releg_lge) AS display_text;
  
  r1: REPEAT
    CALL nth_team_in_league(lge_id, releg_start);
    IF @nth = 0 THEN LEAVE r1; END IF;
    SELECT CONCAT("Relegating ", releg_start , " team, ", team_name(@nth), " into ", releg_lge) AS display_text;
    DELETE FROM league_teams WHERE league_id = lge_id AND team_id = @nth;
    INSERT INTO league_teams(league_id, team_id) VALUES(releg_into, @nth);
    SET releg_start = releg_start + 1;
  UNTIL releg_start > releg_end END REPEAT;
END lerel$$

DROP PROCEDURE IF EXISTS `league_up_down`$$
CREATE DEFINER=`dbowner`@`localhost` PROCEDURE `league_up_down`(lge_id INT)
BEGIN
  CALL league_promote(lge_id);
  CALL league_relegate(lge_id);
END$$

DROP PROCEDURE IF EXISTS `nth_team_in_league`$$
CREATE DEFINER=`dbowner`@`localhost` PROCEDURE `nth_team_in_league`(lge_id INT, position INT)
BEGIN

  DECLARE tm_id INT;
  DECLARE t1 CURSOR FOR SELECT team_id FROM league_table_view WHERE league_id = lge_id ORDER BY  points DESC, goal_diff, team_name;
  
  DECLARE EXIT HANDLER FOR NOT FOUND SELECT '0' AS tm_id;
  SET @x = 0;

  OPEN t1;
  t_loop: REPEAT
    
    FETCH FROM t1 INTO tm_id;
    IF @x = position THEN LEAVE t_loop; END IF;
    SET @nth = tm_id;
    SET @x = @x + 1;
  UNTIL @x = position END REPEAT;
  CLOSE t1;
END$$

DROP PROCEDURE IF EXISTS `promote_relegate`$$
CREATE DEFINER=`dbowner`@`localhost` PROCEDURE `promote_relegate`()
BEGIN
  DECLARE lge_id INT;
  DECLARE lge_name CHAR(32);
  DECLARE done INT DEFAULT FALSE;
  DECLARE pr1 CURSOR FOR select id, name from leagues WHERE active = '1';
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN pr1;
  pr_loop: LOOP
    FETCH FROM pr1 INTO lge_id, lge_name;
    IF done THEN LEAVE pr_loop; END IF;
    SELECT CONCAT("Resolving promotion and relegation for ", lge_name, ".") AS display_text;
    CALL league_up_down(lge_id);
  END LOOP;
  CLOSE pr1;
END$$

--
-- Functions
--
DROP FUNCTION IF EXISTS `default_match_date`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `default_match_date`(fset_id INT) RETURNS char(10) CHARSET utf8 COLLATE utf8_unicode_ci
    DETERMINISTIC
BEGIN

  DECLARE default_date CHAR(10);
  DECLARE sdate CHAR(10);
  DECLARE is_friday CHAR;
  DECLARE same_day INT(1);

  IF fset_id = 0
    THEN RETURN "";
  END IF;
  SELECT start_date,
         CASE DATE_FORMAT(start_date, "%w")
           WHEN '5' THEN 'y'
           ELSE 'n'
         END,
         CASE DATEDIFF(end_date, start_date)
           WHEN '0' THEN 1
           ELSE 0
         END
    INTO sdate, is_friday, same_day
  FROM fixture_set
  WHERE set_id = fset_id;

  IF same_day = 1 || is_friday = 'n' THEN
    SELECT DATE_FORMAT(sdate, "%Y-%m-%d") INTO default_date;
  ELSE
    SELECT DATE_FORMAT(DATE_ADD(sdate, INTERVAL 1 DAY), "%Y-%m-%d") INTO default_date;
  END IF;
  RETURN default_date;
  END$$

DROP FUNCTION IF EXISTS `first_fixture`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `first_fixture`(fset_id INT) RETURNS char(18) CHARSET utf8 COLLATE utf8_unicode_ci
    DETERMINISTIC
BEGIN

  DECLARE earliest CHAR(18);

  IF fset_id = 0
    THEN RETURN "";
  END IF;
SELECT MIN(CONCAT(f1.fixture_date, " ", kickoff)) AS earliest
  INTO earliest
FROM fixtures f1 INNER JOIN fixture_set fs1 ON f1.league_id = fs1.league_id
WHERE f1.fixture_date >= fs1.start_date
  AND f1.fixture_date <= fs1.end_date
  AND fs1.set_id = '1317'
  GROUP BY fs1.set_id;
RETURN earliest;
  END$$

DROP FUNCTION IF EXISTS `fixture_end`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `fixture_end`(fid INT) RETURNS datetime
    DETERMINISTIC
BEGIN
DECLARE fe DATETIME;
SELECT DATE_FORMAT(CONCAT(fixture_date, " ", kickoff), "%Y-%m-%d %T") INTO fe FROM fixtures WHERE id = fid;
RETURN fe;
END$$

DROP FUNCTION IF EXISTS `fixture_start`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `fixture_start`(fid INT) RETURNS datetime
    DETERMINISTIC
BEGIN
DECLARE fs DATETIME;
SELECT DATE_FORMAT(CONCAT(fixture_date, " ", kickoff), "%Y-%m-%d %T") INTO fs FROM fixtures WHERE id = fid;
RETURN fs;
END$$

DROP FUNCTION IF EXISTS `frv_datesub`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `frv_datesub`(days_ago INT) RETURNS char(18) CHARSET utf8
    DETERMINISTIC
BEGIN

  DECLARE frv_date CHAR(18);

  SELECT DATE_SUB(MAX(kickoff), INTERVAL days_ago DAY)
    INTO frv_date
  FROM fixture_results_view;

RETURN frv_date;
END$$

DROP FUNCTION IF EXISTS `league_name`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `league_name`(lid INT) RETURNS char(32) CHARSET utf8 COLLATE utf8_unicode_ci
    DETERMINISTIC
BEGIN
  DECLARE n CHAR(32);
    SELECT name INTO n FROM leagues WHERE id = lid;
  RETURN n;
  END$$

DROP FUNCTION IF EXISTS `num_in_league`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `num_in_league`(lge_id INT) RETURNS char(10) CHARSET utf8 COLLATE utf8_unicode_ci
    DETERMINISTIC
BEGIN
  DECLARE n INT;
  SELECT COUNT(*) INTO n FROM league_teams WHERE league_id = lge_id;
  RETURN n;
  END$$

DROP FUNCTION IF EXISTS `playoff_winner`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `playoff_winner`(lge_id INT) RETURNS char(32) CHARSET utf8 COLLATE utf8_unicode_ci
    DETERMINISTIC
BEGIN
  DECLARE playoff_winner INT;

  SELECT IF(home_goals > away_goals, home, away) INTO playoff_winner 
    FROM fixture_results_view
   WHERE league_id = lge_id
     AND result_type = 'playoff'
     AND fixture_id NOT IN (
         SELECT DISTINCT frv1.fixture_id
           FROM fixture_results_view AS frv1 INNER JOIN fixture_results_view AS frv2
                ON frv1.home = frv2.away
          WHERE frv1.league_id = lge_id
            AND frv1.league_id = frv2.league_id
            AND frv1.result_type = 'playoff'
            AND frv2.result_type = 'playoff'
            AND (frv1.home = frv2.away
                 AND frv2.home = frv1.away)
);
  SELECT IFNULL(playoff_winner, 0) INTO playoff_winner;
  RETURN playoff_winner;
END$$

DROP FUNCTION IF EXISTS `team_name`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `team_name`(tid INT) RETURNS char(32) CHARSET utf8 COLLATE utf8_unicode_ci
    DETERMINISTIC
BEGIN
  DECLARE n CHAR(32);
  SELECT name INTO n FROM predict_teams WHERE id = tid;
  RETURN n;
  END$$

DROP FUNCTION IF EXISTS `win_draw_score`$$
CREATE DEFINER=`dbowner`@`localhost` FUNCTION `win_draw_score`(ah INT, aa INT, ph INT, pa INT) RETURNS char(1) CHARSET utf8
    DETERMINISTIC
BEGIN

  
  IF ah = ph AND aa = pa
    THEN RETURN 'S';
  END IF;
  
  IF (ah = aa) AND (ph = pa) AND ah <> ph
    THEN RETURN 'D';
  END IF;
  
  IF (ah > aa AND ph > pa) OR (ah < aa AND ph < pa) AND (ah <> ph OR aa <> pa)
    THEN RETURN 'W';
  END IF;

  RETURN '-';
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `fixtures`
--

DROP TABLE IF EXISTS `fixtures`;
CREATE TABLE IF NOT EXISTS `fixtures` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `league_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fixture_date` date NOT NULL DEFAULT '0000-00-00',
  `kickoff` time NOT NULL DEFAULT '15:00:00',
  `home_team_id` int(4) NOT NULL DEFAULT '0',
  `away_team_id` int(4) NOT NULL DEFAULT '0',
  `match_type` enum('league','playoff','knockout','friendly') NOT NULL DEFAULT 'league',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='The list of fixtures to make predictions for';

-- --------------------------------------------------------

--
-- Table structure for table `fixtures_seq`
--

DROP TABLE IF EXISTS `fixtures_seq`;
CREATE TABLE IF NOT EXISTS `fixtures_seq` (
  `id` int(4) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=16155 DEFAULT CHARSET=utf8 COMMENT='Sequence for fixtures';

-- --------------------------------------------------------

--
-- Table structure for table `fixture_results`
--

DROP TABLE IF EXISTS `fixture_results`;
CREATE TABLE IF NOT EXISTS `fixture_results` (
  `fixture_id` int(10) unsigned NOT NULL DEFAULT '0',
  `result_type` enum('normal','extra','penalties','abandoned','postponed','playoff') NOT NULL DEFAULT 'normal',
  `home_goals` tinyint(4) NOT NULL DEFAULT '0',
  `away_goals` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Results of the fixtures';

-- --------------------------------------------------------

--
-- Stand-in structure for view `fixture_results_view`
--
DROP VIEW IF EXISTS `fixture_results_view`;
CREATE TABLE IF NOT EXISTS `fixture_results_view` (
`fixture_id` int(10) unsigned
,`league_id` int(10) unsigned
,`kickoff` varchar(19)
,`home` int(4)
,`away` int(4)
,`match_type` enum('league','playoff','knockout','friendly')
,`result_type` enum('normal','extra','penalties','abandoned','postponed','playoff')
,`home_goals` tinyint(4)
,`away_goals` tinyint(4)
);

-- --------------------------------------------------------

--
-- Table structure for table `fixture_set`
--

DROP TABLE IF EXISTS `fixture_set`;
CREATE TABLE IF NOT EXISTS `fixture_set` (
  `set_id` int(10) unsigned NOT NULL DEFAULT '0',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  `league_id` int(10) unsigned NOT NULL DEFAULT '1',
  `num_fixtures` tinyint(4) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='A collection of fixtures on which predictions are to be made';

-- --------------------------------------------------------

--
-- Table structure for table `fixture_set_seq`
--

DROP TABLE IF EXISTS `fixture_set_seq`;
CREATE TABLE IF NOT EXISTS `fixture_set_seq` (
  `id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Sequence for fixture set identifiers';

-- --------------------------------------------------------

--
-- Table structure for table `league`
--

DROP TABLE IF EXISTS `league`;
CREATE TABLE IF NOT EXISTS `league` (
  `id` int(11) NOT NULL,
  `name` char(48) NOT NULL DEFAULT '',
  `url` char(128) DEFAULT NULL,
  `logo` char(25) DEFAULT '',
  `owner` int(4) DEFAULT '1024',
  `active` char(1) NOT NULL DEFAULT 'y'
) ENGINE=MyISAM AUTO_INCREMENT=4011 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `leagues`
--

DROP TABLE IF EXISTS `leagues`;
CREATE TABLE IF NOT EXISTS `leagues` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` char(32) NOT NULL DEFAULT '',
  `timezone` char(64) NOT NULL DEFAULT 'Europe/London',
  `default_match_type` char(16) NOT NULL DEFAULT 'league',
  `num_promoted` tinyint(3) unsigned DEFAULT '3',
  `num_relegated` tinyint(3) unsigned DEFAULT '3',
  `promoted_to` tinyint(3) unsigned DEFAULT '0',
  `relegated_to` tinyint(3) unsigned DEFAULT '0',
  `playoff_start` tinyint(4) NOT NULL DEFAULT '3',
  `playoff_cnt` tinyint(4) NOT NULL DEFAULT '4',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `tournament` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Leagues from which fixtures can be drawn';

-- --------------------------------------------------------

--
-- Table structure for table `leagues_seq`
--

DROP TABLE IF EXISTS `leagues_seq`;
CREATE TABLE IF NOT EXISTS `leagues_seq` (
  `id` int(4) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='Sequence for league id values';

-- --------------------------------------------------------

--
-- Table structure for table `league_table`
--

DROP TABLE IF EXISTS `league_table`;
CREATE TABLE IF NOT EXISTS `league_table` (
  `team_id` int(10) NOT NULL DEFAULT '0',
  `league_id` int(10) NOT NULL DEFAULT '0',
  `home_wins` smallint(6) NOT NULL DEFAULT '0',
  `home_draws` smallint(6) NOT NULL DEFAULT '0',
  `home_losses` smallint(6) NOT NULL DEFAULT '0',
  `home_goals_for` smallint(6) NOT NULL DEFAULT '0',
  `home_goals_against` smallint(6) NOT NULL DEFAULT '0',
  `away_wins` smallint(6) NOT NULL DEFAULT '0',
  `away_draws` smallint(6) NOT NULL DEFAULT '0',
  `away_losses` smallint(6) NOT NULL DEFAULT '0',
  `away_goals_for` smallint(6) NOT NULL DEFAULT '0',
  `away_goals_against` smallint(6) NOT NULL DEFAULT '0',
  `points` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table with team positions for the leagues';

-- --------------------------------------------------------

--
-- Stand-in structure for view `league_table_view`
--
DROP VIEW IF EXISTS `league_table_view`;
CREATE TABLE IF NOT EXISTS `league_table_view` (
`team_id` int(10)
,`league_id` int(10)
,`team_name` char(32)
,`known_name` char(32)
,`played` bigint(11)
,`goals_for` int(7)
,`goals_against` int(7)
,`goal_diff` int(9)
,`points` smallint(6)
);

-- --------------------------------------------------------

--
-- Table structure for table `league_teams`
--

DROP TABLE IF EXISTS `league_teams`;
CREATE TABLE IF NOT EXISTS `league_teams` (
  `team_id` int(4) unsigned NOT NULL DEFAULT '0',
  `league_id` int(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `predictions`
--

DROP TABLE IF EXISTS `predictions`;
CREATE TABLE IF NOT EXISTS `predictions` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fixture_id` int(10) unsigned NOT NULL DEFAULT '0',
  `home_goals` tinyint(4) NOT NULL DEFAULT '0',
  `away_goals` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='The predictions made by users';

-- --------------------------------------------------------

--
-- Table structure for table `predict_comments`
--

DROP TABLE IF EXISTS `predict_comments`;
CREATE TABLE IF NOT EXISTS `predict_comments` (
  `id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `group_id` int(10) NOT NULL DEFAULT '0',
  `title` varchar(64) NOT NULL DEFAULT 'Message title',
  `message` text,
  `posted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table for group comments posted by users';

-- --------------------------------------------------------

--
-- Table structure for table `predict_comments_seq`
--

DROP TABLE IF EXISTS `predict_comments_seq`;
CREATE TABLE IF NOT EXISTS `predict_comments_seq` (
  `id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `predict_teams`
--

DROP TABLE IF EXISTS `predict_teams`;
CREATE TABLE IF NOT EXISTS `predict_teams` (
  `id` int(4) unsigned NOT NULL DEFAULT '0',
  `name` char(32) NOT NULL DEFAULT '',
  `known_name` char(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Teams that make a league for predictions';

-- --------------------------------------------------------

--
-- Table structure for table `predict_teams_seq`
--

DROP TABLE IF EXISTS `predict_teams_seq`;
CREATE TABLE IF NOT EXISTS `predict_teams_seq` (
  `id` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='sequence table for predtictable teams in a league';

-- --------------------------------------------------------

--
-- Table structure for table `predict_team_deduct`
--

DROP TABLE IF EXISTS `predict_team_deduct`;
CREATE TABLE IF NOT EXISTS `predict_team_deduct` (
  `team_id` int(4) NOT NULL DEFAULT '0',
  `league_id` smallint(6) NOT NULL,
  `deduction` int(4) NOT NULL DEFAULT '10'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table listing teams that have had points deducted';

-- --------------------------------------------------------

--
-- Table structure for table `predict_users`
--

DROP TABLE IF EXISTS `predict_users`;
CREATE TABLE IF NOT EXISTS `predict_users` (
  `id` int(10) NOT NULL DEFAULT '0',
  `uname` char(16) NOT NULL DEFAULT '',
  `fullname` char(48) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `timezone` char(64) NOT NULL DEFAULT 'Europe/London',
  `joined` date NOT NULL DEFAULT '0000-00-00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `isadmin` tinyint(1) NOT NULL DEFAULT '0',
  `last_login` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Users with access to the predictions site';

-- --------------------------------------------------------

--
-- Table structure for table `predict_users_seq`
--

DROP TABLE IF EXISTS `predict_users_seq`;
CREATE TABLE IF NOT EXISTS `predict_users_seq` (
  `id` int(4) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='Sequence for user id values';

-- --------------------------------------------------------

--
-- Table structure for table `predict_user_scores`
--

DROP TABLE IF EXISTS `predict_user_scores`;
CREATE TABLE IF NOT EXISTS `predict_user_scores` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `num_predictions` int(10) NOT NULL DEFAULT '0',
  `correct_results` int(10) NOT NULL DEFAULT '0',
  `correct_diffs` int(10) NOT NULL DEFAULT '0',
  `correct_scores` int(10) NOT NULL DEFAULT '0',
  `points` int(10) NOT NULL DEFAULT '0',
  `last_updated` datetime NOT NULL DEFAULT '2007-08-11 12:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table with the sum of the user correct score and exact resul';

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

DROP TABLE IF EXISTS `seasons`;
CREATE TABLE IF NOT EXISTS `seasons` (
  `season_id` int(10) unsigned NOT NULL DEFAULT '0',
  `season_name` char(32) NOT NULL DEFAULT 'Football Season',
  `season_start` date NOT NULL DEFAULT '0000-00-00',
  `season_end` date NOT NULL DEFAULT '0000-00-00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Season table for identifying which seasons we have predictio';

-- --------------------------------------------------------

--
-- Table structure for table `seasons_seq`
--

DROP TABLE IF EXISTS `seasons_seq`;
CREATE TABLE IF NOT EXISTS `seasons_seq` (
  `id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Season sequence identified by year in which season finishes';

-- --------------------------------------------------------

--
-- Table structure for table `season_deductions`
--

DROP TABLE IF EXISTS `season_deductions`;
CREATE TABLE IF NOT EXISTS `season_deductions` (
  `season_id` smallint(6) NOT NULL,
  `team_id` smallint(6) NOT NULL,
  `league_id` smallint(6) NOT NULL,
  `deduction` smallint(6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Archive record of points deductions for teams';

-- --------------------------------------------------------

--
-- Table structure for table `season_fixtures`
--

DROP TABLE IF EXISTS `season_fixtures`;
CREATE TABLE IF NOT EXISTS `season_fixtures` (
  `season_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fixture_id` int(10) unsigned NOT NULL DEFAULT '0',
  `league_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fixture_date` date NOT NULL DEFAULT '0000-00-00',
  `kickoff` time NOT NULL DEFAULT '15:00:00',
  `home_team_id` int(4) NOT NULL DEFAULT '0',
  `away_team_id` int(4) NOT NULL DEFAULT '0',
  `match_type` enum('league','playoff','knockout','friendly') NOT NULL DEFAULT 'league'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Historical record of previous season fixtures';

-- --------------------------------------------------------

--
-- Table structure for table `season_fixture_sets`
--

DROP TABLE IF EXISTS `season_fixture_sets`;
CREATE TABLE IF NOT EXISTS `season_fixture_sets` (
  `season_id` int(10) unsigned NOT NULL DEFAULT '0',
  `set_id` int(10) unsigned NOT NULL DEFAULT '0',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  `league_id` int(10) unsigned NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Historical record of previous season fixture sets';

-- --------------------------------------------------------

--
-- Table structure for table `season_league_table`
--

DROP TABLE IF EXISTS `season_league_table`;
CREATE TABLE IF NOT EXISTS `season_league_table` (
  `season_id` int(10) NOT NULL DEFAULT '0',
  `team_id` int(10) NOT NULL DEFAULT '0',
  `league_id` int(10) NOT NULL DEFAULT '0',
  `home_wins` smallint(6) NOT NULL DEFAULT '0',
  `home_draws` smallint(6) NOT NULL DEFAULT '0',
  `home_losses` smallint(6) NOT NULL DEFAULT '0',
  `home_goals_for` smallint(6) NOT NULL DEFAULT '0',
  `home_goals_against` smallint(6) NOT NULL DEFAULT '0',
  `away_wins` smallint(6) NOT NULL DEFAULT '0',
  `away_draws` smallint(6) NOT NULL DEFAULT '0',
  `away_losses` smallint(6) NOT NULL DEFAULT '0',
  `away_goals_for` smallint(6) NOT NULL DEFAULT '0',
  `away_goals_against` smallint(6) NOT NULL DEFAULT '0',
  `points` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Historial record of previous seasons league table';

-- --------------------------------------------------------

--
-- Table structure for table `season_predictions`
--

DROP TABLE IF EXISTS `season_predictions`;
CREATE TABLE IF NOT EXISTS `season_predictions` (
  `season_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fixture_id` int(10) unsigned NOT NULL DEFAULT '0',
  `home_goals` tinyint(4) NOT NULL DEFAULT '0',
  `away_goals` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='The predictions made by users';

-- --------------------------------------------------------

--
-- Table structure for table `season_predict_comments`
--

DROP TABLE IF EXISTS `season_predict_comments`;
CREATE TABLE IF NOT EXISTS `season_predict_comments` (
  `season_id` int(10) NOT NULL DEFAULT '0',
  `comment_id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `group_id` int(10) NOT NULL DEFAULT '0',
  `title` varchar(64) NOT NULL DEFAULT 'Message title',
  `message` text,
  `posted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table for group comments posted by users';

-- --------------------------------------------------------

--
-- Table structure for table `season_predict_team_deduct`
--

DROP TABLE IF EXISTS `season_predict_team_deduct`;
CREATE TABLE IF NOT EXISTS `season_predict_team_deduct` (
  `season_id` int(4) NOT NULL DEFAULT '0',
  `team_id` int(4) NOT NULL DEFAULT '0',
  `deduction` int(4) NOT NULL DEFAULT '10'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Historical record of teams that have had points deducted';

-- --------------------------------------------------------

--
-- Table structure for table `season_predict_user_scores`
--

DROP TABLE IF EXISTS `season_predict_user_scores`;
CREATE TABLE IF NOT EXISTS `season_predict_user_scores` (
  `season_id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `num_predictions` int(10) NOT NULL DEFAULT '0',
  `correct_results` int(10) NOT NULL DEFAULT '0',
  `correct_diffs` int(10) NOT NULL DEFAULT '0',
  `correct_scores` int(10) NOT NULL DEFAULT '0',
  `points` int(10) NOT NULL DEFAULT '0',
  `last_updated` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Historical record with the sum of the user correct score and';

-- --------------------------------------------------------

--
-- Table structure for table `season_results`
--

DROP TABLE IF EXISTS `season_results`;
CREATE TABLE IF NOT EXISTS `season_results` (
  `season_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fixture_id` int(10) unsigned NOT NULL DEFAULT '0',
  `result_type` enum('normal','extra','penalties','abandoned','postponed','playoff') NOT NULL DEFAULT 'normal',
  `home_goals` tinyint(4) NOT NULL DEFAULT '0',
  `away_goals` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Historical record of previous season results';

-- --------------------------------------------------------

--
-- Table structure for table `season_teams_leagues`
--

DROP TABLE IF EXISTS `season_teams_leagues`;
CREATE TABLE IF NOT EXISTS `season_teams_leagues` (
  `season_id` int(10) unsigned NOT NULL DEFAULT '0',
  `team_id` int(4) unsigned NOT NULL DEFAULT '0',
  `league_id` int(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Which teams were in which leagues for the season';

-- --------------------------------------------------------

--
-- Table structure for table `user_leagues`
--

DROP TABLE IF EXISTS `user_leagues`;
CREATE TABLE IF NOT EXISTS `user_leagues` (
  `id` int(4) unsigned NOT NULL DEFAULT '0',
  `name` char(32) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '2007-07-01 15:04:31',
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Leagues for groups of users';

-- --------------------------------------------------------

--
-- Table structure for table `user_leagues_seq`
--

DROP TABLE IF EXISTS `user_leagues_seq`;
CREATE TABLE IF NOT EXISTS `user_leagues_seq` (
  `id` int(4) unsigned NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='User league sequence';

-- --------------------------------------------------------

--
-- Table structure for table `user_league_members`
--

DROP TABLE IF EXISTS `user_league_members`;
CREATE TABLE IF NOT EXISTS `user_league_members` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_league_id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Which users are in which user league';

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

DROP TABLE IF EXISTS `user_subscriptions`;
CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `league_id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Table listing what leagues a user has signed up tomake predi';

-- --------------------------------------------------------

--
-- Structure for view `fixture_results_view`
--
DROP TABLE IF EXISTS `fixture_results_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbowner`@`localhost` SQL SECURITY DEFINER VIEW `fixture_results_view` AS select `fixtures`.`id` AS `fixture_id`,`fixtures`.`league_id` AS `league_id`,concat(`fixtures`.`fixture_date`,' ',`fixtures`.`kickoff`) AS `kickoff`,`fixtures`.`home_team_id` AS `home`,`fixtures`.`away_team_id` AS `away`,`fixtures`.`match_type` AS `match_type`,`fixture_results`.`result_type` AS `result_type`,`fixture_results`.`home_goals` AS `home_goals`,`fixture_results`.`away_goals` AS `away_goals` from (`fixtures` join `fixture_results`) where ((`fixtures`.`id` = `fixture_results`.`fixture_id`) and (`fixture_results`.`result_type` <> 'abandoned') and (`fixture_results`.`result_type` <> 'postponed'));

-- --------------------------------------------------------

--
-- Structure for view `league_table_view`
--
DROP TABLE IF EXISTS `league_table_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dbowner`@`localhost` SQL SECURITY DEFINER VIEW `league_table_view` AS select `league_table`.`team_id` AS `team_id`,`league_table`.`league_id` AS `league_id`,`predict_teams`.`name` AS `team_name`,`predict_teams`.`known_name` AS `known_name`,(((((`league_table`.`home_wins` + `league_table`.`home_draws`) + `league_table`.`home_losses`) + `league_table`.`away_wins`) + `league_table`.`away_draws`) + `league_table`.`away_losses`) AS `played`,(`league_table`.`home_goals_for` + `league_table`.`away_goals_for`) AS `goals_for`,(`league_table`.`home_goals_against` + `league_table`.`away_goals_against`) AS `goals_against`,(((`league_table`.`home_goals_for` + `league_table`.`away_goals_for`) - `league_table`.`home_goals_against`) - `league_table`.`away_goals_against`) AS `goal_diff`,`league_table`.`points` AS `points` from (`league_table` join `predict_teams` on((`league_table`.`team_id` = `predict_teams`.`id`))) order by `league_table`.`league_id`,`league_table`.`points` desc,(((`league_table`.`home_goals_for` + `league_table`.`away_goals_for`) - `league_table`.`home_goals_against`) - `league_table`.`away_goals_against`) desc,(`league_table`.`home_goals_for` + `league_table`.`away_goals_for`) desc,`predict_teams`.`name`;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fixtures`
--
ALTER TABLE `fixtures`
  ADD PRIMARY KEY (`fixture_date`,`home_team_id`,`away_team_id`), ADD UNIQUE KEY `id` (`id`), ADD KEY `id_2` (`id`);

--
-- Indexes for table `fixtures_seq`
--
ALTER TABLE `fixtures_seq`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `fixture_results`
--
ALTER TABLE `fixture_results`
  ADD PRIMARY KEY (`fixture_id`), ADD KEY `fixture_id` (`fixture_id`);

--
-- Indexes for table `fixture_set`
--
ALTER TABLE `fixture_set`
  ADD UNIQUE KEY `league_id` (`league_id`,`start_date`,`end_date`);

--
-- Indexes for table `fixture_set_seq`
--
ALTER TABLE `fixture_set_seq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `league`
--
ALTER TABLE `league`
  ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`);

--
-- Indexes for table `leagues`
--
ALTER TABLE `leagues`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `leagues_seq`
--
ALTER TABLE `leagues_seq`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `league_table`
--
ALTER TABLE `league_table`
  ADD PRIMARY KEY (`team_id`,`league_id`);

--
-- Indexes for table `league_teams`
--
ALTER TABLE `league_teams`
  ADD PRIMARY KEY (`team_id`,`league_id`);

--
-- Indexes for table `predictions`
--
ALTER TABLE `predictions`
  ADD PRIMARY KEY (`user_id`,`fixture_id`), ADD KEY `user_id` (`user_id`), ADD KEY `fixture_id` (`fixture_id`);

--
-- Indexes for table `predict_comments`
--
ALTER TABLE `predict_comments`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `predict_comments_seq`
--
ALTER TABLE `predict_comments_seq`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `predict_teams`
--
ALTER TABLE `predict_teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `predict_teams_seq`
--
ALTER TABLE `predict_teams_seq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `predict_team_deduct`
--
ALTER TABLE `predict_team_deduct`
  ADD UNIQUE KEY `team_id` (`team_id`);

--
-- Indexes for table `predict_users`
--
ALTER TABLE `predict_users`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uname` (`uname`), ADD KEY `id` (`id`);

--
-- Indexes for table `predict_users_seq`
--
ALTER TABLE `predict_users_seq`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `predict_user_scores`
--
ALTER TABLE `predict_user_scores`
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`season_id`);

--
-- Indexes for table `seasons_seq`
--
ALTER TABLE `seasons_seq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `season_deductions`
--
ALTER TABLE `season_deductions`
  ADD PRIMARY KEY (`season_id`,`team_id`,`league_id`);

--
-- Indexes for table `season_fixtures`
--
ALTER TABLE `season_fixtures`
  ADD PRIMARY KEY (`season_id`,`fixture_date`,`home_team_id`,`away_team_id`), ADD UNIQUE KEY `fixture_id` (`fixture_id`);

--
-- Indexes for table `season_fixture_sets`
--
ALTER TABLE `season_fixture_sets`
  ADD PRIMARY KEY (`season_id`,`set_id`), ADD UNIQUE KEY `league_id` (`league_id`,`start_date`,`end_date`);

--
-- Indexes for table `season_league_table`
--
ALTER TABLE `season_league_table`
  ADD PRIMARY KEY (`season_id`,`team_id`,`league_id`);

--
-- Indexes for table `season_predictions`
--
ALTER TABLE `season_predictions`
  ADD PRIMARY KEY (`season_id`,`user_id`,`fixture_id`);

--
-- Indexes for table `season_predict_comments`
--
ALTER TABLE `season_predict_comments`
  ADD PRIMARY KEY (`comment_id`,`season_id`);

--
-- Indexes for table `season_predict_team_deduct`
--
ALTER TABLE `season_predict_team_deduct`
  ADD UNIQUE KEY `season_id` (`season_id`), ADD UNIQUE KEY `team_id` (`team_id`);

--
-- Indexes for table `season_predict_user_scores`
--
ALTER TABLE `season_predict_user_scores`
  ADD PRIMARY KEY (`season_id`,`user_id`);

--
-- Indexes for table `season_results`
--
ALTER TABLE `season_results`
  ADD PRIMARY KEY (`season_id`,`fixture_id`);

--
-- Indexes for table `season_teams_leagues`
--
ALTER TABLE `season_teams_leagues`
  ADD PRIMARY KEY (`season_id`,`team_id`,`league_id`);

--
-- Indexes for table `user_leagues`
--
ALTER TABLE `user_leagues`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `user_leagues_seq`
--
ALTER TABLE `user_leagues_seq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_league_members`
--
ALTER TABLE `user_league_members`
  ADD PRIMARY KEY (`user_id`,`user_league_id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`user_id`,`league_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fixtures_seq`
--
ALTER TABLE `fixtures_seq`
  MODIFY `id` int(4) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16155;
--
-- AUTO_INCREMENT for table `league`
--
ALTER TABLE `league`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4011;
--
-- AUTO_INCREMENT for table `leagues_seq`
--
ALTER TABLE `leagues_seq`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `predict_users_seq`
--
ALTER TABLE `predict_users_seq`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `user_leagues_seq`
--
ALTER TABLE `user_leagues_seq`
  MODIFY `id` int(4) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
