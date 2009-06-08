<?php

if (!function_exists('snap_blowfish_encrypt')) {
    
    require_once 'lib.php';
    
    function snap_blowfish_encrypt($string, $key) {
        return snap_blowfish_out(Blowfish::encrypt($string, $key));
    }

    function snap_blowfish_decrypt($string, $key) {
        return Blowfish::decrypt(snap_blowfish_in($string), $key);
    }
    
    function snap_blowfish_out($string) {
        return str_replace('%', '-', rawurlencode(base64_encode($string)));
    }
    
    function snap_blowfish_in($string) {
        return base64_decode(rawurldecode(str_replace('-', '%', $string)));
    }
}
