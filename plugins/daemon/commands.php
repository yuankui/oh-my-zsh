<?php
/**
 * Created by PhpStorm.
 * User: yuankui
 * Date: 16/8/28
 * Time: 下午11:10
 */


$ironHome = getenv('IRON_HOME');

if (empty($ironHome)) {
    $ironHome = '/usr/local/apps/iron';
}

function preg_ls($path=".", $rec=false, $pat="/.*/") {
    // it's going to be used repeatedly, ensure we compile it for speed.
    $pat=preg_replace("|(/.*/[^S]*)|s", "\\1S", $pat);
    //Remove trailing slashes from path
    while (substr($path,-1,1)=="/") $path=substr($path,0,-1);
    //also, make sure that $path is a directory and repair any screwups
    if (!is_dir($path)) $path=dirname($path);
    //assert either truth or falsehoold of $rec, allow no scalars to mean truth
    if ($rec!==true) $rec=false;
    //get a directory handle
    $d=dir($path);
    //initialise the output array
    $ret=Array();
    //loop, reading until there's no more to read
    while (false!==($e=$d->read())) {
        //Ignore parent- and self-links
        if (($e==".")||($e=="..")) continue;
        //If we're working recursively and it's a directory, grab and merge
        if ($rec && is_dir($path."/".$e)) {
            $ret=array_merge($ret,preg_ls($path."/".$e,$rec,$pat));
            continue;
        }
        //If it don't match, exclude it
        if (!preg_match($pat,$e)) continue;
        //In all other cases, add it to the output array
        $ret[]=$path."/".$e;
    }
    //finally, return the array
    return $ret;
}

$ret = preg_ls("$ironHome/v2/api/trade/controllers/daemon", true, '/.*.php/');

function repl($path) {
    global $ironHome;
    $str = str_replace("$ironHome/v2/api", '', $path);
    $str = str_replace('.php', '', $str);
    return str_replace('controllers/', '', $str);
}

$paths = array_map('repl', $ret);
?>

function listIronCompletions {
    reply=(
<?php
foreach ($paths as $path) {
    echo $path . "\n";
}
?>
    );
}

compctl -K listIronCompletions daemon

