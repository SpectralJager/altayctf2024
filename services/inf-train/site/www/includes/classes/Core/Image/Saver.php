<?php

namespace Core\Image;

use Core\Image;
use DateTime;
use Exception;

abstract class Saver{

	/**
	 * @var Image[] $saved_images - массив сохраненных изображений за текущий сеанс, с помощью этого класса
	 * [image_id => Image, ...]
	 *
	 * @see save
	 */
	static protected $saved_images = [];

	/**
	 * Возвращает список сохраненных изображений в текущем сеансе
	 * @return Image[]
	 * @see $saved_images
	 */
	public static function get_saved_images(){
		return self::$saved_images;
	}

	/**
	 * Удаляет изображения сохраненные в текущем сеансе
	 *
	 * @see $saved_images
	 * @see delete
	 * @return array - массив возвращенных значений методом delete
	 */
	public static function delete_saved_images(){
		$ret = [];
		foreach(self::$saved_images as $id => $img){
			$ret[$id] = self::delete($img);
		}
		return $ret;
	}



	/**
	 * Производит копирование изображения в место расположения и сохранение в БД
	 * Все передаваемые параметры должны быть верными
	 *
	 * @param string $img_path - путь до загруженного изображения
	 * @param string $type - mime тип изображения
	 * @param string $name - имя изображения БЕЗ РАСШИРЕНИЯ
	 * @param string $storage - часть пути к изображению
	 * @param bool $public - доступ к изображению
	 * @return Image
	 * @throws Exception
	 */
	public static function save($img_path, $type, $name, $storage, $public = false){
		$img_gd = null;
		switch($type){
			case 'image/jpeg':
				$img_gd = imagecreatefromjpeg($img_path);
				break;
			case 'image/png':
				$img_gd = imagecreatefrompng($img_path);
				break;
			default:
				throw new Exception('Неопознанный тип изображения '.$type);
		}

		if($img_gd === false){
			throw new Exception('Файл не является изображением типа '.$type);
		}

		global $DB;

		$insert = [];

		$insert['image_w'] = imagesx($img_gd);
		$insert['image_h'] = imagesy($img_gd);
		if($insert['image_h'] <= 0 || $insert['image_h'] > Image::MAX_IMAGE_HEIGHT){
			throw new Exception('Высота изображения выходит за пределы (0; '.Image::MAX_IMAGE_HEIGHT.']px');
		}
		if($insert['image_w'] <= 0 || $insert['image_w'] > Image::MAX_IMAGE_WIDTH){
			throw new Exception('Ширина изображения выходит за пределы (0; '.Image::MAX_IMAGE_WIDTH.']px');
		}

		$insert['file_size'] = get_filesize($img_path);
		if($insert['file_size'] <= 0 || $insert['file_size'] > Image::MAX_IMAGE_WEIGHT){
			throw new Exception('Вес изображения выходит за пределы (0; '.Image::MAX_IMAGE_WEIGHT.']byte');
		}

		$insert['type'] = mb_substr(mb_strrchr($type, '/'), 1);
		$insert['name'] = $name;
		$insert['storage'] = $storage;
		$insert['public'] = !!$public;
		$insert['time_create'] = new DateTime();
		
		$img = (new Image())->set_data($insert);

		$path = MAIN_DIR.$img->get_image_path();
		create_dirs_for_file($path);
		$res = rename($img_path, $path);
		if(!$res){
			imagedestroy($img_gd);
			throw new Exception('Не удалось сохранить изображение');
		}

		//файлы, загруженные с помощью PHP-CLI будут иметь права 600, могут потребоваться права 644
		chmod($path, 0644);

		imagedestroy($img_gd);
		
		$insert = [
			'storage' => $DB->escape($insert['storage']),
			'time_create' => $DB->escape($insert['time_create']->format(DB_DATETIME_FORMAT)),
			'public' => absint($insert['public']),
			'image_w' => absint($insert['image_w']),
			'image_h' => absint($insert['image_h']),
			'file_size' => absint($insert['file_size']),
			'name' => $DB->escape($insert['name']),
			'type' => $DB->escape($insert['type']),
		];
		try{
			$DB->exec("INSERT INTO `image` (`storage`, `time_create`, `public`, `image_w`, `image_h`, `name`, `type`, `file_size`) VALUES (
				'{$insert['storage']}',
				'{$insert['time_create']}',
				{$insert['public']},
				{$insert['image_w']},
				{$insert['image_h']},
				'{$insert['name']}',
				'{$insert['type']}',
				{$insert['file_size']}
			)");
		}catch(Exception $ex){
			self::delete_img_files($img);
			throw $ex;
		}
		$insert['id'] = $DB->insert_id();
		
		$img->set_data($insert);

		self::$saved_images[$img->id] = $img;

		return $img;
	}

	/**
	 * Сохраняет изображение из GD ресурса в место расположения и сохраняет изображение в БД
	 *
	 * Все передаваемые параметры должны быть верными
	 *
	 * @param resource|\GdImage $img_gd - GD ресурс изображения, ресурс не закрывается внутри функции
	 * @param string $type - mime тип изображения
	 * @param string $name - имя изображения БЕЗ РАСШИРЕНИЯ
	 * @param string $storage - часть пути к изображению
	 * @param bool $public - доступ к изображению
	 *
	 * @throws Exception
	 * @see self::save
	 * @return Image
	 */
	public static function save_gd($img_gd, $type, $name, $storage, $public = false){
		switch($type){
			case 'image/jpeg':
			case 'jpeg':
				$type = 'image/jpeg';
				break;
			case 'image/png':
			case 'png':
				$type = 'image/png';
				break;
			default:
				throw new Exception('Unidentified MIME type '.$type);
		}

		$file_path = tempnam(sys_get_temp_dir(), 'img');
		$ret = false;
		switch($type){
			case 'image/jpeg':
				$ret = imagejpeg($img_gd, $file_path, 100);
				break;
			case 'image/png':
				$ret = imagepng($img_gd, $file_path);
				break;
		}
		if(!$ret){
			unlink($file_path);
			throw new Exception('TMP image could not be saved');
		}
		try{
			$img = self::save($file_path, $type, $name, $storage, $public);
		}catch(Exception $ex){
			unlink($file_path);
			throw $ex;
		}
		return $img;
	}

	/**
	 * удаляет текущее изображение из БД и файл, объект становится пустым изображением (если изображение имеет ID)
	 *
	 * @return bool
	 */
	public static function delete(Image $img){
		global $DB;

		$DB->start_transaction();
		$DB->exec("DELETE FROM `image` WHERE `id` = {$img->id}");

		if(!self::delete_img_files($img)){
			$DB->rollback();
			return false;
		}

		$DB->commit();

		unset(self::$saved_images[$img->id]);
		$img->set_data([]);

		return true;
	}

	/**
	 * Удаляет только файлы текущего изображения
	 * @return bool
	 */
	public static function delete_img_files(Image $img){

		$path = MAIN_DIR.$img->get_image_path();
		if(check_file($path)){
			if(!unlink($path)){
				trigger_error('Изображение #'.$img->id.' не удалось удалить', E_USER_WARNING);
				return false;
			}
		}else{
			trigger_error('Изображение #'.$img->id.' не имело изображение ('.$path.')', E_USER_WARNING);
		}

		return true;
	}

}