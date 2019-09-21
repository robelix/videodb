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

$GLOBALS['urlPrefix'] = 'url:';


function urlActor($name, $actorid)
{

    $ary = array();
    $ary[0][0] = $name;
    $ary[0][1] = $actorid;
    return $ary;
}
?>