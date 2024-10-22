<?php

namespace Core;

use Core\Url\Node;
use Exception;

class Url{

	/** @var string $main_page_id - id главной страницы */
	private $main_page_id = '';

	/** @var string $_404_page_id - id 404 страницы */
	private $_404_page_id = '';
	
	/** @var string $current_page - id текущей страницы */
	private $current_page = '';
	
	/** @var Node[] $pages - массив дочерних страниц [{url_slice} => Node] */
	private $pages;
	
	/** @var string $url - текущий url (path) */
	private $url;
	
	/**
	 * массив использованных id, id являются ключами
	 * [{page_id} => true, ...]
	 * @var bool[]
	 */
	private $id_list;
	
	/**
	 * массив ID страниц и частей url от родительской до текущей дочерней
	 * [{page_id} => url_slice, ...]
	 * @var string[]
	 */
	private $breadcrumbs;
	
	/**
	 * @var bool $current_page_changed - была ли текущая страница изменена
	 * @see set_current_page
	 * @see set_current_404
	 */
	private $current_page_changed = false;


	/**
	 * @param string $main_page_id - id главной страницы
	 * @param string $_404_page_id - id 404 страницы
	 */
	public function __construct(string $main_page_id = 'system_main_page', string $_404_page_id = 'system_404_page'){
		if(empty($main_page_id) || empty($_404_page_id) || $main_page_id === $_404_page_id){
			trigger_error('main_page_id = "'.$main_page_id.'" and "'.$_404_page_id.'" are invalid', E_USER_ERROR);
		}
		
		$this->main_page_id = $main_page_id;
		$this->_404_page_id = $_404_page_id;
		
		$this->url = urldecode(Request::instance()->url_data['path']);
		$this->pages = [];
		$this->id_list = [];
		$this->breadcrumbs = [];
	}

	/**
	 * добавление страницы
	 *
	 * @param string $id        - уникальный ID
	 * @param string $url_slice - кусочек УРЛа, пустой для параметризованного уровня
	 * @param string|null $parent_id - ID родителя (null для верхней страницы)
	 * @return true
	 * @throws Exception 'Page ID is empty', 'Not unique page ID', 'URL slice is collisional'
	 */
	public function add_page(string $id, string $url_slice, $parent_id = null){
		if($id === ''){
			throw new Exception('Page ID is empty');
		}
		if($this->has_page($id))
			throw new Exception('Not unique page ID '.$id);
		if(!is_null($parent_id) && !isset($this->id_list[$parent_id]))
			throw new Exception('Parent '.$parent_id.' is not found');
		
		$node = new Node();
		$node->id = $id;
		$node->url_slice = $url_slice;
		
		if(is_null($parent_id)){
			//родителя нет
			if(isset($this->pages[$node->url_slice])){
				throw new Exception('URL slice '.$url_slice.' is collisional (id: '.$this->pages[$node->url_slice]->id.')');
			}
			
			$this->pages[$url_slice] = $node;
			$this->id_list[$id] = true;
			
		}else{
			//родитель есть
			$parent_page = $this->get_page_recursion($parent_id, $this->pages);
			if(isset($parent_page->child[$node->url_slice])){
				throw new Exception('URL slice '.$url_slice.' is collisional (id: '.$parent_page->child[$node->url_slice]->id.')');
			}
			
			$node->parent = $parent_page;
			$parent_page->child[$node->url_slice] = $node;
			$this->id_list[$node->id] = true;
		}
		return true;
	}

	/**
	 * возвращает, добавлена ли страница с указанным id
	 * @param string $id
	 * @return bool
	 */
	public function has_page(string $id){
		return isset($this->id_list[$id]) || $id === $this->_404_page_id || $id === $this->main_page_id;
	}

	/** 
	 * была ли текущая страница изменена вручную
	 * @see set_current_page
	 * @see set_current_404
	 * @return bool
	 */
	public function is_current_page_changed():bool{
		return $this->current_page_changed;
	}

	
	/**
	 * возвращает текущий УРЛ (path)
	 * @return string
	 */
	public function get_current_url(){
		return $this->url;
	}

	/**
	 * возвращает id текущей страницы
	 * перед этим загружает текущую страницу
	 * @see load_current_page
	 * @return string
	 */
	public function get_current_page(){
		$this->load_current_page();
		return $this->current_page;
	}
	
	/**
	 * Вернет массив хлебных крошек
	 * [{page_id} => url_slice, ...]
	 * @return string[]
	 */
	public function get_breadcrumbs(){
		return $this->breadcrumbs;
	}

	/**
	 * возвращает содержимое параметризованного уровня, null - если такого уровня нет
	 * перед этим загружает текущую страницу
	 * @param string $page_id - ID параметризованной страницы
	 * @see load_current_page
	 * @return string|null
	 */
	public function get_parameter(string $page_id){
		$this->load_current_page();
		return $this->breadcrumbs[$page_id] ?? null;
	}
	
