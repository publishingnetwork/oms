$(document).ready(function() {


    $('input.input-date')
    	.datepicker()
    	.on('changeDate', function() {
	    	$(this).parent('form:first').submit();
    	});


	$('#calculate-payment').click(function(e) {
		e.preventDefault();
		
		$.ajax({
		   url      : base_path + 'affiliatepayment/calculate/' + $(this).parents('form:first').find('input[name="affiliate_id"]').val(),
		   dataType : 'json',
		   method   : 'POST',
		   data     : {
			   start_date : $('#start-date').val(),
			   end_date   : $('#end-date').val()
		   },
		   
		   success  : function (response) {

			   if (response.status == 'success') {
				   $('#orders_awaiting_payment').val(response.orders_awaiting_payment.join(','));
				   $('#orders_awaiting_refund').val(response.orders_awaiting_refund.join(','));
				   $('#refunds').val(response.refunds);
				   $('#commissions').val(response.commissions);
				   
				   $('#total').val(response.commissions - response.refunds);
				   
			   } else {
				  js_message('error', 'An error occured, please try again!'); 
			   }

		    },
		   error    : function () {
		    	$('#affiliate-payment-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
			
		});
	});

	$('.affiliate-payment-update').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		clean_new_affiliate_payment_payment_form();
		
		$('#affiliate-payment-update-modal input[name="id"]').val($tr.data('affiliate_payment_id'));
		
		$('#affiliate-payment-update-modal .modal-header h3').text('Update affiliate payment');
		
		$.ajax({
		   url      : base_path + 'affiliatepayment/get/' + $tr.data('affiliate_payment_id'),
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {

			   if (response.status == 'success') {
				    $.each(response.data, function(index, value) {
						$('#affiliate-payment-update-modal [name="' + index + '"]').val(value);
	
				    });
				    
				    $('#affiliate-payment-update-modal').modal('show');
			   } else {
				  js_message('error', 'An error occured, please try again!'); 
			   }

		    },
		   error    : function () {
		    	$('#affiliate-payment-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	    
		//$('#user-update-modal').modal('show');
	});
	
	$('.affiliate-payment-delete').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		js_confirm({
			label      : 'Confirm deleting affiliate payment',
			message    : 'Are you sure you want to delete this affiliate payment?',
			yes_button : 'Confirm'
		}, function () {
			$.ajax({
			   url      : base_path + 'affiliatepayment/delete/' + $tr.data('affiliate_payment_id'),
			   dataType : 'json',
			   method   : 'GET',
			   success  : function (response) {
			   
			   	   if (response.status == 'success') {
				   	   js_message('success', 'Affiliate payment deleted!');
			       
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
	
	$('.affiliate-payment-new').click(function(e) {
		e.preventDefault();
		new_affiliate_payment();

	});

	if (document.location.hash.substr(1) == 'new-payment') {
		new_affiliate_payment();
	}

	$('#new-payment').click(function(e) {
		e.preventDefault();
		new_affiliate_payment();
	});



	function new_affiliate_payment() {
		
		$('#affiliate-payment-new-modal input').val('');
		
		$('#affiliate').val($('#affiliate-payment-table').data('affiliate_name'));
		$('#affiliate_id').val($('#affiliate-payment-table').data('affiliate_id'));
		$('#paypal_email').val($('#affiliate-payment-table').data('paypal_email'));
		
		$('#affiliate-payment-new-modal').modal('show');
	}
		
	$('#affiliate-payment-update-modal-save').click(function(e) {
	   e.preventDefault();

	   
	   $.ajax({
		   url      : base_path + 'affiliatepayment/save',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $('#affiliate-payment-update-modal').find('form:first').serialize(),
		   success  : function (response) {
			   $('#affiliate-payment-update-modal').modal('hide');
			   
		       if (response.status == 'success') {
			       js_message('success', 'Affiliate payment saved');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
				   
		       } else {
			       $('#affiliate-payment-update-modal').modal('hide');
				   js_message('error', 'An error occured, please try again!');
		       }
		   	   
		    },
		   error    : function () {
		    	$('#affiliate-payment-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
    });	

	$('#affiliate-payment-new-modal-save').click(function(e) {
	   e.preventDefault();

	   
	   $.ajax({
		   url      : base_path + 'affiliatepayment/new',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $('#affiliate-payment-new-modal').find('form:first').serialize(),
		   success  : function (response) {
			   $('#affiliate-payment-new-modal').modal('hide');
			   
		       if (response.status == 'success') {
			       js_message('success', 'Affiliate payment saved');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
				   
		       } else {
			       $('#affiliate-payment-new-modal').modal('hide');
				   js_message('error', 'An error occured, please try again!');
		       }
		   	   
		    },
		   error    : function () {
		    	$('#affiliate-payment-new-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
    });	

		
    var affiliate_payment_table = $('#affiliate-payment-table').dataTable({
		"sDom": "<'row-fluid'<'span5'T><'span7'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
		"aaSorting": [],
		"iDisplayLength" : 15,
		"aoColumnDefs": [
		  { "bSortable": false, "aTargets": [ "_all" ] }
		]

    });
    
    $('.dataTables_filter, .dataTables_paginate').addClass('pull-right');
    
    
    $('#new-payment').detach().appendTo($('#affiliate-payment-table_wrapper div.span5'));
    //order_table.fnSortOnOff( '_all', false );
});