<?php
namespace Core\Image;

use Core\Image;
use Exception;

abstract class Factory{

	/**
	 * @var array данные пользователей, владельцах изображения [
	 * 	storage => [
	 * 		'id' => string,
	 * 		'first_name' => string,
	 * 		'last_name' => string,
	 * 	],
	 * 	...
	 * ]
	 */
	protected static $user_data = [];
	
	/**
	 * @param int $id - ID изображения в БД
	 * @param bool $load_user - Загружать данные пользователя
	 * @throws Exception
	 * @return Image
	 */
	public static function get_from_db(int $id, bool $load_user = false){
		global $DB;
		$res = $DB->get_row("SELECT * FROM `image` WHERE `id` = '$id'");
		if(!$res){
			throw new Exception('Image #'.$id.' not found');
		}
		
		$img = (new Image())->set_data($res);
		
		if($load_user && !isset($user_data[$img->storage])){
			$storage_c = $DB->escape($img->storage);
			$user = $DB->get_row("SELECT `id`, `first_name`, `last_name` FROM `user` WHERE `storage` = '$storage_c'");
			if($user){
				self::$user_data[$img->storage] = $user;
			}
		}

		return $img;
	}

	/**
	 * @param string $storage - часть пути к изображению
	 * @param bool $load_user - Загружать данные пользователя
	 * @return Image[] - [image_id => self, ...]
	 */
	public static function get_from_storage(string $storage, bool $load_user = false){
		global $DB;

		$storage_c = $DB->escape($storage);
		$res = $DB->get_ind('id', "SELECT * FROM `image` WHERE `storage` = '$storage_c'");

		foreach($res as $id => &$img){
			$img = (new Image())->set_data($img);
		}

		if($load_user && !isset($user_data[$storage])){
			$user = $DB->get_row("SELECT `id`, `first_name`, `last_name` FROM `user` WHERE `storage` = '$storage_c'");
			if($user){
				self::$user_data[$img->storage] = $user;
			}
		}

		return $res;
	}

	/**
	 * @param bool $load_user - Загружать данные пользователя
	 * @return Image[] - [image_id => self, ...]
	 */
	public static function get_all_public(bool $load_user = false){
		global $DB;

		$res = $DB->get_ind('id', "SELECT * FROM `image` WHERE `public` != 0");
		
		$storages = [];

		foreach($res as $id => &$img){
			$img = (new Image())->set_data($img);
			if($load_user && !isset($user_data[$img->storage])){
				$storages[$img->storage] = $DB->escape($img->storage); 
			}
		}
		
		if($load_user && $storages){
			$query = '';
			foreach($storages as $storage_c){
				$query .= "'$storage_c', ";
			}
			$query = mb_substr($query, 0, mb_strlen($query)-2);
			$query = "SELECT `id`, `first_name`, `last_name`, `storage` FROM `user` WHERE `storage` IN($query)";
			$users = $DB->get_ind_col('storage', $query);
			self::$user_data = array_merge(self::$user_data, $users);
		}

		return $res;
	}

	/**
	 * Возвращает данные пользователя-владельца изображения
	 * @param Image $img
	 * @return array|false
	 * [
	 * 		'id' => string,
	 * 		'first_name' => string,
	 * 		'last_name' => string,
	 * 	]
	 */
	public static function get_user_data(Image $img){
		if(isset(self::$user_data[$img->storage])){
			return self::$user_data[$img->storage];
		}
		global $DB;

		$storage_c = $DB->escape($img->storage);
		$user = $DB->get_row("SELECT `id`, `first_name`, `last_name` FROM `user` WHERE `storage` = '$storage_c'");
		if($user){
			self::$user_data[$img->storage] = $user;
		}
		
		return $user;
	}
}