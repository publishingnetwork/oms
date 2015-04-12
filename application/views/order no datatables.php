<table class="table table-bordered table-hover" id="order-table">
	<caption>Orders</caption>
	
	<thead>
		<tr>
		
			<th data-name="actions">
			</th>
			<?php //if ($user->type == 'admin'): ?>
			<th  data-name="checkbox">
			</th>
			<?php //endif; ?>
			<th data-name="type">
				Type
			</th>
			<th data-name="date_added">
				Date
			</th>
			<th data-name="item_title">
				PayPal Item
			</th>
			<th data-name="name">
				Name
			</th>
			<th data-name="address">
				Address
			</th>
			<th data-name="city">
				City
			</th>
			<th data-name="zip">
				Zip
			</th>
			<th data-name="state">
				State
			</th>
			<th data-name="country">
				Country
			</th>
			<th data-name="item_id">
				PayPal Item #
			</th>
			<th data-name="email">
				Email
			</th>
			<th data-name="public_id">
				Order #
			</th>
			<th data-name="time_added">
				Time
			</th>
			<th data-name="internal_status">
				OMS Status
			</th>
			<th data-name="fullfillment_status">
				Fullfillment Status
			</th>
			<th data-name="tracking_id">
				Tracking #
			</th>
			<th  data-name="shipping_method">
				Shipping Method
			</th>
			<th  data-name="fullfillment_id">
				Fullfillment #
			</th>
			<th  data-name="comments">
				Comments
			</th>
			<th  data-name="gross">
				Gross
			</th>
			<th  data-name="fee">
				Fee
			</th>
			<th  data-name="net">
				Net
			</th>
			<th  data-name="shipping_cost">
				Shipping Cost
			</th>
		</tr>
	</thead>
	<tbody>
	
		<?php foreach ($order_rows as $o_r): ?>
		<?php $type = empty($o_r['type']) ? 'paypal' : $o_r['type']; ?>
		
		<tr data-order_id="<?php echo $o_r['order_id']; ?>" data-public_id="<?php echo $o_r['public_id']; ?>" data-fullfillment_id="<?php echo !empty($o_r['fullfillment_id']) ? $o_r['fullfillment_id'] : ''; ?>" data-specific_id="<?php echo $o_r['id']; ?>" data-type="<?php echo $type; ?>">
			<td>
				<div class="btn-group">
					<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="icon-cog"></i>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<?php if ($user->type == 'admin'): ?>
						<li><a href="#" class="order-send">Send for Fullfillment</a></li>
						<?php endif; ?>
						
						<li><a href="#" class="order-details">Details</a></li>
						<li><a href="#" class="order-history">Order History</a></li>
						
						<?php if (in_array($user->type, array('admin', 'staff'))): ?>
						<li><a href="#" class="order-address-update">Update Address</a></li>
						<li><a href="#" class="order-details-update">Update Details</a></li>
						<li><a href="#" class="order-address-fix">Fix Address</a></li>
						<li><a href="#" class="order-merge">Merge</a></li>
						<?php endif; ?>
						
						<?php if ($user->type == 'admin' && $type != 'paypal'): ?>
						<li><a href="#" class="order-delete" data-type="<?php $type; ?>">Delete</a></li>
						<?php endif; ?>
					</ul>
				</div>
			</td>
			<?php //if ($user->type == 'admin'): ?>
			<td>
				
				
				<?php if ($type != 'paypal' || ($type == 'paypal' && !empty($o_r['checkbox']))): ?>
				<input type="checkbox" class="send-checkbox">
				<?php endif; ?>
			</td>
			<?php //endif; ?>
			<td class="type">
				<?php echo $type;
				 ?>
			</td>
			<td>
				<?php echo date('m/d/Y', strtotime($o_r['date_added'])); ?>
			</td>
			<td>
				<?php echo !empty($o_r['item_title']) ? $o_r['item_title'] : ''; ?>
			</td>
			<td>
				<?php 
					if (preg_match('/_MISMATCH$/', $o_r['name'])) {
						$name = str_replace('_MISMATCH', '', $o_r['name']) . ' <span class="label label-warning show-tooltip" title="Mismatch"><i class="icon-exclamation-sign icon-white"></i></span>';
						
						if (in_array($user->type, array('admin', 'staff'))) {
							$name .= ' <input type="checkbox" class="fix-address" data-field="name" data-type="' . $type . '" data-id="' . $o_r['id'] . '">';
						}
					} else {
						$name = $o_r['name'];
					}
				echo $name; 
				?>
			</td>
			<td>
				<?php 
					if (preg_match('/_MISMATCH$/', $o_r['address1'])) {
						$o_r['address1'] .= empty($o_r['type']) && !empty($o_r['address2']) ? $o_r['address2'] : '';
					
						$address = str_replace('_MISMATCH', '', $o_r['address1']) . ' <span class="label label-warning show-tooltip" title="Mismatch"><i class="icon-exclamation-sign icon-white"></i></span>';
						
						if (in_array($user->type, array('admin', 'staff'))) {
							$address .= ' <input type="checkbox" class="fix-address" data-field="address1" data-type="' . $type . '" data-id="' . $o_r['id'] . '">';
						}
						
					} else {
						$address = $o_r['address1'];
					}
				echo $address; 
				?>
			</td>
			<td>
				<?php 
					if (preg_match('/_MISMATCH$/', $o_r['city'])) {
					
						$city = str_replace('_MISMATCH', '', $o_r['city']) . ' <span class="label label-warning show-tooltip" title="Mismatch"><i class="icon-exclamation-sign icon-white"></i></span>';
						
						if (in_array($user->type, array('admin', 'staff'))) {
							$city .= ' <input type="checkbox" class="fix-address" data-field="city" data-type="' . $type . '" data-id="' . $o_r['id'] . '">';
						}
					} else {
						$city = $o_r['city'];
					}
				echo $city; 
				?>
			</td>
			<td>
				<?php 
					if (preg_match('/_MISMATCH$/', $o_r['zip'])) {
					
						$zip = str_replace('_MISMATCH', '', $o_r['zip']) . ' <span class="label label-warning show-tooltip" title="Mismatch"><i class="icon-exclamation-sign icon-white"></i></span>';
						
						if (in_array($user->type, array('admin', 'staff'))) {
							$zip .= ' <input type="checkbox" class="fix-address" data-field="zip" data-type="' . $type . '" data-id="' . $o_r['id'] . '">';
						}
					} else {
						$zip = $o_r['zip'];
					}
				echo $zip; 
				?>
			</td>
			<td>
				<?php 
					if (preg_match('/_MISMATCH$/', $o_r['state'])) {
					
						$state = str_replace('_MISMATCH', '', $o_r['state']) . ' <span class="label label-warning show-tooltip" title="Mismatch"><i class="icon-exclamation-sign icon-white"></i></span>';
						
						if (in_array($user->type, array('admin', 'staff'))) {
							$state .= ' <input type="checkbox" class="fix-address" data-field="state" data-type="' . $type . '" data-id="' . $o_r['id'] . '">';
						}
					} else {
						$state = $o_r['state'];
					}
				echo $state; 
				?>
			</td>
			<td>
				<?php 
					if (preg_match('/_MISMATCH$/', $o_r['country'])) {
					
						$country = str_replace('_MISMATCH', '', $o_r['country']) . ' <span class="label label-warning show-tooltip" title="Mismatch"><i class="icon-exclamation-sign icon-white"></i></span>';
						
						if (in_array($user->type, array('admin', 'staff'))) {
							$country .= ' <input type="checkbox" class="fix-address" data-field="country" data-type="' . $type . '" data-id="' . $o_r['id'] . '">';
						}
					} else {
						$country = $o_r['country'];
					}
				echo $country; 
				?>
			</td>
			<td>
				<?php echo !empty($o_r['item_id']) ? $o_r['item_id'] : ''; ?>
			</td>
			<td>
				<?php 
					if (preg_match('/_MISMATCH$/', $o_r['email'])) {
					
						$email = str_replace('_MISMATCH', '', $o_r['email']) . ' <span class="label label-warning show-tooltip" title="Mismatch"><i class="icon-exclamation-sign icon-white"></i></span>';
						
						if (in_array($user->type, array('admin', 'staff'))) {
							$email .= ' <input type="checkbox" class="fix-address" data-field="email" data-type="' . $type . '" data-id="' . $o_r['id'] . '">';
						}
					} else {
						$email = $o_r['email'];
					}
				echo $email; 
				?>
			</td>
			<td>
				<?php echo !empty($o_r['public_id']) ? $o_r['public_id'] : ''; ?>
			</td>
			<td>
				<?php echo date('H:i:s', strtotime($o_r['date_added'])); ?> UTC
			</td>
			<td>
				<?php 
				
				if ($o_r['internal_status'] == 'email') {
					$label = 'info';
				} elseif ($o_r['internal_status'] == 'new') {
					$label = 'info';
				} elseif ($o_r['internal_status'] == 'processed') {
					$label = 'success';
				} elseif ($o_r['internal_status'] == 'pending') {
					$label = 'warning';
				} elseif ($o_r['internal_status'] == 'cancelled') {
					$label = 'important';
				}
				
				?>
				<span class="label label-<?php echo $label; ?>"><?php echo $o_r['internal_status']; ?></span>
			</td>
			<td>
				<?php echo !empty($o_r['fullfillment_status']) ? $o_r['fullfillment_status'] : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['tracking_id']) ? $o_r['tracking_id'] : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['shipping_method']) ? $o_r['shipping_method'] : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['fullfillment_id']) ? $o_r['fullfillment_id'] : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['comments']) ? $o_r['comments'] : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['gross']) ? '$' . money_format($o_r['gross'], 2) : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['fee']) ? '$' . money_format($o_r['fee'], 2) : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['net']) ? '$' . money_format($o_r['net'], 2) : ''; ?>
			</td>
			<td>
				<?php echo !empty($o_r['shipping_cost']) ? '$' . money_format($o_r['shipping_cost'], 2) : ''; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>



