<?php

namespace phld;

use \phld\PhLD as PhLD;

/**
 * Node object.
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class Node {
	
	public $namespaces = array();
	public $id;
	public $props = array();
	public $links = array();

	public function __construct($id) {
		$this->id = $id;
	}
	
	public static function init($id) {
		$className = get_class();
		return new $className($id);
	}
	
	public function each($property, $callback) {
		if (!isset($this->$property)) return;
		foreach ($this->$property as $k => $v) {
			$callback($k, $v);
		}
	}
	
	public function serialize($format, $serializer = null) {
		if (!$serializer) {
			$serializer = PhLD::getSerializer($format);
		}
		return $serializer->serializeNode($this);
	}
	
	public function addProp($prop, $value, $valueAttributes = null) {
		if (!isset($this->props[$prop])) {
			$this->props[$prop] = array();
		}
		$this->props[$prop][] = !empty($valueAttributes) ? array('value' => $value, 'attrs' => $valueAttributes) : $value;
		return $this;
	}
	
	public function addLink($link, $value) {
		if (!isset($this->links[$link])) {
			$this->links[$link] = array();
		}
		$this->links[$link][] = $value;
		return $this;
	}
	
	public function getProps($id) {
		return isset($this->props[$id]) ? $this->props[$id] : array();
	}
	
	public function getLinks($id) {
		return isset($this->links[$id]) ? $this->links[$id] : array();
	}
	
	public function getProp($id) {
		$props = $this->getProps($id);
		return empty($props) ? null : $props[0];
	}
	
	public function getPropValue($id) {
		$prop = $this->getProp($id);
		return is_array($prop) ? $prop['value'] : $prop;
	}
	
	public function getLink($id) {
		$links = $this->getLinks($id);
		return empty($links) ? null : $links[0];
	}
	
	
}
