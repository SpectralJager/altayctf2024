<?php

namespace Core;

use Exception;

/**
 * Класс предоставляет методы работы с данными пользователей
 * у пользователя есть уникальные ID, имя ($first_name) и фамилия ($last_name), дата регистрации ($date), хэш пароля($password)
 */
final class User extends User\Auth{
	
	/**
	 * создает объект юзера
	 * @param int $id - ID юзера, 0 - пользователь гость
	 */
	public function __construct(int $id = 0){
		parent::__construct($id);
	}

	/**
	 * возвращает объект текущего пользователя, путем парсинга и проверки $_COOKIE['sid']
	 * @return static
	 */
	public static function current() : self{
		$current = new self();
		if(!empty(CookieStorage::instance()->get_value('sid'))){
			try{
				$current->load_user_by_token(CookieStorage::instance()->get_value('sid'));
			}catch(\Exception $e){
				$current->user_logout();
			}
		}
		
		return $current;
	}
	
	/**
	 * добавляет в БД нового пользователя
	 * @param string $first_name - имя пользователя
	 * @param string $last_name - фамилия пользователя
	 * @param string $password - пароль пользователя (НЕ хэш)
	 * @return int id нового пользователя
	 * @throws Exception
	 */
	public static function create_new_user($first_name, $last_name, $password){
		global $DB;

		$first_name = trim($first_name);
		$first_name = mb_strtolower($first_name);
		if(mb_strlen($first_name) <= 0 || mb_strlen($first_name) > 100){
			throw new Exception('Имя слишком длинное');
		}
		
		$last_name = trim($last_name);
		$last_name = mb_strtolower($last_name);
		if(mb_strlen($last_name) <= 0 || mb_strlen($last_name) > 100){
			throw new Exception('Фамилия слишком длинное');
		}
		
		$storage = self::create_storage($first_name, $last_name);

		$first_name_clear = mb_substr($DB->escape($first_name), 0, 100);
		$last_name_clear = mb_substr($DB->escape($last_name), 0, 100);
		$storage_clear = $DB->escape($storage);
		
		$id = $DB->get_one("SELECT `id` FROM `user` WHERE `first_name` = '$first_name_clear' AND `last_name` = '$last_name_clear'");
		if($id !== false){
			throw new Exception('Эти имя и фамилия уже заняты');
		}

		$password = trim($password);
		if(mb_strlen($password) < 6){
			throw new Exception('Пароль менее 6 знаков');
		}
		
		$pass_hash = self::password_hash($password, $first_name.$last_name, 0);
		$now = (new \DateTime())->format(DB_DATETIME_FORMAT);

		$DB->exec("INSERT INTO `user` (`first_name`, `last_name`, `password`, `date`, `storage`) VALUES('$first_name_clear', '$last_name_clear', X'$pass_hash', '$now', '$storage_clear')");
		
		return $DB->insert_id();
	}

	/**
	 * @param string $first_name
	 * @param string $last_name
	 * @throws Exception
	 */
	protected static function create_storage($first_name, $last_name){
		$first_dir = filename_clear($first_name);
		if($first_dir === false){
			throw new Exception('Имя содержит недопустимые символы');
		}

		$second_dir = filename_clear($last_name);
		if($second_dir === false){
			throw new Exception('Фамилия содержит недопустимые символы');
		}
		
		return $first_dir.'/'.$second_dir;
	}

	/**
	 * Удалить пользователя
	 */
	public function delete(){
		global $DB;
		if(!$this->id){
			return;
		}
		
		$DB->exec("DELETE FROM `user` WHERE `id` = {$this->id}");
	}
}