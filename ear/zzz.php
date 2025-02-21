<pre>

<?php

include 'func.php';


// prints e.g. 'Current PHP version: 4.1.1'
echo 'Current PHP version: ' . phpversion();

// prints e.g. '2.0' or nothing if the extension isn't enabled
echo phpversion('tidy');

echo getSolicitudTotal(2073);
?>


</pre>
