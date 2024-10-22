if(!window.general_functions){
	window.general_functions = {

		/**
		 * синоним alert
		 */
		rad_alert: function(message){
			alert(message);
		},

		/**
		 * функция обработчик неудачно завершившегося ajax запроса
		 */
		ajax_error: function(jqXHR, textStatus){
			if(jqXHR.hasOwnProperty('responseJSON')){
				let out = 'Ошибка: ';
				if(jqXHR.responseJSON.hasOwnProperty('msg')){
					out += jqXHR.responseJSON.msg;
				}
				general_functions.rad_alert(out);
			}else{
				if(navigator.hasOwnProperty('onLine') && !navigator.onLine){
					general_functions.rad_alert('Ошибка. Вы не подключены к сети.');
				}else{
					general_functions.rad_alert('Ошибка. Попробуйте позже или обратитесь к администратору сайта.');
					console.log(jqXHR);
				}
			}
		},

		/**
		 * возвращает строку округленного объема данных с единицами измерения (конвертация через 1024) 
		 * @param {number} size
		 * @param {string} delimiter - разделитель между числом и единицей измерения 
		 * @returns {string}
		 */
		round_memsize: function(size, delimiter = ' '){
			if(!size){
				return '0';
			}

			size = +size;

			let unit = 'b';

			if(size > 1024){
				size = size/1024.0;
				unit = 'Kb';
			}

			if(size > 1024){
				size = size/1024.0;
				unit = 'Mb';
			}

			if(size > 1024){
				size = size/1024.0;
				unit = 'Gb';
			}

			if(size < 100){
				size = size.toFixed(2);
			}else if(size < 1000){
				size = size.toFixed(1);
			}else{
				size = size.toFixed(0);
			}

			return size + delimiter + unit;
		},

		/**
		 * производит транслитерацию русского текста на английский
		 * @param text
		 * @returns {string}
		 */
		url_transliterate: function(text){
			let converter = {
				'а': 'a',	'б': 'b',	'в': 'v',	'г': 'g',	'д': 'd',
				'е': 'e',	'ё': 'yo',	'ж': 'j',	'з': 'z',	'и': 'i',
				'й': 'y',	'к': 'k',	'л': 'l',	'м': 'm',	'н': 'n',
				'о': 'o',	'п': 'p',	'р': 'r',	'с': 's',	'т': 't',
				'у': 'u',	'ф': 'f',	'х': 'h',	'ц': 'c',	'ч': 'ch',
				'ш': 'sh',	'щ': 'sch',	'ь': '',	'ы': 'y',	'ъ': '',
				'э': 'e',	'ю': 'yu',	'я': 'ya'
			};

			text = (text+'').toLowerCase();

			let ret = '';
			for(let i in text){
				if(converter.hasOwnProperty(text[i])){
					ret += converter[text[i]];
				}else if(text[i] >= 'a' && text[i] <= 'z' || text[i] >= '0' && text[i] <= '9' || text[i] == '-'){
					ret += text[i];
				}else{
					ret += '-';
				}
			}
			ret = ret.replace(/[-]+/g, '-');
			ret = ret.replace(/^-+|-+$/g, '');
			return ret;
		},

		/**
		 * обертка над $.ajax
		 * @param {string} url - адрес запроса
		 * @param {string} type - метод запроса
		 * @param {object} data - передаваемые данные
		 * @param {function} success - функция обработчик, если данные успешно получены и содержат err=false
		 * @param {jQuery} $loader - элемент, с которого удаляются классы loader и submit по окончанию запроса
		 * @param {function} error - функция обработчик неудачного завершения запроса
		 */
		api_ajax: function(url, type, data, success, $loader = $(), error = general_functions.ajax_error){
			$.ajax({
				url: url,
				type: type,
				data: data,
				dataType: 'json',
				cache: false,
				success: function(result){
					if(result.hasOwnProperty('err')){
						if(result.err === false){

							success(result);

						}else{
							let out = 'Ошибка: ';
							if(result.hasOwnProperty('msg')){
								out += result.msg;
							}
							rad_alert(out);
						}
					}else{
						rad_alert('Ошибка: ');
					}
				},
				error: error,
				complete: function(){
					$loader.removeClass('loader submit');
				}
			});
		},
		
		sizeof: function(obj){
			if(Array.isArray(obj)){
				return obj.length;
			}
			if(typeof obj === 'object' && obj !== null){
				return Object.keys(obj).length;
			}
		},

		/**
		 * вставляет элемент в отсортированный массив
		 * @param {any} element
		 * @param {any[]} array
		 * @param {function(any, any):int} compare
		 */
		insert_to_order_array(element, array, compare = null){
			if(!array.length){
				array.splice(0, 0, element);
				return;
			}

			let left = 0;
			let right = array.length-1;
			let mid = 0;

			for(;left != right;){
				mid = left + (((right - left) / 2) | 0);
				if(typeof compare === 'function'){
					if(compare(element, array[mid]) > 0){
						left = mid + 1;
					}else{
						right = mid;
					}
				}else{
					if(element > array[mid]){
						left = mid + 1;
					}else{
						right = mid;
					}
				}
			}

			if(typeof compare === 'function'){
				if(compare(element, array[left]) > 0){
					left += 1;
				}
			}else{
				if(element > array[left]){
					left += 1;
				}
			}

			array.splice(left, 0, element);
		},

		/**
		 * является ли событие кликом по элементу (самый нижний элемент не динамический)
		 * или клавиатурная активация (нажатие пробела или enter)
		 * @param {event} event
		 * @param {html|null} element
		 * @return {bool}
		 */
		is_activate_event(event){
			let tag = event.target.tagName.toLowerCase();
			if(event.currentTarget !== event.target){
				switch(tag){
					case 'a':
					case 'select':
					case 'input':
					case 'button':
					case 'textarea':
						return false;
				}
			}

			if(event.type == 'keydown'){
				if((event.which != 13 && event.which != 32) || event.altKey || event.ctrlKey || event.shiftKey){
					return false;
				}
			}
			return true;
		}
	}
}