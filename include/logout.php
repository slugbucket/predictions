<?php
/*
 * Site logout script
 */
function show()
{
  $disp_str = "
<p>You have now been logged out of the YJFC predictions site.
<br />
For better security, you are advised to close the browser window.
</p>
<p>
<a href=\"http://www.yjfc.co.uk/\">Return</a> to YJFC site.
</p>";

  return($disp_str);
}

function navtabs()
{
  $navstr = "<ul><li class=\"selected\">Logout</li></ul>\n";

  return($navstr);
}
?>
