<?php

namespace Core;

use Exception;
use DateTime;

final class ErrorLog{
	/** @var string $path - путь к папке в которой хранятся логи */
	private $path;
	/** @var string[] $error_type - массив расшифровки констант E_USER_ERROR в текст */
	private $error_type = [
		E_USER_ERROR	=> 'E_USER_ERROR',
		E_USER_WARNING	=> 'E_USER_WARNING',
		E_USER_NOTICE	=> 'E_USER_NOTICE',
		E_ERROR			=> 'E_ERROR',
		E_WARNING		=> 'E_WARNING',
		E_NOTICE		=> 'E_NOTICE',
	];
	
	/** @var string $curr_error_f - имя текущего файла лога */
	private $curr_error_f;


	/** @var ErrorLog $_instance */
	private static $_instance = null;

	/**
	 * параметры передаются при создании первого экземпляра
	 * @param string $path - путь до папки с логами (должен оканчиваться на /)
	 */
	public static function instance($path=null){
		if(is_null(self::$_instance)){
			self::$_instance = new self($path);
		}

		return self::$_instance;
	}

	private function __clone(){
		throw new Exception('try to clone singleton '.self::class);
	}
	public function __wakeup(){
		throw new Exception('try to wakeup singleton '.self::class);
	}
	
	/**
	 * @param string $path - путь до папки с логами
	 */
	private function __construct($path){
		$this->path = realpath((string)$path).'/';
		if(!check_dir($this->path))
			throw new Exception('undefined log path');
		$this->curr_error_f = 'error.log';
		
		error_reporting(E_ALL);
		
		set_error_handler([$this, 'log_error']);
		set_exception_handler([$this, 'log_exception']);
	}

	/**
	 * callback для set_error_handler
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 */
	public function log_error($errno, $errstr, $errfile, $errline){
		$type = $this->error_type[$errno] ?? 'UDENFINED('.$errno.')';
		
		$trace = '';
		switch($errno){
			case E_ERROR:
			case E_USER_ERROR:
			case E_USER_WARNING:
			case E_WARNING:
				$trace = ', trace: '.json_encode(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 10));
				break;
		}
		$now = new DateTime();
		$out = '['.$now->format('Y-M-d H:i:s').'] ['.$type.'] '.$errstr.'; File: '.$errfile.', line: '.$errline.$trace.PHP_EOL;
		$this->log_write($out);
		if($errno === E_USER_ERROR){
			die();
		}
	}

	/**
	 * callback для set_exception_handler
	 * @param Exception $exception
	 */
	public function log_exception($exception){
		$now = new DateTime();
		$out = '['.$now->format('Y-M-d H:i:s').'] [EXCEPTION] '.$exception->getMessage().'; File: '.($exception->getFile()).', line: '.($exception->getLine()).', trace: '.($exception->getTraceAsString()).PHP_EOL;
		$this->log_write($out);
		die();
	}

	/**
	 * Производит запись текста в log файл
	 * @param string $data
	 * @return false|int
	 */
	private function log_write($data){
		return file_put_contents($this->path.$this->curr_error_f, $data, FILE_APPEND | LOCK_EX);
	}
	
	public function __destruct(){
		restore_error_handler();
		restore_exception_handler();
	}
}