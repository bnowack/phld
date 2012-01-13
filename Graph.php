<?php

namespace phld;

use \phld\PhLD as PhLD;
use \phld\Node as Node;
use \phweb\Exception as Exception;

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
		if (!isset($this->$property)) return;
		foreach ($this->$property as $k => $v) {
			$callback($k, $v);
		}
		return $this;
	}
	
	/**
	 * Returns or created a Node object.
	 * 
	 * @param string $id
	 * @return Node 
	 */
	public function node($id) {
		if (!isset($this->nodes[$id])) {
			$this->nodes[$id] = new Node($id);
		}
		return $this->nodes[$id];
	}
	
	/**
	 * Serialises the graph in the specified format (e.g. "turtle")
	 * 
	 * @param string $format
	 * @param Serializer $serializer
	 * @return string
	 */
	public function serialize($format, $serializer = null) {
		if (!$serializer) {
			$serializer = PhLD::getSerializer($format);
		}
		return $serializer->serializeGraph($this);
	}
	
	/**
	 * Imports an array of RDF triples into the graph.
	 * 
	 * @param array $triples
	 * @return Graph 
	 */
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

	/**
	 * Imports $data of format $format (e.g. "turtle") into the graph.
	 * 
	 * @param string $data
	 * @param string $format
	 * @return Graph 
	 */
	public function importData($data, $format) {
		$graph = $this;
		try {
			PhLD::getParser($format)->parseData($data, function($triple) use ($graph) {
				$graph->importTriples(array($triple));
			});
		}
		catch (Exception $e) { $e->handleException(); }
		return $this;
	}
	
}
