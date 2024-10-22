<?php

namespace Core;

/**
 * @property-read array $request_body_data - массив параметров переданных в теле запроса, заполняется при первом обращении
 * @property-read array $uploaded_files - многомерный массив загруженных файлов (UploadedFiles), заполняется при первом обращении
 */
final class ApiStorage{

	/** @var ApiStorage $_instance */
	protected static $_instance;
	

	/** @var array $loaded_resources_classes - массив имен классов (ключи) для обработки ресурсов */
	protected $loaded_resources_classes;
	
	/** @var ApiStorage\Resource $current_resource - экземпляр текущего ресурса */
	protected $current_resource = null;

	/** @var string $current_method - текущий HTTP метод в нижнем регистре */
	protected $current_method = '';

	/**
	 * @var array $request_body_data - массив параметров переданных в теле запроса, заполняется при первом обращении
	 * @see ApiStorage::parse_request_body
	 */
	protected $request_body_data = null;
	
	/**
	 * @var array $uploaded_files - многомерный массив загруженных файлов, заполняется при первом обращении
	 * @see ApiStorage::reload_uploaded_files
	 * @see Request\UploadedFile
	 */
	protected $uploaded_files = null;

	/**
	 * @return ApiStorage возвращает экземпляр класса
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
		$this->loaded_resources_classes = [];
		$this->current_method = mb_strtolower(trim(Request::instance()->method));
		if($this->current_method != 'post'){
			header('Accept: application/json, application/x-www-form-urlencoded');
		}

		spl_autoload_register([$this, 'autoload_api_resource_classes'], true, true);

		$classes = file_list_recursive(MAIN_DIR.'includes/classes/Resources/', '.php');
		$classes = file_list_to_class_names($classes, '\\Resources\\');
		$len = sizeof($classes);
		for($i=0; $i<$len; $i++){

			$class_name = $classes[$i];
			if(trait_exists($class_name)){
				continue;
			}
			call_user_func([$class_name, 'set_url']);
		}
		
		spl_autoload_unregister([$this, 'autoload_api_resource_classes']);
	}
	
	private function autoload_api_resource_classes($class){
		if(preg_match('#^Resources\\\\#i', $class)){
			$file = MAIN_DIR.'includes/classes/'.str_replace('\\', '/', $class).'.php';
			if(check_file($file)){
				require_once $file;
				$this->loaded_resources_classes[$class] = true;
			}
		}
	}

	/**
	 * загружает экземпляр текущего ресурса основываясь на Url::get_current_page
	 * @see Url::get_current_page
	 */
	public function load_current_api_resource(){
		global $URL;
		$page_ind = $URL->get_current_page();
		if(is_null($this->current_resource) || $this->current_resource::get_ind() !== $page_ind){
			$this->current_resource = new $page_ind($this->current_method);
		}
	}

	/**
	 * производит проверку на разрешение обработки текущего ресурса (вызывает ApiStorage\Resource::test_exec)
	 * если обработка текущего ресурса будет запрещена будет установлен 404 ресурс или возвращена ошибка
	 * @see ApiStorage\Resource::test_exec()
	 * @return true|array
	 * <pre>
	 * [
	 * 	'data' => mixed - ответ метода, будет закодирован с помощью json_encode
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	private function test_resource(){
		global $USER, $URL;
		
		$this->load_current_api_resource();
		$curr_r = $this->current_resource;
		
		//если гость получить доступ к ресурсу, где нужно быть зарегистрированным 
		//выводим 403 ошибку
		if(!$USER->id && $curr_r::get_static('private')){
			nocache_headers();
			return ['data' => ['err' => true, 'msg' => 'Требуется авторизация для доступа к ресурсу'], 'status' => 403];
		}
		
		//проверка на поддержку метода
		$allowed_methods = $curr_r::get_static('allowed_methods');
		if(is_array($allowed_methods)){
			if(!in_array($this->current_method, $allowed_methods)){
				header('Allow: '.implode(', ', $allowed_methods));
				return ['data' => ['err' => true, 'msg' => 'Ресурс не поддерживает данный метод'], 'status' => 405];
			}
		}
		
		//собственная проверка ресурса
		if(!$curr_r->test_exec($this->current_method)){
			//nocache_headers();
			$URL->set_current_404();
			$this->load_current_api_resource();
			return true;
		}
		return true;
	}
	
	/**
	 * Производит проверку доступа к ресурсу и выполняет его
	 * @see test_resource
	 * @see ApiStorage\Resource::execute()
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => mixed - ответ метода, будет закодирован с помощью json_encode
	 * 	'status' => int - http код ответа
	 * 	'log_str' => null|string - строка записанная в лог
	 * ]
	 * </pre>
	 */
	public function exec_current_resource(){
		$this->load_current_api_resource();
		
		$ret = $this->test_resource();
		if($ret !== true){
			return $ret;
		}

		$allowed_methods = $this->current_resource::get_static('allowed_methods');

		if(!is_array($allowed_methods)){
			$ret = $this->current_resource->execute();
		}else{
			$ret = $this->current_resource->{'execute_'.$this->current_method}();
		}
		
		return $ret;
	}
	
