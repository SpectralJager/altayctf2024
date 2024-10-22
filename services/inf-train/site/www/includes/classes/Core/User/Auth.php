<?php
namespace Core\User;

use Core\CookieStorage;
use Core\CookieStorage\Cookie;
use Core\User\Auth\Base;

abstract class Auth extends Auth\Base{

	public function __construct($id){
		parent::__construct($id);
	}

	/**
	 * Загружает только активного пользователя с помощью имя-фамилии и пароля
	 * если не удачно, текущий экземпляр станет гостем
	 * @param string $first_name - имя пользователя
	 * @param string $last_name - фамилия пользователя
	 * @param string $password - пароль пользователя (НЕ хэш)
	 * @see Auth::password_hash()
	 * @return bool - загружен ли пользователь
	 */
	final public function load_by_loginpass(string $first_name, string $last_name, string $password){
		global $DB;
		$first_name = trim($first_name);
		$first_name = mb_strtolower($first_name);
		
		$last_name = trim($last_name);
		$last_name = mb_strtolower($last_name);
		
		$password = trim($password);
		$pass_hash = self::password_hash($password, $first_name.$last_name, 0);

		$first_name_clear = mb_substr($DB->escape($first_name), 0, 100);
		$last_name_clear = mb_substr($DB->escape($last_name), 0, 100);
		
		$id = $DB->get_one("SELECT `id` FROM `user` WHERE `first_name` = '$first_name_clear' AND `last_name` = '$last_name_clear' AND `password` = X'$pass_hash'");
		if(!$id){
			$this->set_guest();
			return false;
		}
		
		if(!$this->load_user($id)){
			$this->set_guest();
			return false;
		}
		return true;
	}


	/**
	 * загружает только активного пользователя с помощью токена
	 * если не удачно, текущий экземпляр станет гостем
	 * если удачно, обновит токен
	 * @param string $token - JWT токен пользователя
	 * @return bool - загружен ли пользователь
	 */
	final function load_user_by_token(string $token){
		
		$token_data = decode_jwt_token($token, AUTH_TOKEN_KEY);
		
		if($token_data){
			if(is_array($token_data) && isset($token_data['user_id'], $token_data['date_interval'], $token_data['iat'])){
				$user_id = absint($token_data['user_id']);

				if($this->load_user($user_id) && $this->id){
					//пользователь загружен
					
					//обновим время работы токена
					if($this->update_auth_token($this->create_token($token_data['date_interval']))){
						return true;
					}
				}
			}
		}
		$this->user_logout();
		return false;
	}

	/**
	 * Производит обновление текущего токена аутентификации для активного пользователя
	 * @param array $create_token_result - результат работы метода create_token
	 * @see create_token()
	 * @return bool - успешно ли обновлено
	 */
	final public function update_auth_token(array $create_token_result){
		if($this->id && $create_token_result['status'] == 0){
			
			CookieStorage::instance()->set(
				new Cookie('sid', $create_token_result['token'], 0, [
					'expires' => $create_token_result['token_data']['exp'],
					'httponly' => 1,
					'samesite' => 'Lax'
				])
			);
			return true;
		}
		
		return false;
	}

	/**
	 * обертка для set_default()
	 * @see Base::set_default
	 */
	final function set_guest(){
		$this->set_default();
	}

	/**
	 * Возвращает хеш пароля пользователя
	 * пароль не обрабатывается
	 * @param string $password
	 * @param string $dynamic
	 * @param bool $binary
	 * @return string
	 */
	static final public function password_hash($password, $dynamic='', $binary=true){
		return hash_hmac('sha3-256', $password.$dynamic, PASSWORD_KEY, $binary);
	}

	/**
	 * Делает юзера гостем и удаляет сессию текущего юзера
	 */
	final function user_logout(){
		CookieStorage::instance()->delete('sid');
		$this->set_guest();
	}



	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//Все что связано с токенами
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Создает токен аутентификации для текущего пользователя
	 * @param string $type - 'session': SESSION_TOKEN_LIVE_SECONDS, 'remember':REMEMBER_TOKEN_LIVE_DAYS
	 * @return array
	 * <pre>
	 * [
	 * 	'status' => int (0: успешно, -1: юзер - гость, -2: ошибочный $type),
	 * 	'token' => string,
	 * 	'token_data' => [
	 * 		'user_id' => int,
	 * 		'date_interval' => string,
	 * 		'exp' => int,
	 * 		'iat' => int
	 * 	]
	 * ]
	 * </pre>
	 * @see REMEMBER_AUTH_TOKEN_LIVE_DAYS, SESSION_AUTH_TOKEN_LIVE_SECONDS
	 */
	final public function create_token($type='session'){
		//todo удалить обновление токена?
		$ret = ['status' => 0, 'token' => '', 'token_data' => []];
		if(!$this->id){
			$ret['status'] = -1;
			return $ret;
		}
		
		$date_interval = '';
		switch($type){
			case 'session':
				$date_interval = 'PT'.SESSION_AUTH_TOKEN_LIVE_SECONDS.'S';
				break;
			case 'remember':
				$date_interval = 'P'.REMEMBER_AUTH_TOKEN_LIVE_DAYS.'D';
				break;
			default:
				$ret['status'] = -2;
				return $ret;
		}
		
		
		$date_start_token = new \DateTime();
		$date_end_token = (clone $date_start_token)->add(new \DateInterval($date_interval));
		$ret['token_data'] = [
			'user_id' => $this->id,
			'date_interval' => $type,
			'exp' => $date_end_token->getTimestamp(),
			'iat' => $date_start_token->getTimestamp(),
		];
		$ret['token'] = encode_jwt_token($ret['token_data'], AUTH_TOKEN_KEY);
		return $ret;
	}
}