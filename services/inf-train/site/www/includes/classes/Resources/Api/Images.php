<?php
namespace Resources\Api;

use Core\ApiStorage;
use Core\Image;
use Exception;
use Resources\Api;
use Core\Request\UploadedFile;

/**
 * метод POST - Добавляет изображения на сайт
 */
class Images extends Api{

	/** @var bool $private - доступ к странице только зарегистрированному пользователю */
	protected static $private = true;
	/** @var string $url_slice - строка-уровень используемая в url, наследуется от родителя */
	protected static $url_slice = 'images';
	/** @var array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы */
	protected static $allowed_methods = ['post'];
	
	/**
	 * Вызывается после test_exec, обработка текущего запроса метода POST
	 * Добавляет изображения
	 * array $_FILES['images']
	 * array $_POST['images_public']
	 * 
	 * 
	 * @return array
	 * <pre>
	 * [
	 * 	'data' => [
	 * 		'err' => bool,
	 * 		'msg' => string,
	 * 		'added_images' => null|[
	 * 			id => [
	 * 				'id' => int,
	 * 				'time_create' => string,
	 * 				'public' => bool,
	 * 				'image_w' => int,
	 * 				'image_h' => int,
	 * 				'file_size' => int,
	 * 				'type' => string,
	 * 				'name' => string,
	 * 				'storage' => string,
	 * 				'old_name' => string,
	 * 			],
	 * 			...
	 * 		],
	 * 	],
	 * 	'status' => int - http код ответа
	 * ]
	 * </pre>
	 */
	public function execute_post(){
		if(!isset($_FILES['images'], $_POST['images_public'])){
			return ['data' => ['err' => true, 'msg' => 'Изображения не переданы'], 'status' => 400];
		}
		
		global $USER;

		$msg = '';
		
		$added_image_data = [];
		$uploaded_size = 0;
		
		$files = ApiStorage::instance()->uploaded_files['images'];
		if(!is_array($files)){
			$files = [$files];
		}
		$files = array_values($files);
		
		$public = $_POST['images_public'];
		if(!is_array($public)){
			$public = [$public];
		}
		$public = array_values($public);
		
		$len = sizeof($files);
		for($i=0; $i<$len; $i++){
			$image = $files[$i];
			
			if(!is_object($image)){
				continue;
			}
			
			/** @var UploadedFile $image */

			if($image->error !== UPLOAD_ERR_OK){
				$msg .= 'Ошибка загрузки '.$image->name.PHP_EOL;
				continue;
			}

			if(!is_uploaded_file($image->tmp_name)){
				$msg .= 'Ошибка загрузки '.$image->name.PHP_EOL;
				continue;
			}
			
			$new_name = filename_clear(pathinfo($image->user_name, PATHINFO_FILENAME));
			if(!$new_name){
				$msg .= 'Не удалось преобразовать имя изображения '.$image->name.PHP_EOL;
				continue;
			}

			$saved_img = null;
			try{
				$saved_img = Image\Saver::save($image->tmp_name, $image->type, $new_name, $USER->storage, !!$public[$i] ?? false);
			}catch(Exception $ex){
				$msg .= 'Не удалось сохранить изображение '.$image->name.' (ошибка: '.$ex->getMessage().')'.PHP_EOL;
				continue;
			}
			
			$added_image_data[$saved_img->id] = [
				'id' => $saved_img->id,
				'time_create' => $saved_img->time_create->format(DB_DATETIME_FORMAT),
				'public' => $saved_img->public,
				'image_w' => $saved_img->image_w,
				'image_h' => $saved_img->image_h,
				'file_size' => $saved_img->file_size,
				'type' => $saved_img->type,
				'name' => $saved_img->name,
				'storage' => $saved_img->storage,
				'old_name' => $image->name,
			];
			$msg .= 'Изображение '.$image->name.' сохранено по пути /'.$saved_img->get_image_path().PHP_EOL;
			$uploaded_size += $saved_img->file_size;
		}
		
		return [
			'data' => [
				'err' => false,
				'msg' => $msg,
				'added_images' => $added_image_data,
			],
			'status' => 200,
		];
		//дочерний класс должен будет переопределить метод, иначе будет срабатывать метод родителя
	}
}