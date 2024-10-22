<?php
namespace Resources\Api\Images;

use Core\Image;
use Exception;
use Resources\Api\Images;

/**
 * метод DELETE - Удаляет изображение с сайта
 */
class Edit extends Images{
	/** @var bool $private - доступ к странице только зарегистрированному пользователю */
	protected static $private = true;
	/** @var string $url_slice - строка-уровень используемая в url, наследуется от родителя */
	protected static $url_slice = '';
	/** @var array|true $allowed_methods - массив доступных методов для обработки или true - допустимы все методы */
	protected static $allowed_methods = ['delete'];
	
	/** @var Image $image - текущее изображение */
	protected $image = null;

	public function test_exec(string $method): bool{

		global $URL;
		$image_id = absint($URL->get_parameter(self::class));
		
		try{
			$this->image = Image\Factory::get_from_db($image_id);
		}catch(Exception $ex){
			return false;
		}

		return true;
	}
	
	/**
	 * Вызывается после test_exec, обработка текущего запроса метода DELETE
	 * удаляет изображение
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
			return ['data' => ['err' => true, 'msg' => 'Не достаточно прав для удаления этого изображения'], 'status' => 403];
		}
		
		$res = Image\Saver::delete($this->image);
		
		if(!$res){
			return ['data' => ['err' => true, 'msg' => 'Не удалось удалить изображение'], 'status' => 500];
		}

		return ['data' => ['err' => false, 'msg' => 'Изображение удалено'], 'status' => 200];
	}
}