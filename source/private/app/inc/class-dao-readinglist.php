<?php

// this class uses some functions from the php-project-framework library
require_once getPathIncLib()."php-project-framework/functions.php";
// however, it does not use any other functions or variables shared with the lti application
// this class can be used as stand alone with everything necessary to run passed to it in the constructor

class ReadingListData {
	
	// passed data
	private $course = array();
	private $domain = "";
	private $apikey = "";
	private $alma = array();
	private $ruleset = null;
	private $timeZone = "";
	
	// return data
	private $queryInfo = [ "search" => "", "matched" => "", "rule" => "" ];
	private $stats = ["exec_ms" => 0, "api_requests" => 0, "citations_total" => 0, "citations_visible" => 0, "citations_hidden" => 0, "summary" => ""];
	private $courseInfo = array();
	private $readinglist = array();
	
	// process data
	private $preCount = 0;
	private $postCount = 0;
	private $bibList = array();
	
/*  ============================================================================================
    ********************************************************************************************
    CONSTRUCTOR 
	******************************************************************************************** 
*/
	
	function __construct($courseData, $domain, $apikey, $alma, $ruleset, $timeZone = "America/Chicago") {
	
		$this->course = $courseData;
		$this->domain = $domain;
		$this->apikey = $apikey;
		$this->alma = $alma;
		$this->ruleset = $ruleset;
		$this->timezone = $timeZone;
	
	}
	 
	
/*  ============================================================================================
    ********************************************************************************************
    PUBLIC 
	******************************************************************************************** 
*/
	