<button class="btn btn-info hide" id="choose-columns">Show/Hide Columns</button>

<?php if ($user->type == 'admin'): ?>
<button class="btn btn-primary" id="submit-selected">Submit Selected for Fullfillment</button>

<?php endif; ?>				
<button class="btn btn-warning" id="csv-export">CSV Export</button>
				
<table class="table table-bordered" id="product-quantities">
	<tr>
		<?php foreach ($product_quantities as $name => $q) : ?>
		<th><?php echo $name; ?></th>
		<?php endforeach; ?>
	</tr>
	<tr>
		<?php foreach ($product_quantities as $name => $q) : ?>
		<td><?php echo $q; ?></td>
		<?php endforeach; ?>
	</tr>
</table>				
				
<div class="modal hide fade" id="columns-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Choose columns to hide</h3>
	</div>
	<div class="modal-body">
		<div class="row-fluid">
		
	<form action="<?php echo URL::base(); ?>user/save_hidden_columns" method="post">
				<div class="span6">
					<label class="checkbox">
						<input type="checkbox" value="type" name="type"> Type
					</label>
					<label class="checkbox">
						<input type="checkbox" value="public_id" name="public_id"> Order #
					</label>
					<label class="checkbox">
						<input type="checkbox" value="date_added" name="date_added"> Date
					</label>
					<label class="checkbox">
						<input type="checkbox" value="time_added" name="time_added"> Time
					</label>
					<label class="checkbox">
						<input type="checkbox" value="internal_status" name="internal_status"> Status
					</label>
					<label class="checkbox">
						<input type="checkbox" value="email" name="email"> Email
					</label>
					<label class="checkbox">
						<input type="checkbox" value="name" name="name"> Name
					</label>
					<label class="checkbox">
						<input type="checkbox" value="address" name="address"> Address
					</label>
					<label class="checkbox">
						<input type="checkbox" value="comments" name="comments"> Comments
					</label>
					<label class="checkbox">
						<input type="checkbox" value="item_title" name="item_title"> Paypal Item Name
					</label>
					<label class="checkbox">
						<input type="checkbox" value="country" name="country"> Country
					</label>
					<label class="checkbox">
						<input type="checkbox" value="zip" name="zip"> Zip
					</label>
				</div>
				<div class="span6">
					<label class="checkbox">
						<input type="checkbox" value="city" name="city"> City
					</label>
					<label class="checkbox">
						<input type="checkbox" value="state" name="state"> State
					</label>
					<label class="checkbox">
						<input type="checkbox" value="tracking_id" name="tracking_id"> Tracking #
					</label>
					<label class="checkbox">
						<input type="checkbox" value="gross" name="gross"> Gross
					</label>
					<label class="checkbox">
						<input type="checkbox" value="fee" name="fee"> Fee
					</label>
					<label class="checkbox">
						<input type="checkbox" value="net" name="net"> Net
					</label>
					<label class="checkbox">
						<input type="checkbox" value="shipping_cost" name="shipping_cost"> Shipping Cost
					</label>
					<label class="checkbox">
						<input type="checkbox" value="shipping_method" name="shipping_method"> Shipping Method
					</label>
					<label class="checkbox">
						<input type="checkbox" value="item_id" name="item_id"> Paypal Item #
					</label>
					<label class="checkbox">
						<input type="checkbox" value="fullfillment_status" name="fullfillment_status"> Fullfillment Status
					</label>
					<label class="checkbox">
						<input type="checkbox" value="fullfillment_id" name="fullfillment_id"> Fullfillment #
					</label>
				</div>
			</form>
		</div>


	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary modal-submit">Save changes</a>
	</div>
