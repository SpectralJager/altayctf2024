<?php

namespace Core;

use DateTime;
use Exception;

/**
 * @property-read int $id - ID изображения в БД
 * @property-read DateTime|null $time_create - время сохранения изображения
 * @property-read bool $public - видимость изображения всем посетителям сайта
 * @property-read int $image_w - ширина изображения в пикселях
 * @property-read int $image_h - высота изображения в пикселях
 * @property-read int $file_size - размер изображения в байтах
 * @property-read string $type - тип изображения (расширение без точки)
 * @property-read string $name - имя изображения без расширения
 * @property-read string $storage - часть пути к изображению
 */
class Image{
	
	/** @var int MAX_IMAGE_HEIGHT - максимальная высота изображения в пикселях */
	const MAX_IMAGE_HEIGHT = 200;
	/** @var int MAX_IMAGE_WIDTH - максимальная ширина изображения в пикселях */
	const MAX_IMAGE_WIDTH = 200;
	/** @var int MAX_IMAGE_WEIGHT - максимальный вес изображения в байтах */
	const MAX_IMAGE_WEIGHT = 2*BYTES_PER_KB;

	/** @var int $id - ID изображения в БД */
	protected $id = 0;
	/** @var DateTime|null $time_create - время сохранения изображения */
	protected $time_create = null;
	/** @var bool $public - видимость изображения всем посетителям сайта */
	protected $public = false;
	/** @var int $image_w - ширина изображения в пикселях */
	protected $image_w = 0;
	/** @var int $image_h - высота изображения в пикселях */
	protected $image_h = 0;
	/** @var int $file_size - размер изображения в байтах */
	protected $file_size = 0;
	/** @var string $type - тип изображения (расширение без точки) */
	protected $type = '';
	/** @var string $name - имя изображения без расширения */
	protected $name = '';
	/** @var string $storage - часть пути к изображению */
	protected $storage = '';
	
	public function __construct(){
	}

	/**
	 * Устанавливает данные изображения
	 * @param array $data - данные изображения из БД
	 * @return $this
	 */
	public function set_data($data){
		$this->id = absint($data['id'] ?? 0);
		if(isset($data['time_create'])){
			if($data['time_create'] instanceof DateTime){
				$this->time_create = clone $data['time_create'];
			}else{
				$this->time_create = DateTime::createFromFormat(DB_DATETIME_FORMAT, (string)$data['time_create']);
			}
		}else{
			$this->time_create = null;
		}
		$this->public = !!($data['public'] ?? false);
		$this->image_w = absint($data['image_w'] ?? 0);
		$this->image_h = absint($data['image_h'] ?? 0);
		$this->file_size = absint($data['file_size'] ?? 0);
		$this->type = $data['type'] ?? '';
		$this->name = $data['name'] ?? '';
		$this->storage = $data['storage'] ?? '';
		
		return $this;
	}

	/**
	 * Устанавливает состояние публичности изображения
	 * @param bool $public
	 * @return bool результат установки
	 */
	public function set_public(bool $public){
		if($public != $this->public){
			$public_clear = absint($public);
			global $DB;
			$res = $DB->exec("UPDATE `image` SET `public` = $public_clear WHERE `id` = {$this->id}");
			if(!$res){
				return false;
			}
			
			$this->public = $public;
		}
		return true;
	}
	
	public function __get($key){

		if(property_exists($this, $key)){
			if(is_object($this->$key)){
				return clone $this->$key;
			}
			return $this->$key;
		}
		trigger_error('try to get undefined property '.$key, E_USER_WARNING);
		return null;
	}

	/**
	 * Возвращает предполагаемый путь к изображению (не проверяет его существование), БЕЗ НАЧАЛЬНОГО СЛЕША
	 * @return string
	 */
	public function get_image_path(){
		return 'images/'.$this->storage.'/'.$this->name.'.'.$this->type;
	}
}