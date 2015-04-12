<?php if (empty($reports)): ?>
<div class="row-fluid">
	<div class="span6 offset3">
		<center><h3>Run report</h3></center>
		<form class="form-horizontal" method="GET">
			<div class="control-group">
				<label class="control-label" for="start_date"><b>Date</b></label>
				<div class="controls">
					<select name="start_date" id="start_date">
						<?php foreach ($dates as $d => $v): ?>
						<option value="<?php echo $v; ?>"><?php echo $d; ?></option>
						<?php endforeach; ?>
						<option value="last_6_months">Last 6 Months</option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="type"><b>Type</b></label>
				<div class="controls">
					<select name="type" id="type">
						<option value="sales">Sales</option>
						<option value="affiliate_breakdown">Affiliate Breakdown</option>
					</select>
				</div>
			</div>
			
			<div class="control-group">
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" name="regenerate" value="yes"> Run again if already cached.
					</label>
					<button type="submit" class="btn">Run</button>
				</div>
			</div>			
			
		</form>
	
	</div>
</div>


<?php else: //if (empty($report)): ?>
	
	<?php if ($type == 'sales'): ?>
		<?php foreach ($reports as $date => $report): ?>
		<h3>Report for <?php echo $date; ?></h3>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>SKU</th>
					<th>Quantity</th>
				</tr>
			</thead>
			<tbody>	
				<?php foreach ($report['skus'] as $sku => $q): ?>
				<tr>
					<td>
						<?php echo $sku; ?>
					</td>
					<td>
						<?php echo $q; ?>
					</td>			
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Total Bottles</th>
					<th>Total Orders</th>
					<th>Total Sales</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo $report['totals']['bottles']; ?></td>
					<td><?php echo $report['totals']['orders']; ?></td>
					<td>$<?php echo $report['totals']['sales']; ?></td>
				</tr>
			</tbody>
		</table>
		<?php endforeach; ?>
	
	<?php else: //if ($type == 'sales'): ?>
	
		<?php foreach ($reports as $date => $report): ?>
			<h3>Report for <?php echo $date; ?></h3>
			<?php foreach ($report as $affiliate => $data): ?>
			
				
				<h4><?php echo $affiliate; ?></h4>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>SKU</th>
							<th>Quantity</th>
						</tr>
					</thead>
					<tbody>	
						<?php foreach ($data['skus'] as $sku => $q): ?>
						<tr>
							<td>
								<?php echo $sku; ?>
							</td>
							<td>
								<?php echo $q; ?>
							</td>			
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Total Bottles</th>
							<th>Total Orders</th>
							<th>Total Sales</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $data['totals']['bottles']; ?></td>
							<td><?php echo $data['totals']['orders']; ?></td>
							<td>$<?php echo $data['totals']['sales']; ?></td>
						</tr>
					</tbody>
				</table>
				<hr>
				<br>
			<?php endforeach; //foreach ($report as $affiliate => $data):  ?>
		<?php endforeach; //foreach ($reports as $date => $report): ?>
		
	<?php endif; //if ($type == 'sales'): ?>


<?php endif; //if (empty($report)): ?>