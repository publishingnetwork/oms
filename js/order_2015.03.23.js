$(document).ready(function() {

	$('select').each(function() {
		//easy value set for selects
		$(this).data('value') && $(this).val($(this).data('value'));
	});

	$('input[type="checkbox"]:checked').prop('checked', false);

	$('#choose-columns').click(function(e) {
		e.preventDefault();
		
		$.ajax({
		   url      : base_path + 'user/get_hidden_columns',
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {

			    $.each(response.hidden_columns, function(index, value) {
					$('#columns-modal input[type="checkbox"][value="' + value + '"]').prop('checked', true); 
			    });
			    
			    $('#columns-modal').modal('show');
		    },
		   error    : function () {
		    	$('#columns-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
		
		
	});
	
	
	$('.order-merge').click(function(e) {
		e.preventDefault();
		
		var order_id = $(this).parents('tr:first').data('order_id');
		
		$.ajax({
		   url      : base_path + 'order/get_orders_for_merge/' + order_id,
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {
		   		$select = $('#order-merge-modal #parent_order_id');
			    $select.empty();
			    $('#child_order_id').val('');
			    
			    $.each(response.orders, function(index, order) {
					$select.append(
						$('<option></option>')
							.attr('value', order.id)
							.text(order.public_id + ' - ' + order.date)
					); 
			    });
			    $('#child_order_id').val(order_id);
			    $('#order-merge-modal').modal('show');
		    },
		   error    : function () {
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
		
	});	
	
	$('#order-merge-modal-save').click(function(e) {
		$('#order-merge-modal').modal('hide');
		$.ajax({
		   url      : base_path + 'order/merge/',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $(this).parents('.modal:first').find('form:first').serialize(),
		   success  : function (response) {
		   	
		   	   if (response.status == 'success') {
			   	   js_message('success', 'Orders merged!');
			       
			       setTimeout(function() {
					  //location.hash = order_table.fnSettings()._iDisplayStart;
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
	
	$('.order-address-fix').click(function(e) {
		e.preventDefault();
		$tr = $(this).parents('tr:first');
		
		var fields = (function(fields) {
			var a = [];
			$(fields).each(function(index, field) {
				a.push($(field).data('field'));
			});
			return a.join(', ');
		})($tr.find('.fix-address:checked'));
		
		if (!fields.length) {
			alert('Please select at least one checkbox!');
			return;
		}
		
		var $input = $(this);
		
		js_confirm({
			label      : 'Confirm choosing address',
			message    : 'Are you sure these are the correct ' + fields + ' for # ' + $tr.data('public_id') + ' ?',
			yes_button : 'Confirm'
		}, function() {
			$.ajax({
			   url      : base_path + 'order/fix_address',
			   dataType : 'json',
			   method   : 'POST',
			   data     : {
				   type   : $tr.data('type'),
				   id     : $tr.data('specific_id'),
				   fields : fields
			   },
			   success  : function (response) {
				   js_message('success', 'Address updated!');
			       
			       setTimeout(function() {
					 //location.hash = order_table.fnSettings()._iDisplayStart;
				      location.reload(); 
			       }, 300);
			    },
			   error    : function () {
				    js_message('error', 'An error occured, please try again!');
			    }
		    });
		});
	});
	
	$('.order-delete').click(function(e) {
		e.preventDefault();
		$tr = $(this).parents('tr:first');
		$this = $(this);
		
		js_confirm({
			label      : 'Confirm deleting',
			message    : 'Are you sure you want to delete it?',
			yes_button : 'Confirm'
		}, function() {
			$.ajax({
			   url      : base_path + 'order/delete/',
			   data     : {
				   id    : $tr.data('specific_id'),
				   type  : $this.data('type')
			   },
			   dataType : 'json',
			   method   : 'POST',
			   success  : function (response) {
				   js_message('success', 'Row deleted!');
			       
			       setTimeout(function() {
					  //location.hash = order_table.fnSettings()._iDisplayStart;
				      location.reload(); 
			       }, 300);
			    },
			   error    : function () {
				    js_message('error', 'An error occured, please try again!');
			    }
		    });
		});
	});	
	
/*
	$('#show-all').click(function(e) {
		e.preventDefault();
		$('#show-paypal, #show-unprocessed').parent().removeClass('active');
		$(this).parent().addClass('active');
		
		order_table.fnDraw();
	});
	
	$('#show-paypal').click(function(e) {
		e.preventDefault();
		$('#show-all, #show-unprocessed').parent().removeClass('active');
		$(this).parent().addClass('active');
		
		order_table.fnDraw();
	});
	
	$('#show-unprocessed').click(function(e) {
		e.preventDefault();
		$('#show-all, #show-paypal').parent().removeClass('active');
		$(this).parent().addClass('active');
		
		order_table.fnDraw();
	});
*/
		
	$.fn.dataTableExt.afnFiltering.push(
		function( oSettings, aData, iDataIndex ) {
			var paypal_only = $('#show-paypal').parent().hasClass('active');
			var unprocessed_only = $('#show-unprocessed').parent().hasClass('active');
			
			var status_ok = true;
			if (unprocessed_only) {
				status_ok =  !aData[15].match(/processed/) ? true : false;
			}			
			
			var paypal_ok = true;
			if (paypal_only) {
				paypal_ok =  aData[2] == 'paypal' ? true : false;
			}
			
			
			var start_ok = true;
			if ($('#start-date').val() != '') {
				if (new Date(aData[3]) < new Date($('#start-date').val())) {
					start_ok = false;
				}
			}
			
			var end_ok = true;
			if ($('#end-date').val() != '') {
				if (new Date(aData[3]) > new Date($('#end-date').val())) {
					end_ok = false;
				}
			}
			
			return paypal_ok && start_ok && end_ok && status_ok ? true : false;
		}
	);
	
	$('.order-history').click(function(e) {
		e.preventDefault();
		$tr = $(this).parents('tr:first');
		
		$modal_body = $('#order-history-modal .modal-body');
		$modal_body.empty();
		
		$.ajax({
		   url      : base_path + 'order/modal_history/' + $tr.data('order_id'),
		   dataType : 'html',
		   method   : 'GET',
		   success  : function (response) {
		       $modal_body.html(response);
		   	   $('#order-history-modal').modal('show');
		    },
		   error    : function () {
		    	$('#order-history-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	    
	});
	
	$('.order-details').click(function(e) {
		e.preventDefault();
		$tr = $(this).parents('tr:first');
		
		order_details($tr.data('order_id'));
		
	});
	
	$('body').on('click', '.history-link', function(e) {
		e.preventDefault();
		
		$('#order-history-modal').modal('hide');
		
		var order_id = $(this).data('order_id');
		
		setTimeout(function() {
			order_details(order_id);
		}, 150);
		
	});
	
	if (document.location.hash.substr(1) == 'new-order') {
		$('#order-new-modal').modal('show');
	}
	
	function order_details(id) {
		$modal_body = $('#order-details-modal .modal-body');
		$modal_body.empty();
		
		$.ajax({
		   url      : base_path + 'order/modal_details/' + id,
		   dataType : 'html',
		   method   : 'GET',
		   success  : function (response) {
		       $modal_body.html(response);
		   	   $('#order-details-modal').modal('show');
		    },
		   error    : function () {
		    	$('#order-confirm-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	}
	
	$('.order-address-update').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		$.ajax({
		   url      : base_path + 'order/get_address/',
		   dataType : 'json',
		   method   : 'POST',
		   data     : {
			   type : $tr.data('type'),
			   id   : $tr.data('specific_id')
		   },
		   success  : function (response) {
		   	
		   	   if (response.status == 'success') {
			   	   $.each(response.data, function(index, value) {				   	     
				   	   $('#order-address-update-modal [name="'+index+'"]').val(value);  
			   	   });
			   	   $('#order-address-update-modal').modal('show');
		   	   } else {
			   	   js_message('error', 'An error occured, please try again!');
		   	   }
		       
		    },
		   error    : function () {
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
		
	});
	
	$('#order-address-update-modal-save').click(function(e) {
		$('#order-address-update-modal').modal('hide');
		$.ajax({
		   url      : base_path + 'order/save_address/',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $(this).parents('.modal:first').find('form:first').serialize(),
		   success  : function (response) {
		   	
		   	   if (response.status == 'success') {
			   	   js_message('success', 'Address updated!');
			       
			       setTimeout(function() {
					  //location.hash = order_table.fnSettings()._iDisplayStart;
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
	
	$('#order-new-modal-save').click(function(e) {
		e.preventDefault();
		
		$li = $('#order-new-modal .chosen-items-list li');
		
		if (!$li.length) {
			alert('Please select at least one item!');
			return;
		}
		
		if (!/^[0-9]+$/.test($('#order-new-modal input[name="public_id"]').val()) && $('#order-new-modal input[name="public_id"]').val() != '') {
			alert('Order # can contain digits only!');
			return;
		}
		
		$('#order-new-modal').modal('hide');
		
		var list = [];
		$li.each(function() {
			list.push($(this).data('product_name'));	
		});
		
		$('#order-new-modal input[name="items"]').val(list.join(','));
		
		$.ajax({
		   url      : base_path + 'order/new_order/',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $(this).parents('.modal:first').find('form:first').serialize(),
		   success  : function (response) {
		   	
		   	   if (response.status == 'success') {
			   	   js_message('success', 'New order saved!');
			       
			       setTimeout(function() {
					  //location.hash = order_table.fnSettings()._iDisplayStart;
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
	
	$('.order-details-update').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		$modal = $('#order-details-update-modal');
		$modal.find('.chosen-items-list').empty();
		
		$.ajax({
		   url      : base_path + 'order/get_details/' + $tr.data('order_id'),
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {
		   	
		   	   if (response.status == 'success') {
			   	   $.each(response.data, function(index, value) {
				   	   if (index == 'products') {
					   	   $.each(value, function(i, item) {
								$('<li data-product_name="' + item + '">' + item + ' <button class="btn btn-mini item-remove"><i class="icon-trash"></i></button></li>')
									.appendTo($modal.find('.chosen-items-list'));						   	   
					   	   });
					   	   return;
				   	   }
			   	   
				   	   $('#order-details-update-modal [name="'+index+'"]').val(value);  
			   	   });
			   	   $modal.modal('show');
		   	   } else {
			   	   js_message('error', 'An error occured, please try again!');
		   	   }
		       
		    },
		   error    : function () {
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
		
		
		$modal.modal('show');
	});
	
	$('#order-details-update-modal-save').click(function(e) {
		
		
		$li = $('#order-details-update-modal .chosen-items-list li');
		
		if (!$li.length) {
			alert('Please select at least one item!');
			return;
		}
		
		$('#order-details-update-modal').modal('hide');
		
		var list = [];
		$li.each(function() {
			list.push($(this).data('product_name'));	
		});
		
		$('#order-details-update-modal input[name="items"]').val(list.join(','));
		
		$.ajax({
		   url      : base_path + 'order/save_details/',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $(this).parents('.modal:first').find('form:first').serialize(),
		   success  : function (response) {
		   	
		   	   if (response.status == 'success') {
			   	   js_message('success', 'Details updated!');
			       
			       setTimeout(function() {
					  //location.hash = order_table.fnSettings()._iDisplayStart;
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
	
	$('.order-send').click(function(e) {
		e.preventDefault();
		$tr = $(this).parents('tr');
				
		if ($tr.data('fullfillment_id') != '')
			$('#already-sent-warning').show();
		else 
			$('#already-sent-warning').hide();
		
		$('#order-confirm-modal .lead').text('Are you sure you want to send order #' + $tr.data('public_id') + ' for fullfillment?');
		
		$('#order-confirm-yes').data('order_id', $tr.data('order_id'));
		
		$('#order-confirm-modal').modal('show');
		
	});
	
	$('#order-confirm-yes').click(function(e) {
		e.preventDefault();
		
		$('#order-confirm-modal').modal('hide');
		js_message('info', 'Sending order!');
		
		$.ajax({
		   url      : base_path + 'order/send_for_fullfillment',
		   dataType : 'json',
		   method   : 'POST',
		   data     : {
			   id   : $(this).data('order_id')
		   },
		   success  : function (response) {
			   
			   
		       if (response.status == 'success') {
			       js_message('success', 'Order sent for fullfillment!');
			       
			       setTimeout(function() {
					  //location.hash = order_table.fnSettings()._iDisplayStart;
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
	
	$('#order-multi-fullfillment-yes').click(function(e) {
		e.preventDefault();
		
		var ids = [];
		$('.send-checkbox:checked').each(function() {
			ids.push($(this).parents('tr:first').data('order_id'));
		});
		
		
		$('#order-multi-fullfillment-modal').modal('hide');
		js_message('info', 'Sending orders!');
		
		$.ajax({
		   url      : base_path + 'order/send_for_fullfillment',
		   dataType : 'json',
		   method   : 'POST',
		   data     : {
			   ids   : ids.join(',')
		   },
		   success  : function (response) {
			   
			   
		       if (response.status == 'success') {
			       js_message('success', 'Orders sent for fullfillment!');
			       
			       setTimeout(function() {
					  //location.hash = order_table.fnSettings()._iDisplayStart;
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
	
	$('#submit-selected').click(function(e) {
		e.preventDefault();
		
		$checked = $('.send-checkbox:checked');
		
		$modal_body = $('#order-multi-fullfillment-modal .modal-body');
		$modal_body.empty();
		
		if ($checked.length == 0) {
			js_message('error', 'Please select at least one order!');
			return;
		}
		
		var ids = [];
		$checked.each(function() {
			ids.push($(this).parents('tr:first').data('order_id'));
		});
		
		$.ajax({
		   url      : base_path + 'order/modal_fullfillment',
		   dataType : 'html',
		   method   : 'POST',
		   data     : {
			   ids   : ids.join(',')
		   },
		   success  : function (response) {
			   $modal_body.html(response);
		   	   $('#order-multi-fullfillment-modal').modal('show');
		    },
		   error    : function () {
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
		
	});
	
	
	$('#csv-export').click(function(e) {
		e.preventDefault();
		
		$checked = $('.send-checkbox:checked');
		
		if ($checked.length == 0) {
			js_message('error', 'Please select at least one order!');
			return;
		}
		
		var ids = [];
		$checked.each(function() {
			ids.push($(this).parents('tr:first').data('order_id'));
		});
		console.log('click');
		document.location.href = base_path + 'order/export/' + ids.join('-');
		
	});	
	
	$('.order-new').click(function(e) {
		e.preventDefault();
		$('#order-new-modal').modal('show');
	});
	
	
	var products = $('#available-products').val().split(',');
	$('.item-typeahead').typeahead({
		source  : products,
		updater : function (item) {
		
			$('<li data-product_name="' + item + '">' + item + ' <button class="btn btn-mini item-remove"><i class="icon-trash"></i></button></li>')
				.hide()
				.appendTo(this.$element.parents('.modal:first').find('.chosen-items-list'))
				.fadeIn('fast');
			
			return;
		}
	});
	
	$('.chosen-items-list').on('click', '.item-remove', function(e) {
		e.preventDefault();
		$(this).parents('li:first').fadeOut('fast', function() {
			$(this).remove();
		});
	});

    
    $('body').on('click', '.paypal-table-details', function(e) {
    	console.log('test');
    
    	e.preventDefault();
    	
	    $this = $(this);
	    
	    if ($this.data('status') == 'shown') {
		    $this
		    	.data('status', 'hidden')
		    	.text('Hide Details')
		    	.parents('.paypal-table:first')
		    	.find('tr.hide')
		    	.removeClass('hide')
		    	.addClass('hide-me');
	    } else {
		    $this
		    	.data('status', 'shown')
		    	.text('Show Details')
		    	.parents('.paypal-table:first')
		    	.find('tr.hide-me')
		    	.removeClass('hide-me')
		    	.addClass('hide');
	    }
    });
    
    $('input.input-date')
    	.datepicker()
    	.on('changeDate', function() {
	    	$(this).parent('form:first').submit();
    	});
    
    $('input.input-date').change(function() {
	    //order_table.fnDraw();
    });
    
    $('#type-filter').on('click', 'a', function() {
	   $this = $(this).parents('li:first');
	   $form = $this.parents('form:first');
	  
	   $form
	   	.find('input[name="filter"]')
	   	.val($this.data('filter'));

	   $form.submit();
    });

	$('#search-query, input.input-date, #affiliate').change(function() {
		$(this).parents('form:first').submit();
	});

    
    //order_table.fnSortOnOff( '_all', false );
});
