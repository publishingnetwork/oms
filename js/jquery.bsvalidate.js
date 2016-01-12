(function ($) {
	
	$.fn.bsvalidate = function (validated_callback) {
		
		var errors = 0;
		
		var that = this;
		
		var validate = function (that) {
		
			var show_error = function (element, error) {
				
				if ($(element).attr(error) == undefined) {
					var message = $(element).parents('form:first').attr(error);
				} else {
					var message = $(element).attr(error);
				}
				
				
				if ($(element).attr('data-after') != undefined) {
					var after = $($(element).attr('data-after'));
				} else {
					var after = $(element);
				}
				
				$('<span class="help-block">' + message + '</span>')
					.hide()
					.insertAfter(after)
					.fadeIn('fast');
					
				$(element).parents('.control-group').addClass('error');
			};
			
			var hide_error = function (element, callback) {				
				
				if ($(element).attr('data-after') != undefined) {
					var after = $($(element).attr('data-after'));
				} else {
					var after = $(element);
				}
				
				after.siblings('.help-block:first')
					.fadeOut('fast')
					.remove();
				after.parents('.control-group').removeClass('error');
				
				callback();
					
			};

			
			
			var data_regexp = {
				email: /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
				//password: /(?=[^A-Z]*[A-Z])(?=[^!@#\$%]*[!@#\$%])/,
				password: /.*/,
				phone: /^[0-9\-\(\) \+]{6,}$/,
				password: /^.{6,}$/,
				money: /^[0-9]+(\.[0-9]{1,2})?$/,
				
			};
			
			var element_value = $(that).val();
			
			hide_error(that, $.proxy(function() {
			
				if ($(this).attr('data-empty') != 'ok' && ( element_value == '' || element_value == 0)) {
					show_error(this, 'data-empty-error');
					errors++;
					return;
				} else if ($(this).attr('data-empty') == 'ok' && ( element_value == '' || element_value == 0)) {
					return;
				}
				
				if ($(this).attr('data-type') != undefined && $(this).attr('data-type') != 'checkbox-group') {
	
					var regexp = data_regexp[$(this).attr('data-type')];
	
					if (!regexp.test(element_value)) {
						show_error(this, 'data-type-error');
						errors++;
						return;
					}
					
				} else if ($(this).attr('data-type') == 'checkbox-group') {
					if (!$(this).parents('.control-group').hasClass('error')) {
						
						if ($(this).parents('.control-group').find('input[data-type="checkbox-group"]:checked').length == 0) {
							show_error(this, 'data-empty-error', false);
							errors++;
							return;
						}
					}
				}	
				
				
				if ($(this).attr('data-ajax') != undefined) {
	
					var query = $(this).attr('data-ajax');
	
					$.post(
						query,
						$(this).serialize(), 
						$.proxy(function(response) {
							
							//if (response.result != 'valid') {
							if (response.status != 'valid') {
								show_error(this, 'data-ajax-error');
								errors++;
							}
						}, this),
						'json');
					
				}
				
			}, that));
		}
		
		
		$(this).submit(function(e) {
			errors = 0;
			
			var form = this;
			
			$(this).find('.validate').each(function() {
				validate(this);
				
			});
			
			if (errors > 0) {
				e.preventDefault();
				return false;
			} else {
				
				//we pass the event, so the callback can decide whether it will be ajax form
				validated_callback(form, e);
				return true;
			}
			
		});
		
		$(this).find('.validate').change(function() {
			validate(this);
		});
		
		$(this).find('.password-show').click(function(e) {
			e.preventDefault();
			
			var input_password = $(this).siblings('input[type="password"]:first');
			
			
			if (input_password.val() == '') {
				return;
			}
			
			$(this)
				.tooltip({
					title: input_password.val(),
					placement: 'left',
					trigger: 'manual',
				})
				.tooltip('show');
			
			setTimeout($.proxy(function() {
				$(this)
					.tooltip('hide')
					.tooltip('destroy');
			}, this), 2000);
			
		});
		
		$(this).find('.dropdown-option').click(function() {
			$(this)
				.parents('div:first')
				.siblings('input[type="hidden"]:first')
				.val(
					$(this).attr('data-value')
				);
				
			$(this)
				.siblings(':hidden')
				.show();
			
			$(this).hide();
			
			$(this)
				.parent()
				.siblings('button')
				.html(
					$(this).attr('data-caption') + '&nbsp;<span class="caret"></span>'
				);
			
		});
		
	}
	
}) (jQuery);
