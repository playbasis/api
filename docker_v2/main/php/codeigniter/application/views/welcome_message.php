<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<html>
<body>

	<h1>DEVMODE_CHECK: Refresh page to add random item - SHOULD be identical to http://localhost/devmode_check.php</h1>
	<table>
   	<thead>
      <tr>
           <th>name</th>
           <th>created</th>
      </tr>
   	</thead>
    <?php
    foreach ($docs as $doc) { ?>
    <tr>
        <td><?php echo $doc['name'];  ?></td>
        <td><?php echo date('Y-m-d H:i:s', $doc['created']->sec);  ?></td>
    </tr>
    <?php } ?>
	</table>
</body>
</html>