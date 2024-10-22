<?php

namespace Core\Request;

/**
 * @property-read string $name - имя файла, переданное пользователем
 * @property-read string $user_name - имя файла, переданное пользователем
 * @property-read string $type - mime тип файла, переданный пользователем
 * @property-read string $mime - mime тип файла, переданный пользователем
 * @property-read string $tmp_name - путь до tmp файла
 * @property-read string $tmp_path - путь до tmp файла
 * @property-read int $error - код ошибки,
 * 	UPLOAD_ERR_OK - файл загружен успешно,
 * 	UPLOAD_ERR_INI_SIZE - превышен максимальный размер допустимый upload_max_filesize, 
 * 	UPLOAD_ERR_FORM_SIZE - превышен размер указанный в MAX_FILE_SIZE формы
 * 	UPLOAD_ERR_PARTIAL - файл получен частично
 * 	UPLOAD_ERR_NO_FILE - файл не загружен
 * 	UPLOAD_ERR_NO_TMP_DIR - временная папка отсутствует
 * 	UPLOAD_ERR_CANT_WRITE - не удалось записать файл на диск
 * 	UPLOAD_ERR_EXTENSION - модуль PHP остановил загрузку файла
 * @property-read int $size - размер загруженного файла в байтах
 * @property-read int $size_b - размер загруженного файла в байтах
 */
class UploadedFile{

	private $tmp_path;
	private $mime;
	private $error;
	private $size_b;
	private $user_name;

	public function __construct(string $tmp_path, string $mime, int $error, int $size_b, string $user_name){
		$this->tmp_path = $tmp_path;
		$this->mime = $mime;
		$this->error = $error;
		$this->size_b = $size_b;
		$this->user_name = $user_name;
	}
	
	public function __get($key){
		switch($key){
			case 'name':
			case 'user_name':
				return $this->user_name;
			case 'type':
			case 'mime':
				return $this->mime;
			case 'tmp_name':
			case 'tmp_path':
				return $this->tmp_path;
			case 'error':
				return $this->error;
			case 'size':
			case 'size_b':
				return $this->size_b;
			default:
				trigger_error('try to get undefined property '.$key, E_USER_WARNING);
				return null;
		}
	}
}