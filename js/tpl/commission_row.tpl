<tr>
	<td><%= product_name %></td>
	<td>
		<select class="commission-type input-medium" name="product[<%= product_id %>][type]">
			<option value="default">Default</option>
			<option value="custom">Custom</option>
		</select>
	</td>
	<td>
		<div class="input-append">
			<input type="text" class="commission input-small" name="product[<%= product_id %>][commission]" value="<%= commission %>"
				data-default_commission="<%= default_commission %>">
			<span class="add-on">%</span>
		</div>
	</td>
</tr>