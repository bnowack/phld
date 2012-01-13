<?php

namespace phld\parsers;

use \phweb\PhWeb as PhWeb;
use \phld\PhLD as PhLD;
use \phweb\Exception as Exception;

/**
 * Turtle Parser.
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class TurtleParser {
	
	public $buffers = array();
	
	public function parseData($data, $callback = null) {
		$this->extractTriples($data, $callback);
	}
	
	public function extractTriples($data, $callback = null) {
		$bufferId = md5($data);
		$this->buffers[$bufferId] = $data;
		while ($subject = $this->extractSubject($bufferId)) {
			while ($predicate = $this->extractPredicate($bufferId)) {
				while ($object = $this->extractObject($bufferId)) {
					$triple = array('s' => $subject, 'p' => $predicate, 'o' => $object);
					if ($callback) {
						if (is_array($callback)) {
							$object = $callback[0];
							$method = $callback[1];
							$object->$method($triple, $this, $bufferId);
						}
						else {
							$callback($triple, $this);
						}
					}
				}
			}
		}
		return false;
	}
	
	public function extractSubject($bufferId) {
		$this->extractCharacter($bufferId, '.');
		return $this->extractResourceTerm($bufferId);
	}
	
	public function extractPredicate($bufferId) {
		$this->extractCharacter($bufferId, ';');
		return $this->extractUri($bufferId);
	}
	
	public function extractObject($bufferId) {
		$this->extractCharacter($bufferId, ',');
		$term = $this->extractResourceTerm($bufferId);
		if (!$term) $term = $this->extractLiteral($bufferId);
		return $term;
	}
	
	public function extractComments($bufferId) {
		$data = $this->buffers[$bufferId];
	    while (preg_match('/^\s*(\#[^\xd\xa]*)(.*)$/si', $data, $m)) {
			$data = $m[2];
		}
		$this->buffers[$bufferId] = $data;
	}
	
	public function extractCharacter($bufferId, $char) {
		$this->extractComments($bufferId);
		$data = $this->buffers[$bufferId];
	    if (preg_match('/^\s*' . preg_quote($char, '/') . '(.*)$/si', $data, $m)) {
			$data = $m[1];
		}
		$this->buffers[$bufferId] = $data;
	}
	
	public function extractResourceTerm($bufferId) {
		$term = $this->extractBlankNode($bufferId);
		if (!$term) $term = $this->extractUri($bufferId);
		return $term;
	}
	
	public function extractBlankNode($bufferId) {
		$this->extractComments($bufferId);
		$data = ltrim($this->buffers[$bufferId]);
		// _:name
		if (substr($data, 0, 2) == '_:') {
			$rest = strpbrk("$data ", "\r\n\t ");
			$value = trim(substr("$data ", 2, -strlen($rest)));
			$this->buffers[$bufferId] = $rest;
			return array('type' => 'bnode', 'value' => $value);
		}
		// []
		
		return false;
	}
	
	public function extractUri($bufferId) {
		$this->extractComments($bufferId);
		$data = ltrim($this->buffers[$bufferId]);
		// <uri>
		if (substr($data, 0, 1) == '<') {
			$rest = substr(strpbrk("$data ", ">"), 1);
			$value = substr("$data ", 1, -strlen($rest) - 1);
			$this->buffers[$bufferId] = $rest;
			return array('type' => 'uri', 'value' => $value);
		}
		// prefix:name
		
		return false;
	}
		
	public function extractLiteral($bufferId) {
		$this->extractComments($bufferId);
		$data = ltrim($this->buffers[$bufferId]);
		$qms = array('"""', '"', "'");
		foreach ($qms as $qm) {
			if (strpos($data, $qm) !== 0) continue;
			$start = strlen($qm);
			$offset = $start;
			do {
				$end = strpos($data, $qm, $offset);
				if ($end === false) {
					throw new Exception('Unterminated literal');
				}
				$endFound = true; 
				// excaped qm
				if (substr($data, $end - 1, 1) == '\\') {
					$endFound = false;
					$offset = $end + 1;
					if ($offset >= strlen($data)) {
						throw new Exception('Unterminated literal');
					}
				}
			} while (!$endFound);
			$rest = substr($data, $end + strlen($qm));
			$value = substr($data, $start, $end - strlen($qm));
			$this->buffers[$bufferId] = $rest;
			// datatype / language
			
			return array('type' => 'literal', 'value' => $value);
		}
		return false;
	}
		
	
}
