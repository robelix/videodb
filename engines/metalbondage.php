<?php
/**
 * IMDB Parser
 *
 * Parses data from the Internet Movie Database
 *
 * @package Engines
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @link    http://www.imdb.com  Internet Movie Database
 * @version $Id: imdb.php,v 1.76 2013/04/10 18:11:43 andig2 Exp $
 */

$GLOBALS['metalbondagePrefix'] = 'metalbondage:';

function metalbondageData($metalbondageId)
{
	global $metalbondagePrefix;
	
	global $CLIENTERROR;
    global $cache;

    $metalbondageId = preg_replace('/^'.$metalbondagePrefix.'/', '', $metalbondageId);
    $data= array();

	$resp = httpClient('http://www.metalbondage.com/'.$metalbondageId, $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	
	$data['encoding'] = 'UTF-8';
	
	//print_r($resp['data']);
	$response = $resp['data'];
	
	preg_match('/http:\/\/www.metalbondage.com\/updates\/mb\d+\/mb\d+.jpg/i', $response, $matches);
	$data['coverurl'] = $matches[0];
	
	$data['director'] = "metalbondage";
	
	preg_match('/Added .*? \d+, (\d+)/i', $response, $matches);
	$data['year'] = $matches[1];
	
	preg_match('/(\d+):\d+ min/i', $response, $matches);
	$data['runtime'] = $matches[1];
	
	preg_match('/>(MB\d+ - .*?)</i', $response, $matches);
	$data['title'] = $matches[1];
	
	preg_match('/<p>(.*)<\/p/i', $response, $matches);
	$data['plot'] = $matches[1];
	
	
	preg_match_all('/<a.*href=".*tag\/(.*)\/.*rel="tag".*?>(.*?)</i', $response, $matches, PREG_SET_ORDER);
	foreach($matches as $match) {
		$cast .= $match[2].'::::url:http://www.metalbondage.com/models/'.$match[1].".jpg\n";
	}
	$data['cast'] = $cast;
	
	$data['genres'][]='Adult';
	//print_r($data);
	 
	 return $data;
}
?>