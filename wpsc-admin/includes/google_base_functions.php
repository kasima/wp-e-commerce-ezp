<?php

/* Our Google Base API developer key. */
//$GLOBALS['developerKey'] = "ABQIAAAA6ggeUfjN1SpHwYsrpccTGhRuQWnos65R7rFyIjvnCKH4e1YArxSdx2HKFtraZCwQgrQplEXLG99isg";
//$GLOBALS['developerKey'] = "ABQIAAAAytglg4w-F970cu2wgEJJfBS0dHaC-2dNNjDFDkd1HH21BJ65AhT1FKTndoMPr-hz7ZR87MhvYqxlag";
$GLOBALS['developerKey'] = "ABQIAAAAytglg4w-F970cu2wgEJJfBTkn4bjjtLhLTDtEb0EwHFW9tR8IRSJLrLd5UwWvO_iLJ90wKh2_OHatA";

/* The items feed URL, used for queries, insertions and batch commands. */
$GLOBALS['itemsFeedURL'] = "http://www.google.com/base/feeds/items";

/* Parsed recipe entries from a query. */
$GLOBALS['parsedEntries'] = array();

/* Are we currently parsing an XML ENTRY tag? */
$GLOBALS['foundEntry'] = false;

/* Current XML element being processed. */
$GLOBALS['curElement'] = "";

/**
 * Creates the XML content used to insert a new recipe.
 */
function buildInsertXML($name,$price,$description) {
	$result = "<?xml version='1.0'?>" . "\n";
	
	$result .= "<entry xmlns='http://www.w3.org/2005/Atom' xmlns:g='http://base.google.com/ns/1.0'>
				<category scheme='http://base.google.com/categories/itemtypes' term='Products'/>
				<g:item_type type='text'>Products</g:item_type>
				<g:price type='floatUnit'> ".$price."</g:price>
				<title type='text'>".$name."</title>
				<content>".$description."</content>
			</entry>";
	return $result;
}

/**
 * Creates the XML content used to perform a batch delete.
 */
function buildBatchXML() {
	$counter = 0;
	
	$result =  '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$result .= '<feed xmlns="http://www.w3.org/2005/Atom"' . "\n";
	$result .= ' xmlns:g="http://base.google.com/ns/1.0"' . "\n";
	$result .= ' xmlns:batch="http://schemas.google.com/gdata/batch">' . "\n";
	foreach($_POST as $key => $value) {
		if(substr($key, 0, 5) == "link_") {
			$counter++;

			$result .= '<entry>' . "\n";
			$result .= '<id>' . $value . '</id>' . "\n";
			$result .= '<batch:operation type="delete"/>' . "\n";
			$result .= '<batch:id>' . $counter . '</batch:id>' . "\n";
			$result .= '</entry>' . "\n";
		}
	}
	$result .= '</feed>' . "\n";

	return $result;
}

/**
 * Exchanges the given single-use token for a session
 * token using AuthSubSessionToken, and returns the result.
 */
function exchangeToken($token) {
	$ch = curl_init();    /* Create a CURL handle. */

	curl_setopt($ch, CURLOPT_URL, "https://www.google.com/accounts/AuthSubSessionToken");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: AuthSub token="' . $token . '"'));

	$result = curl_exec($ch);  /* Execute the HTTP command. */
	curl_close($ch);
	$splitStr = split("=", $result);
	return trim($splitStr[1]);
}

/**
 * Performs a query for all of the user's items using the
 * items feed, then parses the resulting XML with the
 * startElement, endElement and characterData functions
 * (below).
 */
function getItems($token) {
	$ch = curl_init();    /* Create a CURL handle. */
	global $developerKey, $itemsFeedURL;

	curl_setopt($ch, CURLOPT_URL, $itemsFeedURL . "?");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml', 'Authorization: AuthSub token="' . trim($token) . '"', 'X-Google-Key: key=' . $developerKey));

	$result = curl_exec($ch);  /* Execute the HTTP command. */
	curl_close($ch);

	/* Parse the resulting XML. */
	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "startElement", "endElement");
	xml_set_character_data_handler($xml_parser, "characterData");
	xml_parse($xml_parser, $result);
	xml_parser_free($xml_parser);
}