	/**
	 * анализирует УРЛ, устанавливает текущую страницу и параметризованные уровни
	 * @param bool $reload - true - проигнорирует то, что страница была уже установлена, загрузит страницу еще раз
	 * @return true
	 */
	public function load_current_page(bool $reload=false){
		
		if($reload){
			$this->current_page = '';
			$this->breadcrumbs = [];
			$this->current_page_changed = false;
		}
		
		if($this->current_page !== '')
			return true;
		
		$elements = array_slice(explode('/', $this->url), 1);
		
		$len = sizeof($elements);
		if($len == 1 && $elements[0] === ''){
			$this->current_page = $this->main_page_id;
			return true;
		}
		
		$pages = $this->pages;
		for($i=0; $i<$len; $i++){
			if($i == $len-1 && $elements[$i] === '')
				break;
			if(isset($pages[$elements[$i]])){
				//сначала поиск нужной страницы на этом уровне
				$this->current_page = $pages[$elements[$i]]->id;
				$this->breadcrumbs[$pages[$elements[$i]]->id] = $pages[$elements[$i]]->url_slice;
				$pages = $pages[$elements[$i]]->child;
				
			}else if(isset($pages[''])){
				//если не нашли, проверим на параметризованный уровень
				$this->current_page = $pages['']->id;
				$this->breadcrumbs[$pages['']->id] = $elements[$i];
				$pages = $pages['']->child;

			}else {
				//страница не найдена
				$this->set_current_404();
				$this->current_page_changed = false;
				return true;
			}
		}
		
		if($this->current_page === ''){
			$this->set_current_404();
			$this->current_page_changed = false;
			return true;
		}
		
		return true;
	}

	/**
	 * устанавливает текущей страницей 404-ую
	 */
	public function set_current_404(){
		$this->current_page_changed = true;
		$this->current_page = $this->_404_page_id;
		status_header(404);
	}
	
	/**
	 * устанавливает текущей новую страницу
	 * @param string $id - ID новой страницы
	 * @param string[] $parameters - содержимое параметризованных уровней, участвующих в построении url [{page_id} => string]
	 * @throws Exception
	 * @return true
	 */
	public function set_current_page($id, $parameters = []){
		if($this->main_page_id === $id){
			$this->breadcrumbs = [];
			$this->current_page = $this->main_page_id;
			$this->current_page_changed = true;
			return true;
		}
		
		if(!isset($this->id_list[$id])){
			throw new Exception('Undefined page_id: '.$id);
		}
		
		$breadcrumbs = [];
		$tmp = $this->get_page_recursion($id, $this->pages, $breadcrumbs);
		foreach($breadcrumbs as $page_id => $url_slice){
			if($url_slice === ''){
				if(isset($parameters[$page_id])){
					$breadcrumbs[$page_id] = urlencode((string) $parameters[$page_id]);
				}else{
					throw new Exception('Not passed url slice for parametrise page (id: '.$page_id.')');
				}
			}
		}

		$this->breadcrumbs = array_reverse($breadcrumbs);
		$this->current_page = $tmp->id;
		$this->current_page_changed = true;
		return true;
	}
	
	/**
	 * возвращает url path для страницы с указанным ID
	 * @param $id - ID страницы
	 * @param string[] $parameters - содержимое параметризованных уровней, участвующих в построении url [{page_id} => string]
	 * @return string - /level_1/level_2/level_3/
	 * @throws Exception
	 * @see set_current_page
	 */
	public function get_url($id, $parameters = []){
		$tmp = clone $this;
		$tmp->set_current_page($id, $parameters);
		
		$ret = '/';
		if(sizeof($tmp->breadcrumbs)){
			$ret .= implode('/', $tmp->breadcrumbs).'/';
		}
		return $ret;
	}

	/**
	 * Перенаправляет на страницу с указанным ID
	 * @param $id - ID страницы
	 * @param string[] $parameters - содержимое параметризованных уровней, участвующих в построении url [{page_id} => string]
	 * @param array $query_data - массив данных, передаваемый в http_build_query
	 * @param string $fragment - указатель на якорь на странице, будет закодирован с помощью функции rawurlencode
	 * @throws Exception
	 * @see get_url
	 * @see redirect
	 */
	public function redirect($id, array $parameters=[], array $query_data=[], string $fragment = ''){
		$query = sizeof($query_data) ? '?'.http_build_query($query_data, '', '&') : '';
		$fragment = mb_strlen($fragment) ? '#'.rawurlencode($fragment) : '';
		redirect($this->get_url($id, $parameters).$query.$fragment);
	}

	/**
	 * Перенаправляет на текущую страницу с текущим GET массивом (перезагружает)
	 * @throws Exception
	 * @see redirect
	 */
	public function reload(){
		$this->redirect($this->get_current_page(), $this->get_breadcrumbs(), $_GET);
	}

	/**
	 * возвращает узел дерева с нужным ID с помощью поиска в глубину
	 * @param $id - ID нужной страницы (если ID не существует вернет пустой Node)
	 * @param $tree - узел дерева, в первый раз передавать $this->pages
	 * @param $breadcrumbs - массив куда поместятся хлебные крошки (в обратном порядке), [{page_id} => {url_slice}]
	 * @return Node - узел страницы
	 */
	private function get_page_recursion($id, &$tree, &$breadcrumbs = []){
		foreach($tree as $url_slice => &$page){
			if($page->id == $id){
				$breadcrumbs[$page->id] = $page->url_slice;
				return $page;
			}else{
				$tmp = $this->get_page_recursion($id, $page->child, $breadcrumbs);
				if($tmp->id !== ''){
					$breadcrumbs[$page->id] = $page->url_slice;
					return $tmp;
				}
			}
		}
		return new Node();
	}
}