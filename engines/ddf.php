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

$GLOBALS['ddfPrefix'] = 'ddf:';

function ddfSearch($title, $aka=null)
{
    global $ddfPrefix;
    global $CLIENTERROR;
    global $cache;

    $url = 'https://ddfnetwork.com/videos/freeword/'.str_replace ('_', ' ',$title);

    $resp = httpClient($url, $cache);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data = array();

    // add encoding
    $data['encoding'] = $resp['encoding'];

	$response = $resp['data'];

	preg_match_all('/"\/videos\/.*\/(\d+)".*title="([^"]*)"/i', $response, $matches, PREG_SET_ORDER);
	foreach($matches as $match) {
			if($match[1]!="1")
			$info           = array();
			$info['id']     = $ddfPrefix.$match[1];
			$info['title']  = $match[2];
			$data[]         = $info;
#           dump($info);
	}

    return $data;
}

function ddfMeta()
{
    return array('name' => 'DDF', 'stable' => 1);
}

function ddfData($ddfId)
{
	global $ddfPrefix;
	
	global $CLIENTERROR;
    global $cache;

    $ddfId = preg_replace('/^'.$ddfPrefix.'/', '', $ddfId);
    $data= array();

	$resp = httpClient('https://ddfnetwork.com/videos/test/'.$ddfId, $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	
	$data['encoding'] = 'UTF-8';
	
	//print_r($resp['data']);
	$response = $resp['data'];
	
	preg_match('/[^"]*\/preview.jpg/i', $response, $matches);
	$data['coverurl'] = $matches[0];
	
	$data['director'] = "ddfnetwork";
	
	preg_match('/\d+\.\d+\.(\d+)/i', $response, $matches);
	$data['year'] = $matches[1];
	
	preg_match('/<h1>(.*)<\/h1>/i', $response, $matches);
	$data['title'] = $matches[1];
	
	preg_match('/<div.*class="descr-box".*>[^p]*<p>([\w\W]*?)<\/p>/i', $response, $matches);
	$data['plot'] = str_replace ('<br>','\n',$matches[1]);
	
	preg_match('/(\d+) min/i', $response, $matches);
	$data['runtime'] = $matches[1];
	
	preg_match_all('/data-src="(.*\/models\/.*?)".*alt="(.*?)"/i', $response, $matches, PREG_SET_ORDER);
	foreach($matches as $match) {
		$cast .= $match[2].'::::url:https:'.$match[1]."\n";
	}
	$data['cast'] = $cast;
	
	$data['genres'][]='Adult';
	 //print_r($data);
	 
	 return $data;
}
?>