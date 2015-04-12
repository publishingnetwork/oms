<table class="table table-bordered table-hover" id="affiliate-table">
	<caption>Affiliates</caption>
	
	<thead>
		<tr>
			<?php if ($user_type != 'guest'): ?>
			<th>
			</th>
			<?php endif; ?>
			
			<th>
				Name
			</th>
			<th>
				Business Name
			</th>
			<th>
				Website
			</th>
			<th>
				Country
			</th>
			<th>
				Login
			</th>
			<th>
				Date Added
			</th>
			<th>
				Status
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($affiliates as $a): ?>
		<tr data-affiliate_id="<?php echo $a->id; ?>">
			<?php if ($user_type != 'guest'): ?>
			<td>
				<div class="btn-group">
					<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="icon-cog"></i>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo URL::base(); ?>affiliatestat?affiliate_id=<?php echo $a->id; ?>">Stats</a></li>
						<li><a href="<?php echo URL::base(); ?>affiliatepayment?affiliate_id=<?php echo $a->id; ?>#new-payment">New Payment</a></li>
						<li><a href="<?php echo URL::base(); ?>order?affiliate=<?php echo $a->id; ?>">Orders</a></li>
						<li><a href="<?php echo URL::base(); ?>affiliatepayment?affiliate_id=<?php echo $a->id; ?>">Payments</a></li>
						<li><a href="#" class="affiliate-commissions" data-type="normal">Commissions</a></li>
						<li><a href="#" class="affiliate-commissions" data-type="int">Int. Commissions</a></li>
						<li><a href="#" class="affiliate-products">Products</a></li>
						<li><a href="#" class="affiliate-update">Update</a></li>
						<li><a href="#" class="affiliate-delete">Delete</a></li>
					</ul>
				</div>
			</td>
			<?php endif; ?>
			
			<td>
				<?php echo $a->name; ?>
			</td>
			<td>
				<?php echo $a->business_name; ?>
			</td>
			<td>
				<?php echo $a->website; ?>
			</td>
			<td>
				<?php echo $a->country; ?>
			</td>
			<td>
				<?php echo $a->login; ?>
			</td>
			<td>
				<?php echo date('m/d/Y', strtotime($a->date_added)); ?>
			</td>
			<td>
				<?php if ($a->status == 'active'): ?>
				<span class="label label-success">Active</span>
				<?php else: ?>
				<span class="label label-warning">Pending</span>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<div class="modal hide fade" id="affiliate-update-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Update Affiliate</h3>
	</div>
	<div class="modal-body">	
		<form action="" method="post">
			<input type="hidden" name="id">
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<label for="name">Name</label>
						<div class="controls">
							<input type="text" name="name" id="name">
						</div>
					</div>
					<div class="control-group">
						<label for="business_name">Business Name</label>
						<div class="controls">
							<input type="text" name="business_name" id="business_name">
						</div>
					</div>
					<div class="control-group">
						<label for="login">Login</label>
						<div class="controls">
							<input type="text" name="login" id="login">
						</div>
					</div>
					<div class="control-group">
						<label for="email">Email</label>
						<div class="controls">
							<input type="text" name="email" id="email">
						</div>
					</div>
					<div class="control-group">
						<label for="country">Country</label>
						<div class="controls">
							<select name="country" id="country">
								<?php foreach ($countries as $a => $f): ?>
								<option value="<?php echo $a; ?>"><?php echo $f; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<label for="paypal_email">PayPal Email</label>
						<div class="controls">
							<input type="text" name="paypal_email" id="paypal_email">
						</div>
					</div>
					<div class="control-group">
						<label for="website">Website</label>
						<div class="controls">
							<input type="text" name="website" id="website">
						</div>
					</div>
					<div class="control-group">
						<label for="password">Password <small>leave empty if no change</small></label>
						<div class="controls">
							<input type="password" name="password" id="password">
						</div>
					</div>
					<div class="control-group">
						<label for="status">Status</label>
						<div class="controls">
							<select id="status" name="status">
								<option value="active">Active</option>
								<option value="pending">Pending</option>
							</select>
						</div>
					</div>
				</div>

			</div>

		</form>

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="affiliate-update-modal-save">Save Changes</a>
	</div>
</div>	


<div class="modal hide fade" id="affiliate-commissions-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Update Affiliate Commissions</h3>
	</div>
	<div class="modal-body">	
		<form action="<?php echo URL::base();?>affiliate/commissions_save">
			<input type="hidden" name="affiliate_id" value="">
			<input type="hidden" name="type" value="">
			
			<table class="table table-bordered">				
				<thead>
					<tr>
						<th>
							Product Name
						</th>
						<th>
							Commission Type
						</th>
						<th>
							Commission
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>

					</tr>
				
				</tbody>
				
			</table>
			
		</form>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="affiliate-commissions-modal-save">Save Changes</a>
	</div>
</div>

<div class="modal hide fade" id="affiliate-products-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Update Affiliate Products</h3>
	</div>
	<div class="modal-body">	
		<form action="<?php echo URL::base();?>affiliate/products_save">
			<input type="hidden" name="affiliate_id" value="">
			
			<table class="table table-bordered">				
				<thead>
					<tr>
						<th>
							Product Name
						</th>
						<th>
							Status
						</th>
					</tr>
				</thead>
				<tbody>
					<tr>

					</tr>
				
				</tbody>
				
			</table>
			
		</form>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="affiliate-products-modal-save">Save Changes</a>
	</div>
</div>
