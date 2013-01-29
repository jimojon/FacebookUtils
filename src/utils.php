<?php

function print_a($a){
	echo '<pre>'.print_r($a, true).'</pre>';
}

class Utils {

	static function printArray($a){
		echo '<pre>'.print_r($a, true).'</pre>';
	}

	static function formatBoolean($b){
		return $b ? 'true' : 'false';
	}
}

