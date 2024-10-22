<?php
namespace Pages\InfTrain;
use Core\Image;
use Pages\InfTrain;

final class Main extends InfTrain{

	/** @var string $title - заголовок страницы по умолчанию, используется когда экземпляр страницы не создается */
	protected static $title = 'Infinity Train';
	
	const SCALE_COEF = 3;

	/** @var Image[] $images - изображения текущего пользователя */
	protected $images  = [];
	
	public function get_title(){
		return self::$title;
	}

	public function prepare_page_data(){
		
		if($this->page_data_prepared)
			return;
		
		$this->images = array_reverse(Image\Factory::get_all_public(1));

		InfTrain::prepare_page_data();
		
		$this->page_data->add_script('main.js');
		
		$this->page_data_prepared = true;
	}

	protected function render_body(){
		global $URL;
		?>
		<body class="main">
			<div class="horizon"></div>
			<div class="land">
				<div class="path">
					<div class="track">
						<?php
						foreach($this->images as $img){
							$user_data = Image\Factory::get_user_data($img);
							echo '<img
	class="wagon" loading="lazy"
	alt="Изображение #'.$img->id.'"
	src="/'.esc_attr($img->get_image_path()).'"
	data-time="'.esc_attr($img->time_create->format(DB_DATETIME_FORMAT)).'"
	data-user-first-name="'.esc_attr($user_data['first_name']).'"
	data-user-last-name="'.esc_attr($user_data['last_name']).'"
	height="'.($img->image_h * self::SCALE_COEF).'"
	width="'.($img->image_w * self::SCALE_COEF).'">'.PHP_EOL;
						}
						?>
						<img class="locomotive" alt="Локомотив" src="/assets/train.apng" height="<?= 112 * self::SCALE_COEF ?>" width="<?= 144 * self::SCALE_COEF ?>">
					</div>
				</div>
			</div>
			<div class="cloud">
				<div class="cloud__container"></div>
			</div>
			<a class="sun" href="<?= $URL->get_url(Login::class)?>" title="Вход"></a>
		</body>
		<?php
	}

}