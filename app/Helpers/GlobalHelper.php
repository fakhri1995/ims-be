<?php

/**
 * automatic DB raw decryption
 * @return Query
 */

function _rQ($var)
{
    return \DB::raw('aes_decrypt(from_base64(' . $var . '),"' . config('encrypt.key') . '")');
}

/**
 * automatic set decryption
 * @return String
 */
function _sQ($var)
{
    return 'aes_decrypt(from_base64(' . $var . '),"' . config('encrypt.key') . ')';
}
