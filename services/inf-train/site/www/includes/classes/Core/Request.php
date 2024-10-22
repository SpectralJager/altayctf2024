<?php

namespace Core;

use Exception;

/**
 * Class Request
 * @property-read string $protocol - протокол, по которому обратились к странице ('http' или 'https')
 * @property-read string $user_agent - user agent
 * @property-read string $method - метод запроса страницы ('GET', 'HEAD', 'POST', 'PUT' ...)
 * @property-read string $user_ip - ip клиента
 * @property-read array|false $referer_data - результат parse_url на строке реферере
 * @property-read array|false $url_data - результат parse_url текущего url
 * @property-read float $time_start - Временная метка начала запроса с точностью до микросекунд
 * 
 * @property-read string $site_name - имя сайта
 * 
 * @see parse_url()
 */
final class Request{
	/** @var Request $_instance */
	protected static $_instance;
	
	protected array $data;

	private function __construct(){
		$this->data = [
			'protocol' => $this->get_protocol(),
			'user_agent' => empty($_SERVER['HTTP_USER_AGENT']) ? '' : trim($_SERVER['HTTP_USER_AGENT']),
			'method' => $_SERVER['REQUEST_METHOD'],
			'time_start' => $_SERVER['REQUEST_TIME_FLOAT'],
			'user_ip' => $this->get_ip(),
			'referer_data' => parse_url(empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER']),
			'url_data' => parse_url(empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI']),
		];
		
		$this->data['site_name'] = 'Infinity Train';
	}

	/**
	 * @return Request возвращает экземпляр класса
	 */
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

	/**
	 * @param string $key
	 * @throws Exception Undefined property
	 * @return mixed
	 */
	public function __get($key){
		if(array_key_exists($key, $this->data)){
			if(is_object($this->data[$key])){
				return clone $this->data[$key];
			}else{
				return $this->data[$key];
			}
		}
		throw new Exception('Undefined property Request->'.$key);
	}

	/**
	 * возвращает протокол, по которому обратились к странице
	 * @return string
	 */
	protected function get_protocol(){
		if((isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')){
			return 'https';
		}else{
			return 'http';
		}
	}

	/**
	 * возвращает ip пользователя
	 * @return string
	 */
	protected function get_ip() {
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}