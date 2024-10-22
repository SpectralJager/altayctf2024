<?php
namespace Core\User\Auth;

/**
 * @property-read int $id - ID пользователя
 * @property-read string $first_name - имя пользователя
 * @property-read string $last_name - фамилия пользователя
 * @property-read \DateTime $date - дата регистрации пользователя
 * @property-read string $storage - путь к хранилищу
 * 
 */
abstract class Base{
	/**  @var int $id - ID пользователя */
	protected $id;
	/** @var string $first_name - имя пользователя */
	protected $first_name;
	/** @var string $last_name - фамилия пользователя */
	protected $last_name;
	/** @var string $pass_hash - хэш пароля пользователя */
	protected $pass_hash;
	/** @var \DateTime $date - дата регистрации пользователя */
	protected $date;
	/** @var string $storage - путь к хранилищу */
	protected $storage;
	

	/**
	 * создает объект юзера
	 * если пользователь не найден
	 * то становится по умолчанию @see $this::set_default
	 * @param int $id - ID юзера, 0 для дефолтного юзера
	 */
	public function __construct($id){
		$id = absint($id);
		if(!empty($id)){
			if($this->load_user($id))
				return;
		}
		$this->set_default();
	}
	
	/**
	 * Устанавливает стандартные данные для пользователя
	 */
	public function set_default(){
		$this->id = 0;
		$this->first_name = '';
		$this->last_name = '';
		$this->storage = '';
		$this->pass_hash = '';
		$this->date = new \DateTime();
	}
	
	/**
	 * Загружает данные пользователя из БД
	 * @param int $id - ID пользователя
	 * @return bool - false - пользователь не найден
	 */
	public function load_user($id){
		global $DB;
		$id = absint($id);
		$tmp = $DB->get_row("SELECT `first_name`, `last_name`, `password`, `storage`, `date` FROM `user` WHERE `id` = $id");
		if(empty($tmp)){
			return false;
		}
		$this->id = $id;
		$this->first_name = $tmp['first_name'];
		$this->last_name = $tmp['last_name'];
		$this->storage = $tmp['storage'];
		$this->pass_hash = $tmp['password'];
		$this->date = \DateTime::createFromFormat(DB_DATETIME_FORMAT, $tmp['date']);
		return true;
	}

	public function __get($prop){
		switch($prop){
			case 'first_name':
			case 'last_name':
			case 'storage':
			case 'id':
				return $this->$prop;
			case 'date':
				return (clone $this->date);
			default:
				trigger_error('try to get undefined property '.$prop, E_USER_WARNING);
				return null;
		}
	}
}