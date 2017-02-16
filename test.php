<?php

include "phpSArray.php" ;

function check($a,$b) {
	if ( $a == $b ) {
		echo "    OK " ;
	} else {
		echo "*** KO " ;
	}
	echo $a . ' =? ' . $b . '<br/>' ;
}

function raw_dump($a) {
	echo '<pre>'.var_dump($a).'</pre>';
}

?>

<h1>Array set</h1>
<pre>
<?php
$o = array() ; 
$o = set($o,"a.a","aa") ;
$o = set($o,"a.b","ab") ;
$o = set($o,"c","c") ;
$o = set($o,"d.a","da") ;
$o = set($o,"d.b","db") ;
$o = set($o,"d.c","dc") ;

$o1 = $o ;

check(json_encode($o),'{"a":{"a":"aa","b":"ab"},"c":"c","d":{"a":"da","b":"db","c":"dc"}}') ;
check(json_encode(set($o,'a.a','aa2')),'{"a":{"a":"aa2","b":"ab"},"c":"c","d":{"a":"da","b":"db","c":"dc"}}') ;
check(json_encode(set($o,'a','a')),'{"a":"a","c":"c","d":{"a":"da","b":"db","c":"dc"}}') ;
check(json_encode(uset($o,'a')),'{"c":"c","d":{"a":"da","b":"db","c":"dc"}}') ;
check(json_encode(uset($o,'d')),'{"c":"c"}') ;
?>

<h1>Array get</h1>
<?php
check( get($o1,"d.c") , 'dc' ) ;

$n = json_decode('{"a":{"b":{"c":"abc","d":"abd"}}}')	 ;
check( get($n,'a.b.c') , 'abc' ) ;
?>

<h1>Array change</h1>
<?php
$o = array() ; 
set($o,"a","a") ;
set($o,"b","b") ;
set($o,"c","c") ;
set($o,"d.a","da") ;
set($o,"d.b","db") ;
set($o,"d.c","dc") ;
$n = $o ;
set($n,"d.c","dcn") ;
set($n,"c","cn") ;
$var = array_change($o,$n) ;

check ( json_encode($var), '{"old":{"c":"c","d":{"c":"dc"}},"new":{"c":"cn","d":{"c":"dcn"}}}') ;
?>


