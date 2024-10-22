<?php

namespace Core\PageStorage;

/**
 * Class Page
 * содержит верстку header и footer
 * 
 * @property-read Page\Data $page_data - свойства страницы
 * @property-read bool $page_data_prepared - подготовлены ли данные страницы
 */
abstract class Page{

	/** @var bool $private - доступ к странице только зарегистрированному пользователю */
	protected static $private = false;
	/** @var string $url_slice - строка-уровень используемая в url, пустая строка означает, что данный уровень параметризован */
	protected static $url_slice = '';
	/** @var string $title - заголовок страницы по умолчанию, используется когда экземпляр страницы не создается */
	protected static $title = '';
	
	
	/** @var Page\Data $page_data - свойства страницы */
	private $page_data;
	
	/** @var bool $page_data_prepared - prepare_page_data был вызван
	 * @see prepare_page_data 
	 */
	protected $page_data_prepared = false;

	/**
	 * инициализирует свойства, заполняет массив $need_roles, вызывает add_roles_for_page
	 * @see add_roles_for_page
	 */
	final public function __construct(){

		$this->page_data = new Page\Data();
	}
	
	final public function __get($key){
		if(property_exists($this, $key)){
			return $this->$key;
		}else{
			trigger_error('try to get undefined property '.$key, E_USER_WARNING);
			return null;
		}
	}

	/**
	 * @param $key - 
	 * <pre>
	 * bool $private - доступ к странице только зарегистрированному пользователю
	 * string $url_slice - строка-уровень используемая в url, пустая строка означает, что данный уровень параметризован
	 * string $title - заголовок страницы по умолчанию, используется когда экземпляр страницы не создается
	 * </pre>
	 * @return mixed|null
	 */
	public static function get_static($key){
		switch($key){
			case 'private':
				return static::$private;
			case 'url_slice':
				return static::$url_slice;
			case 'title':
				return static::$title;
			default:
				return null;
		}
	}

	/**
	 * возвращает заголовок текущего экземпляра страницы (не тот что отображается в &lt;title&gt;)
	 * @return string
	 */
	public function get_title(){
		return '';
	}

	/**
	 * @return bool - является ли уровень параметрическим и не имеет кусочка url'а
	 */
	final public static function is_parametrised():bool{
		return static::$url_slice === '';
	}

	/**
	 * Вызывается первым для установок настроек ЧПУ (кусочек url) текущей страницы.
	 * Устанавливает настройку только 1 раз
	 * автоматически вызывает родительские реализации
	 */
	final public static function set_url(){
		global $URL;
		$ind = static::get_ind();
		if(is_null($ind)){
			return;
		}
		$parent_ind = static::get_parent_ind();
		if(!$URL->has_page($ind)){
			if(!is_null($parent_ind) && !$URL->has_page($parent_ind)){
				call_user_func([$parent_ind, 'set_url']);
			}
			
			$URL->add_page($ind, static::$url_slice, static::get_parent_ind());
		}
	}

	/**
	 * Дочерний класс
	 * проверяет разрешение на отображение данной страницы
	 * или производит редирект на другую страницу
	 * или изменяет текущий URL на другую страницу, проверка прав новой страницы также будет проводиться
	 * Проверка требуемых прав проводится до вызова этого метода
	 * @see redirect() 
	 * @see \Core\Url::redirect()
	 * @see \Core\Url::set_current_page() 
	 * @return bool - true - страница отобразится, false - будет отображена 404 страница
	 */
	public function test_view():bool{
		return true;
	}

	/**
	 * Генерирует динамические данные, требуемые для отображения текущей страницы
	 * Дочерний класс должен переопределять данный метод
	 */
	public function prepare_page_data(){}

	/**
	 * Вызывается после prepare_page_data, рендер текущей страницы
	 * 
	 * @param bool $return - true - возвращает рендер страницы в строке, false - выводит страницу 
	 * @return string
	 */
	final public function render($return = false){
		
		if($return){
			ob_start();
		}
		
		$this->render_header();
		$this->render_body();
		$this->render_footer();
		
		if($return){
			return ob_get_clean();
		}
		return '';
	}


	/**
	 * Выводит заголовок страницы
	 */
	protected function render_header(){}

	/**
	 * Выводит тело страницы
	 */
	protected function render_body(){}

	/**
	 * Выводит подвал страницы
	 */
	protected function render_footer(){}
	
	

	/**
	 * @return string|null - идентификатор страницы (название класса)
	 */
	public static function get_ind(){
		return static::class;
	}

	/**
	 * @return string|null - идентификатор родительской страницы (если он есть) (название родительского класса, кроме \Core\PageStorage\Page)
	 */
	final public static function get_parent_ind(){
		$parent_class = get_parent_class(static::class);
		return !$parent_class || $parent_class === self::class ? null : call_user_func([$parent_class, 'get_ind']);
	}
}