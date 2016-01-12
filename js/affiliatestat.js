$(document).ready(function() {
	
    var affiliate_stats_table = $('#affiliate-stats-table').dataTable({
		"sDom": "<'row-fluid'<'span5'T><'span7'>r>t<'row-fluid'<'span6'i><'span6'p>>",
		"aaSorting": [],
		"iDisplayLength" : 30,
		"aoColumnDefs": [
		  { "bSortable": false, "aTargets": [ "_all" ] }
		]

    });
    
    
    $('#product, #campaign').change(function() {
	    $(this).parents('form:first').submit();
    });
    
    $('input.input-date-submit')
    	.datepicker()
    	.on('changeDate', function() {
	    	//affiliate_stats_table.fnDraw();
	    	$('#date-range').submit();
    	});
    
    $('input.input-date-submit').change(function() {
	    $('#date-range').submit();
    });
    
    $('.dataTables_filter, .dataTables_paginate').addClass('pull-right');
    
    
    $.fn.dataTableExt.afnFiltering.push(
		function( oSettings, aData, iDataIndex ) {
			
			var start_ok = true;
			if ($('#start-date').val() != '') {
				if (new Date(aData[0]) < new Date($('#start-date').val())) {
					start_ok = false;
				}
			}
			
			var end_ok = true;
			if ($('#end-date').val() != '') {
				if (new Date(aData[0]) > new Date($('#end-date').val())) {
					end_ok = false;
				}
			}
			
			return end_ok && start_ok ? true : false;
		}
	);
});