<?php
namespace lay\util;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 工具类
 *
 * @author Lay Li
 */
class Util {
    private static $_IsWindows;
    public static function isWindows() {
        if(! is_bool(self::$_IsWindows)) {
            self::$_IsWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        }
        return self::$_IsWindows;
    }
    public static function isAbsolutePath($path) {
        return false;
    }
    /**
     * php array to php content
     *
     * @param array $arr
     *            convert array
     * @param boolean $encrypt
     *            if encrypt
     * @return string
     */
    public static function array2PHPContent($arr, $encrypt = false) {
        if($encrypt) {
            $r = '';
            $r .= self::array2String($arr);
        } else {
            $r = "<?php return ";
            self::a2s($r, $arr);
            $r .= ";?>\r\n";
        }
        return $r;
    }
    /**
     * convert a multidimensional array to url save and encoded string
     *
     * 在Array和String类型之间转换，转换为字符串的数组可以直接在URL上传递
     *
     * @param array $Array
     *            convert array
     */
    public static function array2String($Array) {
        $Return = '';
        $NullValue = "^^^";
        foreach($Array as $Key => $Value) {
            if(is_array($Value))
                $ReturnValue = '^^array^' . self::array2String($Value);
            else
                $ReturnValue = (strlen($Value) > 0) ? $Value : $NullValue;
            $Return .= urlencode(base64_encode($Key)) . '|' . urlencode(base64_encode($ReturnValue)) . '||';
        }
        return urlencode(substr($Return, 0, - 2));
    }
    /**
     * convert a string generated with Array2String() back to the original (multidimensional) array
     *
     * @param string $String
     *            convert string
     */
    public static function string2Array($String) {
        $Return = array();
        $String = urldecode($String);
        $TempArray = explode('||', $String);
        $NullValue = urlencode(base64_encode("^^^"));
        foreach($TempArray as $TempValue) {
            list($Key, $Value) = explode('|', $TempValue);
            $DecodedKey = base64_decode(urldecode($Key));
            if($Value != $NullValue) {
                $ReturnValue = base64_decode(urldecode($Value));
                if(substr($ReturnValue, 0, 8) == '^^array^')
                    $ReturnValue = self::string2Array(substr($ReturnValue, 8));
                $Return[$DecodedKey] = $ReturnValue;
            } else {
                $Return[$DecodedKey] = NULL;
            }
        }
        return $Return;
    }
    /**
     * array $a to string $r
     *
     * @param string $r
     *            output string pointer address
     * @param array $a
     *            input array pointer address
     * @return void
     */
    public static function a2s(&$r, array &$a, $l = "", $b = "    ") {
        $f = false;
        $h = false;
        $i = 0;
        $r .= 'array(' . "\r\n";
        foreach($a as $k => $v) {
            if(! $h)
                $h = array(
                        'k' => $k,
                        'v' => $v
                );
            if($f)
                $r .= ',' . "\r\n";
            $j = ! is_string($k) && is_numeric($k) && $h['k'] === 0;
            self::o2s($r, $k, $v, $i, $j, $l, $b);
            $f = true;
            if($j && $k >= $i)
                $i = $k + 1;
        }
        $r .= "\r\n$l" . ')';
    }
    /**
     * to string $r
     *
     * @param string $r
     *            output string pointer address
     * @param string $k            
     * @param string $v            
     * @param string $i            
     * @param string $j            
     * @return void
     */
    private static function o2s(&$r, $k, $v, $i, $j, $l, $b) {
        $isW = self::isWindows();
        if($k !== $i) {
            if($j)
                $r .= "$l$b$k => ";
            else
                $r .= "$l$b'$k' => ";
        } else {
            $r .= "$l$b";
        }
        if(is_array($v))
            self::a2s($r, $v, $l . $b);
        else if(is_numeric($v))
            $r .= "" . $v;
        else
            $r .= "'" . str_replace("'", "\'", $v) . "'";
    }
    
    /**
     * xml format string to php array
     * 
     * @param string $xml xml format string
     * @param bool $simple if use simplexml,default false
     * @return array|bool
     */
    public static function xml2Array($xml,$simple = false) {
        if(!is_string($xml)) {
            return false;
        }
        if($simple) {
            $xml = @simplexml_load_string($xml);
        } else {
            $xml = @json_decode(json_encode((array) simplexml_load_string($xml)),1);
        }
        return $xml;
    }
    /**
     * php array to xml format string
     * 
     * @param array $value convert array
     * @param string $root xml root tag
     * @param string $encoding xml encoding
     * @return string 
     */
    public static function array2XML($value, $encoding='utf-8', $root='root', $nkey = '') {
        if( !is_array($value) && !is_string($value) && !is_bool($value) && !is_numeric($value) && !is_object($value) ) {
            return false;
        }
        $nkey = preg_match('/^[A-Za-z_][A-Za-z0-9\-_]{0,}$/', $nkey)?$nkey:'';
        return simplexml_load_string('<?xml version="1.0" encoding="'.$encoding.'"?>'.self::x2str($value, $root, $nkey))->asXml();
    }
    /**
     * object or array to xml format string
     * 
     * @param object $xml array or object
     * @param string $key tag name
     * @return string 
     */
    private static function x2str($xml, $key, $nkey) {
        if (!is_array($xml) && !is_object($xml)) {
            return "<$key>".htmlspecialchars($xml)."</$key>";      
        }
        
        $xml_str = '';
        foreach ($xml as $k => $v) {  
            if(is_numeric($k)) {
                $k = (($nkey)?$nkey:$key.'-').$k;
            }
            $xml_str .= self::x2str($v, $k, $nkey);
        }    
        return "<$key>$xml_str</$key>"; 
    }
}
?>
