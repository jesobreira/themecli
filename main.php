<?php


define('PHPTHEMECLI_INPUT', cmdline::getvalbyindex(1));
define('PHPTHEMECLI_OUTPUT', cmdline::getvalbyindex(2));
define('PHPTHEMECLI_JSON', cmdline::getvalbyindex(3));

if(!PHPTHEMECLI_INPUT || !PHPTHEME_OUTPUT || !PHPTHEMECLI_JSON) exit("Missing needed parameter.");

extract((array)(fromJSON(base64_decode(PHPTHEMECLI_JSON))));

ob_start();

include_once PHPTHEMECLI_INPUT;

define('PHPTHEMECLI_RETURN', ob_get_clean());

$hFile = fopen(PHPTHEMECLI_OUTPUT, "w");
fwrite($hFile, PHPTHEMECLI_RETURN);
fclose($hFile);




# class {
	class cmdline {
		function get($sKey, $mDefault = Null) {
			global $argv,$argc;
			for($i = 1; $i <= ($argc-1); $i++) {
				if($argv[$i]=="/".$sKey OR $argv[$i]=="-".$sKey OR $argv[$i]=="--".$sKey) {
					if($argc>=$i+1) {
						return $argv[$i+1];
					}
				}
			}
		}
		
		function keyexists($sKey) {
			global $argv,$argc;
			for($i = 1; $i <= ($argc-1); $i++) {
				if($argv[$i]=="/".$sKey OR $argv[$i]=="-".$sKey OR $argv[$i]=="--".$sKey) {
					return true;
				}
			}
			return false;
		}
		
		function valueexists($sValue) {
			global $argv,$argc;
			for($i = 1; $i <= ($argc-1); $i++) {
				if($argv[$i]==$sValue) return true;
			}
			return false;
		}
		
		function flagenabled($sKey) {
			global $argv,$argc;
			for($i = 1; $i <= ($argc-1); $i++) {
				if(preg_match("/\+([a-zA-Z]*)".$sKey."([a-zA-Z]*)/", $argv[$i])) {
					return true;
				}
			}
			return false;
		}
		
		function flagdisabled($sKey) {
			global $argv,$argc;
			for($i = 1; $i <= ($argc-1); $i++) {
				if(preg_match("/\-([a-zA-Z]*)".$sKey."([a-zA-Z]*)/", $argv[$i])) {
					return true;
				}
			}
			return false;
		}
		
		function flagexists($sKey) {
			global $argv,$argc;
			for($i = 1; $i <= ($argc-1); $i++) {
				if(preg_match("/(\+|\-)([a-zA-Z]*)".$sKey."([a-zA-Z]*)/", $argv[$i])) {
					return true;
				}
			}
			return false;
		}
		
		function getvalbyindex($iIndex, $mDefault = null) {
			global $argv,$argc;
			if(($argc-1)>=$iIndex) {
				return $argv[$iIndex];
			} else {
				return $mDefault;
			}
		}
	}
# }


/**
 * Parses a JSON string into a PHP variable.
 * @param string $json  The JSON string to be parsed.
 * @param bool $assoc   Optional flag to force all objects into associative arrays.
 * @return mixed        Parsed structure as object or array, or null on parser failure.
 */
function fromJSON ( $json, $assoc = false ) {

  /* by default we don't tolerate ' as string delimiters
     if you need this, then simply change the comments on
     the following lines: */

  // $matchString = '/(".*?(?<!\\\\)"|\'.*?(?<!\\\\)\')/';
  $matchString = '/".*?(?<!\\\\)"/';
  
  // safety / validity test
  $t = preg_replace( $matchString, '', $json );
  $t = preg_replace( '/[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/', '', $t );
  if ($t != '') { return null; }

  // build to/from hashes for all strings in the structure
  $s2m = array();
  $m2s = array();
  preg_match_all( $matchString, $json, $m );
  foreach ($m[0] as $s) {
    $hash       = '"' . md5( $s ) . '"';
    $s2m[$s]    = $hash;
    $m2s[$hash] = str_replace( '$', '\$', $s );  // prevent $ magic
  }
  
  // hide the strings
  $json = strtr( $json, $s2m );
  
  // convert JS notation to PHP notation
  $a = ($assoc) ? '' : '(object) ';
  $json = strtr( $json, 
    array(
      ':' => '=>', 
      '[' => 'array(', 
      '{' => "{$a}array(", 
      ']' => ')', 
      '}' => ')'
    ) 
  );
  
  // remove leading zeros to prevent incorrect type casting
  $json = preg_replace( '~([\s\(,>])(-?)0~', '$1$2', $json );
  
  // return the strings
  $json = strtr( $json, $m2s );

  /* "eval" string and return results. 
     As there is no try statement in PHP4, the trick here 
     is to suppress any parser errors while a function is 
     built and then run the function if it got made. */
  $f = @create_function( '', "return {$json};" );
  $r = ($f) ? $f() : null;

  // free mem (shouldn't really be needed, but it's polite)
  unset( $s2m ); unset( $m2s ); unset( $f );

  return $r;
}

/**
 * Encodes a PHP variable into a JSON string.
 * @param mixed $value A PHP variable to be encoded.
 */
function toJSON ( $value ) {

  if ($value === null) { return 'null'; };  // gettype fails on null?

  $out = '';
  $esc = "\"\\/\n\r\t" . chr( 8 ) . chr( 12 );  // escaped chars
  $l   = '.';  // decimal point
  
  switch ( gettype( $value ) ) 
  {
    case 'boolean':
      $out .= $value ? 'true' : 'false';
      break;
      
    case 'float':
    case 'double':
      // PHP uses the decimal point of the current locale but JSON expects %x2E
      $l = localeconv();
      $l = $l['decimal_point'];
      // fallthrough...

    case 'integer':
      $out .= str_replace( $l, '.', $value );  // what, no getlocale?
      break;

    case 'array':
      // if array only has numeric keys, and is sequential... ?
      for ($i = 0; ($i < count( $value ) && isset( $value[$i]) ); $i++);
      if ($i === count($value)) {
        // it's a "true" array... or close enough
        $out .= '[' . implode(',', array_map('toJSON', $value)) . ']';
        break;
      }
      // fallthrough to object for associative arrays... 

    case 'object':
      $arr = is_object($value) ? get_object_vars($value) : $value;
      $b = array();
      foreach ($arr as $k => $v) {
        $b[] = '"' . addcslashes($k, $esc) . '":' . toJSON($v);
      }
      $out .= '{' . implode( ',', $b ) . '}';
      break;

    default:  // anything else is treated as a string
      return '"' . addcslashes($value, $esc) . '"';
      break;
  }
  return $out;
  
}