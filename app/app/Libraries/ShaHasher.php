<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 08.02.2018
 * Time: 16:10
 */

namespace App\Libraries;


use Illuminate\Contracts\Hashing\Hasher;

class ShaHasher implements Hasher
{
    public function make($value, array $options = [])
    {
        $hash = hash('sha1', $value);

        if ($hash === false) {
            throw new \RuntimeException('Sha1 hashing not supported.');
        }

        return $hash;
    }

    public function check($value, $hashedValue, array $options = [])
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

       return hash_equals($hashedValue, hash('sha1', $value));
    }

    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }

    public function setRounds()
    {

    }


}