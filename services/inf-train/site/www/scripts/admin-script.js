//import * as general_functions from '/scripts/functions.js';

jQuery(document).ready(function($){

	if($.hasOwnProperty('modal')){
		$.modal.defaults.closeText = '×';
	}
	
	$('body')
		.on('click.delete_post', '*[data-js="api_delete"],*[data-js="api_post"]', function(e){
			//удаление через API
			let $this = $(this);
	
			if($this.hasClass('loader')){
				return false;
			}
			
			let type = $this.data('js') == 'api_delete' ? 'delete' : 'post';
			
			if(type == 'delete'){
				if(!confirm('Вы точно хотите удалить объект?'))
					return false;
			}
	
			$this.addClass('loader');
	
			general_functions.api_ajax(
				$this.data('url'),
				type,
				{},
				function(result){
					
					let msg = result.hasOwnProperty('msg') ? result.msg : '';
					
					if(result.hasOwnProperty('confirm') && result.confirm){

						if(!confirm(msg)){
							return;
						}

						general_functions.api_ajax(
							$this.data('url'),
							type,
							{'token':result.token},
							function(result){
								if(result.hasOwnProperty('msg')){
									general_functions.rad_alert(result.msg);
								}
								let redirect = $this.data('parent-url');
								if(redirect){
									window.location.replace(redirect);
								}else{
									location.reload();
								}
							}
						);
						
					}else{
						general_functions.rad_alert(msg);
						let redirect = $this.data('parent-url');
						if(redirect){
							window.location.replace(redirect);
						}else{
							location.reload();
						}
					}
				},
				$this
			);
			return false;
		})
		.on('click.publication keydown.publication', '*[data-js="api_pulication"]', function(e){
			if(!general_functions.is_activate_event(e)){
				return;
			}

			let $this = $(this);
			let is_public = $this.prop('checked');

			if(e.type == 'click'){
				is_public = !is_public;
				$this.prop('checked', is_public);
			}
			
			let method = is_public ? 'delete' : 'post';

			if($this.hasClass('loader')){
				return false;
			}

			$this.addClass('loader');

			general_functions.api_ajax(
				$this.data('url'),
				method,
				{},
				function(result){
					$this.prop('checked', !is_public);
				},
				$this
			);
			return false;
		});
});