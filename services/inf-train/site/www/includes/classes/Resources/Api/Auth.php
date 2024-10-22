<?php
namespace Resources\Api;

use Core\ApiStorage;
use Core\CookieStorage;
use Core\User;
use Exception;
use Resources\Api;

/**
 * метод POST - производит вход пользователя в аккаунт, требует string $_POST[PREFIX.'first_name'], требует string $_POST[PREFIX.'last_name'], string $_POST[PREFIX.'password'], bool|null $_POST['remember']
 * метод PUT - производит регистрацию пользователя, требует string BODY[PREFIX.'first_name'], требует string BODY[PREFIX.'last_name'], string BODY[PREFIX.'password']
 * метод DELETE - производит выход текущего пользователя из аккаунта
 */
class Auth extends Api{
	
	/** @var string $url_slice - строка-уровень используемая в url, наследуется от родителя */
	protected static $url_slice = 'auth';
	/** @var array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы */
	protected static $allowed_methods = ['post', 'delete', 'put'];
	
	const PREFIX = 'inf-train_';
	
	/**
	 * Вызывается после test_exec, обработка текущего запроса метода POST
	 * производит вход пользователя в аккаунт, требует
	 * string $_POST[PREFIX.'first_name'],
	 * string $_POST[PREFIX.'last_name'],
	 * string $_POST[PREFIX.'password'],
	 * bool|null $_POST['remember']
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
		$post = ApiStorage::instance()->request_body_data;
		if(!isset($post[self::PREFIX.'first_name'], $post[self::PREFIX.'last_name'], $post[self::PREFIX.'password'])){
			return ['data' => ['err' => true, 'msg' => 'Пустое поле \'first_name\' \'last_name\', или \'password\''], 'status' => 400];
		}
		
		global $USER;
		
		if($USER->id){
			return ['data' => ['err' => false, 'msg' => 'Уже авторизован'], 'status' => 200];
		}
		
		if(!$USER->load_by_loginpass($post[self::PREFIX.'first_name'], $post[self::PREFIX.'last_name'], $post[self::PREFIX.'password'])){
			return ['data' => ['err' => true, 'msg' => 'Доступы не верны'], 'status' => 406];
		}

		$type = isset($post['remember']) && $post['remember'] ? 'remember' : 'session';
		$new_token = $USER->create_token($type);
		if($new_token['status']){
			//ошибка создания токена
			return ['data' => ['err' => true, 'msg' => 'Не удалось создать токен'], 'status' => 500];
		}else{
			CookieStorage::instance()->set(
				new CookieStorage\Cookie('sid', $new_token['token'], 0, [
					'expires' => $new_token['token_data']['exp'],
					'httponly' => 1,
					'samesite' => 'Lax'
				])
			);
			
			return ['data' => ['err' => false, 'msg' => 'Успешно'], 'status' => 200];
		}
		//дочерний класс должен будет переопределить метод, иначе будет срабатывать метод родителя
	}

	/**
	 * Вызывается после test_exec, обработка текущего запроса метода PUT
	 * создает нового пользователя в случае успеха производит вход пользователя в аккаунт, требует
	 * string BODY[PREFIX.'first_name'],
	 * string BODY[PREFIX.'last_name'],
	 * string BODY[PREFIX.'password']
	 *
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => ['err' => bool, 'msg' => string]
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	public function execute_put(){
		
		$data = ApiStorage::instance()->request_body_data;

		global $USER;
		if($USER->id){
			return ['data' => ['err' => true, 'msg' => 'Уже авторизован'], 'status' => 409];
		}
		
		if(!isset($data[self::PREFIX.'first_name'], $data[self::PREFIX.'last_name'], $data[self::PREFIX.'password'])){
			return ['data' => ['err' => true, 'msg' => 'Пустое поле \'first_name\' \'last_name\', или \'password\''], 'status' => 400];
		}
		
		$new_user = 0;
		try{
			$new_user = $USER::create_new_user($data[self::PREFIX.'first_name'], $data[self::PREFIX.'last_name'], $data[self::PREFIX.'password']);
		}catch(Exception $e){
			return ['data' => ['err' => true, 'msg' => $e->getMessage()], 'status' => 406];
		}

		$new_user = new User($new_user);

		$new_token = $new_user->create_token();
		if($new_token['status']){
			//ошибка создания токена
			return ['data' => ['err' => true, 'msg' => 'Не удалось создать токен'], 'status' => 500];
		}else{
			CookieStorage::instance()->set(
				new CookieStorage\Cookie('sid', $new_token['token'], 0, [
					'expires' => $new_token['token_data']['exp'],
					'httponly' => 1,
					'samesite' => 'Lax'
				])
			);

			return ['data' => ['err' => false, 'msg' => 'Успешно'], 'status' => 200];
		}
		//дочерний класс должен будет переопределить метод, иначе будет срабатывать метод родителя
	}

	/**
	 * Вызывается после test_exec, обработка текущего запроса метода DELETE
	 * производит выход текущего пользователя из аккаунта
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
		
		if(!$USER->id){
			return ['data' => ['err' => false, 'msg' => 'Не авторизован'], 'status' => 200];
		}
		$USER->user_logout();

		return ['data' => ['err' => false, 'msg' => 'Успешно'], 'status' => 200];
		//дочерний класс должен будет переопределить метод, иначе будет срабатывать метод родителя
	}
}