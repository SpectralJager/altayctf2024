<?php
namespace Core;

use Core\CookieStorage\Cookie;
use Exception;

final class CookieStorage{
	
	/** @var Cookie[] - полученные сайтом cookie, дополнительные свойства cookie установлны по умолчанию */
	private $received_cookies;
	/** @var Cookie[] - добавленные/измененные сайтом cookie */
	private $add_cookies;
	/** @var Cookie[] - удаленные сайтом cookie */
	private $delete_cookies;
	/** @var bool $cookies_sended - отправлены ли заголовки cookie */
	private $cookies_sended = false;
	
	/** @var Cookie $_instance */
	private static $_instance = null;

	public static function instance(){
		if(is_null(self::$_instance)){
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	private function __clone(){
		throw new Exception('try to clone singleton '.self::class);
	}
	public function __wakeup(){
		throw new Exception('try to wakeup singleton '.self::class);
	}
	private function __construct(){
		$this->add_cookies = [];
		$this->delete_cookies = [];

		$this->received_cookies = [];
		//someday сделать ручной парсинг cookie для получения дополнительных свойств cookie
		if(isset($_COOKIE)){
			foreach($_COOKIE as $name => $value){
				$this->received_cookies[$name] = new Cookie($name, $value);
			}
		}
	}

	/**
	 * Возвращает, отправлены ли уже cookie
	 * @return bool
	 */
	public function is_cookie_sended(){
		return $this->cookies_sended;
	}

	/**
	 * Проверяет существование cookie
	 * @param string $name - имя cookie
	 * @return bool
	 */
	public function has(string $name){
		return isset($this->add_cookies[$name]) || (isset($this->received_cookies[$name]) && !isset($this->delete_cookies[$name]));
	}

	/**
	 * Возвращает cookie, если он установлен
	 * @param string $name - имя cookie
	 * @return Cookie|null
	 */
	public function get(string $name){
		if(isset($this->add_cookies[$name]))
			return $this->add_cookies[$name];
		if(isset($this->received_cookies[$name]) && !isset($this->delete_cookies[$name]))
			return $this->received_cookies[$name];
		return null;
	}

	/**
	 * Возвращает значение cookie, если он установлен
	 * @param string $name - имя cookie
	 * @return mixed|null
	 */
	public function get_value(string $name){
		if(isset($this->add_cookies[$name]))
			return $this->add_cookies[$name]->value;
		if(isset($this->received_cookies[$name]) && !isset($this->delete_cookies[$name]))
			return $this->received_cookies[$name]->value;
		return null;
	}

	/**
	 * Устанавливает cookie
	 * @throws Exception - 'Cookie headers already sended'
	 */
	public function set(Cookie $cookie){
		if($this->cookies_sended)
			throw new Exception('Cookie headers already sended');
		
		$name = $cookie->name;
		
		if(isset($this->delete_cookies[$name])){
			unset($this->delete_cookies[$name]);
		}

		$this->add_cookies[$name] = clone $cookie;
	}

	/**
	 * Устанавливает cookie
	 * @throws Exception - 'Cookie headers already sended'
	 */
	public function set_cookie(string $name, $value, int $expires, $raw = false){
		if($this->cookies_sended)
			throw new Exception('Cookie headers already sended');


		if(isset($this->delete_cookies[$name])){
			unset($this->delete_cookies[$name]);
		}

		$this->add_cookies[$name] = new Cookie($name, $value, $raw, ['expires' => $expires]);
	}

	/**
	 * Удаляет cookie по указанному имени
	 * 
	 * @param string $name - имя cookie
	 * @throws Exception - 'Cookie headers already sended'
	 * @return false|Cookie - Если cookie с таким именем был уже установлен, то вернет объект установленного cookie.
	 * Если cookie с таким именем был добавлен, то вернет объект добавленного cookie.
	 * Во всех других случаях false.
	 */
	public function delete(string $name){
		if($this->cookies_sended)
			throw new Exception('Cookie headers already sended');
		
		$ret = null;
		
		if(isset($this->delete_cookies[$name])){
			return false;
		}

		if(isset($this->add_cookies[$name])){
			$ret = $this->add_cookies[$name];
			unset($this->add_cookies[$name]);
		}
		if(isset($this->received_cookies[$name])){
			$ret = $this->received_cookies[$name];

			$this->delete_cookies[$name] = clone $ret;
			$this->delete_cookies[$name]->expires = time() - 10*SECONDS_PER_DAY;
		}else{
			return false;
		}
		
		return $ret;
	}
	
	public function send_cookies(){
		if($this->cookies_sended)
			throw new Exception('Cookie headers already sended');
		
		$cookie_for_delete = [];

		//установка cookie
		foreach($this->add_cookies as $cookie_name => $cookie){

			$setted_names = [];
			$this->setcookie_array($cookie_name, $cookie->value, $cookie, $setted_names);
			
			//если был получен массив cookie и с этим массивом было взаимодействие
			if(isset($this->received_cookies[$cookie_name])){
				$recived_cookie_value = $this->received_cookies[$cookie_name]->value;
				if(is_array($recived_cookie_value)){
					/*
					//то нужно обрубить ветви которые при установки нового значения cookie отсутсвовали
					
					старое значение: z => [a=>[0 => 0, 1 => 1], b=>[a=>0, b=>1]]
					старое значение как оно записано в cookie:
						z[a][0] = 0
						z[a][1] = 1
						z[b][a] = 0
						z[b][b] = 1
					новое значение: z => [a=>[0 => 15, 3 => 4], b=>12]
					новое значение как оно записано в cookie:
						z[a][0] = 15
						z[a][3] = 4
						z[b] = 12
					требуется удалить следующие записи:
						z[a][1]
						z[b][a]
						z[b][b]
					*/
					
					$recived_cookie_names = [];
					$this->get_array_cookie_names($cookie_name, $recived_cookie_value, $recived_cookie_names);
					$cookie_for_delete[] = ['cookie' => $this->received_cookies[$cookie_name], 'names' => array_values(array_diff($recived_cookie_names, $setted_names))];
				}
			}
			
			
		}
		
		//удаление cookie
		$len = sizeof($cookie_for_delete);
		for($cookie_i=0; $cookie_i<$len; $cookie_i++){
			$len_names = sizeof($cookie_for_delete[$cookie_i]['names']);
			for($name_i=0; $name_i<$len_names; $name_i++){
				$this->delcookie_array($cookie_for_delete[$cookie_i]['names'][$name_i], '', $cookie_for_delete[$cookie_i]['cookie']);
			}
		}
		
		foreach($this->delete_cookies as $cookie_name => $cookie){
			$this->delcookie_array($cookie_name, $cookie->value, $cookie);
		}
		
		$this->cookies_sended = true;
	}

	/**
	 * в пустой массив $names помещает имена массивного cookie
	 * если cookie имеет вид: $name => $value,
	 * где $name = 'z', $value = [a=>[0 => 0, 1 => 1], b=>[a=>0, b=>1]]
	 * $names будет иметь вид: ['z[a][0]', 'z[a][1]', 'z[b][a]', 'z[b][b]']
	 * @param string $name - имя массивного cookie
	 * @param string|array $value содержимое cookie, если это массив, то для каждого элемента функция вызовится рекурсивно, к имени cookie будет добавлен [array_key]
	 * @param string[] $names - массив в который будут помещены имена массивного cookie
	 */
	private function get_array_cookie_names(string $name, $value, &$names){
		if(!is_array($value)){
			$names[] = $name;
		}else{
			foreach($value as $key => &$val){
				$this->get_array_cookie_names($name.'['.urlencode($key).']', $val, $setted_names);
			}
		}
	}
	
	/**
	 * Вызывает функцию установки cookie, если $value массив, то рекурсивно вызывается
	 * @param string $name - имя cookie, в ходе рекурсивного вызова обрастает элементами типа [subname][subname...]
	 * @param string|array $value - устанавливаемое значение, если это массив, то для каждого элемента функция вызовится рекурсивно, к имени cookie будет добавлен [array_key]
	 * @param Cookie $cookie
	 * @param string[] $setted_names - массив, который будет заполнен именами устанновленных cookie
	 */
	private function setcookie_array(string $name, $value, Cookie $cookie, &$setted_names=[]){
		if(!is_array($value)){
			if($cookie->raw){
				setrawcookie($name, (string)$value, $cookie->get_options());
			}else{
				setcookie($name, (string)$value, $cookie->get_options());
			}
			$setted_names[] = $name;
		}else{
			foreach($value as $key => &$val){
				$this->setcookie_array($name.'['.urlencode($key).']', $val, $cookie, $setted_names);
			}
		}
	}

	/**
	 * Вызывает функцию удаления (установки пустого значения и прошедшего времени cookie, если $value массив, то рекурсивно вызывается
	 * @param string $name - имя cookie, в ходе рекурсивного вызова обрастает элементами типа [subname][subname...]
	 * @param string|array $value - если это массив, то для каждого элемента функция вызовится рекурсивно, к имени cookie будет добавлен [array_key]
	 * @param Cookie $cookie
	 * @param string[] $deleted_names - массив, который будет заполнен именами удаленных cookie
	 */
	private function delcookie_array(string $name, $value, Cookie $cookie, &$deleted_names=[]){
		if(!is_array($value)){
			$options = $cookie->get_options();
			$options['expires'] = time() - 10*SECONDS_PER_DAY;
			setcookie($name, '', $options);
			$deleted_names[] = $name;
		}else{
			foreach($value as $key => &$val){
				$this->delcookie_array($name.'['.urlencode($key).']', $val, $cookie, $deleted_names);
			}
		}
	}
}