</div>

<div class="modal hide fade" id="order-history-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Order History</h3>
	</div>
	<div class="modal-body">	

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
	</div>
</div>

<div class="modal hide fade" id="order-details-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Order Details</h3>
	</div>
	<div class="modal-body">	
	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
	</div>
</div>

<div class="modal hide fade" id="order-confirm-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Please confirm</h3>
	</div>
	<div class="modal-body">
		<div class="alert alert-warning hide" id="already-sent-warning">This order has already been sent for fullfullment!</div>
		<div class="lead"></div>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">No</a>
		<a href="#" class="btn btn-primary" id="order-confirm-yes" data-order_id="">Yes</a>
	</div>
</div>


<div class="modal hide fade" id="order-multi-fullfillment-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Please confirm</h3>
	</div>
	<div class="modal-body">
	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">No</a>
		<a href="#" class="btn btn-primary" id="order-multi-fullfillment-yes" data-order_id="">Yes</a>
	</div>
</div>

<div class="modal hide fade" id="order-address-update-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Update Address</h3>
	</div>
	<div class="modal-body">	
		<form action="" method="post">
			<input type="hidden" name="type">
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
						<label for="email">Email</label>
						<div class="controls">
							<input type="text" name="email" id="email">
						</div>
					</div>
					<div class="control-group">
						<label for="phone">Phone</label>
						<div class="controls">
							<input type="text" name="phone" id="phone">
						</div>
					</div>
					<div class="control-group">
						<label for="address1">Address</label>
						<div class="controls">
							<input type="text" name="address1" id="address1">
						</div>
					</div>
					<div class="control-group">
						<label for="address2">Address 2</label>
						<div class="controls">
							<input type="text" name="address2" id="address2">
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="control-group">
						<label for="city">City</label>
						<div class="controls">
							<input type="text" name="city" id="city">
						</div>
					</div>
					<div class="control-group">
						<label for="state">State</label>
						<div class="controls">
							<input type="text" name="state" id="state">
						</div>
					</div>
					<div class="control-group">
						<label for="zip">ZIP</label>
						<div class="controls">
							<input type="text" name="zip" id="zip">
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
			
			</div>
		


		</form>

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="order-address-update-modal-save">Save Changes</a>
	</div>
