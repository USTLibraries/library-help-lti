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

		// 201740LIBX201-01 in the searchable ids
		if ($index === $r++ ) {
			if ($course['year'] && $course['termcode'] && $course['dept'] && $course['num'] && $course['section']) {
				$c = $course['id'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}

		// 2017 40 LIBX201 01 searching year, term, dept and num, and section as separate fields
		if ($index === $r++ ) {
			if ($course['year'] && $course['termcode'] && $course['dept'] && $course['num'] && $course['section']) {
				$c = $course['dept'] . $course['num']." AND section~".$course['section']." AND year~".$course['year']." AND term~".$course['termdesc'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}


		// LIBX201-01
		/* // removed by clkluck 20180814 - with course loader we have less worry about this

		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] && $course['section'] ) {
				$c = $course['dept'].$course['num']." AND section~".$course['section'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}
		*/

		// LIBX201-01 and LIBX201-LAMBERT
		/* // removed by clkluck 20180814 - with course loader we have less worry about this

		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] && $course['section']) {
				$c = $course['dept'].$course['num']."-".$course['section'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}
		*/

		// LIBX201-LAMBERT (instructor taken from instructor param)
		/* // removed by clkluck 20180814 - with course loader we have less worry about this

		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] && $course['instructor']) {
				$c = $course['dept'].$course['num']."-".$course['instructor'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}
		*/

		// LIBX201
		/* // removed by clkluck 20180814 - with course loader we have less worry about this
		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] ) {
				$c = $course['dept'].$course['num'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}
		*/

		// LIBX-LAMBERT (course param override, instructor taken from section)
		/* // removed by clkluck 20180814 - with course loader we have less worry about this
		if ($index === $r++ ) {
			if ( $course['dept'] && preg_match("/^[A-Z]{2,}$/", $course['section'])) {
				$c = $course['dept']."-".$course['section'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}
		*/

		/* // removed by clkluck 20180814 - with course loader we have less worry about this
		// LIBX-LAMBERT (instructor taken from instructor param)
		if ($index === $r++ ) {
			if ( $course['dept'] && $course['instructor']) {
				$c = $course['dept']."-".$course['instructor'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}
		*/

		/* // removed by clkluck 20180814 - with course loader we have less worry about this
		// LIBX
		if ($index === $r++ ) {
			if ( $course['dept'] ) {
				$c = $course['dept'];
				$q = "searchable_ids~".$c;
			} else {
				$index++; // move on to next rule
			}
		}
		*/

		// SOME_UNCONVENTIONAL_ID in the code (like TEST_COURSE_100)
		if ($index === $r++ ) {
			if ( $course['id'] ) {
				$c = $course['id'];
				$q = "code~".strtolower($c);
			} else {
				$index++;
			}
		}

		// SOME_UNCONVENTIONAL_ID in the searchable ids (like TEST_COURSE_100)
		if ($index === $r++ ) {
			if ( $course['id'] ) {
				$c = $course['id'];
				$q = "searchable_ids~".$c;
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