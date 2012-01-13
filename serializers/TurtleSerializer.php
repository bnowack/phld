<?php

namespace phld\serializers;

use \phld\PhLD as PhLD;

/**
 * Serializer.
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class TurtleSerializer extends Serializer {
	
	public function serializeNode($node) {
		$result = '';
		
		$nodeTerm = $this->getResourceTerm($node->id);
		$pad = str_pad('', strlen($nodeTerm), ' ', STR_PAD_LEFT);
		
		$propsResult = $this->serializeProps($node->id, $node->props, $pad);
		$linksResult = $this->serializeLinks($node->id, $node->links, $pad);
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
	
	public function serializeProps($nodeId, $props, $pad) {
		$result = '';
		foreach ($props as $prop => $values) {
			$result .= ($result ? " ;\n" : '') . "$pad ";
			$result .= $this->serializeProp($nodeId, $prop, $values, $pad);
		}
		return $result;
	}
	
	public function serializeProp($nodeId, $prop, $values, $pad) {
		$result = '';
		$propTerm = $this->getResourceTerm($prop);
		$pad .= str_pad('', strlen($propTerm), ' ', STR_PAD_LEFT);
		
		foreach ($values as $value) {
			$result .= ($result ? " ,\n" : '') . "$pad ";
			$result .= $this->serializePropValue($nodeId, $prop, $value);
		}
		$result = trim($result);
		return "$propTerm $result";
	}
	
	public function serializeLinks($nodeId, $links, $pad) {
		$result = '';
		foreach ($links as $link => $values) {
			$result .= ($result ? " ;\n" : '') . "$pad ";
			$result .= $this->serializeLink($nodeId, $link, $values, $pad);
		}
		return $result;
	}
	
	public function serializeLink($nodeId, $link, $values, $pad) {
		$result = '';
		$linkTerm = $this->getResourceTerm($link);
		$pad .= str_pad('', strlen($linkTerm), ' ', STR_PAD_LEFT);
		
		foreach ($values as $value) {
			$result .= ($result ? " ,\n" : '') . "$pad ";
			$result .= $this->serializeLinkValue($nodeId, $link, $value);
		}
		$result = trim($result);
		return "$linkTerm $result";
	}
	
	public function serializeLinkValue($nodeId, $link, $value) {
		return $this->getResourceTerm($value);
	}

	public function getResourceTerm($id) {
		// bnode
		if (substr($id, 0, 2) == '_:') return $id;
		// uri
		return "<$id>";
	}
	
	public function getValueTerm($value, $attrs = null) {
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
		if ($attrs && !empty($attrs['lang'])) {
			$suffix = "@{$attrs['lang']}";
		}
		if ($attrs && !empty($attrs['datatype'])) {
			$suffix = "^^{$attrs['datatype']}";
		}
		return "{$qm}{$value}{$qm}{$suffix}";
	}
	
	
	
}