</div>	

<?php
$list = array();
foreach ($products as $p) {
	$list[] = $p->name;
} 
?>
<input type="hidden" name="products" id="available-products" value="<?php echo implode(',', $list); ?>">

<div class="modal hide fade" id="order-new-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>New Order</h3>
	</div>
	<div class="modal-body">	
		<form action="" method="post">
			<div class="row-fluid">
				<div class="span4">
					<div class="control-group">
						<label for="public_id">Order # <small>(leave empty to auto assign)</small></label>
						<div class="controls">
							<input type="text" name="public_id" id="public_id">
						</div>
					</div>
					<div class="control-group">
						<label for="name">Name</label>
						<div class="controls">
							<input type="text" name="name" id="name">
						</div>
					</div>
					<div class="control-group">
						<label for="email">Email</label>
						<div class="controls">
							<input type="text" name="email" id="email">
						</div>
					</div>
					<div class="control-group">
						<label for="phone">Phone</label>
						<div class="controls">
							<input type="text" name="phone" id="phone">
						</div>
					</div>
					<div class="control-group">
						<label for="address1">Address</label>
						<div class="controls">
							<input type="text" name="address1" id="address1">
						</div>
					</div>
				</div>
				<div class="span4">
					<div class="control-group">
						<label for="city">City</label>
						<div class="controls">
							<input type="text" name="city" id="city">
						</div>
					</div>
					<div class="control-group">
						<label for="state">State</label>
						<div class="controls">
							<input type="text" name="state" id="state">
						</div>
					</div>
					<div class="control-group">
						<label for="zip">ZIP</label>
						<div class="controls">
							<input type="text" name="zip" id="zip">
						</div>
					</div>
					<div class="control-group">
						<label for="country">Country</label>
						<div class="controls">
							<select name="country" id="country">
								<?php foreach ($countries as $c): ?>
								<option value="<?php echo $c; ?>"><?php echo $c; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label for="shipping_method">Shipping Method</label>
						<div class="status">
							<select name="shipping_method" id="shipping_method">
								<option value="">Not Selected</option>
								<option value="FED1">FedEx Next Day</option>
								<option value="FED2">FedEx 2 Day </option>
								<option value="FEDG">FedEx Ground</option>
								<option value="FCD">USPS Domestic First Class Mail Delivery Confirmation</option>
								<option value="FCS">USPS Domestic First Class Mail Signature Required</option>
								<option value="PMD">USPS Domestic Priority Mail Delivery Confirmation</option>
								<option value="PMS">USPS Domestic Priority Mail Signature Required</option>
								<option value="EM">USPS Domestic Express Mail</option>
								<option value="GPM">USPS International Mail (no trackability)</option>
								<option value="PMI">USPS International Priority Mail</option>
								<option value="GEM">USPS International Express Mail</option>
							</select>
						</div>
					</div>
				</div>
				<div class="span4">
					<input type="hidden" name="items">
					

					<div class="control-group">
						<label for="item">Add Item</label>
						<div class="controls">
							<input type="text" name="item" class="item-typeahead">
						</div>
					</div>
					
					<div class="control-group">
						<label>Chosen Items:</label>
						<div class="controls">
							<ul class="chosen-items-list">
