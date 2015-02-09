<?php

/**
 * 编码和加解密应用函数库
 * 
 * @name		Encode
 * @package     library
 * @subpackage  encode
 * @version 	1.0
 * @author		hfcorriez@gmail.com
 * @copyright 	2010 PPTV.COM
 */

/**
 * 3DES加密算法
 *   
 * @param string $input	需要加密的数据
 * @param string $key	密钥
 * @param string $iv	偏移向量
 * @return string
 */
function des_encrypt($input, $key, $iv) {
    $key = pack('H48', $key);
    $iv = pack('H16', $iv);
    //PaddingPKCS7补位
    $block_size = mcrypt_get_block_size('tripledes', 'ecb');
    $padding_char = $block_size - (strlen($input) % $block_size);
    $srcdata .= str_repeat(chr($padding_char), $padding_char);
    return mcrypt_encrypt(MCRYPT_3DES, $key, $srcdata, MCRYPT_MODE_CBC, $iv);
}

/**
 * 3DES解密算法
 *        
 * @param string $input	需要解密的数据
 * @param string $key	密钥
 * @param string $iv	偏移向量
 * @return string
 */
function des_decrypt($input, $key, $iv) {
    $key = pack('H48', $key);
    $iv = pack('H16', $iv);
    $result = mcrypt_decrypt(MCRYPT_3DES, $key, $input, MCRYPT_MODE_CBC, $iv);
    $end = ord(substr($result, - 1));
    $out = substr($result, 0, - $end);
    return $out;
}

/**
 * BASE64加密算法       
 *
 * @param string $data 需要加密的数据
 * @param string $key  密钥
 * @return string
 */
function base64_encrypt($data, $key) {
    $key = md5($key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l)
            $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return $str;
}

/**
 * BASE64解密算法
 *
 * @param string $data 需要解密的数据
 * @param string $key  密钥
 * @return string
 */
function base64_decrypt($data, $key) {
    $key = md5($key);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l)
            $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * XXTEA加密算法
 *
 * @param string $str 需要加密的数据
 * @param string $key 密钥
 * @return string 
 */
function xxtea_encrypt($str, $key) {
    if ($str == "") {
        return "";
    }
    $v = str2long($str, true);
    $k = str2long($key, false);
    $n = count($v) - 1;

    $z = $v [$n];
    $y = $v [0];
    $delta = 0x9E3779B9;
    $q = floor(6 + 52 / ($n + 1));
    $sum = 0;
    while (0 < $q--) {
        $sum = int32($sum + $delta);
        $e = $sum >> 2 & 3;
        for ($p = 0; $p < $n; $p++) {
            $y = $v [$p + 1];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
            $z = $v [$p] = int32($v [$p] + $mx);
        }
        $y = $v [0];
        $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
        $z = $v [$n] = int32($v [$n] + $mx);
    }
    return long2str($v, false);
}

/**
 * XXTEA解密算法
 *
 * @param string $str 需要解密的数据
 * @param string $key 密钥
 * @return string 
 */
function xxtea_decrypt($str, $key) {
    if ($str == "") {
        return "";
    }
    $v = str2long($str, false);
    $k = str2long($key, false);
    $n = count($v) - 1;

    $z = $v [$n];
    $y = $v [0];
    $delta = 0x9E3779B9;
    $q = floor(6 + 52 / ($n + 1));
    $sum = int32($q * $delta);
    while ($sum != 0) {
        $e = $sum >> 2 & 3;
        for ($p = $n; $p > 0; $p--) {
            $z = $v [$p - 1];
            $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
            $y = $v [$p] = int32($v [$p] - $mx);
        }
        $z = $v [$n];
        $mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
        $y = $v [0] = int32($v [0] - $mx);
        $sum = int32($sum - $delta);
    }
    return long2str($v, true);
}

/**
 * PPLive加密算法
 *
 * @param string $str   需要加密的数据
 * @return string $key  密钥
 */
