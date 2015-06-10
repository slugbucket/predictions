<?php
/*
 * Script to generate the top navigation tabs with a tab for each league that
 * the logged in user predicts on as listed in the database table.
 * @param:
 * none
 * @returns:
 * string containing CSS-friendly naivgation tabs
 */
function navtabs()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  /* Get the league subscriptions for the user
echo "Running query:<br />" . $query_ary['subscriptions'] . " with uid =1.<br />";
   */
  $subs = $db->query($query_ary['subscriptions'], array($_SESSION['uid']));

  /* Trap common error conditions */
  if(!$subs) { return("Navigation tab error.<br />\n"); }
#  if(!$subs->numRows()) { return("<ul></ul>\n"); }
  
  $navstr = "<ul>";
  $tcnt = 0;
  while($subs->fetchInto($tab))
  {
    /* Default to first tab if nothing specified in URL (GET) */
    if(!$tcnt && !$_GET['tabnum']) {
      $class = " class=\"selected\"";
    } else
    {
    $class = ($_GET['tabnum'] == $tab['league_id']
             ? " class=\"selected\""
             : "");
    }
    $navstr .= "<li" . $class . ">" . $tab['league_name'] . "</li>";

    ++$tcnt;
  }
  $subs->free();

  $navstr .= "</ul>\n";

  return($navstr);
}
?>
