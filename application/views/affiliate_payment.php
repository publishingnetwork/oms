
<?php if (!empty($affiliate_id)): ?>
<table class="table table-bordered table-condensed">
	<tr>
		<td class="span4">Total Made</td>
		<td class="span4">Total Paid</td>
		<td class="span4">Total Refunds (commissions)</td>
		<td class="span4">Current Balance</td>
	</tr>
	<tr>
		<td>$<?php echo $total_made; ?></td>
		<td>$<?php echo $total_paid; ?></td>
		<td>$<?php echo $total_refunds; ?></td>
		<td>$<?php echo $current_balance; ?></td>
	</tr>
</table>
<?php endif; ?>


<table class="table table-bordered table-hover" id="affiliate-payment-table" data-affiliate_name="<?php echo $affiliate_name; ?>"
	data-paypal_email="<?php echo $paypal_email; ?>" data-affiliate_id="<?php echo $affiliate_id; ?>">
	<caption>Affiliate Payments <?php echo !empty($affiliate_id) ? (' sent to ' . $affiliate_name ) : ''; ?></caption>
	
	<thead>
		<tr>
			<?php if ($user_type != 'guest'): ?>
			<th>
			</th>
			<?php endif; ?>
			<th>
				Affiliate
			</th>
			<th>
				Date Added
			</th>
			<th>
				PayPal Email
			</th>
			<th>
				Total Paid
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($affiliate_payments as $a_p): ?>
		
		<tr data-affiliate_payment_id="<?php echo $a_p->id; ?>">
			<?php if ($user_type != 'guest'): ?>
			<td>
				<div class="btn-group">
					<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="icon-cog"></i>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
<!-- 						<li><a href="#" class="affiliate-payment-update">Update</a></li> -->
						<li><a href="#" class="affiliate-payment-delete">Delete</a></li>
					</ul>
				</div>
			</td>
			<?php endif; ?>
			
			<td>
				<?php echo $a_p->affiliate; ?>
			</td>
			<td>
				<?php echo date('m/d/Y', strtotime($a_p->date_added)); ?>
			</td>
			<td>
				<?php echo $a_p->paypal_email; ?>
			</td>
			<td>
				$<?php echo number_format($a_p->total, 2); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ($user_type != 'guest'): ?>
<?php if (!empty($affiliate_id)): ?>
<button class="btn btn-success" id="new-payment">New Payment</button>
<?php endif; ?>
<?php endif; ?>

<div class="modal hide fade" id="affiliate-payment-new-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>New Affiliate Payment</h3>
	</div>
	<div class="modal-body">	
		<form action="" method="post">
			<input type="hidden" name="affiliate_id" value="<?php echo !empty($affiliate_id) ? $affiliate_id : ''; ?>"
				id="affiliate_id">
			<input type="hidden" name="id">
			<input type="hidden" name="orders_awaiting_payment" id="orders_awaiting_payment" value="">
			<input type="hidden" name="orders_awaiting_refund" id="orders_awaiting_refund" value="">
			
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<label for="affiliate">Affiliate</label>
						<div class="controls">
							<input type="text" name="affiliate" id="affiliate" disabled="">
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<label for="paypal_email">PayPal email</label>
						<div class="controls">
							<input type="text" name="paypal_email" id="paypal_email">
						</div>
					</div>
				</div>
				
			</div>
			<div class="row-fluid">
				
				<div class="span3">
					<div class="control-group">
						<label for="start-date">Start</label>
						<div class="controls">
							<input type="text" class="input-small input-date" id="start-date">
						</div>
					</div>
				</div>
				
				<div class="span3">
					<div class="control-group">
						<label for="end-date">End</label>
						<div class="controls">
							<input type="text" class="input-small input-date" id="end-date">
						</div>
					</div>
				</div>
				
				<div class="span6">
					<label>&nbsp;</label>
					<button class="btn btn-success" id="calculate-payment">Calculate</button>
				</div>
			</div>
			<hr>
			<div class="row-fluid">
				<div class="span2">
					<div class="control-group">
						<label for="refunds">Refunds:</label>
						<div class="controls">
							<input type="text" name="refunds" id="refunds" class="input-mini" disabled>
						</div>
					</div>
				</div>
				<div class="span2">
					<div class="control-group">
						<label for="commissions">Sales:</label>
						<div class="controls">
							<input type="text" name="commissions" id="commissions" class="input-mini" disabled>
						</div>
					</div>
				</div>
				<div class="span3">
					<div class="control-group">
						<label for="total">Total to Pay:</label>
						<div class="controls">
							<input type="text" name="total" id="total" class="input-mini">
						</div>
					</div>
				</div>
			
			</div>

		</form>

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="affiliate-payment-new-modal-save">Save Changes</a>
	</div>
</div>	

