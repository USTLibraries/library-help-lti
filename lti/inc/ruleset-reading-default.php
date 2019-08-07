<?php
/*
CAUTION! BEFORE MODIFYING RULESETS IT IS RECOMMENDED YOU HAVE A FIRM UNDERSTANDING
OF HOW THE LOGIC OF THESE RULESETS WORK!

ALSO REMEMBER TO BACK UP AND VERSION YOUR CHANGES!
DO NOT CHANGE THE ruleset-reading-default.php FILE
SAVE ALL CUSTOM RULESETS IN THE custom/rulesets/ DIRECTORY

AGAIN! BACK UP AND VERSION YOUR CHANGES
AND AFTER YOU CREATE A NEW VERSION ID, BE SURE TO UPDATE THE config.ini.php FILE!

*/

class ReadingListRuleSet {

	function __construct() {

	}

	public function apply($index, $course) {

		$c = "";
		$q = array();
		$r = 0; // we use $r to auto number the rules - note that ($index === $r++) Returns $r, THEN increments $r by one.
				// http://php.net/manual/en/language.operators.increment.php

		/* RULESET EXAMPLE:

		Only modify the inner if(), the $c =, and the $q =

		// LIBX201-01
		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] && $course['section'] ) {
				$c = $course['dept'].$course['num']."-".$course['section'];
				$q['q'] = "searchable_ids~".$c;
			} else {
				$index++;
			}
		}

		*/

		// EDIT BELOW THIS LINE
		// *********************************************************


	// 201740LIBX201-01 in the code
	if ($index === $r++ ) {
		if ($course['year'] && $course['termcode'] && $course['dept'] && $course['num'] && $course['section']) {
			$c = $course['id'];
			$q = "code~".$c;
		} else {
			$index++; // move on to next rule
		}
	}


	// 201740LIBX201-01
	if ($index === $r++ ) {
		if ($course['year'] && $course['termcode'] && $course['dept'] && $course['num'] && $course['section']) {
			$c = $course['id'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// 201740LIBX201-01
	if ($index === $r++ ) {
		if ($course['year'] && $course['termcode'] && $course['dept'] && $course['num'] && $course['section']) {
			$c = $course['dept'] . $course['num']."%20AND+section~".$course['section']."+AND%20year~".$course['year']."%20AND%20term~".$course['termdesc'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}


	// LIBX201-01
	if ($index === $r++ ) {
		if ( $course['dept'] && $course['num'] && $course['section'] ) {
			$c = $course['dept'].$course['num']."%20AND%20section~".$course['section'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// LIBX201-01 and LIBX201-LAMBERT
	if ($index === $r++ ) {
		if ( $course['dept'] && $course['num'] && $course['section']) {
			$c = $course['dept'].$course['num']."-".$course['section'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// LIBX201-LAMBERT (instructor taken from instructor param)
	if ($index === $r++ ) {
		if ( $course['dept'] && $course['num'] && $course['instructor']) {
			$c = $course['dept'].$course['num']."-".$course['instructor'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// LIBX201
	if ($index === $r++ ) {
		if ( $course['dept'] && $course['num'] ) {
			$c = $course['dept'].$course['num'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// LIBX-LAMBERT (course param override, instructor taken from section)
	if ($index === $r++ ) {
		if ( $course['dept'] && preg_match("/^[A-Z]{2,}$/", $course['section'])) {
			$c = $course['dept']."-".$course['section'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// LIBX-LAMBERT (instructor taken from instructor param)
	if ($index === $r++ ) {
		if ( $course['dept'] && $course['instructor']) {
			$c = $course['dept']."-".$course['instructor'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// LIBX
	if ($index === $r++ ) {
		if ( $course['dept'] ) {
			$c = $course['dept'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++; // move on to next rule
		}
	}

	// LIBRARY_SANDBOX_COMMUNITY (last ditch, just take the raw id)
	if ($index === $r++ ) {
		if ( $course['id'] ) {
			$c = $course['id'];
			$q['q'] = "code~".strtolower($c);
		} else {
			$index++;
		}
	}

	// LIBRARY_SANDBOX_COMMUNITY (last ditch, just take the raw id)
	if ($index === $r++ ) {
		if ( $course['id'] ) {
			$c = $course['id'];
			$q['q'] = "searchable_ids~".$c;
		} else {
			$index++;
		}
	}

		// DO NOT EDIT BELOW THIS LINE
		// *********************************************************

		if ($index === $r++) { $index = -1;	} // found nothing

		return ( array("q" => ["q" => $q], "c" => $c, "index" => $index) );
	}
}

?>