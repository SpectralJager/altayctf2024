<?php

/**
 * создает токен для записи в cookie
 * собираем JWT токен
 * имеет вид base64.base64.base64, == обрезаются
 * 1-ая часть - header, {"alg": "HS256","typ": "JWT"}
 * 2-ая часть - payload, json_encode($payload)
 * 3-ая часть - token, base64($header).'.'.base64($payload), если $hash не пустой то base64($header).'.'.base64($payload).'.'.$hash
 * @param array|mixed $payload - вложенность(глубина) не больше 10
 * @param string $key - ключ для hash_hmac
 * @param string $hash - доп хэш (sha1 от useragent)
 * @return string
 */
function encode_jwt_token($payload, string $key, string $hash=''){
	$header = ['alg' => 'HS256', 'typ' => 'JWT'];
	$header = base64_encode(json_encode($header));
	$payload = base64_encode(json_encode($payload));

	$token = $header.'.'.$payload;
	$token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

	$signature = hash_hmac('sha256', $token.($hash === '' ? '': '.'.$hash), $key, 1);
	$signature = base64_encode($signature);
	$signature = str_replace(['+', '/', '='], ['-', '_', ''], $signature);

	return $token.'.'.$signature;
}

/**
 * @param string $token - строка сгенерированная encode_jwt_token
 * @param string $key - ключ для hash_hmac
 * @param string $hash - доп хэш (sha1 от useragent)
 * @param bool $check_expiration_time - проверяет наличие поля exp:int в payload и время работы токена еще не истекло
 * @return array|false - данные из payload или false при неудаче парсинга
 * @see encode_jwt_token
 */
function decode_jwt_token(string $token, string $key, string $hash='', bool $check_expiration_time=true){
	$token_data = explode('.', $token, 3);
	if(sizeof($token_data) < 3)
		return false;

	$header = $token_data[0];
	$payload = $token_data[1];
	$signature = $token_data[2];

	$signature = str_replace(['-', '_'], ['+', '/'], $signature);
	$signature = base64_decode($signature, 1);
	if(!$signature){
		return false;
	}

	$test_signature = hash_hmac('sha256', $header.'.'.$payload.($hash === '' ? '' : '.'.$hash), $key, 1);
	if(!hash_equals($test_signature, $signature)){
		return false;
	}

	$payload = str_replace(['-', '_'], ['+', '/'], $payload);
	$payload = base64_decode($payload, 1);

	if($payload === false)
		return false;

	try{
		$payload = json_decode($payload, true, 10, JSON_THROW_ON_ERROR);
	}catch(Exception $ex){
		return false;
	}

	if($check_expiration_time){
		if(!is_array($payload) || !isset($payload['exp'])){
			return false;
		}

		$now = new DateTime();

		if($now->getTimestamp() > absint($payload['exp'])){
			return false;
		}
	}

	return $payload;
}

/**
 * вставляет элемент в отсортированный массив
 * @param mixed $element
 * @param array $array
 * @param callable|null $compare
 */
function insert_to_order_array($element, &$array, $compare = null){
	if(!sizeof($array)){
		array_splice($array, 0, 0, [$element]);
		return;
	}

	$left = 0;
	$right = sizeof($array)-1;
	$mid = 0;

	for(;$left != $right;){
		$mid = $left + (int)(($right - $left) / 2);
		if(is_callable($compare)){
			if($compare($element, $array[$mid]) > 0){
			$left = $mid + 1;
		}else{
				$right = $mid;
			}
		}else{
			if($element > $array[$mid]){
				$left = $mid + 1;
			}else{
				$right = $mid;
			}
		}
	}

	if(is_callable($compare)){
		if($compare($element, $array[$left]) > 0){
			$left += 1;
		}
	}else{
		if($element > $array[$left]){
			$left += 1;
		}
	}

	array_splice($array, $left, 0, [$element]);
}

/**
 * Конвертирует дерево файлов, возвращенное функцией file_list_recursive (для .php файлов)
 * в список классов, где путь до файла становится namespace, а имя файла без расширения наименованием класса
 * 
 * @see file_list_recursive
 * 
 * @param array $file_list - древовидный массив, возвращенный file_list_recursive
 * @param string $prevent_namespace - предшествующий namespace, например '\\UserRoles\\'
 * @return string[]
 */
function file_list_to_class_names(array $file_list, string $prevent_namespace = '\\'){
	$ret = [];
	foreach($file_list as $file_name => $val){
		if(is_array($val)){
			$ret = array_merge($ret, file_list_to_class_names($val, $prevent_namespace.$file_name.'\\'));
		}else{
			$ret[] = $prevent_namespace.mb_substr($file_name, 0, strrpos($file_name, '.'));
		}
	}
	return $ret;
}

/**
 * Возвращает величину, веса максимально допустимую для загрузки файлов в байтах
 * @return int
 */
function get_MAX_FILE_SIZE(){
	$max_size = ini_get('upload_max_filesize');
	$max_size = mb_strtolower($max_size);
	$symb = mb_substr($max_size, -1);
	$max_size = absint($max_size);
	if($symb === 'k'){
		return $max_size * BYTES_PER_KB;
	}else if($symb === 'm'){
		return $max_size * BYTES_PER_MB;
	}else if($symb === 'g'){
		return $max_size * BYTES_PER_GB;
	}else{
		return $max_size;
	}
}