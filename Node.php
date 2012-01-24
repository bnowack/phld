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
	
	public $id;
	public $namespaces;
	public $elements;// props and links

	public function __construct($id) {
		$this->id = $id;
		$this->namespaces = array();
		$this->elements = array('props' => array(), 'links' => array());
	}

	/**
	 * Static constructer for fluent instantiation
	 * E.g.: Bucket::init($bucketId)->clearGraph($uri);
	 * 
	 * @param string $bucketId
	 * @return Bucket
	 */
	public static function init($id) {
		$className = get_class();
		return new $className($id);
	}
	
	/**
	 * Iterates over an instance property with callback support.
	 * 
	 * @param string $property
	 * @param function $callback
	 * @return Graph 
	 */
	public function each($property, $callback) {
		if (isset($this->$property)) {
			$items = $this->$property;
		}
		else if (isset($this->elements[$property])) {
			$items = $this->elements[$property];
		}
		else {
			return;
		}
		foreach ($items as $k => $v) {
			call_user_func($callback, $k, $v);
		}
	}
	
	/**
	 * Loads a serialiser for the given format (if not provided already) and serialises the node.
	 * 
	 * @param string $format format identifier, e.g. "turtle"
	 * @param Serializer $serializer
	 * @return string 
	 */
	public function serialize($format, $serializer = null) {
		if (!$serializer) {
			$serializer = PhLD::getSerializer($format);
		}
		return $serializer->serializeNode($this);
	}
	
	/**
	 * Adds a propery element to the node.
	 * 
	 * @param string $label
	 * @param string $value
	 * @param array $attrs property arributes
	 * @return Node 
	 */
	public function addProp($label, $value, $attrs = array()) {
		return $this->addElement('props', $label, $value, $attrs);
	}
	
	/**
	 * Adds a link element to the node.
	 * 
	 * @param string $label
	 * @param string $value
	 * @param array $attrs link arributes
	 * @return Node 
	 */
	public function addLink($label, $value, $attrs = array()) {
		return $this->addElement('links', $label, $value, $attrs);
	}
	
	/**
	 * Returns a list of properties matching $label.
	 * 
	 * @param string $label
	 * @return array elements
	 */
	public function getProps($label) {
		return $this->getElements('props', $label);
	}
	
	/**
	 * Returns a list of links matching $label.
	 * 
	 * @param string $label
	 * @return array elements
	 */
	public function getLinks($label) {
		return $this->getElements('links', $label);
	}
	
	/**
	 * Adds an element (prop or link) to the node
	 * @param string $key "prop" or "link"
	 * @param string $label
	 * @param string $value
	 * @param array $attrs
	 * @return Node 
	 */
	protected function addElement($key, $label, $value, $attrs = array()) {
		if (!isset($this->elements[$key][$label])) {
			$this->elements[$key][$label] = array();
		}
		$this->elements[$key][$label][] = array('value' => $value, 'attrs' => $attrs);
		return $this;
	}
	
	/**
	 * Returns a list of elements matching $key ("props" or "links") and $label. 
	 * 
	 * @param string $key
	 * @param string $label
	 * @return array elements
	 */
	protected function getElements($key, $label) {
		return empty($this->elements[$key][$id]) ? array() : $this->elements[$key][$id];
	}
	
	/**
	 * Returns a single property with the given $label, or null.
	 * 
	 * @param string $label
	 * @return array
	 */
	public function getProp($label) {
		return $this->getElement('props', $label);
	}
	
	/**
	 * Returns a single link with the given $label, or null.
	 * 
	 * @param string $label
	 * @return array
	 */
	public function getLink($label) {
		return $this->getElement('links', $label);
	}
	
	/**
	 * Returns a single $key element with the given $label, or null.
	 * 
	 * @param string $key "prop" or "link"
	 * @param string $label
	 * @return array
	 */
	protected function getElement($key, $label) {
		$els = $this->getElements($key, $label);
		return empty($els) ? null : $els[0];
	}
	
	/**
	 * Returns the value of a single property.
	 * 
	 * @param string $label
	 * @return string 
	 */
	public function getPropValue($label) {
		return $this->getElementValue('props', $label);
	}
	
	/**
	 * Returns the value of a single link.
	 * 
	 * @param string $label
	 * @return string 
	 */
	public function getLinkValue($label) {
		return $this->getElementValue('links', $label);
	}
	
	protected function getElementValue($key, $label) {
		$el = $this->getElement($key, $label);
		return $el ? $el['value'] : null;
	}
	
}
