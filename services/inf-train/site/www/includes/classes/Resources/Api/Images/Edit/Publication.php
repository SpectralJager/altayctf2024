<?php
namespace Resources\Api\Images\Edit;

use Resources\Api\Images\Edit;

/**
 * POST - делает изображение публичным
 * DELETE - делает изображение приватным
 */
class Publication extends Edit{
	/** @var bool $private - доступ к странице только зарегистрированному пользователю */
	protected static $private = true;
	/** @var string $url_slice - строка-уровень используемая в url, наследуется от родителя */
	protected static $url_slice = 'publication';
	/** @var array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы */
	protected static $allowed_methods = ['post', 'delete'];

	public function test_exec(string $method): bool{
		return parent::test_exec($method);
	}

	/**
	 * Вызывается после test_exec, обработка текущего запроса метода POST
	 * делает изображение публичным
	 *
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => ['err' => bool, 'msg' => string]
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	public function execute_post(){
		global $USER;

		if($USER->storage !== $this->image->storage){
			return ['data' => ['err' => true, 'msg' => 'Не достаточно прав для управления этим изображением'], 'status' => 403];
		}
		
		if($this->image->public){
			return ['data' => ['err' => false, 'msg' => 'Изображение уже опубликовано'], 'status' => 200];
		}
		
		if($this->image->set_public(true)){
			return ['data' => ['err' => false, 'msg' => 'Изображение опубликовано'], 'status' => 200];
		}else{
			return ['data' => ['err' => false, 'msg' => 'Не удалось опубликовать изображение'], 'status' => 500];
		}
	}
	
	/**
	 * Вызывается после test_exec, обработка текущего запроса метода DELETE
	 * делает изображение приватным
	 *
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => ['err' => bool, 'msg' => string]
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	public function execute_delete(){
		global $USER;

		if($USER->storage !== $this->image->storage){
			return ['data' => ['err' => true, 'msg' => 'Не достаточно прав для управления этим изображением'], 'status' => 403];
		}

		if(!$this->image->public){
			return ['data' => ['err' => false, 'msg' => 'Изображение уже скрыто'], 'status' => 200];
		}

		if($this->image->set_public(false)){
			return ['data' => ['err' => false, 'msg' => 'Изображение скрыто'], 'status' => 200];
		}else{
			return ['data' => ['err' => false, 'msg' => 'Не удалось скрыть изображение'], 'status' => 500];
		}
	}
}