function pplive_encrypt($str, $key) {
    $keylen = strlen($key);
    $length = strlen($str);
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= chr(ord($str {$i}) + ord($key {$i % $keylen}));
    }
    return $result;
}

/**
 * PPLive解密算法
 *
 * @param string $str   需要解密的数据
 * @return string $key  密钥
 */
function pplive_decrypt($str, $key) {
    $keylen = strlen($key);
    $length = strlen($str);
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= chr(ord($str {$i}) - ord($key {$i % $keylen}));
    }
    return $result;
}

function long2str($v, $w) {
    $len = count($v);
    $s = array();
    for ($i = 0; $i < $len; $i++) {
        $s [$i] = pack("V", $v [$i]);
    }
    if ($w) {
        return substr(join('', $s), 0, $v [$len - 1]);
    } else {
        return join('', $s);
    }
}

function str2long($s, $w) {
    $v = unpack("V*", $s . str_repeat("\0", (4 - strlen($s) % 4) & 3));
    $v = array_values($v);
    if ($w) {
        $v [count($v)] = strlen($s);
    }
    return $v;
}

function int32($n) {
    while ($n >= 2147483648)
        $n -= 4294967296;
    while ($n <= - 2147483649)
        $n += 4294967296;
    return (int) $n;
}

/**
 * PP播放ID加密
 *
 * @param string $input		播放ID
 * @return string			加密后的ID
 */
function ppid_encode($input) {
    if (!$input)
        return '';
    return base32_encode(pplive_encrypt($input, 'ikan'));
}

/**
 * PP播放ID解密
 *
 * @param string $input		加密后的ID
 * @return string			播放ID
 */
function ppid_decode($input) {
    $id = '';
    if (!is_numeric($input) && $input) {
        $sid = pplive_decrypt(base64_decode($input), 'pplive');
        if (!is_numeric($sid)) {
            $sid = pplive_decrypt(base32_decode($input), 'ikan');
            if (is_numeric($sid)) {
                $id = $sid;
            }
        } else {
            $id = $sid;
        }
    } elseif (is_numeric($input)) {
        $id = $input;
    }
    return $id;
}

/**
 * BASE32编码
 * @param string $input     需要编码的字符串
 * @return string           编码后的字符串
 */
function base32_encode($input) {
    // Reference: http://www.ietf.org/rfc/rfc3548.txt
    $BASE32_ALPHABET = 'aBcDeFgHiJkLmNoPqRsTuVwXyZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;

    for ($i = 0, $j = strlen($input); $i < $j; $i++) {
        $v <<= 8;
        $v += ord($input [$i]);
        $vbits += 8;

        while ($vbits >= 5) {
            $vbits -= 5;
            $output .= $BASE32_ALPHABET [$v >> $vbits];
            $v &= ( (1 << $vbits) - 1);
        }
    }

    if ($vbits > 0) {
        $v <<= ( 5 - $vbits);
        $output .= $BASE32_ALPHABET [$v];
    }

    return $output;
}

/**
 * BASE32解码
 * @param string $input     需要解码的字符串
 * @return string           解码后的字符串
 */
function base32_decode($input) {
    $output = '';
    $v = 0;
    $vbits = 0;
    $input = strtolower($input);

    for ($i = 0, $j = strlen($input); $i < $j; $i++) {
        $v <<= 5;
        if ($input [$i] >= 'a' && $input [$i] <= 'z') {
            $v += ( ord($input [$i]) - 97);
        } elseif ($input [$i] >= '2' && $input [$i] <= '7') {
            $v += ( 24 + $input [$i]);
        } else {
            exit(1);
        }

        $vbits += 5;
        while ($vbits >= 8) {
            $vbits -= 8;
            $output .= chr($v >> $vbits);
            $v &= ( (1 << $vbits) - 1);
        }
    }
    return $output;
}

/**
 * BASE62编码
 * @param string $value
 * @return string
 */
function base62_encode($value) {
    return dec2base(base2dec($value, 256), 62);
}