<!-- 								<li>Energize Greens <button class="btn btn-mini item-remove"><i class="icon-trash"></i></button></li> -->
							</ul>
						</div>
					</div>
				</div>
			</div>

		</form>

		</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="order-new-modal-save">Save Order</a>
	</div>
</div>	


<div class="modal hide fade" id="order-details-update-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Update Details</h3>
	</div>
	<div class="modal-body">	
		<form action="" method="post">
			<input type="hidden" name="id">
			
			<div class="row-fluid">
				<div class="span8">
				
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<label for="public_id">Order #</label>
								<div class="controls">
									<input type="text" name="public_id" id="public_id">
								</div>
							</div>
						</div>
						<div class="span6">
							<div class="control-group">
								<label for="internal_status">Status</label>
								<div class="internal_status">
									<select name="internal_status" id="internal_status">
										<option value="email">Email</option>
										<option value="processed">Processed</option>
										<option value="new">New</option>
										<option value="pending">Pending</option>
										<option value="cancelled">Cancelled</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="control-group">
								<label for="comments">Comments</label>
								<div class="status">
									<textarea name="comments" id="comments" class="input-block-level"></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="control-group">
								<label for="shipping_method">Shipping Method</label>
								<div class="status">
									<select name="shipping_method" id="shipping_method" class="input-block-level">
										<option value="">Not Selected</option>
										<option value="FED1">FedEx Next Day</option>
										<option value="FED2">FedEx 2 Day </option>
										<option value="FEDG">FedEx Ground</option>
										<option value="FCD">USPS Domestic First Class Mail Delivery Confirmation</option>
										<option value="FCS">USPS Domestic First Class Mail Signature Required</option>
										<option value="PMD">USPS Domestic Priority Mail Delivery Confirmation</option>
										<option value="PMS">USPS Domestic Priority Mail Signature Required</option>
										<option value="EM">USPS Domestic Express Mail</option>
										<option value="GPM">USPS International Mail (no trackability)</option>
										<option value="PMI">USPS International Priority Mail</option>
										<option value="GEM">USPS International Express Mail</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<label for="tracking_id">Tracking #</label>
								<div class="controls">
									<input type="text" name="tracking_id" id="tracking_id">
								</div>
							</div>
						</div>
						<div class="span6">
							<div class="control-group">
								<label for="fullfillment_id">Fullfillment #</label>
								<div class="controls">
									<input type="text" name="fullfillment_id" id="fullfillment_id">
								</div>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<div class="control-group">
								<label for="fullfillment_status">Fullfillment Status</label>
								<div class="controls">
									<input type="text" name="fullfillment_status" id="fullfillment_status">
								</div>
							</div>
						</div>
		
					</div>
				</div>
				<div class="span4">

					<input type="hidden" name="items">
					

					<div class="control-group">
						<label for="item">Add Item</label>
						<div class="controls">
							<input type="text" name="item" class="item-typeahead">
						</div>
					</div>
					
					<div class="control-group">
						<label>Chosen Items:</label>
						<div class="controls">
							<ul class="chosen-items-list">
