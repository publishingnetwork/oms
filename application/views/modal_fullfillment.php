<div class="lead">Are you sure you want to send these orders for fullfillment?</div>
<table class="table table-bordered table-striped">
	<thead>
		<tr>
			<th>Order #</th>
			<th>Shipping</th>
			<th>Name</th>
			<th>Address</th>
			<th>Address 2</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
			<th>Country</th>
			<th>Items</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($orders as $o): ?>
		<tr>
			<td><?php echo $o['public_id']; ?></td>
			<td><?php echo $o['shipping_method']; ?></td>
			<td><?php echo $o['name']; ?></td>
			<td><?php echo $o['address1']; ?></td>
			<td><?php echo $o['address2']; ?></td>
			<td><?php echo $o['city']; ?></td>
			<td><?php echo $o['state']; ?></td>
			<td><?php echo $o['zip']; ?></td>
			<td><?php echo $o['country']; ?></td>
			<td>
			<?php
				foreach ($o['skus'] as $s):
					if($s['quantity'] > 0){
				?>
				<span class="label"><?php echo $s['quantity'] . ' x ' . $s['sku']; ?></span>
				<?php
					}
				endforeach;
			 ?>
			 </td>
		</tr>
		<?php endforeach; ?>
	</tbody>

</table>

<?php

//echo "<pre>";
//	var_dump($orders);
//echo "</pre>";