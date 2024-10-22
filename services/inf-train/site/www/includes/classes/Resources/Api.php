<?php
namespace Resources;

use Core\ApiStorage\Resource;

class Api extends Resource{

	/** @var string $url_slice - строка-уровень используемая в url, пустая строка означает, что данный ресурс параметризован */
	protected static $url_slice = 'api';
	/** @var array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы */
	protected static $allowed_methods = true;

	public function execute(){

		$data = [
			'err'   => false,
			'msg'   => 'Infinity Train API',
			'version' => '1.0'
		];

		return ['data' => $data, 'status' => 200];
	}

	/**
	 * Вызывается после test_exec, обработка текущего запроса для метода HEAD
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => mixed - ответ метода, будет закодирован с помощью json_encode
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	public function execute_head(){
		return $this->execute();
	}

}