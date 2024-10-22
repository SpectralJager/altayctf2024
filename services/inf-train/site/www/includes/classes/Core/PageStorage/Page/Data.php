<?php

namespace Core\PageStorage\Page;

/**
 * @property-read string[] $inline_styles  - дополнительные стили которые будут выведены в head
 * @property-read string $addition_scripts  - строки подключения скриптов, добавляется get параметр ver={UNIX время изменения файла}
 * @property-read string $addition_styles  - строки подключения стилей, добавляется get параметр ver={UNIX время изменения файла}
 * @property-read string $addition_libs  - строки подключения библиотек
 * @property-read bool[] $scripts  - массив путей подключения доп скриптов из папки /scripts/, где путь это ключ, а значение - является ли скрипт модулем
 * @property-read bool[] $styles  - массив путей подключения доп стилей из папки /styles/, где путь это ключ
 * @property-read bool[] $libs  - массив путей подключения доп скриптов из папки /libs/, где путь это ключ, а значение - является ли библиотека модулем
 * @property array $js_data - переменные для передачи в js страницы
 */
final class Data{
	/** @var string $title - заголовок страницы */
	public $title = '';
	/** @var string $description - содержимое META NAME="description" */
	public $description = '';
	/** @var string $keywords - содержимое META NAME="keywords" */
	public $keywords = '';
	/** @var string[] $inline_styles - дополнительные стили которые будут выведены в head */
	protected $inline_styles;
	/** @var array $js_data - переменные для передачи в js страницы */
	protected $js_data;
	/** @var bool[] $scripts - массив путей подключения доп скриптов из папки /scripts/, где путь это ключ, а значение - является ли скрипт модулем */
	protected $scripts;
	/** @var bool[] $styles - массив путей подключения доп стилей из папки /styles/, где путь это ключ */
	protected $styles;
	/** @var bool[] $libs - массив путей подключения доп скриптов из папки /libs/, где путь это ключ, а значение - является ли библиотека модулем */
	protected $libs;

	// мета теги Open Graph
	/** @var string $og_title - og:title */
	public $og_title = '';
	/** @var string $og_description - og:description */
	public $og_description = '';
	/** @var string $og_type - og:type */
	public $og_type = 'website';
	/** @var string $og_image - og:image, ссылка на картинку страницы */
	public $og_image = '';
	/** @var string $og_url - og:url, каноническая ссылка на страницу */
	public $og_url = '';
	/** @var string $og_locale - og:locale, язык описания объекта */
	public $og_locale = 'ru_RU';
	/** @var string $og_site_name - og:site_name, название сайта, на котором размещена информация об объекте */
	public $og_site_name = '';

	/** @var array $addition_data - массив дополнительных свойств страницы */
	protected $addition_data;

	public function __construct(){
		$this->addition_data = [];
		$this->inline_styles = [];
		$this->scripts = [];
		$this->styles = [];
		$this->libs = [];
		$this->js_data = [];
	}

	/**
	 * @param string $style - строка со стилем, уникальность не проверяется
	 */
	public function add_inline_style(string $style){
		$this->inline_styles[] = $style;
	}

	/**
	 * если свойство $key не пустое, вернет `<meta name="$meta_name" content="Значение_свойства">`, иначе пустую строку
	 * @param string $meta_name
	 * @param null|string $key - ключ свойства страницы, если null будет использован $meta_name
	 * @return string
	 */
	public function get_filled_meta_name($meta_name, $key = null){
		if(empty($key)){
			$key = $meta_name;
		}

		$val = (string)($this->$key);
		return mb_strlen($val) ? '<meta name="'.esc_attr($meta_name).'" content="'.esc_attr($val).'">' : '';
	}

	/**
	 * если свойство $key не пустое, вернет `<meta property="$meta_property" content="Значение_свойства">`, иначе пустую строку
	 * @param string $meta_property
	 * @param null|string $key - ключ свойства страницы, если null будет использован $meta_property
	 * @return string
	 */
	public function get_filled_meta_property($meta_property, $key = null){
		if(empty($key)){
			$key = $meta_property;
		}

		$val = (string)($this->$key);
		return mb_strlen($val) ? '<meta property="'.esc_attr($meta_property).'" content="'.esc_attr($val).'">' : '';
	}

