<tr>
	<td><%= product_name %></td>
	<td>
		<select class="input-medium status" name="product[<%= product_id %>][status]">
			<option value="default">Default</option>
			<option value="show">Show</option>
			<option value="hide">Hide</option>
		</select>
	</td>
</tr>