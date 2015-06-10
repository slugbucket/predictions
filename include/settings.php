<?php
// Standard requires
// Requires that the mysql.so extention is enabled in /etc/php5/apache2/php.ini
// and that the following PEAR packages are installed:
// DB
// DB_DataObject
// HTML_QuickForm
require_once 'PEAR.php';
// Remote settings for pear

ini_set('include_path', '~/pear/lib' . PATH_SEPARATOR
        . ini_get('include_path'));

date_default_timezone_set("US/Central");
/* Default timezone for MySQL according to
 * http://support.hostgator.com/articles/getting-started/general-help/can-i-change-the-server-clock-or-time-zone
 */

// From PHP 4.3.0 onward, you can use the following,
// which especially useful on shared hosts:
set_include_path('~/pear/lib' . PATH_SEPARATOR
                 . get_include_path());



// defines for box styles
define ("OK", 0);
define ("WARNING", 1);
define ("ERROR", 2);

// Database connection details
$dsn = 'mysql://predict_user:predict_pass@localhost/predictions';
ssrequire_once "connect.php";
?>
