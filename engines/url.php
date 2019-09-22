<?php

$GLOBALS['urlPrefix'] = 'url:';


function urlActor($name, $actorid)
{

    $ary = array();
    $ary[0][0] = $name;
    $ary[0][1] = $actorid;
    return $ary;
}
?>