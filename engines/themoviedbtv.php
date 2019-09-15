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

$GLOBALS['themoviedbPrefix'] = 'themoviedbtv:';


function themoviedbtvSearch($title, $aka=null)
{
    global $themoviedbPrefix;
    global $CLIENTERROR;
    global $cache;
	global $config;

	$apiKey = $config['themoviedbtvapikey'];
	$language = $config['themoviedbtvlocale'];

    $url = 'https://api.themoviedb.org/3/search/tv?api_key='.$apiKey.'&language='.$language.'&query='.str_replace ('_', ' ',$title).'&include_adult=true';

    $resp = httpClient($url, $cache);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data = array();

    // add encoding
    $data['encoding'] = $resp['encoding'];

	$jsonObject = json_decode($resp['data']);

    // multiple matches
	foreach ($jsonObject->{'results'} as $row)
	{
			$info           = array();
			$info['id']     = $themoviedbPrefix.$row->{'id'};
			$info['title']  = $row->{'name'}.' ('.$row->{'original_name'}.') ('.$row->{'first_air_date'}.')';;
			$data[]         = $info;
#           dump($info);
	}

    return $data;
}


function themoviedbtvMeta()
{
    return array('name' => 'The Movie DB TV', 'stable' => 1, 'config' => array(
                                array('opt' => 'locale', 'name' => 'The Movie DB Language',
                                      'values' => array('en-US'=>'en-US', 'de-DE'=>'de-DE'), 
                                      'desc' => 'Select language.'),
                                array('opt' => 'apikey', 'name' => 'API access key',
                                      'desc' => 'You know it!')
    ));
}

function themoviedbtvData($moviedbId)
{
	global $themoviedbPrefix;
	
	global $CLIENTERROR;
    global $cache;
	global $config;
	
	$apiKey = $config['themoviedbtvapikey'];
	$language = $config['themoviedbtvlocale'];

    $moviedbId = preg_replace('/^'.$themoviedbPrefix.'/', '', $moviedbId);
    $data= array();

	$resp = httpClient('https://api.themoviedb.org/3/tv/'.$moviedbId.'?api_key='.$apiKey.'&language='.$language, $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	
	$data['encoding'] = 'UTF-8';
	
	$jsonObject = json_decode($resp['data']);
	
	$data['year'] = substr($jsonObject->{'first_air_date'},0,4);
	$data['title'] = $jsonObject->{'name'};
	$coverurl = $jsonObject->{'backdrop_path'};
	$data['coverurl'] = 'https://image.tmdb.org/t/p/w300_and_h450_bestv2'.$coverurl;
	$data['director'] =$jsonObject->{'created_by'}[0]->{'name'};
	$countries = array();
	foreach($jsonObject->{'origin_country'} as $country) {
		$countries[] = $country;
	}
	$data['country'] = join(', ', $countries);
	$data['runtime'] = $jsonObject->{'episode_run_time'}[0];
	$data['rating']=$jsonObject->{'vote_average'};
	foreach($jsonObject->{'genres'} as $genre) {
        $data['genres'][] = $genre->{'name'};
    }
	 $data['plot']=$jsonObject->{'overview'};
	 
	 $castResp = httpClient('https://api.themoviedb.org/3/tv/'.$moviedbId.'/credits?api_key='.$apiKey, $cache);
	 if (!$castResp['success']) $CLIENTERROR .= $castResp['error']."\n";
	 
	 $castJsonObject = json_decode($castResp['data']);
	 foreach($castJsonObject->{'cast'} as $cast) {
		 $castHtml  .= $cast->{'name'}."::".$cast->{'character'}."::themoviedb:".$cast->{'profile_path'}."\n";
	 }
	 $data['cast'] = $castHtml;
	 foreach($castJsonObject->{'crew'} as $crew) {
		if($crew->{'job'} == "Producer")
		{
			$data['director'] = $crew->{'name'};
			break;
		}
	 }
	 //print_r($data);
	 
	 return $data;
}
?>