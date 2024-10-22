<?php
namespace Resources\Api;

use Resources\Api;

final class _404 extends Api{

	/** @var string $url_slice - строка-уровень используемая в url, наследуется от родителя */
	protected static $url_slice = '';
	/** @var array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы */
	protected static $allowed_methods = true;

	/**
	 * Вызывается после test_exec, обработка текущего запроса для методов, реализация которых не существует
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => mixed - ответ метода, будет закодирован с помощью json_encode
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	public function execute(){

		$data = [
			'err' => true,
			'msg' => 'Ресурс не существует'
		];

		return ['data' => $data, 'status' => 404];
	}
}