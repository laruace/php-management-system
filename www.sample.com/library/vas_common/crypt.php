<?php
/**
 * PPLive 3DES加密算法
 *
 * @param string $input	需要加密的数据
 * @param string $key	密钥
 * @param string $iv	偏移向量
 * @return string
 */
function pplive_3des_encrypt($input, $key, $iv)
{
    $key = pack('H48', $key);
    $iv = pack('H16', $iv);
    //PaddingPKCS7补位
    $srcdata = $input;
    $block_size = mcrypt_get_block_size('tripledes', 'ecb');
    $padding_char = $block_size - (strlen($input) % $block_size);
    $srcdata .= str_repeat(chr($padding_char), $padding_char);
    return mcrypt_encrypt(MCRYPT_3DES, $key, $srcdata, MCRYPT_MODE_CBC, $iv);
}

/**
 * PPLive 3DES解密算法
 *
 * @param string $input	需要解密的数据
 * @param string $key	密钥
 * @param string $iv	偏移向量
 * @return string
 */
function pplive_3des_decrypt($input, $key, $iv)
{
    $key = pack('H48', $key);
    $iv = pack('H16', $iv);
    $result = mcrypt_decrypt(MCRYPT_3DES, $key, $input, MCRYPT_MODE_CBC, $iv);
    $end = ord(substr($result, - 1));
    $out = substr($result, 0, - $end);
    return $out;
}

function getStaticKey($index=1)
{
    $index = (int)$index - 1;
    static $array = array(
        '15B9FDAEDA40F86BF71C73292516924A294FC8BA31B6E9EA',
        '29028A7698EF4C6D3D252F02F4F79D5815389DF18525D326',
        'D046E6B6A4A85EB6C44C73372A0D5DF1AE76405173B3D5EC',
        '435229C8F79831131923F18C5DE32F253E2AF2AD348C4615',
        '9B2915A72F8329A2FE6B681C8AAE1F97ABA8D9D58576AB20',
        'B3B0CD830D92CB3720A13EF4D93B1A133DA4497667F75191',
        'AD327AFB5E19D023150E382F6D3B3EB5B6319120649D31F8',
        'C42F31B008BF257067ABF115E0346E292313C746B3581FB0',
        '529B75BAE0CE2038466704A86D985E1C2557230DDF311ABC',
        '8A529D5DCE91FEE39E9EE9545DF42C3D9DEC2F767C89CEAB'
    );
    return $array[$index];
}


/**
 * BASE32编码
 * @param string $input     需要解码的字符串
 * @return string           解码后的字符串
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

