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
		$result .= $this->serializeElements($node);
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
	
	public function serializeElements($node) {
		return $this->serializeProps($node) . $this->serializeLinks($node);
	}

	public function serializeProps($node) {
		$result = '';
		$serializer = $this;
		$node->each('props', function($label, $values) use (&$result, $serializer, $node) {
			$result .= $serializer->serializeProp($node, $label, $values);
		});
		return $result;
	}
	
	public function serializeLinks($node) {
		$result = '';
		$serializer = $this;
		$node->each('links', function($label, $values) use (&$result, $serializer, $node) {
			$result .= $serializer->serializeLink($node, $label, $values);
		});
		return $result;
	}
	
	public function serializeProp($node, $label, $values) {
		$result = '';
		foreach ($values as $value) {
			$result .= $this->serializePropValue($node, $label, $value);
		}
		return $result;
	}
	
	public function serializeLink($node, $label, $values) {
		$result = '';
		foreach ($values as $value) {
			$result .= $this->serializeLinkValue($node, $label, $value);
		}
		return $result;
	}
	
	public function serializePropValue($node, $label, $value) {
		return $this->getValueTerm($node, 'prop', $label, $value);
	}
		
	public function serializeLinkValue($node, $label, $value) {
		return $this->getValueTerm($node, 'link', $label, $value);
	}
		
	public function getValueTerm($node, $valueType, $label, $value) {
		return "{$valueType}\t{$node->id}\t{$prop}\t{$value['value']}\t" . json_encode($value['attrs']);
	}
	
}
