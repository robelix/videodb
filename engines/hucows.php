<?php

$GLOBALS['hucowsPrefix'] = 'hucows:';

function hucowsData($hucowsId)
{
	global $hucowsPrefix;
	
	global $CLIENTERROR;
    global $cache;

    $hucowsId = preg_replace('/^'.$hucowsPrefix.'/', '', $hucowsId);
    $data= array();

	$resp = httpClient('http://www.hucows.com/'.$hucowsId, $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	
	$data['encoding'] = 'UTF-8';
	
	//print_r($resp['data']);
	$response = $resp['data'];
	
	preg_match('/http:\/\/www.hucows.com\/wp-content\/[^"]*hu\d+[^"]*/i', $response, $matches);
	$data['coverurl'] = $matches[0];
	
	$data['director'] = "hucows";
	
	preg_match('/uploads\/(\d{4})\/[^"]*hu\d+/i', $response, $matches);
	$data['year'] = $matches[1];
	
	preg_match('/class="entry-title".*>(.*)</i', $response, $matches);
	$data['title'] = $matches[1];
	
	preg_match('/<p>(.*)<\/p>[\W]*Members/i', $response, $matches);
	$data['plot'] = $matches[1];
	
	
	preg_match_all('/<a.*?href=".*?tag\/(.*?)\/.*?rel="tag".*?>(.*?)</i', $response, $matches, PREG_SET_ORDER);
	foreach($matches as $match) {
		$cast .= $match[2].'::::url:http://www.hucows.com/girls/'.$match[1].".jpg\n";
	}
	$data['cast'] = $cast;
	
	$data['genres'][]='Adult';
	 //print_r($data);
	 
	 return $data;
}
?>