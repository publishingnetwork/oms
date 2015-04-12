$(document).ready(function() {

	$('.product-update').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		clean_new_product_form();
		
		$('#product-update-modal input[name="id"]').val($tr.data('product_id'));
		
		$('#product-update-modal .modal-header h3').text('Update product');
		
		$.ajax({
		   url      : base_path + 'product/get/' + $tr.data('product_id'),
		   dataType : 'json',
		   method   : 'GET',
		   success  : function (response) {

			    $.each(response.data, function(index, value) {
					index != 'skus' ? $('#product-update-modal input[name="' + index + '"]').val(value) : (function() {
						$.each(value, function(i, v) {
							$('#product-update-modal .skus').append('<div class="controls controls-row"><span class="sku"><button class="span1 btn sku-delete"><i class="icon-trash"></i></button><input type="text" class="span4 sku-name" value="' + v.sku + '" placeholder="SKU"><input type="text" class="span4 sku-quantity" value="' + v.quantity + '" placeholder="Quantity"></span></div>');
						});
					})(); 
					
					index == 'affiliate_status' &&
						$('#affiliate_status').val(value);
						
					index == 'urls' &&
						$('#urls').val(value);	
			    });
			    
			    
			    
			    $('#product-update-modal').modal('show');
		    },
		   error    : function () {
		    	$('#product-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
	    
		$('#product-update-modal').modal('show');
	});
	
	$('.product-delete').click(function(e) {
		e.preventDefault();
		
		$tr = $(this).parents('tr:first');
		
		js_confirm({
			label      : 'Confirm deleting product',
			message    : 'Are you sure you want to delete this product?',
			yes_button : 'Confirm'
		}, function () {
			$.ajax({
			   url      : base_path + 'product/delete/' + $tr.data('product_id'),
			   dataType : 'json',
			   method   : 'GET',
			   success  : function (response) {
			   
			   	
				   js_message('success', 'Product deleted!');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
			    },
			   error    : function () {
				    js_message('error', 'An error occured, please try again!');
			    }
		    });
		});
	});
	
	$('.product-new').click(function(e) {
		e.preventDefault();
		new_product();

	});

	if (document.location.hash.substr(1) == 'new-product') {
		new_product();
	}

	function clean_new_product_form() {
		$('#product-update-modal input').each(function() {
			$(this).val('');
		});
		
		$('#affiliate_status').val('hide');
		
		$('#product-update-modal .skus').empty();
	}

	function new_product() {
		$('#product-update-modal .modal-header h3').text('New product');
	
		clean_new_product_form();
		
		$('#product-update-modal').modal('show');
	}

	$('.product-delete').click(function(e) {
		e.preventDefault();
		$('#product-confirm-modal').modal('show');
	});
	
	$('.product-new').click(function(e) {
		e.preventDefault();
		$('#product-new-modal').modal('show');
	});

	
    var product_table = $('#product-table').dataTable({
		"sDom": "<'row-fluid'<'span5'T><'span7'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
		"aaSorting": [],
		"iDisplayLength" : 15,
		"aoColumnDefs": [
		  { "bSortable": false, "aTargets": [ "_all" ] }
		]

    });
    
    $('.dataTables_filter, .dataTables_paginate').addClass('pull-right');
    
        
    $('#new-sku').click(function(e) {
	    e.preventDefault();
	    
	    $('#product-update-modal .skus').append('<div class="controls controls-row"><span class="sku"><button class="span1 btn sku-delete"><i class="icon-trash"></i></button><input type="text" class="span4 sku-name" placeholder="SKU"><input type="text" class="span4 sku-quantity" placeholder="Quantity"></span></div>');
	    
    });
    
    $('#product-update-modal').on('click', '.sku-delete', function(e) {
	    e.preventDefault();
	    
	    $(this).parents('.sku:first').fadeOut('fast', function() {
		    $(this).remove();
	    });
    });
    
    $('#product-update-modal-save').click(function(e) {
	   e.preventDefault();
	   
	   var skus = [];
	   $('#product-update-modal .sku').each(function() {
		   skus.push($(this).find('.sku-name').val() + '=' + $(this).find('.sku-quantity').val());
	   });
	   
	   $('#product-update-modal input[name="skus"]').val(skus.join(','));
	   
	   $.ajax({
		   url      : base_path + 'product/save',
		   dataType : 'json',
		   method   : 'POST',
		   data     : $('#product-update-modal').find('form:first').serialize(),
		   success  : function (response) {
			   $('#product-update-modal').modal('hide');
			   
		       if (response.status == 'success') {
			       js_message('success', 'Product saved');
			       
			       setTimeout(function() {
				      location.reload(); 
			       }, 300);
				   
		       } else {
			       $('#product-update-modal').modal('hide');
				   js_message('error', 'An error occured, please try again!');
		       }
		   	   
		    },
		   error    : function () {
		    	$('#product-update-modal').modal('hide');
			    js_message('error', 'An error occured, please try again!');
		    }
	    });
    });
    
    //order_table.fnSortOnOff( '_all', false );
});