/**
 * BASE62解码
 * @param string $value
 * @return string
 */
function base62_decode($value) {
    return dec2base(base2dec($value, 62), 256);
}

function dec2base($dec, $base, $digits = FALSE) {
    if ($base < 2 or $base > 256) {
        die("Invalid Base: .$base\n");
    }
    bcscale(0);
    $value = '';
    if (!$digits) {
        $digits = digits($base);
    }
    while ($dec > $base - 1) {
        $rest = bcmod($dec, $base);
        $dec = bcdiv($dec, $base);
        $value = $digits[$rest] . $value;
    }
    $value = $digits[intval($dec)] . $value;
    return (string) $value;
}

function base2dec($value, $base, $digits = FALSE) {
    if ($base < 2 or $base > 256) {
        die("Invalid Base: .$base\n");
    }
    bcscale(0);
    if ($base < 37) {
        $value = strtolower($value);
    }
    if (!$digits) {
        $digits = digits($base);
    }
    $size = strlen($value);
    $dec = '0';
    for ($loop = 0; $loop < $size; $loop++) {
        $element = strpos($digits, $value[$loop]);
        $power = bcpow($base, $size - $loop - 1);
        $dec = bcadd($dec, bcmul($element, $power));
    }
    return (string) $dec;
}

function digits($base) {
    if ($base < 64) {
        return substr('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_', 0, $base);
    } else {
        return substr("\x0\x1\x2\x3\x4\x5\x6\x7\x8\x9\xa\xb\xc\xd\xe\xf\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f !\x22#\x24%&'()*+,-./0123456789:;<=>\x3f@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~\x7f\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f\xa0\xa1\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xab\xac\xad\xae\xaf\xb0\xb1\xb2\xb3\xb4\xb5\xb6\xb7\xb8\xb9\xba\xbb\xbc\xbd\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe\xff", 0, $base);
    }
}

/**
 * PPLive混淆加密算法
 *
 * @param string $str   需要加密的数据
 * @return string $key  密钥
 */
function pplive_encrypt2($str, $key) {
    $keylen = strlen($key);
    $length = strlen($str);
    $result = '';
    $byte = array();
    $result = substr($str, 0, 1);

    for ($i = 0; $i < $length; $i++)
        $byte[$i] = char2byte($str{$i});

    for ($i = 1; $i < $length; $i++) {
        $byte[$i] = ($byte[$i] ^ $byte[$i - 1]) + char2byte($key {$i % $keylen});
        $result .= byte2char($byte[$i]);
    }
    return $result;
}

/**
 * PPLive混淆解密算法
 *
 * @param string $str   需要解密的数据
 * @return string $key  密钥
 */
function pplive_decrypt2($str, $key) {
    $keylen = strlen($key);
    $length = strlen($str);
    $result = '';
    $byte = array();
    $result = '';

    for ($i = 0; $i < $length; $i++)
        $byte[$i] = char2byte($str{$i});

    for ($i = $length - 1; $i > 0; $i--) {
        $byte[$i] = ($byte[$i] - char2byte($key {$i % $keylen})) ^ $byte[$i - 1];
        $result = byte2char($byte[$i]) . $result;
    }
    $result = substr($str, 0, 1) . $result;
    return $result;
}

/**
 * 将单个字节字符数据转换成字节值
 * @param string $char
 * @return int
 */
function char2byte($char) {
    return (int) array_pop(unpack('c', $char));
}

/**
 * 将单个字节值转换成字符数据
 * @param int $byte
 * @return string
 */
function byte2char($byte) {
    return pack('c', $byte);
}

/**
 * 16进制转成二进制
 * @param hex $h
 * @return string
 */
function hex2bin($h) {
    if (!is_string($h))
        return null;
    $r = '';
    for ($a = 0; $a < strlen($h); $a+=2) {
        $r.=chr(hexdec($h{$a} . $h{($a + 1)}));
    }
    return $r;
}

?>