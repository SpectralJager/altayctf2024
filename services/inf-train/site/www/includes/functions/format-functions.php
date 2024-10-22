<?php

/**
 * возвращает целое положительное число
 * @param mixed $a
 * @return int
 */
function absint($a){
	return abs(intval($a));
}

/**
 * возвращает положительное число с плавающей точкой
 * @param mixed $a
 * @return float
 */
function absfloat($a){
	return abs(floatval($a));
}

/**
 * переводит количество секунд во время
 * @param int $sec - 0, SECONDS_PER_DAY
 * @param bool $hour_zero - предшествующий 0 для часов
 * @return string
 */
function seconds_to_time($sec, $hour_zero = true){
	$sec = absint($sec);
	$tmp = intdiv($sec, SECONDS_PER_HOUR);
	if($hour_zero){
		$tmp = str_pad($tmp, 2, '0', STR_PAD_LEFT);
	}
	$tmp .= ':'.str_pad(intdiv($sec % SECONDS_PER_HOUR, SECONDS_PER_MINUTE), 2, '0', STR_PAD_LEFT);
	return $tmp;
}

/**
 * возвращает форматированный объем памяти
 * @param int|float $size
 * @param string $delimiter - разделитель между размером файла и единицей измерения
 * @return string
 */
function round_memsize($size, $delimiter = ' '){
	$unit = 'b';
	
	if($size > 1024){
		$size = (float) $size / 1024;
		$unit = 'Kb';
	}
	
	if($size > 1024){
		$size = (float) $size / 1024;
		$unit = 'Mb';
	}
	
	if($size > 1024){
		$size = (float) $size / 1024;
		$unit = 'Gb';
	}
	
	if($size < 100){
		$size = round($size, 2);
	}else if($size < 1000){
		$size = round($size, 1);
	}else{
		$size = round($size, 0);
	}
	
	return $size.$delimiter.$unit;
}

/**
 * Возвращает строку смещения относительно московского времени
 * типа 'МСК' (если смещение == 0), 'МСК+4', 'МСК-3', 'МСК+2:30'
 * @param DateTime $date_time
 * @return string 
 */
function get_msk_time_offset($date_time){
	$offset = $date_time->getOffset() / SECONDS_PER_HOUR - 3.0;//3.0 - сдвиг московсокого часового пояса относительно UTC
	$offset_str = '';
	if($offset != 0){
		$offset_str = ($offset >= 0 ? '+' : '-').absint($offset);
		$offset = abs($offset);
		if(intval($offset) != $offset){
			$offset -= intval($offset);
			$offset_str .= ':'.round(60 * $offset);
		}
	}
	return 'МСК'.$offset_str;
}

/**
 * Склонение слова после числа.
 *
 * Примеры вызова:
 * num_decline($num, ['книга','книги','книг'])
 * num_decline($num, 'книга', 'книги', 'книг')
 * num_decline($num, 'книга', 'книг')
 *
 * @param  int    $number  Число после которого будет слово. Можно указать число в HTML тегах.
 * @param  string|array  $titles  Варианты склонения или первое слово для кратного 1.
 * @param  string        $param2  Второе слово, если не указано в параметре $titles.
 * @param  string        $param3  Третье слово, если не указано в параметре $titles.
 *
 * @return string 1 книга, 2 книги, 10 книг.
 */
function num_decline($number, $titles, $param2 = '', $param3 = ''){
	if($param2)
		$titles = [$titles, $param2, $param3];

	if(empty($titles[2]))
		$titles[2] = $titles[1]; // когда указано 2 элемента

	$cases = [2, 0, 1, 1, 1, 2];

	$number = absint($number);

	return $number.' '. $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}

/**
 * Возвращает ' checked="checked"' если $need_set true, иначе ''
 * @param bool $need_set - нужно ли check
 * @return string
 */
function set_checked(bool $need_set): string{
	return $need_set ? ' checked="checked"' : '';
}

/**
 * Возвращает ' selected="selected"' если $need_set true, иначе ''
 * @param bool $need_set - нужно ли selected
 * @return string
 */
function set_selected(bool $need_set): string{
	return $need_set ? ' selected="selected"' : '';
}

/**
 * Возвращает $href если $need_set true, иначе javascript:void(0)
 * @param bool $need_set - нужно ли установить ссылку
 * @param string $href - возвращаемый адрес ссылки
 * @return string
 */
function set_href(bool $need_set, string $href): string{
	return $need_set ? $href : 'javascript:void(0)';
}

/**
 * Возвращает javascript:void(0) если текущая страница $page, иначе ссылку на эту страницу
 * @param class-string<\Core\PageStorage\Page> $page - класс требуемой страницы
 * @param string[] $parameters - параметры для get_url
 * @see Url::get_url()
 * @return string
 */
function set_href_for_current_page(string $page, array $parameters=[]): string{
	global $URL;
	return $URL->get_current_page() === $page ? 'javascript:void(0)' : $URL->get_url($page, $parameters);
}

/**
 * Если $page текущая страница вернет $current, иначе $another
 * @param class-string<\Core\PageStorage\Page> $page - класс требуемой страницы
 * @param mixed $current - Возвращаемое значение если переданная страница текущая
 * @param mixed $another - Возвращаемое значение если переданная страница НЕ текущая
 * @return mixed
 */
function if_current(string $page, $current, $another = ''){
	global $URL;
	return $URL->get_current_page() === $page ? $current : $another;
}