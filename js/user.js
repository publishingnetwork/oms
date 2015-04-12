$(document).ready(function() {


	$('.user-update').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		clean_new_user_form();
		
		$('#user-update-modal input[name="id"]').val($tr.data('user_id'));
		
		$('#user-update-modal .modal-header h3').text('Update user');
		
		$.ajax({
		   url      : base_path + 'user/get/' + $tr.data('user_id'),
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {

			   if (response.status == 'success') {
				    $.each(response.data, function(index, value) {
						$('#user-update-modal [name="' + index + '"]').val(value);
	
				    });
				    
				    $('#user-update-modal').modal('show');
			   } else {
				  js_message('error', 'An error occured, please try again!'); 
			   }

		    },
		   error    : function () {
		    	$('#user-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	    
		//$('#user-update-modal').modal('show');
	});
	
	$('.user-delete').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		js_confirm({
			label      : 'Confirm deleting user',
			message    : 'Are you sure you want to delete this user?',
			yes_button : 'Confirm'
		}, function () {
			$.ajax({
			   url      : base_path + 'user/delete/' + $tr.data('user_id'),
			   dataType : 'json',
			   method   : 'GET',
			   success  : function (response) {
			   
			   	   if (response.status == 'success') {
				   	   js_message('success', 'User deleted!');
			       
				       setTimeout(function() {
					      location.reload(); 
				       }, 300);
			   	   } else {
				   	   js_message('error', 'An error occured, please try again!');
			   	   }
				   
			    },
			   error    : function () {
				    js_message('error', 'An error occured, please try again!');
			    }
		    });
		});
	});
	
	$('.user-new').click(function(e) {
		e.preventDefault();
		new_user();

	});

	if (document.location.hash.substr(1) == 'new-user') {
		new_user();
	}

	function clean_new_user_form() {
		$('#user-update-modal input').each(function() {
			$(this).val('');
		});
	}

	function new_user() {
		$('#user-update-modal .modal-header h3').text('New user');
	
		clean_new_user_form();
		
		$('#user-update-modal').modal('show');
	}
		
	$('#user-update-modal-save').click(function(e) {
	   e.preventDefault();

	   
	   $.ajax({
		   url      : base_path + 'user/save',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $('#user-update-modal').find('form:first').serialize(),
		   success  : function (response) {
			   $('#user-update-modal').modal('hide');
			   
		       if (response.status == 'success') {
			       js_message('success', 'User saved');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
				   
		       } else {
			       $('#user-update-modal').modal('hide');
				   js_message('error', 'An error occured, please try again!');
		       }
		   	   
		    },
		   error    : function () {
		    	$('#user-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
    });	
		
    var user_table = $('#user-table').dataTable({
		"sDom": "<'row-fluid'<'span5'T><'span7'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
		"aaSorting": [],
		"iDisplayLength" : 15,
		"aoColumnDefs": [
		  { "bSortable": false, "aTargets": [ "_all" ] }
		]

    });
    
    $('.dataTables_filter, .dataTables_paginate').addClass('pull-right');
    
    
    
    //order_table.fnSortOnOff( '_all', false );
});