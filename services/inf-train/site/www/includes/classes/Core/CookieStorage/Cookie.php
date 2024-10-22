<?php

namespace Core\CookieStorage;

class Cookie{
	//someday валидация
	
	/** @var string $name - Название cookie */
	public $name = '';
	/** @var mixed $value - Значение cookie */
	public $value = null;
	/** @var int $expires - время истечения жизни cookie в Unix формате, 0 - сессионная cookie */
	public $expires = 0;
	/** @var string $domain - (Под)домен, которому доступны cookie */
	public $domain = '';
	/** @var bool $httponly - cookie будут доступны только через HTTP-протокол */
	public $httponly = false;
	/** @var string $path - Путь к директории на сервере, из которой будут доступны cookie */
	public $path = '/';
	/** @var bool $raw - cookie без URL-кодирования значения */
	public $raw = false;
	/** @var null|string $samesite - Enforces the use of a Lax or Strict SameSite policy. Available values: 'None'|'Lax'|'Strict' */
	public $samesite = null;
	/** @var bool $secure - значение cookie должно передаваться от клиента по защищённому соединению HTTPS */
	public $secure = USE_SSL;

	/**
	 * @param string $name - Название cookie
	 * @param null $value - Значение cookie
	 * @param bool $raw - cookie без URL-кодирования значения
	 * @param array $options - значение дополнительных параметров ['expires' => int, 'domain' => string, 'httponly' => bool, 'path' => string, 'samesite' => string, 'secure' => bool]
	 * @see setcookie()
	 */
	public function __construct(string $name = '', $value = null, bool $raw = false, array $options = []){
		$this->name = $name;
		$this->value = $value;
		$this->raw = $raw;
		
		if(isset($options['expires'])){
			$this->expires = absint($options['expires']);
		}
		if(isset($options['domain'])){
			$this->domain = (string)$options['domain'];
		}
		if(isset($options['httponly'])){
			$this->httponly = !!$options['httponly'];
		}
		if(isset($options['path'])){
			$this->path = (string)$options['path'];
		}
		if(isset($options['samesite'])){
			$this->samesite = (string)$options['samesite'];
		}
		if(isset($options['secure'])){
			$this->secure = !!$options['secure'];
		}
	}

	/**
	 * @return array ['expires' => int, 'domain' => string, 'httponly' => bool, 'path' => string, 'samesite' => string, 'secure' => bool]
	 */
	public function get_options(){
		return ['expires' => $this->expires, 'domain' => $this->domain, 'httponly' => $this->httponly, 'path' => $this->path, 'samesite' => $this->samesite, 'secure' => $this->secure];
	}
	
}