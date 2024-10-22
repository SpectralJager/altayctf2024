//import * as general_functions from '/scripts/functions.js';

jQuery(document).ready(function($){
//(function($){

	if($.hasOwnProperty('modal')){
		$.modal.defaults.closeText = '×';
	}

	if(document.forms.hasOwnProperty('upload-image')){
		upload_images.bind_events();
	}

//})(jQuery);
});

let upload_images = {
	$image_drag: null,
	$image_switcher: null,
	$submit_button: null,
	$image_preview: null,
	images: [],
	sum_images_size: 0,
	max_file_size: 0,
	stop_events: false,

	bind_events: function(){
		this.$image_drag = $(document.forms['upload-image']);
		this.$image_switcher = $('.image-upload-page__switcher__container');
		this.$submit_button = $('input[type="submit"][form="upload-image"]');
		this.$image_preview = $('#image-preview');
		this.max_file_size = document.forms['upload-image'].elements['MAX_FILE_SIZE'].value;

		this.$image_drag
		.on('drag dragstart dragend', function(e){
			return false;
		})
		.on('dragover dragenter', function(e){
			upload_images.$image_drag.addClass('dragover');
			return false;
		})
		.on('dragleave', function(e){
			let dx = e.pageX - upload_images.$image_drag.offset().left;
			let dy = e.pageY - upload_images.$image_drag.offset().top;
			if(dx < 0 || dx > upload_images.$image_drag.outerWidth() || dy < 0 || dy > upload_images.$image_drag.outerHeight()){
				upload_images.$image_drag.removeClass('dragover');
			}
			return false;
		})
		.on('drop', function(e){
			if(upload_images.stop_events){
				return false;
			}
			upload_images.$image_drag.removeClass('dragover');
			let files = e.originalEvent.dataTransfer.files;
			upload_images.add_images(files);
			return false;
		})
		.on('submit',upload_images.sent_images);

		this.$image_switcher
		.on('click', '.file-name', function(e){
			if(upload_images.stop_events){
				return false;
			}
			let $parent = $(this).parent();
			let ind = upload_images.$image_switcher.children().index($parent);
			upload_images.set_preview(ind);
			return false;
		})
		.on('click', '.delete', function(e){
			if(upload_images.stop_events){
				return false;
			}
			let $parent = $(this).parent();
			let ind = upload_images.$image_switcher.children().index($parent);
			upload_images.cancel_image(ind);
			return false;
		});

		$('#file-input').change(function(){
			if(upload_images.stop_events){
				this.value = '';
				return false;
			}
			upload_images.add_images(this.files);
			this.value = '';
		});
	},

	add_images: function(files){
		//console.log(files);

		let wrong_message = '';
		let old_images_len = this.images.length;

		for(let i = 0; i < files.length; i++){
			if(
				files[i].type !== 'image/jpeg'
				&& files[i].type !== 'image/png'
			){
				wrong_message += 'Файл ' + files[i].name + ' не изображение.\n';
				continue;
			}

			if(this.sum_images_size + files[i].size > this.max_file_size){
				wrong_message += 'Размер файла ' + files[i].name + ' (' + general_functions.round_memsize(files[i].size, '&nbsp;') + ') не позволяет добавить его в форму\n';
				continue;
			}

			let new_ind = this.images.length;

			this.sum_images_size += files[i].size;

			this.images.push({
				file: files[i],
				public_switcher: $('<input type="checkbox" class="eye" title="Видимость">'),
				switcher: $('<div><button class="file-name"></button></div>'),
			});
			this.images[new_ind].switcher.children().text(files[i].name);
			this.images[new_ind].switcher.prepend(this.images[new_ind].public_switcher);
			this.images[new_ind].switcher.append(
				'&nbsp;<button class="delete">&#10008;</button>'
			);
			this.$image_switcher.append(this.images[new_ind].switcher);
		}

		if(old_images_len != this.images.length){
			this.set_preview(this.images.length - 1);
			this.$image_drag.find('.form-fullness').html(general_functions.round_memsize(this.sum_images_size, '&nbsp;'));
		}

		if(this.images.length){
			this.$submit_button.prop('disabled', false);
		}

		if(wrong_message.length){
			general_functions.rad_alert(wrong_message);
		}
	},

	set_preview: function(image_ind){
		if(image_ind >= 0 && image_ind < this.images.length){

			if(this.images[image_ind].switcher.hasClass('active')){
				return false;
			}

			this.$image_switcher.children().removeClass('active');
			this.images[image_ind].switcher.addClass('active');
		}

		let src = this.$image_preview.attr('src');
		if(src){
			URL.revokeObjectURL(src);
		}
		if(image_ind >= 0 && image_ind < this.images.length){
			this.$image_preview.attr('src', URL.createObjectURL(this.images[image_ind].file));
		}else{
			this.$image_preview.attr('src', '');
		}
	},

	cancel_image: function(image_ind){
		if(image_ind < 0 || image_ind >= this.images.length){
			return;
		}
		if(this.images[image_ind].switcher.hasClass('active')){
			this.set_preview(image_ind + (image_ind < this.images.length-1 ? 1 : -1));
		}

		this.sum_images_size -= this.images[image_ind].file.size;
		this.$image_drag.find('.form-fullness').html(general_functions.round_memsize(this.sum_images_size, '&nbsp;'));

		this.images[image_ind].switcher.remove();
		this.images.splice(image_ind, 1);

		if(!this.images.length){
			this.$submit_button.prop('disabled', true);
		}
	},

	sent_images: function(e){
		if(!upload_images.images.length){
			return false;
		}

		let $this = upload_images.$image_drag;
		if(upload_images.stop_events){
			return false;
		}

		upload_images.stop_events = true;

		upload_images.$submit_button.addClass('loader');

		// создадим объект данных формы
		let data = new FormData();
		let field_name = $('#file-input').attr('name');

		for(let i in upload_images.images){
			data.append(field_name+'['+i+']', upload_images.images[i].file);
			data.append(field_name+'_public['+i+']', +upload_images.images[i].public_switcher.prop('checked'));
		}

		$.ajax({
			url: $this.attr('action'),
			type: $this.attr('method'),
			data: data,
			dataType: 'json',
			processData: false,
			contentType: false,
			cache: false,
			success: function(result){
				if(result.hasOwnProperty('err')){
					if(result.err === false){
						let len = upload_images.images.length;
						for(let i=0; i<len; i++){
							upload_images.cancel_image(0);
						}
						if(result.msg !== ''){
							general_functions.rad_alert(result.msg);
						}
						
						location.reload();
						
					}else{
						let out = 'Ошибка: ';
						if(result.hasOwnProperty('msg')){
							out += result.msg;
						}
						general_functions.rad_alert(out);
					}
				}else{
					general_functions.rad_alert('Ошибка: ');
				}
			},
			error: general_functions.ajax_error,
			complete: function(){
				upload_images.stop_events = false;
				upload_images.$submit_button.removeClass('loader');
			}
		});

		return false;
	}
};