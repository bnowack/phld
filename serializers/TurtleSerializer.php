<?php

namespace phld\serializers;

use \phld\PhLD as PhLD;

/**
 * Turtle Serializer.
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class TurtleSerializer extends Serializer {
	
	public function serializeNode($node) {
		$result = '';
		
		$nodeTerm = $this->getResourceTerm($node->id);
		$pad = str_pad('', strlen($nodeTerm), ' ', STR_PAD_LEFT);
		
		$propsResult = $this->serializeProps($node, $pad);
		$linksResult = $this->serializeLinks($node, $pad);
		$connector = ($propsResult && $linksResult) ? ';' : ($propsResult ? '.' : '');
		$result .= "$nodeTerm " .
			trim($propsResult) .
			($connector ? " $connector\n$linksResult" : trim($linksResult)) .
			($linksResult ? " ." : '');
		
		return $this->getNodeHeader() . $result . $this->getNodeFooter();
	}
	
	public function getNodeFooter() {
		return "\n\n";
	}
	
	public function serializeProps($node, $pad) {
		$result = '';
		$serializer = $this;
		$node->each('props', function($label, $values) use (&$result, $serializer, $node, $pad) {
			$result .= ($result ? " ;\n" : '') . "$pad ";
			$result .= $serializer->serializeProp($node, $label, $values, $pad);
		});
		return $result;
	}
	
	public function serializeProp($node, $label, $values, $pad) {
		$result = '';
		$term = $this->getResourceTerm($label);
		$pad .= str_pad('', strlen($term), ' ', STR_PAD_LEFT);
		foreach ($values as $value) {
			$result .= ($result ? " ,\n" : '') . "$pad ";
			$result .= $this->serializePropValue($node, $label, $value);
		}
		return "$term " . trim($result);
	}
	
	public function serializeLinks($node, $pad) {
		$result = '';
		$serializer = $this;
		$node->each('links', function($label, $values) use (&$result, $serializer, $node, $pad) {
			$result .= ($result ? " ;\n" : '') . "$pad ";
			$result .= $serializer->serializeLink($node, $label, $values, $pad);
		});
		return $result;
	}
	
	public function serializeLink($node, $label, $values, $pad) {
		$result = '';
		$term = $this->getResourceTerm($label);
		$pad .= str_pad('', strlen($term), ' ', STR_PAD_LEFT);
		foreach ($values as $value) {
			$result .= ($result ? " ,\n" : '') . "$pad ";
			$result .= $this->serializeLinkValue($node, $label, $value);
		}
		return "$term " . trim($result);
	}
	

	public function getValueTerm($node, $valueType, $label, $value) {
		if ($valueType == 'link') {
			return $this->getResourceTerm($value['value']);
		}
		else {// prop
			return $this->getLiteralTerm($value['value'], $value['attrs']);
		}
	}
	
	public function getResourceTerm($id) {
		// bnode
		if (substr($id, 0, 2) == '_:') return $id;
		// uri
		return "<$id>";
	}
	
	public function getLiteralTerm($value, $attrs = array()) {
		// detect the quotation marks
		$qm = '"';
		if (preg_match('/[^\x92]\"/', $value)) {
			$qm = "'";
			if (preg_match('/[\x0d\x0a]/', $value) || preg_match('/[^\\]\'/', $value)) {
				$qm = '"""';
				$value = str_replace('"""', '\\"\\"\\"', $value);
			}
		}
		if (strlen($qm) == 1 && preg_match('/[\x0d\x0a]/', $value)) {
			$qm = "$qm$qm$qm";
		}

		$suffix = '';
		if (!empty($attrs['lang'])) {
			$suffix = "@{$attrs['lang']}";
		}
		if (!empty($attrs['datatype'])) {
			$suffix = "^^{$attrs['datatype']}";
		}
		return "{$qm}{$value}{$qm}{$suffix}";
	}
	
	
	
}
