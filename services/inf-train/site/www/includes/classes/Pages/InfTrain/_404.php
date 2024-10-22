<?php
namespace Pages\InfTrain;
use Pages\InfTrain;

final class _404 extends InfTrain{

	/** @var string $title - заголовок страницы по умолчанию, используется когда экземпляр страницы не создается */
	protected static $title = 'Ошибка 404';
	
	public function get_title(){
		return self::$title;
	}
	
	public function prepare_page_data(){
		global $URL;

		if($this->page_data_prepared)
			return;

		InfTrain::prepare_page_data();
		
		$this->page_data_prepared = true;
	}

	protected function render_body(){
		?>
		<body class="_404">
			<div class="page-section">
				<h2 style="text-align:center;margin-bottom:20px">Ошибка&nbsp;404&nbsp;- страница не&nbsp;найдена</h2>
			</div>
		</body>
		<?php
	}

}