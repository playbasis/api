<?php

if (getenv('DEVMODE') == true) {
  $mongoClient = new MongoClient(getenv('MONGO_DB_URL'));
  $user = $mongoClient->selectDB('core')->user;
  $name = 'name'.mt_rand();
  $insertResult = $user->insert([
    'name' => $name,
    'created' => new MongoDate(),
  ]);
  $cursor = $user->find();
?>

  <h1>DEVMODE_CHECK: Refresh page to add random item</h1>
  <table>
     <thead>
        <tr>
              <th>name</th>
              <th>created</th>
        </tr>
     </thead>
      <?php
      foreach ($cursor as $doc) { ?>
      <tr>
          <td><?php echo $doc['name'];  ?></td>
          <td><?php echo date('Y-m-d H:i:s', $doc['created']->sec);  ?></td>
      </tr>
      <?php } ?>
  </table>

<?php } else { echo 'Access denied'; } ?>