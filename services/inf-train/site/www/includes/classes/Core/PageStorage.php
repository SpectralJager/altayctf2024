<?php

namespace Core;

use Core\PageStorage\Page;
use Pages\InfTrain\Login;

final class PageStorage{

	/** @var PageStorage $_instance */
	protected static $_instance;
	

	/** @var array $loaded_page_classes - массив имен классов (ключи) для отображения страниц */
	protected $loaded_page_classes;
	
	/** @var Page $current_page - экземпляр текущей страницы */
	protected $current_page = null;

	/**
	 * @return PageStorage возвращает экземпляр класса
	 */
	public static function instance(){
		if(is_null(self::$_instance)){
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	private function __clone(){
		throw new \Exception('try to clone singleton '.self::class);
	}

	public function __wakeup(){
		throw new \Exception('try to wakeup singleton '.self::class);
	}
	
	

	private function __construct(){
		$this->loaded_page_classes = [];

		spl_autoload_register([$this, 'autoload_page_classes'], true, true);
		
		$classes = file_list_recursive(MAIN_DIR.'includes/classes/Pages/', '.php');
		$classes = file_list_to_class_names($classes, '\\Pages\\');
		$len = sizeof($classes);
		for($i=0; $i<$len; $i++){
			
			$class_name = $classes[$i];
			if(trait_exists($class_name)){
				continue;
			}
			call_user_func([$class_name, 'set_url']);
		}
		
		spl_autoload_unregister([$this, 'autoload_page_classes']);
	}
	
	private function autoload_page_classes($class){
		if(preg_match('#^Pages\\\\#', $class)){
			$file = MAIN_DIR.'includes/classes/'.str_replace('\\', '/', $class).'.php';
			if(check_file($file)){
				require_once $file;
				$this->loaded_page_classes[$class] = true;
			}
		}
	}

	/**
	 * загружает экземпляр текущей страницы основываясь на Url::get_current_page
	 * @see Url::get_current_page
	 */
	public function load_current_page(){
		global $URL;
		$page_ind = $URL->get_current_page();
		if(is_null($this->current_page) || $this->current_page::get_ind() !== $page_ind){
			$this->current_page = new $page_ind();
		}
	}

	/**
	 * производит проверку на разрешение отображение текущей страницы (вызывает Page::test_view)
	 * если отображение текущей страницы запрещена будет установлена 404 страница или произведен редирект или изменена отображаемая страница
	 * @see Page::test_view()
	 */
	private function test_page(){
		global $USER, $URL;
		
		do{
			$this->load_current_page();
			$curr_p = $this->current_page;

			//если гость пытается зайти на страницу, где нужно быть зарегистрированным 
			//отображаем страницу входа
			if(!$USER->id && $curr_p::get_static('private')){
				$URL->set_current_page(Login::get_ind());
				$this->load_current_page();
				return;
			}

			//собственная проверка страницы
			if(!$curr_p->test_view()){
				//nocache_headers();
				$URL->set_current_404();
				$this->load_current_page();
				return;
			}
			
			//повторяем проверку прав в случае замены отображаемой страницы
		}while($curr_p::get_ind() !== $URL->get_current_page());
		
	}

	/**
	 * Производит проверку отображения страницы и подготавливает данные для её отображения
	 * @see test_page
	 * @see Page::prepare_page_data()
	 */
	public function prepare_current_page(){
		$this->load_current_page();
		$this->test_page();

		$this->current_page->prepare_page_data();
	}
	
	/**
	 * выводит текущую страницу
	 * @see Page::render()
	 */
	public function view_current_page(){
		$this->load_current_page();

		$this->current_page->render();
	}
}