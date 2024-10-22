<?php
namespace Pages\InfTrain;
use Core\Image;
use Pages\InfTrain;

class Admin extends InfTrain{

	/** @var bool $private - доступ к странице только зарегистрированному пользователю */
	protected static $private = true;
	/** @var string $url_slice - строка-уровень используемая в url, наследуется от родителя, пустая строка означает, что данный уровень параметризован */
	protected static $url_slice = 'admin';
	
	/** @var string $title - заголовок страницы по умолчанию, используется когда экземпляр страницы не создается */
	protected static $title = 'Панель администратора';
	
	/** @var Image[] $images - изображения текущего пользователя */
	protected $images  = [];

	public function get_title(){
		return self::$title;
	}
	
	public function prepare_page_data(){
		
		global $USER;
		
		if($this->page_data_prepared)
			return;

		$this->images = Image\Factory::get_from_storage($USER->storage);
		
		$this->page_data->add_script('admin-script.js');
		$this->page_data->add_script('images-upload.js');

		InfTrain::prepare_page_data();

		$this->page_data_prepared = true;
	}



	/**
	 * Возвращает html блок для ввода текста (input text)
	 * @param string $form_name - имя формы, внутри которой расположен блок
	 * @param string $name - имя input
	 * @param string $label_html - метка, в формате html
	 * @param string $value - содержимое input
	 * @param string $disabled_type - модификатор работы поля, '' | 'readonly' | 'disabled'
	 * @return string
	 */
	protected function get_input($form_name, $name, $label_html, $value, $disabled_type=''){
		global $URL;
		$form_name = esc_attr($form_name);
		$name = esc_attr($name);
		$value = esc_attr($value);
		$id = $form_name.'-'.$name;
		switch($disabled_type){
			case 'readonly':
			case 'disabled':
				$disabled_type = ' '.$disabled_type.'="'.$disabled_type.'"';
				break;
			default:
				$disabled_type = '';
		}
		
		return '
			<div class="admin-form__left">
				<label for="'.$id.'">'.$label_html.':</label>
				<input autocomplete="off" type="text" id="'.$id.'" name="'.$name.'" value="'.$value.'"'.$disabled_type.'>
			</div>
		';
	}

	/**
	 * Возвращает html блок для карточки изображения с миниатюрой и ссылкой на оригинал
	 * @param Image $image
	 * @param string $class_prefix - префикс классов для блока
	 * @param bool $disable_focus - Выключить фокус у карточки
	 * @param bool $reject_button - Отображать кнопку с крестом
	 * @return string
	 */
	protected function get_image_card(Image $image, string $class_prefix, bool $disable_focus = false, bool $reject_button = false){
		$class_prefix = esc_attr($class_prefix);
		return '
			<div id="image-card-'.$image->id.'" class="'.$class_prefix.'__img__block" data-image-id="'.$image->id.'">
				<div class="'.$class_prefix.'__img__image">
					<img loading="lazy" height="'.$image->image_h.'" width="'.$image->image_w.'" src="/'.esc_attr($image->get_image_path()).'">
				</div>
				<div class="'.$class_prefix.'__img__header">
					<p><span class="time-create">'.$image->time_create->format('d.m.Y H:i').'</span></p>
					<p>'.round_memsize($image->file_size).' '.$image->image_w.'×'.$image->image_h.'</p>
					<p>'.esc_html($image->name).'.<span class="type">'.mb_strtolower($image->type).'</span></p>
				</div>
				<span class="'.$class_prefix.'__img__public">
					<input type="checkbox" class="eye" data-js="api_pulication" data-url="/api/images/'.$image->id.'/publication/" title="Видимость"'.set_checked($image->public).'>
				</span>
				'.($reject_button ? '<button type="button" class="'.$class_prefix.'__img__reject btn" data-js="api_delete" data-url="/api/images/'.$image->id.'/" title="Удалить">✗</button>' : '').'
			</div>';
	}
	
	protected function render_body(){
		?>
		<body class="admin">
			<div class="admin__header">
				<h3>Мои изображения</h3>
				<a href="javascript:void(0)" data-js="logout">Выйти</a>
			</div>
			<div class="images__container">
				<?php
					foreach($this->images as $id => $image){
						echo self::get_image_card($image, 'images', 1, 1);
					}
				?>
			</div>
			<hr>
			<h3>Добавить изображения</h3>
			<div class="image-upload-page">
				<form id="upload-image" name="upload-image" class="image-upload-page__container" method="post" action="/api/images/">
					<input type="hidden" name="MAX_FILE_SIZE" value="<?= get_MAX_FILE_SIZE() ?>">
					<svg class="image-upload-page__upload" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path stroke="var(--upload-icon-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 15V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H7.8C6.11984 21 5.27976 21 4.63803 20.673C4.07354 20.3854 3.6146 19.9265 3.32698 19.362C3 18.7202 3 17.8802 3 16.2V15M17 10L12 15M12 15L7 10M12 15V3"/>
					</svg>
					<div class="image-upload-page__data">
						<input id="file-input" type="file" name="images" accept="image/jpeg,image/png" multiple>
						<label for="file-input">Выберите изображения</label>
						<span>или перетащите их сюда (<span class="form-fullness">0</span>&nbsp;/&nbsp;<span class="form-filesize"><?= round_memsize(get_MAX_FILE_SIZE(), '&nbsp;') ?></span>)</span>
					</div>
				</form>
				<div class="image-upload-page__switcher">
					<div class="image-upload-page__switcher__container">
					</div>
				</div>
				<img class="image-upload-page__preview" id="image-preview">
				<input class="btn btn-primary" type="submit" value="Загрузить" disabled="disabled" form="upload-image">
			</div>
		</body>
		<?php
	}
}