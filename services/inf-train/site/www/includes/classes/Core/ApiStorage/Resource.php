<?php

namespace Core\ApiStorage;

abstract class Resource{

	/** @var bool $private - доступ к странице только зарегистрированному пользователю */
	protected static $private = false;
	/** @var string $url_slice - строка-уровень используемая в url, пустая строка означает, что данный ресурс параметризован */
	protected static $url_slice = '';
	/** @var array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы */
	protected static $allowed_methods = true;

	/**
	 */
	final public function __construct(string $method){
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
	 * string $url_slice - строка-уровень используемая в url
	 * array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы
	 * </pre>
	 * @return mixed|null
	 */
	final public static function get_static($key){
		switch($key){
			case 'private':
				return static::$private;
			case 'url_slice':
				return static::$url_slice;
			case 'allowed_methods':
				return static::$allowed_methods;
			default:
				return null;
		}
	}

	/**
	 * @return bool - является ли ресурс параметрическим и не имеет кусочка url'а
	 */
	final public static function is_parametrised():bool{
		return static::$url_slice === '';
	}

	/**
	 * Вызывается первым для установок настроек ЧПУ (кусочек url) текущей страницы.
	 * Устанавливает настройку только 1 раз
	 * дочерняя реализация должна содержать в начале вызов родительской реализации, также проверку на $URL->has_page(self::get_ind())
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
	 * Дочерний класс проверяет разрешение на доступ к ресурсу или производит редирект на другой ресурс
	 * @param string $method - метод запроса в нижнем регистре, например 'post', 'head', 'get'
	 * @return bool - true - доступ к ресурсу будет получен, false - доступ будет к 404 ресурсу
	 */
	public function test_exec(string $method):bool{
		return true;
	}

	/**
	 * Вызывается после test_exec, обработка текущего запроса для методов, реализация которых не существует
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => mixed - ответ метода, будет закодирован с помощью json_encode 
	 * 	'status' => int - http код ответа
	 * 	'log_str' => null|string - строка для записи в лог
	 * ]
	 * </pre>
	 */
	abstract public function execute();

	/**
	 * @return string - идентификатор ресурса (название класса)
	 */
	final public static function get_ind(){
		return static::class;
	}

	/**
	 * @return string|null - идентификатор родительского ресурса (если он есть) (название родительского класса)
	 */
	final public static function get_parent_ind(){
		$parent_class = get_parent_class(static::class);
		return !$parent_class || $parent_class === self::class ? null : call_user_func([$parent_class, 'get_ind']);
	}
}