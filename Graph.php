<?php

namespace phld;

use \phld\PhLD as PhLD;
use \phld\Node as Node;

/**
 * Graph object.
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class Graph {
	
	protected $namespaces = array();
	public $id;
	public $nodes = array();

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
	
	public function importTriples($triples) {
		foreach ($triples as $triple) {
			if ($triple['o']['type'] == 'literal') {
				$attrs = array();
				if (!empty($triple['o']['datatype'])) $attrs['datatype'] = $triple['o']['datatype'];
				if (!empty($triple['o']['xml:lang'])) $attrs['lang'] = $triple['o']['xml:lang'];
				if (!empty($triple['o']['lang'])) $attrs['lang'] = $triple['o']['lang'];
				$this->node($triple['s']['value'])->addProp($triple['p']['value'], $triple['o']['value'], $attrs);
			}
			else {
				$this->node($triple['s']['value'])->addLink($triple['p']['value'], $triple['o']['value']);
			}
		}
		return $this;
	}

	public function node($id) {
		if (!isset($this->nodes[$id])) {
			$this->nodes[$id] = new Node($id);
		}
		return $this->nodes[$id];
	}
	
	public function serialize($format, $serializer = null) {
		if (!$serializer) {
			$serializer = PhLD::getSerializer($format);
		}
		return $serializer->serializeGraph($this);
	}
	
}
