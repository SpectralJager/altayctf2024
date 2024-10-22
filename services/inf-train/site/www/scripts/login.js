jQuery(document).ready(function($){
	
	let $toggle_buttons_cont = $('.login-form__toggle-buttons');

	let anchor = window.location.href.indexOf('#');
	anchor = anchor == -1 ? '' : window.location.href.substr(anchor+1);
	
	if(anchor != 'registration'){
		anchor = 'login';
	}

	$toggle_buttons_cont.find('a[href="#'+anchor+'"]').addClass('active');
	$(document.forms[anchor]).show();

	$toggle_buttons_cont.on('click.form_toggle', 'a', function(e){
		let $this = $(this);
		if($this.hasClass('active')){
			return false;
		}
		
		let $target = $this.data('target');
		$target = $($target);

		$this.siblings().removeClass('active');
		$this.addClass('active');

		$target.siblings('form').hide();
		$target.show();
	});
	
});