<!-- 								<li>Energize Greens <button class="btn btn-mini item-remove"><i class="icon-trash"></i></button></li> -->
							</ul>
						</div>
					</div>

				</div>
			</div>
		</form>

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="order-details-update-modal-save">Save Changes</a>
	</div>
</div>		


<div class="modal hide fade" id="order-merge-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Merge orders</h3>
	</div>
	<div class="modal-body">
		<p>Choose the order # you want to merge this order with.</p>
		<form action="" method="post">
			<input type="hidden" name="child_order_id" id="child_order_id">
			
			<div class="control-group">
				<label for="parent_order_id">Order #</label>
				<div class="status">
					<select name="parent_order_id" id="parent_order_id" class="input-block-level">

					</select>
				</div>
			</div>

		</form>

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="order-merge-modal-save">Save Changes</a>
	</div>
</div>

<form class="form-inline hide" id="date-range">
	<label>Date Range:</label>
	<input type="text" class="input-small input-date" id="start-date" placeholder="Start">
	<input type="text" class="input-small input-date" id="end-date" placeholder="End">
</form>


<ul class="nav nav-pills hide" id="type-filter">
	<li class="active">
		<a href="#" id="show-all">All</a>
	</li>
	<li><a href="#" id="show-paypal">PayPal</a></li>
	<li><a href="#" id="show-unprocessed">Unprocessed</a></li>
</ul>

<script type="text/javascript">
	<?php

		if ($user->hidden_columns) {
			$columns = explode(',', $user->hidden_columns); 
	
			array_walk($columns, function($value, $index) use (&$columns) {
	
				$columns[$index] = "'" . $value . "'";
	
			});
		}
	?>

	var hidden_columns = [<?php echo isset($columns) ? implode(', ', $columns) : ''; ?>];

</script>