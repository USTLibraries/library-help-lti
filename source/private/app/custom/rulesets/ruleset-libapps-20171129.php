<?php
/*
CAUTION! BEFORE MODIFYING RULESETS IT IS RECOMMENDED YOU HAVE A FIRM UNDERSTANDING
OF HOW THE LOGIC OF THESE RULESETS WORK!

ALSO REMEMBER TO BACK UP AND VERSION YOUR CHANGES!
DO NOT CHANGE THE ruleset-libapps-default.php FILE
SAVE ALL CUSTOM RULESETS IN THE custom/rulesets/ DIRECTORY

AGAIN! BACK UP AND VERSION YOUR CHANGES
AND AFTER YOU CREATE A NEW VERSION ID, BE SURE TO UPDATE THE config.ini.php FILE!

*/

class GuideRuleSet {

	function __construct() {

	}

	public function apply($index, $course) {

		$c = "";
		$q = array();
		$r = 0; // we use $r to auto number the rules - note that ($index === $r++) Returns $r, THEN increments $r by one.

		/* RULESET EXAMPLE:

		Only modify the inner if() and the $c =

		// LIBX201-01
		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] && $course['section'] ) {
				$c = $course['dept'].$course['num']."-".$course['section'];
			} else {
				$index++;
			}
		}

		*/

		// EDIT BELOW THIS LINE
		// *********************************************************

		/* we will comment this one out until there comes a time where we find a libguide tagged with a full course ID */
		// 201740LIBX201-01
		/*
		if($index === $r++ ) {
			if ($course['termcode'] && $course['dept'] && $course['num'] && $course['section']) {
				$c = $course['termcode'] . $course['dept'] . $course['num'] ."-".$course['section'];
			} else {
				$index++;
			}
		}
		*/
		/**/

		/* we will comment this one out until there comes a time where we find a libguide tagged with a full course ID */
		// 201740LIBX201-LAMBERT
		/*
		if($index === $r++ ) {
			if ($course['termcode'] && $course['dept'] && $course['num'] && $course['instructor']) {
				$c = $course['termcode'] . $course['dept'] . $course['num'] ."-".$course['instructor'];
			} else {
				$index++;
			}
		}
		*/
		/**/

		// LIBX201-01 or LIBX201-LAMBERT (course param override, instructor taken from section)
		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] && $course['section'] ) {
				$c = $course['dept'].$course['num']."-".$course['section'];
			} else {
				$index++;
			}
		}

		// LIBX201-LAMBERT (instructor taken from instructor param)
		if ($index === $r++ ) {
			if ( $course['dept'] && $course['num'] && $course['instructor'] ) {
				$c = $course['dept'].$course['num']."-".$course['instructor'];
			} else {
				$index++;
			}
		}

        // LIBX201-TAG (Tag taken from override)
        if ($index === $r++ ) {
            if ( $course['dept'] && $course['num'] && isset($course['tag']) && $course['tag'] !== ""  ) {
                $c = $course['dept'].$course['num']."-".$course['tag'];
            } else {
                $index++;
            }
        }
        
		// LIBX201
		if ($index === $r++ ) {
			if ($course['dept'] && $course['num']) {
				$c = $course['dept'].$course['num'];
			} else {
				$index++;
			}
		}

		// LIBX-LAMBERT (course param override, instructor taken from section)
		if ($index === $r++ ) {
			if ( $course['dept'] && preg_match("/^[A-Z]{2,}$/", $course['section'])) {
				$c = $course['dept']."-".$course['section'];
			} else {
				$index++;
			}
		}

		// LIBX-LAMBERT (instructor taken from instructor param)
		if ($index === $r++ ) {
			if ($course['dept'] && $course['instructor']) {
				$c = $course['dept']."-".$course['instructor'];
			} else {
				$index++;
			}
		}

		// SOME_UNCONVENTIONAL_ID in the code (like TEST_COURSE_100)
		if ($index === $r++ ) {
			if ( $course['id'] ) {
				$c = $course['id'];
			} else {
				$index++;
			}
		}

		if ($index === $r++ ) {
			if ( $course['dept'] ) {
				$c = $course['dept'];
			} else {
				$index++;
			}
		}

		// DO NOT EDIT BELOW THIS LINE
		// *********************************************************

		if ($index === $r++) { $index = -1;	} // found nothing

		return ( array("q" => ["tag_names" => $c], "c" => $c, "index" => $index ) );
	}
}

?>