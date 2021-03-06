<?php

$GLOBALS['steamIdPrefix'] = 'steam:';


function steamSearch($title, $aka=null)
{
    global $steamIdPrefix;
    global $CLIENTERROR;
    global $cache;

    $url = 'https://api.steampowered.com/ISteamApps/GetAppList/v2/';

    $resp = httpClient($url, $cache);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data = array();

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // multiple matches
    if (preg_match_all('/"appid":\d+,"name":"[^"]*'.str_replace (' ', '[^"]*', $title).'[^"]*"/i', $resp['data'], $multi, PREG_SET_ORDER))
    {
        foreach ($multi as $row)
        {
            preg_match('/"appid":(\d+),"name":"([^"]*'.str_replace (' ', '[^"]*', $title).'[^"]*)"/i', $row[0], $ary);
            if ($ary[1] and $ary[2]) {
                $info           = array();
                $info['id']     = $steamIdPrefix.$ary[1];
                $info['title']  = $ary[2];
                $data[]         = $info;
            }
			$data = multidimsort($data, 'title');
#           dump($info);
        }
    }

    return $data;
}

function multidimsort($array_in, $column)
{
    $multiarray = array_column($array_in, $column);
    $array_out  = array();
    asort($multiarray);
    // traverse new array of index values and add the corresponding element of the input array to the correct position in the output array
    foreach ($multiarray as $key => $val)
    {
        $array_out[] = $array_in[$key];
    }
    // return the output array which is all nicely sorted by the index you wanted!
    return $array_out;
}

function steamMeta()
{
    return array('name' => 'Steam', 'stable' => 1);
}

function steamData($steamId)
{
	global $steamIdPrefix;
	
	global $CLIENTERROR;
    global $cache;

    $steamId = preg_replace('/^'.$steamIdPrefix.'/', '', $steamId);
    $data= array();

	$resp = httpClient('https://store.steampowered.com/api/appdetails?appids='.$steamId.'&l=german', $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	
	$jsonObject = json_decode($resp['data']);
	
	if(!($jsonObject->{$steamId}->{'success'})) {$CLIENTERROR .= "Steam Id not found\n";return $data;}
	
	$data['encoding'] = 'UTF-8';
	$data['year'] = substr($jsonObject->{$steamId}->{'data'}->{'release_date'}->{'date'},-4);
	$data['title'] = $jsonObject->{$steamId}->{'data'}->{'name'};
	$coverurl = $jsonObject->{$steamId}->{'data'}->{'header_image'};
	$coverurl = substr($coverurl,0, strrpos($coverurl, '?'));
	$data['coverurl'] = $coverurl;
	$data['director'] =$jsonObject->{$steamId}->{'data'}->{'publishers'}[0];
	$data['rating']=$jsonObject->{$steamId}->{'data'}->{'metacritic'}->{'score'}/10;
	foreach($jsonObject->{$steamId}->{'data'}->{'genres'} as $genre) {
        $data['genres'][] = $genre->{'description'};
    }
	 $data['plot']=$jsonObject->{$steamId}->{'data'}->{'detailed_description'};
	 //print_r($data);
	 
	 return $data;
}
?>