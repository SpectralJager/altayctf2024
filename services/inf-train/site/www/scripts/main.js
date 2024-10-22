jQuery(document).ready(function($){
	
	let $track = $('.track');
	let $path = $track.closest('.path');
	let scroll = $track.outerWidth() - $path.outerWidth();
	if(scroll > 0){
		$path.scrollLeft(scroll);
	}
	
	let $cloud = $('.cloud__container');

	$track
		.on('mouseenter.help', '.wagon,.locomotive', function(e){
			let $this = $(this);
			
			let $message = $(document.createDocumentFragment());
			
			if($this.hasClass('locomotive')){
				$message.append([
					$('<p>').append([
						$('<strong>', {text: 'Локомотив: '}),
						$(document.createDocumentFragment()).text('Infinity Train'),
					]),
					$('<p>').append([
						$('<strong>', {text: 'Автор: '}),
						$(document.createDocumentFragment()).text('RADIOFAN'),
					]),
				]);
			}else{
				let ind = $track.children().length-1 - $this.index();
				
				$message.append([
					$('<p>').append(
						$('<strong>', {text: 'Вагон №'+ind})
					),
					$('<p>').append([
						$('<strong>', {text: 'Имя: '}),
						$(document.createDocumentFragment()).text($this.data('user-first-name')),
					]),
					$('<p>').append([
						$('<strong>', {text: 'Фамилия: '}),
						$(document.createDocumentFragment()).text($this.data('user-last-name')),
					]),
					$('<p>').append([
						$('<strong>', {text: 'Дата загрузки: '}),
						$(document.createDocumentFragment()).text($this.data('time')),
					]),
					$('<p>').append([
						$('<strong>', {text: 'Высота: '}),
						$(document.createDocumentFragment()).text(($this.height()/3)+'px'),
					]),
					$('<p>').append([
						$('<strong>', {text: 'Ширина: '}),
						$(document.createDocumentFragment()).text(($this.width()/3)+'px'),
					]),
				]);
			}

			$cloud.html($message);
		});
});