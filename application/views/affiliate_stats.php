<div class="row-fluid">

	<div class="filter-container">
		<form class="form-inline " id="date-range" action="<?php echo URL::base(); ?>affiliatestat" method="get">
			<label>Date Range:</label>
			<input type="text" class="input-small input-date-submit" id="_start-date" placeholder="Start" name="date_start"
				value="<?php echo $date_start; ?>">
			<input type="text" class="input-small input-date-submit" id="_end-date" placeholder="End"  name="date_end"
				value="<?php echo $date_end; ?>">
			<input type="hidden" name="affiliate_id" value="<?php echo $affiliate_id; ?>">
			<input type="hidden" name="campaign" value="<?php echo $campaign; ?>">
			<input type="hidden" name="product" value="<?php echo $product; ?>">		
		</form>
	</div>
	
	<div class="filter-container">
		<form class="form-inline " id="product-filter" action="<?php echo URL::base(); ?>affiliatestat" method="get">
			<input type="hidden" name="affiliate_id" value="<?php echo $affiliate_id; ?>">
			<input type="hidden" name="campaign" value="<?php echo $campaign; ?>">
			<input type="hidden" name="date_start" value="<?php echo $date_start; ?>">
			<input type="hidden" name="date_end" value="<?php echo $date_end; ?>">			
			<label>Product:</label>
			<select name="product" id="product" data-value="<?php echo $product; ?>">
				<option value="">All</option>
				<?php foreach ($products as $p): ?>
				<option value="<?php echo $p; ?>"><?php echo $p; ?></option>
				
				<?php endforeach; ?>
			</select>
		</form>
	</div>
	
	<div class="filter-container">
		<form class="form-inline " id="campaign-filter" action="<?php echo URL::base(); ?>affiliatestat" method="get">
			<input type="hidden" name="affiliate_id" value="<?php echo $affiliate_id; ?>">
			<input type="hidden" name="product" value="<?php echo $product; ?>">
			<input type="hidden" name="date_start" value="<?php echo $date_start; ?>">
			<input type="hidden" name="date_end" value="<?php echo $date_end; ?>">			
			<label>Campaign:</label>
			<select name="campaign" id="campaign" data-value="<?php echo $campaign; ?>">
				<option value="">All</option>
				<?php foreach ($campaigns as $c): ?>
				<option value="<?php echo $c; ?>"><?php echo $c; ?></option>
				
				<?php endforeach; ?>
			</select>
		</form>
	</div>	
</div>

<table class="table table-bordered table-hover table-condensed" id="affiliate-stats-table" >
	<caption>Affiliate Stats for <?php echo  $affiliate_name; ?></caption>
	
	<thead>
		<tr>
			<th>
				Day
			</th>
			<th>
				Unique/Raw Clicks
			</th>
			<th>
				Leads
			</th>
			<th>
				Customers
			</th>
			<th>
				Sales
			</th>
			<th>
				Commission
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($stats as $day => $s): ?>
		<tr>

			<td>
				<?php echo $day; ?>
			</td>
			<td>
				<?php echo $s['unique'] . '/' . $s['raw'] ; ?>
			</td>
			<td>
				<?php echo $s['leads']; ?>
			</td>
			<td>
				<?php echo $s['customers']; ?>
			</td>
			<td>
				$<?php echo number_format($s['sales'], 2); ?>
			</td>
			<td>
				$<?php echo number_format($s['commission'], 2); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>