<?php

$GLOBALS['shochaPrefix'] = 'shocha:';

function shochaData($shochaId)
{
	global $shochaPrefix;
	
	global $CLIENTERROR;
    global $cache;

    $shochaId = preg_replace('/^'.$shochaPrefix.'/', '', $shochaId);
    $data= array();

	$resp = httpClient('http://www.shockchallenge.com/'.$shochaId, $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	
	$data['encoding'] = 'UTF-8';
	
	//print_r($resp['data']);
	$response = $resp['data'];
	
	preg_match('/http:\/\/www.shockchallenge.com\/[^"]*.jpg/i', $response, $matches);
	$data['coverurl'] = $matches[0];
	
	$data['director'] = "shockchallenge";
	
	preg_match('/\d+, (\d{4})/i', $response, $matches);
	$data['year'] = $matches[1];
	
	preg_match('/id="page-title".*>(.*)</i', $response, $matches);
	$data['title'] = $matches[1];
	
	preg_match('/<p>(.*?)<\/p>/i', $response, $matches);
	$data['plot'] = $matches[1];
	
	
	preg_match_all('/<a.*?href=".*?tag\/(.*?)\/.*?rel="tag".*?>(.*?)</i', $response, $matches, PREG_SET_ORDER);
	foreach($matches as $match) {
		$cast .= $match[2].'::::url:http://www.shockchallenge.com/models/'.$match[1].".jpg\n";
	}
	$data['cast'] = $cast;
	
	$data['genres'][]='Adult';
	 //print_r($data);
	 
	 return $data;
}
?>