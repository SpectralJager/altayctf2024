<?php

namespace Core;

use Exception;

class DB{

	/** @var \SQLite3 $conn - текущее с БД */
	protected $conn;
	/** @var int $transaction_level - уровень транзакции */
	protected $transaction_level = 0;

	protected $options = [
		'db' => '',
		'timeout' => 5000,        //ожидание операции в мсек
		'charset' => 'UTF-8',
		'journal_mode' => 'WAL',
		'temp_store' => 'MEMORY',
	];

	const RESULT_ASSOC = SQLITE3_ASSOC;
	const RESULT_NUM = SQLITE3_NUM;

	function __construct($opt = []){
		$opt = array_merge($this->options, $opt);

		try{
			$this->conn = new \SQLite3($opt['db']);
		}catch(\Exception $ex){
			$this->error($ex->getMessage());
			return;
		}

		// размер страницы БД; страница БД - это единица обмена между диском и кэшом, разумно сделать равным размеру кластера диска
		if(!$this->conn->busyTimeout($opt['timeout'])){
			$this->error($this->conn->lastErrorMsg());
		}

		// размер страницы БД; страница БД - это единица обмена между диском и кэшом, разумно сделать равным размеру кластера диска
		if(!$this->conn->exec('PRAGMA page_size = 4096')){
			$this->error($this->conn->lastErrorMsg());
		}

		// задать размер кэша соединения в килобайтах, по умолчанию он равен 2000 страниц БД
		if(!$this->conn->exec('PRAGMA cache_size = 10000')){
			$this->error($this->conn->lastErrorMsg());
		}

		if(!$this->conn->exec('PRAGMA journal_mode = '.$this->escape($opt['journal_mode']))){
			$this->error($this->conn->lastErrorMsg());
		}

		if(!$this->conn->exec('PRAGMA temp_store = '.$this->escape($opt['temp_store']))){
			$this->error($this->conn->lastErrorMsg());
		}

		if(!$this->conn->exec('PRAGMA synchronous = NORMAL')){
			$this->error($this->conn->lastErrorMsg());
		}

		if(!$this->conn->exec('PRAGMA foreign_keys = 1')){
			$this->error($this->conn->lastErrorMsg());
		}

		if(!$this->conn->exec('PRAGMA encoding = "'.$this->escape($opt['charset']).'"')){
			$this->error($this->conn->lastErrorMsg());
		}

	}

	/**
	 * Выбирает одну строку из результирующего набора и помещает её в ассоциативный или нумерованный массив, или в оба сразу
	 * @param \SQLite3Result $result
	 * @param int $mode
	 * @return array|false
	 * @see \SQLite3Result::fetchArray()
	 */
	public function fetch(\SQLite3Result $result, $mode = self::RESULT_ASSOC){
		return $result->fetchArray($mode);
	}

	/**
	 * Закрывает ресурс
	 * @param \SQLite3Result $result
	 * @see \SQLite3Result::finalize()
	 */
	public function free(\SQLite3Result $result){
		$result->finalize();
	}

	/**
	 * Вернет количество строк, которые были изменены/удалены/вставлены последним запросом
	 * @return int
	 */
	public function affected_rows(){
		return $this->conn->changes();
	}

	/**
	 * Возвращает идентификатор строки последней вставки (INSERT) в базу данных
	 * @return int
	 */
	public function insert_id(){
		return $this->conn->lastInsertRowID();
	}

	/**
	 * Запускает транзакцию, если транзакция уже запущена, увеличивает уровень
	 * @return bool
	 */
	public function start_transaction(){
		if($this->transaction_level){
			$this->transaction_level++;
			return true;
		}
		if($this->conn->exec('BEGIN IMMEDIATE;')){
			$this->transaction_level++;
			return true;
		}
		return false;
	}

	/**
	 * Возвращает запущена ли сейчас транзакция
	 * @return bool
	 */
	public function is_transaction(){
		return !!$this->transaction_level;
	}

	/**
	 * Производит коммит транзакции, если она последняя
	 * @return bool
	 */
	public function commit(){
		if($this->transaction_level > 1){
			$this->transaction_level--;
			return true;
		}else if($this->transaction_level == 1){
			if($this->conn->exec('COMMIT;')){
				$this->transaction_level--;
				return true;
			}
		}
		return false;
	}

	/**
	 * Производит коммит
	 * @return bool
	 */
	public function force_commit(){
		if($this->transaction_level && $this->conn->exec('COMMIT;')){
			$this->transaction_level = 0;
			return true;
		}
		return false;
	}

	/**
	 * Производит откат транзакции, если она последняя
	 * @return bool
	 */
	public function rollback(){
		if($this->transaction_level > 1){
			$this->transaction_level--;
			return true;
		}else if($this->transaction_level == 1){
			if($this->conn->exec('ROLLBACK;')){
				$this->transaction_level--;
				return true;
			}
		}
		return false;
	}

