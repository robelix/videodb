<?php

$GLOBALS['themoviedbPrefix'] = 'themoviedb:';


function themoviedbSearch($title, $aka=null)
{
    global $themoviedbPrefix;
    global $CLIENTERROR;
    global $cache;
	global $config;

	$apiKey = $config['themoviedbapikey'];
	$language = $config['themoviedblocale'];

    $url = 'https://api.themoviedb.org/3/search/movie?api_key='.$apiKey.'&language='.$language.'&query='.str_replace ('_', ' ',$title).'&include_adult=true';

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
			$info['title']  = $row->{'title'}.' ('.$row->{'original_title'}.') ('.$row->{'release_date'}.')';
			$data[]         = $info;
#           dump($info);
	}

    return $data;
}


function themoviedbMeta()
{
    return array('name' => 'The Movie DB', 'stable' => 1, 'config' => array(
                                array('opt' => 'locale', 'name' => 'The Movie DB Language',
                                      'values' => array('en-US'=>'en-US', 'de-DE'=>'de-DE'), 
                                      'desc' => 'Select language.'),
                                array('opt' => 'apikey', 'name' => 'API access key',
                                      'desc' => 'You know it!')
    ));
}

function themoviedbActor($name, $actorid)
{

    $ary = array();
    $ary[0][0] = $name;
    $ary[0][1] = 'https://image.tmdb.org/t/p/w300_and_h450_bestv2'.$actorid;

    return $ary;
}

function themoviedbData($moviedbId)
{
	global $themoviedbPrefix;
	
	global $CLIENTERROR;
    global $cache;
	global $config;
	
	$apiKey = $config['themoviedbapikey'];
	$language = $config['themoviedblocale'];

    $moviedbId = preg_replace('/^'.$themoviedbPrefix.'/', '', $moviedbId);
    $data= array();

	$resp = httpClient('https://api.themoviedb.org/3/movie/'.$moviedbId.'?api_key='.$apiKey.'&language='.$language, $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	
	$data['encoding'] = 'UTF-8';
	
	$jsonObject = json_decode($resp['data']);
	
	$data['year'] = substr($jsonObject->{'release_date'},0,4);
	$data['title'] = $jsonObject->{'title'};
	$coverurl = $jsonObject->{'poster_path'};
	$data['coverurl'] = 'https://image.tmdb.org/t/p/w300_and_h450_bestv2'.$coverurl;
	$data['director'] =$jsonObject->{'production_companies'}[0]->{'name'};
	$countries = array();
	foreach($jsonObject->{'production_countries'} as $country) {
		$countries[] = $country->{'name'};
	}
	$data['country'] = join(', ', $countries);
	$data['runtime'] = $jsonObject->{'runtime'};
	$data['rating']=$jsonObject->{'vote_average'};
	foreach($jsonObject->{'genres'} as $genre) {
        $data['genres'][] = $genre->{'name'};
    }
	 $data['plot']=$jsonObject->{'overview'};
	 
	 $castResp = httpClient('https://api.themoviedb.org/3/movie/'.$moviedbId.'/credits?api_key='.$apiKey, $cache);
	 if (!$castResp['success']) $CLIENTERROR .= $castResp['error']."\n";
	 
	 $castJsonObject = json_decode($castResp['data']);
	 foreach($castJsonObject->{'cast'} as $cast) {
		 $castHtml  .= $cast->{'name'}."::".$cast->{'character'}."::".$themoviedbPrefix.$cast->{'profile_path'}."\n";
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