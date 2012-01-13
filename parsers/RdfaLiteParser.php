<?php

namespace phld\parsers;

use \phld\PhLD as PhLD;
use \phld\parsers\HtmlParser as HtmlParser;

/**
 * Rdfa Lite Parser.
 * 
 * @package PhLD
 * @author Benjamin Nowack <mail@bnowack.de> 
 */
class RdfaLiteParser {
	
	public function parse($url, $callback = null) {
		echo $url;
		PhLD::getParser('html')
			->parse($url, array($this, "processEvent"));
		
		return;
		PhLD::getParser();
		
		
	}
	
	public function processEvent($event) {
		print_r($event);
	}
	
}
