$(document).ready(function() {


	$('.affiliate-update').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		clean_new_affiliate_form();
		
		$('#affiliate-update-modal input[name="id"]').val($tr.data('affiliate_id'));
		
		$('#affiliate-update-modal .modal-header h3').text('Update affiliate');
		
		$.ajax({
		   url      : base_path + 'affiliate/get/' + $tr.data('affiliate_id'),
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {

			   if (response.status == 'success') {
				    $.each(response.data, function(index, value) {
						$('#affiliate-update-modal [name="' + index + '"]').val(value);
	
				    });
				    
				    $('#affiliate-update-modal').modal('show');
			   } else {
				  js_message('error', 'An error occured, please try again!'); 
			   }

		    },
		   error    : function () {
		    	$('#affiliate-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	    
		//$('#s-update-modal').modal('show');
	});
	
	
	$('.affiliate-commissions').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		$('#affiliate-commissions-modal input[name="affiliate_id"]').val($tr.data('affiliate_id'));
		$('#affiliate-commissions-modal input[name="type"]').val($(this).data('type'));

		$tbody = $('#affiliate-commissions-modal tbody');
		$tbody.empty();

		$.ajax({
		   url      : base_path + 'affiliate/get_commissions/' + $tr.data('affiliate_id') + '/' + $(this).data('type'),
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {

			   if (response.status == 'success') {

				    $.each(response.data, function(index, product) {
						$product_row = $(render('commission_row', product));
						$product_row.find('.commission-type').val(product.type);
						
						product.type == 'default' &&
							$product_row.find('.commission').prop('disabled', true);
						
						$product_row.appendTo($tbody);   
				    });
				    $('#affiliate-commissions-modal').modal('show');
			   } else {
				  js_message('error', 'An error occured, please try again!'); 
			   }

		    },
		   error    : function () {
		    	$('#affiliate-commissions-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	    
	});	
	
	
	$('.affiliate-products').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		$('#affiliate-products-modal input[name="affiliate_id"]').val($tr.data('affiliate_id'));

		$tbody = $('#affiliate-products-modal tbody');
		$tbody.empty();

		$.ajax({
		   url      : base_path + 'affiliate/get_products/' + $tr.data('affiliate_id'),
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {

			   if (response.status == 'success') {

				    $.each(response.data, function(index, product) {
						$product_row = $(render('product_row', product));
						$product_row.find('.status').val(product.status);
						
						$product_row.appendTo($tbody);   
				    });
				    $('#affiliate-products-modal').modal('show');
			   } else {
				  js_message('error', 'An error occured, please try again!'); 
			   }

		    },
		   error    : function () {
		    	$('#affiliate-products-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	    
	});	
	
	
	$('#affiliate-commissions-modal tbody').on('change', '.commission-type', function() {
		$this = $(this);
		$commission = $this.parents('tr:first').find('.commission:first');
		
		$commission
			.prop('disabled', $this.val() == 'default')
			.val($this.val() == 'default' ? $commission.data('default_commission') : $commission.val());	
		
	});
	
	
	$('#affiliate-commissions-modal-save').click(function(e) {
	   e.preventDefault();

	   $.ajax({
		   url      : base_path + 'affiliate/save_commissions',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $('#affiliate-commissions-modal').find('form:first').serialize(),
		   success  : function (response) {
			   $('#affiliate-commissions-modal').modal('hide');
			   
		       if (response.status == 'success') {
			       js_message('success', 'Affiliate commissions saved');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
				   
		       } else {
			       $('#affiliate-commissions-modal').modal('hide');
				   js_message('error', 'An error occured, please try again!');
		       }
		   	   
		    },
		   error    : function () {
		    	$('#affiliate-commissions-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
    });		
	
	$('#affiliate-products-modal-save').click(function(e) {
	   e.preventDefault();

	   $.ajax({
		   url      : base_path + 'affiliate/save_products',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $('#affiliate-products-modal').find('form:first').serialize(),
		   success  : function (response) {
			   $('#affiliate-products-modal').modal('hide');
			   
		       if (response.status == 'success') {
			       js_message('success', 'Affiliate products saved');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
				   
		       } else {
			       $('#affiliate-products-modal').modal('hide');
				   js_message('error', 'An error occured, please try again!');
		       }
		   	   
		    },
		   error    : function () {
		    	$('#affiliate-products-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
    });
	
	$('.affiliate-delete').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		js_confirm({
			label      : 'Confirm deleting affiliate',
			message    : 'Are you sure you want to delete this affiliate?',
			yes_button : 'Confirm'
		}, function () {
			$.ajax({
			   url      : base_path + 'affiliate/delete/' + $tr.data('affiliate_id'),
			   dataType : 'json',
			   method   : 'GET',
			   success  : function (response) {
			   
			   	   if (response.status == 'success') {
				   	   js_message('success', 'Affiliate deleted!');
			       
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
	
	$('.affiliate-new').click(function(e) {
		e.preventDefault();
		new_affiliate();

	});

	if (document.location.hash.substr(1) == 'new-affiliate') {
		new_affiliate();
	}

	function clean_new_affiliate_form() {
		$('#affiliate-update-modal input').each(function() {
			$(this).val('');
		});
	}

	function new_affiliate() {
		$('#affiliate-update-modal .modal-header h3').text('New affiliate');
	
		clean_new_affiliate_form();
		
		$('#affiliate-update-modal').modal('show');
	}
		
	$('#affiliate-update-modal-save').click(function(e) {
	   e.preventDefault();

	   
	   $.ajax({
		   url      : base_path + 'affiliate/save',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $('#affiliate-update-modal').find('form:first').serialize(),
		   success  : function (response) {
			   $('#affiliate-update-modal').modal('hide');
			   
		       if (response.status == 'success') {
			       js_message('success', 'Affiliate saved');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
				   
		       } else {
			       $('#affiliate-update-modal').modal('hide');
				   js_message('error', 'An error occured, please try again!');
		       }
		   	   
		    },
		   error    : function () {
		    	$('#affiliate-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
    });	
		
    var affiliate_table = $('#affiliate-table').dataTable({
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