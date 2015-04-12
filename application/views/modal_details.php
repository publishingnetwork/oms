	
<div class="row-fluid">
	<div class="span4">
		<table class="table table-condensed table-hover table-bordered ">
			<caption>General Info</caption>
			<tbody>
				<tr>
					<th>Order #</th>
					<td><?php echo $order['public_id']; ?></td>
				</tr>
				<tr>
					<th>Total</th>
					<td>$<?php echo money_format($order['total'], 2); ?></td>
				</tr>
				<tr>
					<th>Date Ordered</th>
					<td><?php echo date('m/d/Y', strtotime($order['date_added'])); ?></td>
				</tr>
				<tr>
					<th>Status</th>
					<td><?php echo $order['internal_status']; ?></td>
				</tr>
				<tr>
					<th>Tracking #</th>
					<td><?php echo $order['tracking_id']; ?></td>
				</tr>
				<tr>
					<th>Shipping Method</th>
					<td><?php echo $order['shipping_method']; ?></td>
				</tr>
				<tr>
					<th>Shipping Cost</th>
					<td><?php echo $order['shipping_cost']; ?></td>
				</tr>
				<tr>
					<th>Comments</th>
					<td><?php echo $order['comments']; ?></td>
				</tr>
				<tr>
					<th>Items</th>
					<td><?php echo $order['items']; ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="span4">
		<?php foreach ($paypal_details as $p): ?>
		<table class="table table-condensed table-hover table-bordered paypal-table">
			<caption>PayPal Details</caption>
			<tbody>
				<tr>
					<th class="span2">Date</th>
					<td><?php echo date('m/d/Y', strtotime($p['date_added'])); ?></td>
				</tr>
				<tr>
					<th>Item Title</th>
					<td><?php echo $p['item_title']; ?></td>
				</tr>
				<tr>
					<th>Item #</th>
					<td><?php echo $p['item_id']; ?></td>
				</tr>
				<tr>
					<th>Gross</th>
					<td>$<?php echo money_format($p['gross'], 2); ?></td>
				</tr>
				<tr class="hide">
					<th>Time</th>
					<td><?php echo date('H:i:s', strtotime($p['date_added'])); ?> UTC</td>
				</tr>
				<tr class="hide">
					<th>Name</th>
					<td><?php echo $p['name']; ?></td>
				</tr>
				<tr class="hide">
					<th>Status</th>
					<td><?php echo $p['status']; ?></td>
				</tr>
				<tr class="hide">
					<th>Net</th>
					<td>$<?php echo money_format($p['net'], 2); ?></td>
				</tr>
				<tr class="hide">
					<th>Fee</th>
					<td>$<?php echo money_format($p['fee'], 2); ?></td>
				</tr>
				<tr class="hide">
					<th>Email</th>
					<td><?php echo $p['email']; ?></td>
				</tr>
				<tr class="hide">
					<th>Transaction #</th>
					<td><?php echo $p['transaction_id']; ?></td>
				</tr>
				<tr class="hide">
					<th>Shipping Cost</th>
					<td>$<?php echo money_format($p['shipping_cost'], 2); ?></td>
				</tr>
				<tr class="hide">
					<th>Receipt #</th>
					<td><?php echo $p['receipt_id']; ?></td>
				</tr>
				<tr class="hide">
					<th>Address Line 1</th>
					<td><?php echo $p['address1']; ?></td>
				</tr>
				<tr class="hide">
					<th>Address Line 2</th>
					<td><?php echo $p['address2']; ?></td>
				</tr>
				<tr class="hide">
					<th>City</th>
					<td><?php echo $p['city']; ?></td>
				</tr>
				<tr class="hide">
					<th>ZIP</th>
					<td><?php echo $p['zip']; ?></td>
				</tr>
				<tr class="hide">
					<th>Country</th>
					<td><?php echo $p['country']; ?></td>
				</tr>
				<tr class="hide">
					<th>Phone</th>
					<td><?php echo $p['phone']; ?></td>
				</tr>
				<tr class="hide">
					<th>Notes</th>
					<td><?php echo $p['notes']; ?></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2" class="text-center">
						<a href="#" class="paypal-table-details" data-status="shown">Show Details</a>
					</td>
				</tr>
			</tfoot>
		</table>
		<?php endforeach; ?>
	</div>
	<div class="span4">
		<table class="table table-condensed table-hover table-bordered">
			<caption>Shipping Details</caption>
			<tbody>
				<tr>
					<th>Name</th>
					<td><?php echo $shipping['name']; ?></td>
				</tr>
				<tr>
					<th>Email</th>
					<td><?php echo $shipping['email']; ?></td>
				</tr>
				<tr>
					<th>Address Line 1</th>
					<td><?php echo $shipping['address1']; ?></td>
				</tr>
				<tr>
					<th>City</th>
					<td><?php echo $shipping['city']; ?></td>
				</tr>
				<tr>
					<th>ZIP</th>
					<td><?php echo $shipping['zip']; ?></td>
				</tr>
				<tr>
					<th>Country</th>
					<td><?php echo $shipping['country']; ?></td>
				</tr>
				<tr>
					<th>Phone</th>
					<td><?php echo $shipping['phone']; ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