	public function get_addition_scripts(){
		static $addition_scripts = null;
		if(is_null($addition_scripts)){
			$addition_scripts = '';
			foreach($this->scripts as $path => $is_module){
				$addition_scripts .= '<script type="'.($is_module ? 'module' : 'text/javascript').'" src="/scripts/'.$path.'?ver='.filemtime(MAIN_DIR.'scripts/'.$path).'"></script>'.PHP_EOL;
			}
		}
		return $addition_scripts;
	}

	public function get_addition_styles(){
		static $addition_styles = null;
		if(is_null($addition_styles)){
			$addition_styles = '';
			foreach($this->styles as $path => $tmp){
				$addition_styles .= '<link rel="stylesheet" type="text/css" href="/styles/'.$path.'?ver='.filemtime(MAIN_DIR.'styles/'.$path).'"/>'.PHP_EOL;
			}
		}
		return $addition_styles;
	}

	public function get_addition_libs(){
		static $addition_libs = null;
		if(is_null($addition_libs)){
			$addition_libs = '';
			foreach($this->libs as $path => $is_module){
				$addition_libs .= '<script type="'.($is_module ? 'module' : 'text/javascript').'" src="/libs/'.$path.'"></script>'.PHP_EOL;
			}
		}
		return $addition_libs;
	}

	public function __get($key){
		switch($key){
			case 'addition_scripts':
			case 'addition_styles':
			case 'addition_libs':
				return $this->{'get_'.$key}();
		}
		
		if(!property_exists($this, $key)){
			if(array_key_exists($key, $this->addition_data)){
				return $this->addition_data[$key];
			}else{
				trigger_error('try to get undefined property '.$key, E_USER_WARNING);
				return null;
			}
		}else{
			return $this->$key;
		}
	}

	public function __set($key, $value){
		switch($key){
			case 'addition_data':
			case 'inline_styles':
			case 'scripts':
			case 'styles':
			case 'libs':
				trigger_error('try to set private property '.$key, E_USER_ERROR);
			case 'js_data':
				if(!is_array($value)){
					trigger_error('try to set not array value to property js_data', E_USER_ERROR);
				}
				$this->$key = $value;
				return;
		}

		if(!property_exists($this, $key)){
			$this->addition_data[$key] = $value;
		}else{
			$this->$key = (string) $value;
		}
	}

	/**
	 * @param string $script_name - путь до скрипта в папке /scripts/
	 * @param bool $is_module - является ли скрипт модулем
	 * @return bool добавлен ли скрипт
	 */
	public function add_script($script_name, bool $is_module = false){

		$script_name = trim($script_name);
		if(array_key_exists($script_name, $this->scripts))
			return true;
		
		$path = MAIN_DIR.'scripts/'.$script_name;
		if(!check_file($path)){
			trigger_error('script not find, path:'.$path, E_USER_WARNING);
			return false;
		}
		$this->scripts[$script_name] = $is_module;
		
		return true;
	}

	/**
	 * @param string $style_name - путь до скрипта в папке /styles/
	 * @return bool добавлен ли стиль
	 */
	public function add_style($style_name){

		$style_name = trim($style_name);
		if(array_key_exists($style_name, $this->styles))
			return true;

		$path = MAIN_DIR.'styles/'.$style_name;
		if(!check_file($path)){
			trigger_error('style not find, path:'.$path, E_USER_WARNING);
			return false;
		}
		$this->styles[$style_name] = false;

		return true;
	}

	/**
	 * @param string $lib_name - путь до скрипта в папке /libs/
	 * @param bool $is_module - является ли скрипт модулем
	 * @return bool добавлен ли скрипт
	 */
	public function add_lib($lib_name, bool $is_module = false){

		$lib_name = trim($lib_name);
		if(array_key_exists($lib_name, $this->libs))
			return true;

		$path = MAIN_DIR.'libs/'.$lib_name;
		if(!check_file($path)){
			trigger_error('lib not find, path:'.$path, E_USER_WARNING);
			return false;
		}
		$this->libs[$lib_name] = $is_module;

		return true;
	}
}