	/**
	 * Производит откат
	 * @return bool
	 */
	public function force_rollback(){
		if($this->transaction_level && $this->conn->exec('ROLLBACK;')){
			$this->transaction_level = 0;
			return true;
		}
		return false;
	}

	/**
	 * Выполняет запрос возвращающий скалярное значение
	 * @param string $query
	 * @return bool
	 */
	public function exec(string $query){
		$res = false;
		try{
			$res = $this->conn->exec($query);
		}catch(\Exception $e){
			$this->error($e->getMessage().' Full query: ['.$query.']');
			return false;
		}

		if(!$res){
			$error = $this->conn->lastErrorMsg();
			$this->error($error.' Full query: ['.$query.']');
		}
		return $res;
	}

	/**
	 * Выполняет запрос возвращающий SQLite3Result
	 * @param string $query
	 * @return false|\SQLite3Result
	 */
	public function query(string $query){
		$res = false;
		try{
			$res = $this->conn->query($query);
		}catch(\Exception $e){
			$this->error($e->getMessage().' Full query: ['.$query.']');
			return false;
		}

		if(!$res){
			$error = $this->conn->lastErrorMsg();
			$this->error($error.' Full query: ['.$query.']');
		}
		return $res;
	}

	/**
	 * Обертка для единичного запроса с параметрами к БД, для получения одного значения (первая строка, первый столбец)
	 *
	 * @param string $query - запрос
	 * @return mixed|false
	 */
	public function get_one(string $query){
		if($res = $this->query($query)){
			$row = $this->fetch($res);
			if(is_array($row)){
				return reset($row);
			}
			$this->free($res);
		}
		return false;
	}

	/**
	 * Обертка для единичного запроса с параметрами к БД, для получения одной строки (первая строка)
	 *
	 * @param string $query - запрос
	 * @return array|false
	 */
	public function get_row(string $query){
		if($res = $this->query($query)){
			$ret = $this->fetch($res);
			$this->free($res);
			return $ret;
		}
		return false;
	}

	/**
	 * Обертка для единичного запроса с параметрами к БД, для получения одного столбца (первого столбца)
	 *
	 * @param string $query - запрос
	 * @return array|false
	 */
	public function get_col(string $query){
		if($res = $this->query($query)){
			$ret = [];
			while($row = $this->fetch($res)){
				$ret[] = reset($row);
			}
			$this->free($res);
			return $ret;
		}
		return false;
	}

	/**
	 * Обертка для единичного запроса с параметрами к БД, для получения всех данных
	 *
	 * @param string $query - запрос
	 * @return array|false
	 */
	public function get_all(string $query){
		if($res = $this->query($query)){
			$ret = [];
			while($row = $this->fetch($res)){
				$ret[] = $row;
			}
			$this->free($res);
			return $ret;
		}
		return false;
	}
	
	/**
	 * Обертка для единичного запроса с параметрами к БД, для получения всех данных, номера строк заменятся значением указанного столбца
	 *
	 * @param string $index - наименование столбца, значения которго станут ключём в массиве
	 * @param string $query - запрос
	 * @return array|false
	 */
	public function get_ind(string $index, string $query){
		if($res = $this->query($query)){
			$ret = [];
			while($row = $this->fetch($res)){
				$ret[$row[$index]] = $row;
			}
			$this->free($res);
			return $ret;
		}
		return false;
	}

	/**
	 * Обертка для единичного запроса с параметрами к БД, для получения всех данных, номера строк заменятся значением указанного столбца,
	 * значение столбца из строки будет удалено
	 *
	 * @param string $index - наименование столбца, значения которго станут ключём в массиве
	 * @param string $query - запрос
	 * @return array|false
	 */
	public function get_ind_col(string $index, string $query){
		if($res = $this->query($query)){
			$ret = [];
			while($row = $this->fetch($res)){
				$key = $row[$index];
				unset($row[$index]);
				$ret[$key] = $row;
			}
			$this->free($res);
			return $ret;
		}
		return false;
	}

	/**
	 * Производит экранирование строки
	 * @param string $str
	 * @return string
	 */
	public function escape(string $str){
		return $this->conn::escapeString($str);
	}

	protected function error($err){
		$err = self::class.': '.$err;
		$err .= '. Error initiated in '.$this->caller();

		throw new Exception($err);
	}

	protected function caller(){
		$trace = debug_backtrace();
		$caller = '';
		foreach($trace as $t){
			if(isset($t['class']) && $t['class'] == self::class){
				$caller = $t['file'].' on line '.$t['line'];
			}else{
				break;
			}
		}
		return $caller;
	}
}
