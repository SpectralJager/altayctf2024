<?php

namespace Core\Url;

class Node{
	/** @var string $id - уникальный идентификатор страницы */
	public $id = '';
	/** @var string $url_slice - строка-уровень используемая в url, пустая, если это параметризованный уровень */
	public $url_slice = '';
	/** @var Node[] $child - массив дочерних страниц [{url_slice} => Node] */
	public $child;
	/** @var null|Node $parent - родительский узел */
	public $parent = null;
	function __construct(){
		$this->child = [];
		$this->parent = null;
	}

	/**
	 * @return bool - является ли уровень параметрическим и не имеет кусочка url'а
	 */
	function is_parameter():bool{
		return (string) $this->url_slice === '';
	}
}