/**
 * Inserts a new recipe by performing an HTTP POST to the
 * items feed.
 */
function postItem($name,$price,$description, $token='') {
	$ch = curl_init();    /* Create a CURL handle. */
	global $developerKey, $itemsFeedURL;
	
	/* Set cURL options. */
	curl_setopt($ch, CURLOPT_URL, $itemsFeedURL);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: AuthSub token="' . $token . '"', 'X-Google-Key: key=' . $developerKey, 'Content-Type: application/atom+xml'));
	$xml=buildInsertXML($name,$price,$description);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	
	$result = curl_exec($ch);  /* Execute the HTTP request. */
	curl_close($ch);           /* Close the cURL handle. */

	echo("<pre>".htmlentities($xml)."</pre>");
	exit("<pre>".htmlentities($result)."</pre>");
// 	exit($result);
	return $result;
}

/**
 * Updates an existing recipe by performing an HTTP PUT
 * on its feed URI, using the updated values a PUT data.
 */
function updateItem() {
	$ch = curl_init();    /* Create a CURL handle. */
	global $developerKey;

	/* Prepare the data for HTTP PUT. */
	$putString = buildInsertXML();
	$putData = tmpfile();
// 	exit("=======><pre>".var_dump($putData)."</pre>");
	fwrite($putData, $putString);
	fseek($putData, 0);

	/* Set cURL options. */
	curl_setopt($ch, CURLOPT_URL, $_POST['link']);
	curl_setopt($ch, CURLOPT_PUT, true);
	curl_setopt($ch, CURLOPT_INFILE, $putData);
	curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: AuthSub token="' . $_POST['token'] . '"', 'X-Google-Key: key=' . $developerKey, 'Content-Type: application/atom+xml'));

	$result = curl_exec($ch);  /* Execute the HTTP request. */
	fclose($putData);          /* Close and delete the temp file. */
	curl_close($ch);           /* Close the cURL handle. */

	return $result;
}

/**
 * Deletes a recipe by performing an HTTP DELETE (a custom
 * cURL request) on its feed URI.
 */
function deleteItem() {
	$ch = curl_init();
	global $developerKey;

	/* Set cURL options. */
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	curl_setopt($ch, CURLOPT_URL, $_POST['link']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: AuthSub token="' . $_POST['token'] . '"', 'X-Google-Key: key=' . $developerKey));

	$result = curl_exec($ch);  /* Execute the HTTP request. */
	curl_close($ch);           /* Close the cURL handle.    */

	return $result;
}

/**
 * Deletes all recipes by performing an HTTP POST to the
 * batch URI.
 */
function batchDelete() {
	$ch = curl_init();    /* Create a CURL handle. */
	global $developerKey, $itemsFeedURL;

	/* Set cURL options. */
	curl_setopt($ch, CURLOPT_URL, $itemsFeedURL . "/batch");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: AuthSub token="' . $_POST['token'] . '"', 'X-Google-Key: key=' . $developerKey, 'Content-Type: application/atom+xml'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, buildBatchXML());

	$result = curl_exec($ch);  /* Execute the HTTP request. */
	curl_close($ch);           /* Close the cURL handle.    */

	return $result;
}

/**
 * Callback function for XML start tags parsed by
 * xml_parse.
 */
function startElement($parser, $name, $attrs) {
	global $curElement, $foundEntry, $parsedEntries;
	
	$curElement = $name;
	if($curElement == "ENTRY") {
		$foundEntry = true;
		$parsedEntries[count($parsedEntries)] = array();
	} else if($foundEntry && $curElement == "LINK") {
		$parsedEntries[count($parsedEntries) - 1][$attrs["REL"]] = $attrs["HREF"];
	}
}

/**
 * Callback function for XML end tags parsed by
 * xml_parse.
 */
function endElement($parser, $name) {
	global $curElement, $foundEntry, $parsedEntries;
	if($name == "ENTRY") {
		$foundEntry = false;
	}
}

/**
 * Callback function for XML character data parsed by
 * xml_parse.
 */
function characterData($parser, $data) {
  global $curElement, $foundEntry, $parsedEntries;

  if($foundEntry) {
    $parsedEntries[count($parsedEntries) - 1][strtolower($curElement)] = $data;
  }
}