	/** ****************************************************************************************
	 *  init()
	 *
	 *	Initializes the object by triggering all the api requests necessary to fill in the 
	 *  data. Must be called after constructing otherwise the object is empty.
	 *
	 *  @see __construct()
	 *  @access public
	 *  @param none
	 *  @return type Description 
	 */
	public function init() {
		
		// set the info
		$this->queryInfo["search"] = $this->course['id'];
		$start = round(microtime(true) * 1000);
		
		// we do this in two steps, first find a readinglist and then format it
		$this->readinglist = $this->apiAlmaFormatReadingList($this->findAlmaReadingList($this->course));
		
		// set execution time
		$this->stats["exec_ms"] = round(microtime(true) * 1000) - $start;
		
		// calculate stats
		$this->calcStats();
	
	}
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getAll() {
		return ["info" => $this->getQueryInfo(), "stats" => $this->getStats(), "course" => $this->getCourseInfo(), "readinglist" => $this->getReadinglist() ];
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getQueryInfo() {
		return $this->queryInfo;
	}
	
	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getCourseInfo() {
		return $this->courseInfo;
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getReadinglist() {
		return $this->readinglist;
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getStats() {
		return $this->stats;
	}

	
	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access public
	 *  @param type $var Description
	 *  @return type Description 
	 */
	public function getAlmaCourseSearchString($course = array(), $index = 0) {

		$q = "";
		$c = "";

		if ( !isset($course['dept']) || $course['dept'] === "XXXX") {
			$index = -1;
		} else {

			// The ruleset_reading function is a customizable function
			// The default function is included in inc/ruleset-reading-default.php
			// To customize the ruleset create a copy into custom/rulesets/ruleset-reading-[version].php where [version] is your own verison ID
			$t = $this->ruleset->apply($index, $course);

			$q = $t['q'];
			$c = $t['c'];
			$index = $t['index'];
		}

		return [ "query" => $q, "code" => $c, "index" => $index ];	
	}
	
/*  ============================================================================================
    ********************************************************************************************
    PRIVATE 
	******************************************************************************************** 
*/

	/** ****************************************************************************************
	 *  ()
	 *
	 *
	 *
	 *  @see
	 *  @access private
	 *  @param type $var Description
	 *  @return type Description 
	 */
	private function calcStats() {
		
		$this->stats['citations_total'] = $this->preCount;
		$this->stats['citations_visible'] = $this->postCount;
		$this->stats['citations_hidden'] = $this->preCount - $this->postCount;
		
		$this->stats['summary'] = $this->stats['citations_visible'] . " of " . $this->stats['citations_total'] . " citations visible";
		
		$this->stats['citations_requiring_bibs'] = count($this->bibList);

	}

		



	/* *************************************************************
	 *  FIND AN ALMA READING LIST
	 *  
	 *  First we need to find the course, using a hierarchical search from the bottom up.
	 *  Then we need to find a corresponding readinglist
	 *  Finally we need to look up the citations
	 */
	private function findAlmaReadingList($course) {

		$almaCourse = array();
		$readingLists = array();
		$citations = array();
		$match = "";
		$rule = 0;
		$x = -1;

		// do a hierarchical search for course id, CMSR101-01 will resolve to CMSR101 if CMSR101-01 not found but CMSR101 is
		do {

			$v = array();
			$tempAlmaCourse = array();

			$x++;

			$v = $this->getAlmaCourseSearchString( $course, $x );

			if($v['index'] !== -1) {
				
				$tempAlmaCourse = $this->getAlmaData("/courses", $v['query']);

				logMsg("Course Reading List: Result For: ".json_encode($v['query'])." w/ rule ".$v['index'], $tempAlmaCourse);
			}

			// we found something
			if( isset($tempAlmaCourse['course'])) {

				$almaCourseList = array();

				$almaCourseList = $tempAlmaCourse['course'];

				logMsg("Course Reading List: Found one or more courses w/ rule: ".$v['index']);

				// iterate through the course list and their search ids and see if we have a match
				// for courses there really should only be one match. if there are multiple this needs
				// to be fixed on the Alma Course Reserve management side.
				// We aren't going to be making tough decisions here

				// level 1, iterate through each of the returned courses
				$cl_found = false;
				$cl_len = count($almaCourseList);
				for( $si = 0; $si < $cl_len && !$cl_found; $si++ ) {

					if($almaCourseList[$si]['status'] === "ACTIVE" ) { // if this course is active, proceed

						// if it matches the code directly, we're done
						$searchableID = strtoupper($almaCourseList[$si]['code']);
						logMsg("Checking if: code ".$searchableID." === code ".$v['code'] );
						if ( isset( $almaCourseList[$si]['code'] ) && $searchableID === $v['code'] ) {
							logMsg("Found exact match in Code: ". $v['code'], $almaCourseList[$si]);
							$almaCourse = $almaCourseList[$si];
							$cl_found = true;
						} else { // otherwise we move on to the searchable list
							$searchableIDlist = array();
							if( isset($almaCourseList[$si]['searchable_id']) ) {
								$searchableIDlist = $almaCourseList[$si]['searchable_id'];
							}

							// level 2, iterate through the searchable ids for the course
							$cl_sidLen = count($searchableIDlist);
							for( $sii = 0; $sii < $cl_sidLen && !$cl_found; $sii++ ) {

								$searchableID = strtoupper($searchableIDlist[$sii]);
								logMsg("Checking if: searchable_id ".$searchableID." === code ".$v['code'] );

								// check to see if we found it
								if( $searchableID === $v['code'] ) {
									logMsg("Found exact match in Searchable IDs: ". $v['code'], $almaCourseList[$si]);
									$almaCourse = $almaCourseList[$si];
									$cl_found = true;
								}
							}
						}
					}

				}

				// we checked all the courses returned and did not find an exact match
				if (!$cl_found) {
					logMsg("No exact match for searchable_id found");
				} else {
					$match = $v['code']; // save for later once we know there is a readinglist
					$rule = $v['index'];
				}
			}

			// if we came out with a course then we flag that we found one
			if ( count($almaCourse) > 0) {			
				$x = -1; // course was found
				logMsg("Found a course", $almaCourse);				
			} else {
				// do the loop again with the next rule
				$x = $v['index']; // note the rule we left off with
			}

		}  while ($x >= 0); // while there are still more rules

		// we have a course, now go on and find a readinglist
		if ( count($almaCourse) > 0 ) {
			

			$tempReadingLists = $this->getAlmaData("/courses/".$almaCourse['id']."/reading-lists");
			logMsg($tempReadingLists);

			if (isset($tempReadingLists['reading_list']['id'])) {
				// move to an indexed array
				$readingLists[0] = $tempReadingLists['reading_list'];
			} else {
				$readingLists = $tempReadingLists['reading_list'];
			}


			if ( count($readingLists) > 0 ) {
				$len = count($readingLists);

				for ($i=0; $i < $len; $i++) {
					$temp = $this->getAlmaData("/courses/".$almaCourse['id']."/reading-lists/".$readingLists[$i]['id']."/citations");
					logMsg($temp);

					if (isset( $temp['citation'][0])) {
						$llen = count($temp['citation']);
						for ( $ii=0; $ii < $llen; $ii++) {
							if (isset( $temp['citation'][$ii]['status']['value']) && $temp['citation'][$ii]['status']['value'] == "Complete" ) {
								$citations[] = $temp['citation'][$ii];
								logMsg($temp['citation'][$ii]);	
							}
						} 
					} else {
						if (isset( $temp['citation']['status']['value']) && $temp['citation']['status']['value'] == "Complete" ) {
							$citations[] = $temp['citation'];
							logMsg($temp['citation']);
						}
					}

				}

				logMsg("Citations Extracted", $citations);
			}

		}

		// we found a course that has a readinglist that has citations
		if( count($citations) > 0) {
			
			logMsg("Citations", $citations);
			
			$this->queryInfo['matched'] = $match;
			$this->queryInfo['rule'] = $rule;
			
			$this->courseInfo = [ 
				"id" => $almaCourse['id'],
				"code" => $almaCourse['code'],
				"name" => $almaCourse['name'],
				"instructor" => array()
			];

			foreach ( $almaCourse['instructor'] as $instr) {
				$this->courseInfo['instructor'][] = [
					"first_name" => $instr['first_name'], 
					"last_name" => $instr['last_name']
				];
			}			
			
		}
		
		return $citations;

	}


	/* *************************************************************
	 *  apiAlmaLoadBibs()
	 *
	 *  GET AN ALMA BIB FOR ITEM
	 *  
	 *  Sometimes the Citation seems incomplete, or is hard to parse out so we need more info.
	 *  
	 */
	private function apiAlmaLoadBibs() {

		if (count($this->bibList) > 0) {
			$bibKeys = array_keys($this->bibList); // place all the keys as values in an indexed array
			$bibChunks = array_chunk($bibKeys, 99); // we'll iterate through 99 elements at a time

			// go through the bibs 99 items at a time
			foreach ($bibChunks as $chunk) {
				
				// implode chunk and then add to query
				$mmsString = implode(",", $chunk);
				$bibData = $this->getAlmaData("/bibs/", ["mms_id" => $mmsString, "view" => "full", "expand" => "None"]);
				
				// iterate through and add each bib record to $bibList[$id];
				if ( array_key_exists("bib", $bibData) ) {
					foreach($bibData['bib'] as $bib) {
						if ( array_key_exists("mms_id", $bib)) {
							$mms_id = $bib['mms_id'];
							$this->bibList[$mms_id]['bib'] = $bib;
						}
					}	
				}


			}
			
			
		}

		logMsg("Bib List", $this->bibList);
		
	}

	/* *************************************************************
	 *  apiAlmaFormatReadingList()
	 *
	 *  FORMAT THE READING LIST FOR API RETURN DATA
	 * 
	 *  We need to divide into sections and piece together display information such as
	 *  title and author.
	 *  
	 */
	private function apiAlmaFormatReadingList( $items = array() ) {

		$readinglist = array();

		logMsg("Formatting reading list");

		// not every citation will make it into the readinglist based on availability, so we want to track
		$this->preCount = $len = count($items);

		for ($i=0; $i < $len; $i++) {	

			$readinglist = $this->apiAlmaFormatReadingListAdd($items[$i], $readinglist);
		}

		/*

		==================================================================================
		THIS IS WHERE WE DO THE BIB INGESTION
		Go through the readinglist and any bib with an MMSID, add to a list to do a get
		of the mmsids, then substitute.
		==================================================================================
		1. get bib records
		2. loop through and find matches for mms_id and add title and author

		*/

		$this->apiAlmaLoadBibs(); // obtain the list of bibs from ALMA

		// loop through the records and add the bib info
		foreach ($this->bibList as $d) {

			if ( isset ( $d['bib'] ) && $d['bib'] !== null ) {

				$bib = $d['bib'];
				$section_id = $d['section_id'];
				$citation_id = $d['citation_id'];

				if ( isset($bib['title']) ) {
					$title = $this->cleanTitle($bib['title']);
					$readinglist[$section_id]['items'][$citation_id]['title'] = $title;
				}
				if ( isset($bib['author']) ) {
					$author = $bib['author'];
					$readinglist[$section_id]['items'][$citation_id]['author'] = $author;
				}
			}

		}
		
		// a final transformation:
		/* as we added sections to the array, we used the ID as the key. However when we return the data we
		   want an indexed array [0] not ["5324980003211"] so we need to switch the sections from being 
		   indexed by a named value to an index order.
		*/
		$sw_start = microtime(true);
		
		$reOrderedReadingList = array();
		foreach($readinglist as $section) {
			$section['items'] = array_values($section['items']); // remove keys from items in section
			$reOrderedReadingList[] = $section; // add
		}
		
		$readinglist = $reOrderedReadingList; // replace

		logMsg("Readinglist reordering done in ". round((microtime(true) - $sw_start),6) ." seconds");
		unset($sw_start);

		return $readinglist;
	}
	
	/**
	 * We only want to 
	 */
	// we are only taking system UTC times
	private function normalizeUTCDateString($date) {
		if ( !stripos($date, 'T') ) { $date = rtrim($date, 'Z'); }
		return $date;
	}
	
	private function getComparableDates($date) {
		
		$date = ($date === "") ? "now" : $date;
		
		$date = $this->normalizeUTCDateString($date);
		$now = "";
		
		// if it ends in Z then it is UTC
		if ( stripos($date, 'Z') ) {
			$n = new DateTime("now", new DateTimeZone("UTC"));
			$d = new DateTime($date, new DateTimeZone("UTC"));
			$now = $n->format('Y-m-d\TH:i:s\Z');
			$date = $d->format('Y-m-d\TH:i:s\Z');
		} else {
			$n = new DateTime("now", new DateTimeZone($this->timezone));
			$d = new DateTime($date, new DateTimeZone($this->timezone));
			$now = $n->format('Y-m-d');
			$date = $d->format('Y-m-d');	
		}
		
		return array("date" => $date, "now" => $now);
	}
	
	// evaluate the date, has it passed? Inclusive. 2019-01-01 = 2019-01-01 evals to true
	private function dateHasPassed($date = "") {
		$d = $this->getComparableDates($date);
		return ( $d['now'] >= $d['date'] );
	}
	
	// evaluate the date, has it not yet expired? Inclusive 2019-01-01 = 2019-01-01 evals to true
	private function dateHasNotExpired($date = "") {
		$d = $this->getComparableDates($date);
		return ( $d['now'] <= $d['date'] );
	}

	private function apiAlmaFormatReadingListAdd($citation = array(), $readinglist = array() ) {
		
		$n = new DateTime("now", new DateTimeZone($this->timezone));
		$today = $n->format('Y-m-d');

		// there is no section listed in the citation meta data so we will create a default one
		if ( !array_key_exists("section_info", $citation)) {

			$citation['section_info'] = array();

			$citation['section_info']['id'] = 0;
			$citation['section_info']['name'] = "";
			$citation['section_info']['description'] = "";
			$citation['section_info']['start_date'] = $today;
			$citation['section_info']['end_date'] = $today;
			$citation['section_info']['visibility'] = "";

			logMsg("Item has no section associated with it. Creating default.", $citation);

		}

		$id = ( array_key_exists("id", $citation['section_info']) ) ? $citation['section_info']['id'] : 0;

		// check to see if readinglist has a section for this citation, if not add the section
		if ( !array_key_exists($citation['section_info']['id'], $readinglist) ) {

			$isVisible = true;

			if ( array_key_exists("visibility", $citation['section_info']) ) {
				$isVisible = ($citation['section_info']['visibility'] === 0 || $citation['section_info']['visibility'] === "0" ) ? false : true; 
			}

			if ( $isVisible ) {
				if ( array_key_exists("start_date", $citation['section_info']) ) {
					//$isVisible = ( $citation['section_info']['start_date'] === "" || $citation['section_info']['start_date'] <= $today ) ? true : false;
					$isVisible = $this->dateHasPassed( $citation['section_info']['start_date'] );
				}
			}

			if ( $isVisible ) {
				if ( array_key_exists("end_date", $citation['section_info']) ) {
					///$isVisible = ( $citation['section_info']['end_date'] === "" || $today <= $citation['section_info']['end_date'] ) ? true : false;
					$isVisible = $this->dateHasNotExpired( $citation['section_info']['end_date'] );
				}
			}

			if ( $isVisible ) {

				$section = array();

				// we do ID twice, once as a key (temporary) and then as an element in the array (for returning with the data)
				$id = $citation['section_info']['id'];
				$section['id'] = $id;
				$section['name'] = ( array_key_exists( "name", $citation['section_info']) ) ? $citation['section_info']['name'] : "";
				$section['description'] = ( array_key_exists( "description", $citation['section_info']) ) ? $citation['section_info']['description'] : "";
				$section['items'] = array();

				$readinglist[$id] = $section;
				logMsg("Section added to readinglist with ID: ".$id, $section);
			}
		}

		// we had the chance to add the section, now check to see if it was added. If not that means it is not visible so we skip
		if ( array_key_exists($citation['section_info']['id'], $readinglist) ) {

			$citationVisible = true;
			//[material_visibility_end_date]
			//[material_visibility_start_date]
			// $today is set above. If this is ever separated out into a separate functin then we need to restablish $today
			
			// check visibility rules for each citation. 
			if ( $citationVisible ) {
				if ( array_key_exists("material_visibility_start_date", $citation) ) {
					//$citationVisible = ( $citation['material_visibility_start_date'] === "" || $citation['material_visibility_start_date'] <= $today ) ? true : false;
					$citationVisible = $this->dateHasPassed( $citation['material_visibility_start_date'] );
				}
			}

			if ( $citationVisible ) {
				if ( array_key_exists("material_visibility_end_date", $citation) ) {
					//$citationVisible = ( $citation['material_visibility_end_date'] === "" || $today <= $citation['material_visibility_end_date'] ) ? true : false;
					$citationVisible = $this->dateHasNotExpired( $citation['material_visibility_end_date'] );
				}
			}
			
			// if it is visible then begin adding it to the data feed			
			if ($citationVisible) {
			
				// increment the total count visible
				$this->postCount++;

				// begin formatting the citation
				$title = "";
				$author = "";
				$from = "";
				$linkText = "";
				$materialURL = "";
				$mms_id = "";

				if ( isset($citation['metadata']) ) {

					// use the data from the citation, we'll fill in more from bib later
					if ( isset($citation['metadata']['title'])) {
						$title = $citation['metadata']['title'];
					} else {
						if ( isset($citation['metadata']['article_title'])) {
							$title = $citation['metadata']['article_title'];
							$from = ( isset($citation['metadata']['journal_title'])) ? "Article from: " . $citation['metadata']['journal_title'] : "";
						} else if ( isset($citation['metadata']['journal_title'])) {
							$title = $citation['metadata']['journal_title'];
						}
					}

					if ($author === "") {
						if ( isset($citation['metadata']['author'])) {
							$author = $citation['metadata']['author'];
						}
					}

					// if it is a book chapter, then use that info as the title and author
					if ( isset($citation['metadata']['chapter_title'])) {
						$from = "Chapter from: " . $title; // move title to from (the book)
						$title = $citation['metadata']['chapter_title'];
						if ( isset($citation['metadata']['chapter_author'])) {
							$author = $citation['metadata']['chapter_author'];
						}
					}


				}

				if ( $title === "" ) {
					$title = "No title provided";

					// add the mms_id, so we can bring in the bib later
					if ( isset($citation['metadata']['mms_id']) ) {
						$mms_id = $citation['metadata']['mms_id'];
						$this->bibList[$mms_id.""] = [ "section_id" => $id, "bib" => null ];
					}

				} else {
					$title = $this->cleanTitle($title); // get rid of the final / and . found in some titles, and remove all extra spaces before and after slash removal
					$from = $this->cleanTitle($from); // get rid of the final / and . found in some titles, and remove all extra spaces before and after slash removal
				}

				// this is where if no mms id it should use source url
				$linkType = $this->alma['link'];

				if( $linkType === "leganto_permalink" ) {
					$materialURL = $citation["leganto_permalink"];
				} else if ( $linkType === "open_url" ) {
					if ( isset( $citation['metadata']['mms_id'] )) {
						$materialURL = $citation['open_url'];
					} else {
						$materialURL = $citation['metadata']['source'];
					}
				} else {
					// it is a custom url
					$materialURL = str_replace("{{citation_id}}",$citation['id'],$linkType);
				}

				if ($author !== "") {
					$linkText = $title . " / " . $author;
				} else {
					$linkText = $title;
				}

				$item = array();
				$item['id'] = $cid = $citation['id'];
				$item['link'] = $materialURL;
				$item['title'] = $title;
				$item['author'] = $author;
				$item['text'] = $linkText;
				$item['from'] = $from;
				$item['due'] = (array_key_exists("due_date", $citation)) ? $this->normalizeUTCDateString( $citation['due_date'] ) : "";				

				$readinglist[$id]['items'][$cid] = $item;

				logMsg("Item added to readinglist section: ".$id, $item);
			} else {
				logMsg("Citation is not visible. Not adding.", $citation);
			}
		} else {
			logMsg("Reading list item's section is not visible. Not adding.", $citation);		
		}
		
		return $readinglist;
	}

	private function cleanTitle($title) {
		$title = trim(rtrim(trim($title), '/')); // get rid of the final / found in some titles, and remove all extra spaces before and after slash removal
		$title = trim(rtrim(trim($title), '.')); // get rid of the final . found in some titles, and remove all extra spaces before and after dot removal
		return $title;
	}
	
	
	private function getAlmaData($uri = "", $parameters = array() ) {
	
		$parameters['apikey'] = $this->apikey;
		$parameters['format'] = "json";
		
		// remove the trailing / (if there) there are no extra between the pieces (easier than checking and then adding if not)
		$endpoint = rtrim($this->domain,"/") . "/almaws/v1/" . trim($uri, "/");
		
		$this->stats['api_requests']++;

		return generateJSONrequest($endpoint, $parameters);
	}
	
}

?>