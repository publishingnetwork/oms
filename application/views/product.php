<table class="table table-bordered table-hover" id="product-table">
	<caption>Products</caption>
	
	<thead>
		<tr>
			<?php if (in_array($user->type, array('admin', 'staff'))): ?>
			<th>
			</th>
			<?php endif; ?>
			<th>
				Name
			</th>
			<th>
				OMS Product
			</th>
			<th>
				PayPal #
			</th>
			<th>
				SKU
			</th>
			<th>
				Default Commission
			</th>
			<th>
				Default Int. Commission
			</th>			
			<th>
				URLs
			</th>
			<th>
				Affiliate Status
			</th>
			<th>
				Fulfilment Center
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($products as $product): ?>
		<tr data-product_id="<?php echo $product['id']; ?>">
			<?php if (in_array($user->type, array('admin', 'staff'))): ?>
			<td>
				<div class="btn-group">
					<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="icon-cog"></i>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="#" class="product-update">Update</a></li>
						<li><a href="#" class="product-delete">Delete</a></li>
					</ul>
				</div>
			</td>
			<?php endif; ?>
			<td>
				<?php echo $product['actual_name']; ?>
			</td>
			<td>
				<?php echo $product['name']; ?>
			</td>
			<td>
				<?php echo str_replace(',', ', ', $product['paypal_ids']); ?>
			</td>
			<td>
				<?php foreach ($product['skus'] as $s): ?>
					<span class="label show-tooltip" title="Quantity: <?php echo $s['quantity']; ?>"><?php echo $s['sku']; ?></span>
				<?php endforeach; ?>
			</td>
			<td>
				<?php echo $product['default_commission']; ?>%
			</td>
			<td>
				<?php echo $product['default_int_commission']; ?>%
			</td>			
			<td>
				<?php echo preg_replace('/,[ ]*/', '<br>', $product['urls']); ?>
			</td>
			<td>
				<?php echo ucfirst($product['affiliate_status']); ?>
			</td>
			<td>
				<?php echo ucfirst($product['fulfilment_center']); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>


<div class="modal hide fade" id="product-update-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Update Product</h3>
	</div>
	<div class="modal-body">	
		<form action="" method="post">
			<input type="hidden" name="id">
			
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<label for="actual_name">Actual Name</label>
						<div class="controls">
							<input type="text" name="actual_name" id="actual_name">
						</div>
					</div>
				
					<div class="control-group">
						<label for="name">OMS Product</label>
						<div class="controls">
							<input type="text" name="name" id="name">
						</div>
					</div>
					<div class="control-group">
						<label for="paypal_ids">PayPal # <small class="info">separate with comma</small></label>
						<div class="controls">
							<input type="text" name="paypal_ids" id="paypal_ids">
						</div>
					</div>
					<div class="control-group">
						<label for="default_commission">Default Commission</label>
						<div class="controls">
							<input type="text" name="default_commission" id="default_commission">
						</div>
					</div>
					<div class="control-group">
						<label for="default_int_commission">Default Int.Commission</label>
						<div class="controls">
							<input type="text" name="default_int_commission" id="default_int_commission">
						</div>
					</div>					
					<div class="control-group">
						<label for="urls">URLs <small>Separate with comma</small></label>
						<div class="controls">
							<textarea name="urls" id="urls" class="input-block-level"></textarea>
						</div>
					</div>
					<div class="control-group">
						<label for="url">Affiliate Status</label>
						<div class="controls">
							<select name="affiliate_status" id="affiliate_status">
								<option value="hide">Hide</option>
								<option value="show">Show</option>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label for="fulfilment_center">Fulfilment Center</label>
						<div class="controls">
							<select name="fulfilment_center" id="fulfilment_center">
								<option value="US">US</option>
								<option value="AUS">AUS</option>
							</select>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<label for="">SKUs</label>
						<input type="hidden" name="skus">
						<span class="skus">
							<div class="controls controls-row">
								<span class="sku">
									<button class="span1 btn sku-delete"><i class="icon-trash"></i></button>
									<input type="text" class="span4 sku-name" placeholder="SKU">
	
									<input type="text" class="span4 sku-quantity" placeholder="Quantity">
								</span>
							</div>
						</span>
						<button id="new-sku" class="btn btn-mini">New SKU</button>
					</div>
				</div>

			</div>

		</form>

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="product-update-modal-save">Save Changes</a>
	</div>
</div>	