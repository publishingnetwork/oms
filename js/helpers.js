var base_path = '/oms/';

function render(templateName, data) {
	render.cache = render.cache || {};

	render.cache[templateName] = render.cache[templateName] || (function() {
		var template;
	
		$.ajax({
			url      : base_path + 'js/tpl/' + templateName + '.tpl',
			method   : 'GET',
			dataType : 'html',
			async    : false,
			success  : function (response) {
				template = response;
			}
		});
		return template;
	})();

	return _.template(render.cache[templateName], data || {});
}

function js_message(type, message) {
	$('#js_message').find('span').text(message);
	$('#js_message').removeClass().addClass('alert alert-'+type).fadeIn('slow').delay(4000).fadeOut('slow');
}

function js_confirm(data, yes_callback) {
	$('#confirm_modal').remove();
	$(render('confirm_modal', data)).appendTo('body').modal('show');
	
	$('#confirm_modal_yes').click(function(e) {
		e.preventDefault();
		
		$('#confirm_modal').modal('hide');
		
		if (typeof yes_callback == 'function') {
			yes_callback();
		}
	});
	
	
	
}

//http://www.datatables.net/plug-ins/api#fnAddTr
//thanks to Allan Jardine
$.fn.dataTableExt.oApi.fnAddTr = function ( oSettings, nTr, bRedraw ) {
    if ( typeof bRedraw == 'undefined' )
    {
        bRedraw = true;
    }
      
    var nTds = nTr.getElementsByTagName('td');
    if ( nTds.length != oSettings.aoColumns.length )
    {
        alert( 'Warning: not adding new TR - columns and TD elements must match' );
        return;
    }
      
    var aData = [];
    for ( var i=0 ; i<nTds.length ; i++ )
    {
        aData.push( nTds[i].innerHTML );
    }
      
    /* Add the data and then replace DataTable's generated TR with ours */
    var iIndex = this.oApi._fnAddData( oSettings, aData );
    nTr._DT_RowIndex = iIndex;
    oSettings.aoData[ iIndex ].nTr = nTr;
      
    oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
      
    if ( bRedraw )
    {
        this.oApi._fnReDraw( oSettings );
    }
};