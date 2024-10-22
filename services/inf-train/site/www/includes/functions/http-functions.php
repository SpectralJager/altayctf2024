<?php

/**
 * Перенаправляет на страницу с указанным url
 * Отправляет cookie
 * Отменяет все уровни транзакции в БД
 * @param string $url
 * @param int $status - Статус код перенаправления
 */
function redirect($url, $status = 302){
	if($status < 300 || 399 < $status){
		throw new Exception('status not between 300..399');
	}
	\Core\CookieStorage::instance()->send_cookies();
	header('Location: '.$url, true, $status);
	global $DB;
	$DB->force_rollback();
	die();
}

/**
 * Единожды отправляет заголовки препятствующие кэшированию
 */
function nocache_headers(){
	static $is_send = false;
	if($is_send)
		return;

	header_remove('Last-Modified');
	header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
	header('Cache-Control: no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	$is_send = true;
}

/**
 * Выводит JSON кодированные данные
 * Устанавливает заголовок ответа
 * Оправляет cookie
 * Отменяет все уровни транзакции в БД
 * Завершает работу скрипта
 * @param $data
 * @param int $status
 */
function send_json_response($data, $status=200){
	status_header($status);
	\Core\CookieStorage::instance()->send_cookies();
	global $DB;
	$DB->force_rollback();
	die(json_encode($data));
}


/**
 * Устанавливает указанный статус в заголовок HTTP ответа сервера.
 *
 * @param $status - Код состояния HTTP
 */
function status_header($status){
	$status = absint($status);

	$protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';
	if(!in_array($protocol, ['HTTP/1.1', 'HTTP/2', 'HTTP/2.0', 'HTTP/3'], true)){
		$protocol = 'HTTP/1.0';
	}

	static $wp_header_to_desc = false;

	if(!$wp_header_to_desc){
		$wp_header_to_desc = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			103 => 'Early Hints',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			421 => 'Misdirected Request',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			451 => 'Unavailable For Legal Reasons',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			510 => 'Not Extended',
			511 => 'Network Authentication Required',
		];
	}

	$desc = '';

	if(isset($wp_header_to_desc[$status])){
		$desc = $wp_header_to_desc[$status];
	}

	$status_header = $protocol.' '.$status.' '.$desc;

	header($status_header, true, $status);
}

/**
 * выводит данные мимо буфера
 * @param $data - текст для вывода
 * @param bool $flush - отправить ли данные моментально?
 * @return int - количество буферов
 */
function ob_ignore($data, $flush = false){
	$ob = [];
	$len = ob_get_level();
	for($i=0; $i<$len; $i++){
		$ob[] = ob_get_contents();
		ob_end_clean();
	}
	
	echo $data;
	if($flush)
		flush();
	
	for($i=$len-1; $i>=0; $i--){
		ob_start();
		echo $ob[$i];
	}
	return sizeof($ob);
}