
$('.modal-submit').click(function(e) {
	e.preventDefault();
	$(this).parents('.modal:first').find('form:first').submit();
});

$('.show-tooltip').tooltip();

$('select[data-value]').each(function() {
	$(this).val($(this).data('value'));
});