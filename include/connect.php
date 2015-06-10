<?php
// Installed via PEAR
/* require_once 'DB.php';
 require_once 'DB/DataObject.php';
 */
require_once 'MDB2.php';

$options = array('result_buffering' => false);
/* $db =& DB::connect($dsn, $options); */
$db =& MDB2::factory($dsn, $options);
if (PEAR::isError($db)) {
    die($db->getMessage());
}
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$db->loadModule('Function');
#$db->loadModule('Iterator');
?>
