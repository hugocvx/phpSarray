<?php
function valo($a,$k,$d=null) {
	$i = strpos($k,'.') ;
	if ($i>0) {
		$n = substr($k,0,$i) ;
		if ( isset($a->$n)) {
			return get($a->$n,substr($k,$i+1),$d) ;
		}
		return $d ;
	}
	return isset($a->$k) ? $a->$k : $d ;
}

function vala(array $a,$k,$d=null) {
	$i = strpos($k,'.') ;
	if ($i>0) {
		$n = substr($k,0,$i) ;
		if ( isset($a[$n])) {
			return get($a[$n],substr($k,$i+1),$d) ;
		}
		return $d ;
	}
	return isset($a[$k]) ? $a[$k] : $d ;
}

function get($a,$k,$d=null) {
	if ( is_array($a) ) {
		return vala($a,$k,$d) ;
	} else if ( is_object($a) ) {
		return valo($a,$k,$d) ;
	}
	return $a ;
}

function has($a, $k) {
	return get($a,$k,null) != null ;
}

function req($k, $d=null) { return get($_REQUEST,$k,$d) ; }

function _seto(&$a,$k,$v) {
	$i = strpos($k,'.') ;
	if ($i>0) {
		$n = substr($k,0,$i) ;
		if ( ! isset($a->$n) ) {
			$a->$n = new stdClass();
		}
		set($a->$n,substr($k,$i+1),$v) ; 
	} else {
		$a->$k = $v ;
	}		
	return $a ;
}

function _seta(array &$a,$k,$v) {
	$i = strpos($k,'.') ;
	if ($i>0) {
		$n = substr($k,0,$i) ;
		if ( ! array_key_exists($n,$a) ) {
			$a[$n] = array() ;
		}
		set($a[$n],substr($k,$i+1),$v) ; 
	} else {
		$a[$k] = $v ;
	}		
	return $a ;
}

function set(&$a,$k,$v) {
	if ( is_array($a) ) {
		return _seta($a,$k,$v) ;
	} else if ( is_object($a) ) {
		return _seto($a,$k,$v) ;
	}
	return $a ;
}

function copyFromIfNotHas($field,&$dest,$src) {
	if (!has($dest,$field)) {
		$dest = set($dest,$field,val($src,$field)) ;
	}
}

function first(array $f) {
	return $f[0] ;
}
function last(array $f) {
	return $f[count($f)-1] ;
}

function uset(array &$a,$k) {
	$i = strpos($k,'.') ;
	if ($i>0) {
		$n = substr($k,0,$i) ;
		if( $n == '*' ) {
			$b = $a ;
			foreach($b as $kk=>$v) {
				uset($a[$kk],substr($k,$i+1)) ; // $i+1 == 3
			}
		} else if ( array_key_exists($n,$a) ) {
			uset($a[$n],substr($k,$i+1)) ; 
		}
	} else {
		unset($a[$k]) ;
	}		
	return $a ;
}

function array_change($old,$new) {
	if ( ! is_array($old) && strlen($old) == 0)  { $old = array() ; }
	$res = array('old'=>array(),'new'=>array()) ;
	$keys = ( is_array($old) ? array_keys($old) : [] ) + ( is_array($new) ? array_keys($new) : [] ) ;
	$changed = false ;
	
	foreach($keys as $k) {
		$o = get($old,$k,'') ;
		$n = get($new,$k,'') ;
		
		if ( is_array($o) || is_array($n) ) {
			$res2 = array_change($o,$n) ;
			if ( $res2 ) {
				set($res,"old.$k",$res2['old']) ;
				set($res,"new.$k",$res2['new']) ;
				$changed = true ;
			}
		} else {
			if ( $o != $n ) {
				set($res,"old.$k",$o) ;
				set($res,"new.$k",$n) ;
				$changed = true ;
			}
		}
	}
	return $changed ? $res : false ;
}

if ( req('__array_change',false) ) {
	$o = array() ; 
	set($o,"a","a") ;
	set($o,"b","b") ;
	set($o,"c","c") ;
	set($o,"d.a","da") ;
	set($o,"d.b","db") ;
	set($o,"d.c","dc") ;
	$n = $o ;
	set($n,"d.c","dnc") ;
	set($n,"c","cn") ;
	
	var_dump(array_change($o,$n)) ;
	
	exit ;
}

function postToArray(array $a = array()) {

	foreach($_REQUEST as $k=>$v) {
		$k = str_replace(':', '.', $k) ;
		if ( startsWith('sub.',$k) ) {
			$k = substr($k,4) ;
			$a = uset($a,$k) ;
			$a = set($a,$k, []) ;
		}
	}

	foreach($_REQUEST as $k=>$v) {
		$k = str_replace(':', '.', $k) ;
		if ( startsWith('add.',$k) ) {
			$k = substr($k,4) ;
			if (strlen($v)) {
				set($a,$k,$v) ;
			} else {
				uset($a,$k) ;				
			}
		} else if ( startsWith('addb.',$k) ) { // boolean
			$k = substr($k,5) ;
			set($a,$k,$v==1?true:false) ;
		} else if ( startsWith('adds.',$k) ) { // set
			$k = substr($k,5) ;
			if ( is_array($v) ) {
				set($a,$k,$v) ;
			} else {
				set($a,$k,[$v]) ;
			}
		} else if ( startsWith('adda.',$k) ) { // stringified-perline array
			$k = substr($k,5) ;
			if (strlen($v)) {
				$v = explode("\n",str_replace("\r",'',$v)) ;
				set($a,$k,$v) ;
			} else {
				uset($a,$k) ;
			}
		} else if ( startsWith('addo.',$k) ) {
			$k = substr($k,5) ;
			if (strlen($v)) {
				set($a,$k,intval($v)) ;
			} else {
				uset($a,$k) ;
			}
		}
	}
	foreach($_REQUEST as $k=>$v) {
		$k = str_replace(':', '.', $k) ;
		if ( startsWith('del.',$k) ) {
			$k = substr($k,4) ;
			uset($a,$k) ;
		}
	}

	return $a ;
}

?>