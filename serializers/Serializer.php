<?php

namespace phld\serializers;

use \phld\PhLD as PhLD;

/**
 * Serializer.
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class Serializer {
	
	protected $namespaces = array();

	public function serializeGraph($graph) {
		$result = '';
		foreach ($graph->nodes as $id => $node) {
			$result .= $this->serializeNode($node); 
		}
		return $this->getGraphHeader() . $result . $this->getGraphFooter();
	}
		
	public function serializeNode($node) {
		$result = '';
   		$result .= $this->serializeProps($node->id, $node->props);
		$result .= $this->serializeLinks($node->id, $node->links);
		return $this->getNodeHeader() . $result . $this->getNodeFooter();
	}
	
	public function getGraphHeader() {
	}

	public function getGraphFooter() {
	}

	public function getNodeHeader() {
	}

	public function getNodeFooter() {
	}

	public function serializeProps($nodeId, $props) {
		$result = '';
		foreach ($props as $prop => $values) {
			$result .= $this->serializeProp($nodeId, $prop, $values);
		}
		return $result;
	}
	
	public function serializeProp($nodeId, $prop, $values) {
		$result = '';
		foreach ($values as $value) {
			$result .= $this->serializePropValue($nodeId, $prop, $value);
		}
		return $result;
	}
	
	public function serializeLinks($nodeId, $links) {
		$result = '';
		foreach ($links as $link => $values) {
			$result .= $this->serializeLink($nodeId, $link, $values);
		}
		return $result;
	}
	
	public function serializeLink($nodeId, $link, $values) {
		$result = '';
		foreach ($values as $value) {
			$result .= $this->serializeLinkValue($nodeId, $link, $value);
		}
		return $result;
	}
	
	public function serializePropValue($nodeId, $prop, $value) {
		if (is_array($value)) {
			return $this->serializeStructuredPropValue($nodeId, $prop, $value);
		}
		return $this->serializeFlatPropValue($nodeId, $prop, $value)  ;
	}
	
	public function serializeStructuredPropValue($nodeId, $prop, $value) {
		return $this->getValueTerm($value['value'], $value['attrs']);
	}
	
	public function serializeFlatPropValue($nodeId, $prop, $value) {
		return $this->getValueTerm($value);
	}
	
	public function getValueTerm($value, $attrs = null) {
		return "$nodeId	$link	$value";
	}
	
	public function serializeLinkValue($nodeId, $link, $value) {
		return $this->getValueTerm($value);
	}
	
	
}
