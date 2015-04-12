<table class="table table-bordered table-hover" id="user-table">
	<caption>Users</caption>
	
	<thead>
		<tr>
			<?php if ($user->type == 'admin'): ?>
			<th>
			</th>
			<?php endif; ?>
			<th>
				Name
			</th>
			<th>
				Login
			</th>
			<th>
				Type
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
		<?php foreach ($users as $u): ?>
		<tr data-user_id="<?php echo $u->id; ?>">
			<?php if ($user->type == 'admin'): ?>
			<td>
				<div class="btn-group">
					<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
						<i class="icon-cog"></i>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="#" class="user-update">Update</a></li>
						<li><a href="#" class="user-delete">Delete</a></li>
					</ul>
				</div>
			</td>
			<?php endif; ?>
			<td>
				<?php echo $u->name; ?>
			</td>
			<td>
				<?php echo $u->login; ?>
			</td>
			<td>
				<?php echo $u->type; ?>
			</td>
			<td>
				<?php echo date('m/d/Y', strtotime($u->date_added)); ?>
			</td>
			<td>
				<?php if ($u->status == 'active'): ?>
				<span class="label label-success">Active</span>
				<?php else: ?>
				<span class="label label-error">Inactive</span>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<div class="modal hide fade" id="user-update-modal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Update User</h3>
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
						<label for="login">Login</label>
						<div class="controls">
							<input type="text" name="login" id="login">
						</div>
					</div>
					<div class="control-group">
						<label for="type">Type</label>
						<div class="controls">
							<select id="type" name="type">
								<option value="admin">Admin</option>
								<option value="staff">Staff</option>
								<option value="guest">Guest</option>
							</select>
						</div>
					</div>
				</div>
				<div class="span6">
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
								<option value="inactive">Inactive</option>
							</select>
						</div>
					</div>
				</div>

			</div>

		</form>

	</div>
	<div class="modal-footer">
		<a href="#" class="btn btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-primary" id="user-update-modal-save">Save Changes</a>
	</div>
</div>	

