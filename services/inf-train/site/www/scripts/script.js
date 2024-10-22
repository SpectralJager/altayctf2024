//import * as general_functions from '/scripts/functions.js';

jQuery(document).ready(function($){

	if($.hasOwnProperty('modal')){
		$.modal.defaults.closeText = '×';
	}
	
	$('body')
		.on('submit', 'form', function(e){
			let $this = $(this);
			if($this.data('notAjax'))
				return;
			
			let $submit = e.originalEvent.submitter ? $(e.originalEvent.submitter) : $(); 
	
			if($this.hasClass('submit')){
				return false;
			}
			$this.addClass('submit');
			
			let data = $this.serializeArray();
			general_functions.api_ajax(
				$this.attr('action'),
				$this.attr('method'),
				data,
				function(result){
					if(result.hasOwnProperty('msg')){
						general_functions.rad_alert(result.msg);
					}
					if($this.data('ajaxReload')){
						location.reload();
					}
				},
				$this
			);
	
			return false;
		})
		.on('click.logout', '*[data-js="logout"]', function(e){
			let $this = $(this);
	
			if($this.hasClass('loader')){
				return false;
			}
			$this.addClass('loader');
	
			general_functions.api_ajax(
				'/api/auth/',
				'delete',
				{},
				function(result){
					location.reload();
				},
				$this
			);
	
			return false;
		});
});