	/**
	 * производит парсинг тела запроса, если это требует метод ресурса
	 * парсит только 'application/x-www-form-urlencoded'
	 * @return true|array
	 * <pre>
	 * [
	 * 	'data' => mixed - ответ метода, будет закодирован с помощью json_encode
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	private function parse_request_body(){
		$content_type = trim($_SERVER['HTTP_CONTENT_TYPE'] ?? ($_SERVER['CONTENT_TYPE'] ?? ''));
		
		if(mb_strpos($content_type, 'multipart/form-data') !== false){
			return ['data' => ['err' => true, 'msg' => 'Поддерживаемый Content-Type: application/json, application/x-www-form-urlencoded'], 'status' => 406];
		}
		
		if(mb_strpos($content_type, 'application/x-www-form-urlencoded') !== false){
			$this->request_body_data = [];
			parse_str(file_get_contents('php://input'), $this->request_body_data);
			if(!is_array($this->request_body_data)){
				$this->request_body_data = [];
			}
			return true;
		}

		if(mb_strpos($content_type, 'application/json') !== false){
			$this->request_body_data = [];
			$this->request_body_data = json_decode(file_get_contents('php://input'), true, 128);
			if(!is_array($this->request_body_data)){
				$this->request_body_data = [];
			}
			return true;
		}
		
		if(empty($content_type)){
			$this->request_body_data = [];
			return true;
		}

		return ['data' => ['err' => true, 'msg' => 'Поддерживаемый Content-Type: application/json, application/x-www-form-urlencoded'], 'status' => 406];
	}


	/**
	 * парсит массив $_FILES и заполняет массив uploaded_files той же многомерной структурой, переданной пользователем
	 * @see uploaded_files
	 * @see Request\UploadedFile
	 */
	private function reload_uploaded_files(){

		if(isset($_FILES)){
			foreach($_FILES as $key => $val){
				if(is_array($val['name'])){
					$this->uploaded_files[$key] = self::recursive_load_files($val['tmp_name'], $val['type'], $val['error'], $val['size'], $val['name']);
				}else{
					$this->uploaded_files[$key] = new Request\UploadedFile($val['tmp_name'], $val['type'], $val['error'], $val['size'], $val['name']);
				}
			}
		}
	}

	/**
	 * Рекурсивно парсит массив $_FILES
	 * @param array $tmp_name_array - массив с tmp_name
	 * @param array $type_array - массив с type
	 * @param array $error_array - массив с error
	 * @param array $size_array - массив с size
	 * @param array $name_array - массив с name
	 * @return array
	 */
	private static function recursive_load_files(array $tmp_name_array, array $type_array, array $error_array, array $size_array, array $name_array){
		$ret = [];

		foreach($tmp_name_array as $key => $val){
			if(is_array($val)){
				$ret[$key] = self::recursive_load_files($val, $type_array[$key], $error_array[$key], $size_array[$key], $name_array[$key]);
			}else{
				$ret[$key] = new Request\UploadedFile($val, $type_array[$key], $error_array[$key], $size_array[$key], $name_array[$key]);
			}
		}

		return $ret;
	}

	public function __get($key){
		switch($key){
			case 'request_body_data':
				if(is_null($this->request_body_data)){
					$ret = $this->parse_request_body();
					if($ret !== true){
						send_json_response($ret['data'], $ret['status']);
					}
				}
				return $this->$key;
			case 'uploaded_files':
				if(is_null($this->uploaded_files)){
					$this->reload_uploaded_files();
				}
				return $this->$key;
			default:
				trigger_error('try to get undefined property '.$key, E_USER_WARNING);
				return null;
		}
	}
}