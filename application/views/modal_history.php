<table class="table table-bordered table-condensed">
	<caption>Customer</caption>
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Address</th>
			<th>City</th>
			<th>State</th>
			<th>ZIP</th>
			<th>Country</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?php echo $customer['name']; ?></td>
			<td><?php echo $customer['email']; ?></td>
			<td><?php echo $customer['address']; ?></td>
			<td><?php echo $customer['city']; ?></td>
			<td><?php echo $customer['state']; ?></td>
			<td><?php echo $customer['zip']; ?></td>
			<td><?php echo $customer['country']; ?></td>
		</tr>
	</tbody>
</table>
<br>
<table class="table table-condensed table-hover">
	<caption>Orders (<?php echo count($customer['orders']); ?>)</caption>
	<thead>
		<tr>
			<th>Order #</th>
			<th>Date</th>
			<th>Status</th>
			<th>PayPal Status</th>
			<th>Total</th>
		</tr>
	</thead>
	<tbody>
		<?php $sum = 0; ?>
		<?php foreach ($customer['orders'] as $o): ?>
		<?php $sum += $o['total']; ?>
		<tr>
			<td><a href="#" class="history-link" data-order_id="<?php echo $o['id']; ?>"><?php echo $o['public_id']; ?></a></td>
			<td><?php echo date('m/d/Y', strtotime($o['date_added'])); ?></td>
			<td><?php echo $o['internal_status']; ?></td>
			<td><?php echo $o['paypal_status']; ?></td>
			<td>$<?php echo money_format($o['total'], 2); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="4" class="text-right">SUM</th>
			<td>$<?php echo money_format($sum, 2); ?></td>
		</tr>
	</tfoot>
</table>