<?php

//include charts.php in your script
include_once "charts/charts.php";

//change the chart to a bar chart
$chart [ 'chart_type' ] = "line";

/* Queries associated with charting the performance of team members during the 
 * season
 */
$fs_qry = " SELECT end_date FROM fixture_set";
$fs_ary = $db->fetchArray($fs_qry);
/* Loop through each fs_set end date */
foreach($fs_ary as $fs_set)
{
  $edate = $fs_set['end_date'];
  $chart[0][++$col] = $edate;
}

//send the new chart data to the charts.swf flash file
SendChartData ( $chart );

?>
