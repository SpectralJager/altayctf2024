<?php

namespace Pages;

use Core\PageStorage\Page;
use Core\Request;

class InfTrain extends Page{

	final public static function get_ind(){
		return static::class === self::class ? null : static::class;
	}

	public function prepare_page_data(){
		$this->page_data->add_script('script.js', false);
		$this->page_data->add_lib('functions.js', false);
		
		
		$this->page_data->title = $this->get_title().' | '.Request::instance()->site_name;
	}

	protected function render_header(){
		?>
		<!DOCTYPE html>
		<html lang="ru">
		<head>
			<meta http-equiv="content-type" content="text/html; charset=UTF-8">
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?= esc_html($this->page_data->title) ?></title>

			<link rel="icon" href="/favicon.ico" sizes="any">


			<link rel="stylesheet" type="text/css" href="/styles/style.css?ver=<?= filemtime(MAIN_DIR. 'styles/style.css'); ?>">
			<?= $this->page_data->addition_styles ?>
			<?php
			if(sizeof($this->page_data->inline_styles)){
				echo '<style>'.implode(PHP_EOL, $this->page_data->inline_styles).'</style>';
			}
			?>

			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
			<script>window.jQuery || document.write('<script src="/libs/jquery-3.4.1.min.js"><\/script>')</script>
			<script type="text/javascript">
				/* <![CDATA[ */
				var DATA = <?= json_encode($this->page_data->js_data) ?>;
				/* ]]> */
			</script>
			<?= $this->page_data->addition_libs ?>

			<meta name="robots" content="none">
		</head>

		<?php
	}


	protected function render_footer(){

		echo $this->page_data->addition_scripts;
		?>
		</html>
		<?php
	}

}