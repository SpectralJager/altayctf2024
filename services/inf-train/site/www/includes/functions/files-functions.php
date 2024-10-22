<?php

/**
 * подключает файл, возвращает то что вывел данный файл
 * @param $path - путь к файлу
 * @return false|string
 */
function include_file($path){
	if(is_file($path)){
		ob_start();
		include $path;
		return ob_get_clean();
	}
	return false;
}

/**
 * Проверяет существование ФАЙЛА
 * @param $path - путь до файла
 * @return bool
 */
function check_file($path){
	return file_exists($path) && is_file($path);
}

/**
 * Проверяет существование папки
 * @param $path - путь до папки
 * @return bool
 */
function check_dir($path){
	return file_exists($path) && is_dir($path);
}

/**
 * возвращает список имен файлов и папок (включая . и ..) расположенных в папке $path
 * @param string $path - путь до папки с файлами
 * @param string $type - 'all', 'folder', 'file'
 * @param string $ext - расширение файла (с точкой)
 * @param string $name_pattern - шаблон имени, часть регулярного выражения (итоговое выражение '#'.$name_pattern.preg_quote($ext).'$#')
 * @return false|string[]
 */
function file_list($path, $type = 'all', $ext='', $name_pattern='^.*?'){
	if(!check_dir($path)){
		return false;
	}
	
	$path = realpath($path).'/';
	$files = scandir($path);
	
	$pattern = '#'.$name_pattern.preg_quote($ext).'$#';
	
	$len = sizeof($files);
	for($i=0; $i<$len; $i++){
		if(!preg_match($pattern, $files[$i])){
			unset($files[$i]);
			continue;
		}
		if($type === 'folder' && !is_dir($path.$files[$i])){
			unset($files[$i]);
			continue;
		}
		if($type === 'file' && !is_file($path.$files[$i])){
			unset($files[$i]);
			continue;
		}
	}
	return array_values($files);
}

/**
 * возвращает список имен файлов и папок с файлами расположенных в папке $path и дочерних,
 * имена файлов и папок являются ключами массива
 * @param string $path - путь до папки с файлами
 * @param string $ext - расширение файла (с точкой)
 * @param string $name_pattern - шаблон имени, часть регулярного выражения (итоговое выражение '#'.$name_pattern.preg_quote($ext).'$#')
 * @return false|array
 *
 * <pre>
 * [
 * 	'dir_1' => [
 * 		'dir_1.1' => [...],
 * 		'file_1' => true,
 * 		...
 * 	],
 * 	'file_1' => true,
 * 	'file_2' => true,
 * 	...
 * ]
 * </pre>
 */
function file_list_recursive($path, $ext='', $name_pattern='^.*?'){
	if(!check_dir($path)){
		return false;
	}

	$path = realpath($path).'/';
	$files = scandir($path);
	
	$ret = [];

	$pattern = '#'.$name_pattern.preg_quote($ext).'$#';

	$len = sizeof($files);
	for($i=0; $i<$len; $i++){
		if(is_dir($path.$files[$i]) && $files[$i] !== '.' && $files[$i] !== '..'){
			$ret[$files[$i]] = file_list_recursive($path.$files[$i], $ext, $name_pattern);
		}
		if(preg_match($pattern, $files[$i])){
			$ret[$files[$i]] = true;
		}
	}
	return $ret;
}

/**
 * возвращает объем файла в байтах
 * @param string $path - путь до файла
 * @param bool $clear_cache - сбросить кэш
 * @return false|int - false если файл не найден
 */
function get_filesize(string $path, bool $clear_cache = false){
	if(!check_file($path))
		return false;
	if($clear_cache){
		clearstatcache(1, $path);
	}
	return filesize($path);
}

/**
 * рекурсивно создает папки по переданному пути если они не существуют
 * @param string $path - путь до требуемой папки (оканчивается на /) (или предполагаемого файла в ней)
 */
function create_dirs_for_file(string $path){
	$path = pathinfo($path, PATHINFO_DIRNAME);
	if(!check_dir($path)){
		mkdir($path, 0755, 1);
	}
}