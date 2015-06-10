<?php
/*
 * Functions to implement the creation and maintenance of tournaments
 * The purpose of these functions is to create fixture entries that can
 * be predicted by the user.
 * The usual fixture set mechanism needs to be able to identify that a
 * tournament is the source for the list of teams and leagues for the
 * fixtures.
 *
 * show() - main driver
 * navtabs() - tournament selection tabs
 * tournaments_list() - show a table with all the defined tournaments
 * tournaments_preload() - preload tournament details from db/submitted form
 * tournaments_form() - display a form requesting tournament details
 *
 */
function show()
{
  $disp_str = "Football tournaments";

  return($disp_str);
}

function navtabs()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  /* Get the tournaments */
  $navstr = "<ul>";

  $navstr .= "</ul>\n";
  return($navstr);
}

/*
 * Function to extract the details of a tournament from the database,
 * overriding any values with those submitted in a form
 *
 * @params:
 *  none
 * @returns:
 *  array containing elements for the tournaments_form function
 */
function tournaments_preload()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  $form_ary = array();

  return($form_ary);
}

function tournaments_form($tournid = "0")
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  /* If a tournament already exists, load it's details */
  if($tournid)
  {
  }


  return($disp_str);
}

function tournaments_list()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  return($disp_str);
}

function tournaments_insert()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  return($disp_str);
}

function tournaments_update()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  return($disp_str);
}

function tournaments_delete()
{
  global $db; /* Global database handle */

  global $query_ary; /* Array containing all database queries */

  return($disp_str);
}
