<?php
function leftnav()
{
  $action = "summary"; /* Default action */

  if($_GET['action']) { $action = trim($_GET['action']); }

  $tabs = array("summary" => "Summary",
                "nextset" => "Predictions",
                "team"    => "Group table",
                "search"  => "Search",
                "opts"    => "Options",
                "help"    => "Help"
               );
  /* Whether the search link should include the league id */
  $ltabs = array("summary"     => '1',
                 "nextset"     => '1',
                 "team"        => '0',
                 "search"      => '1',
                 "opts"        => '0',
                 "help"        => '0',
                 "users"       => '0',
                 "fixtures"    => '0',
                 "leagues"     => '1',
                 "tournaments" => '0',
                 "results"     => '1',
                 "seasons"     => '0',
                 "userteams"   => '0',
                );

  /* Get the default league subscription for the user */
  if(!$_SESSION['tabnum']) {
    $_SESSION['tabnum'] = mdb2_fetchOne('default_subscription', array($_SESSION['uid']));
  }
  
  $navstr = "<ul>";
  foreach($tabs as $link => $text)
  {
    $navstr .= "<li>";
    $navstr .= "<a href=\"?action=" . $link;
    #$navstr .= ($link == "nextset" ? "&tabnum=" . $league_id : "");
    if($ltabs[$action] && $ltabs[$link] && $_SESSION['tabnum'])
    {
      $navstr .= "&tabnum=" . $_SESSION['tabnum'];
    }
    if($link == $action) /* Highlight link to the page we're on */
    {
      $navstr .= "\" class=\"selected";
    }
    $navstr .= "\">" . $text . "</a></li>\n";
  }

  /* Add the admin menu if applicable */
  if(isadmin())
  {
    $navstr .= "</ul><span style=\"margin: 0.2em; font-weight: bold;\">Admin</span>\n<ul>";

    $admintabs = array("users"       => "Users",
                       "fixtures"    => "Fixtures",
                       "leagues"     => "Leagues",
                       "tournaments" => "Tournaments",
                       "results"     => "Results",
                       "seasons"     => "Seasons",
                       "userteams"   => "Userteams"
                      );
    foreach($admintabs as $link => $text)
    {
      $navstr .= "<li>";
      $navstr .= "<a href=\"?action=" . $link;
      if($ltabs[$action] && $ltabs[$link] && $_SESSION['tabnum'])
      {
        $navstr .= "&tabnum=" . $_SESSION['tabnum'];
      }
      if($link == $action) /* Highlight link to the page we're on */
      {
        $navstr .= "\" class=\"selected";
      }
      $navstr .= "\">" . $text . "</a></li>\n";
      }
  }

  $navstr .= "<li><a href=\"?action=logout\">Logout</a></li>\n";

  $navstr .= "</ul>\n";

  return($navstr);
}
?>
