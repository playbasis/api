<?php

$manager = new MongoDB\Driver\Manager('mongodb://localhost:27017');
$insRec = new MongoDB\Driver\BulkWrite;
$insRec->insert(['sku'=> mt_rand(), 'product_name' => 'test', 'price'=> 100, 'category'=> 'game']);
$result = $manager->executeBulkWrite('onlinestore.products', $insRec);

$filter = [];
$options = ['sort' => ['_id' => -1],];
$query = new MongoDB\Driver\Query($filter, $options);
$cursor = $manager->executeQuery('onlinestore.products', $query);
?>

<h1>Refresh page to add random product item</h1>

<table class='table table-bordered'>
   <thead>
      <tr>
            <th>Sku</th>
            <th>Prodcut</th>
            <th>Price</th>
            <th>Category</th>
      </tr>
   </thead>
    <?php
    foreach ($cursor as $document) { ?>
    <tr>
        <td><?php echo $document->sku;  ?></td>
        <td><?php echo $document->product_name;  ?></td>
        <td><?php echo $document->price;  ?></td>
        <td><?php echo $document->category;  ?></td>
    </tr>
    <?php } ?>
</table>