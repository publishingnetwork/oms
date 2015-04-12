<div id="confirm_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="confirm_modal_label" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="confirm_modal_label"><%= label %></h3>
	</div>
	<div class="modal-body">
		<p class="lead"><%= message %></p>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		<button class="btn btn-primary" id="confirm_modal_yes"><%= yes_button %></button>
	</div>
</div>