<?php

// TODO выглядит паршиво

function gRanStr($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

function pr($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function prf($data, $filenamePostfix = '') {
    ob_start();
    var_dump($data);
    file_put_contents(RK_ROOT_PATH . '/../logs/dump/' . date('Ymd_His') . '-' . $filenamePostfix . '.dmp', ob_get_clean(), FILE_APPEND);
}

// Возвращает отформатированный XML
function prettyXML($xml, $version = '1.0') {
    if (empty($xml)) {
        return null;
    }

    libxml_use_internal_errors(true);

    $dom = new \DOMDocument($version);
    $dom->preserveWhiteSpace = false;
    $dom->loadXML($xml);
    $dom->formatOutput = true;

    $errors = libxml_get_errors();
    libxml_clear_errors();

    if (empty($errors)) {
        return $dom->saveXML();
    }

    return array('error' => $errors, 'xml' => $xml);
}

// Вывод отформатированного XML
function prXML($xml) {
    pr(prettyXML($xml));
}

// Генератор строки Base64UUID
function createBase64UUID() {
    $uuid = getGUID();
    $byteString = "";

    // Удаление
    $uuid = str_replace("-", "", $uuid);

    // Remove the opening and closing brackets
    $uuid = substr($uuid, 1, strlen($uuid) - 2);

    // Read the UUID string byte by byte
    for ($i = 0; $i < strlen($uuid); $i += 2) {
        // Get two hexadecimal characters
        $s = substr($uuid, $i, 2);
        // Convert them to a byte
        $d = hexdec($s);
        // Convert it to a single character
        $c = chr($d);
        // Append it to the byte string
        $byteString = $byteString . $c;
    }

    // Convert the byte string to a base64 string
    $b64uuid = base64_encode($byteString);
    // Replace the "/" and "+" since they are reserved characters
    //$b64uuid = str_replace("/", "_", $b64uuid);
    //$b64uuid = str_replace("+", "-", $b64uuid);
    // Remove the trailing "=="
    //$b64uuid = substr($b64uuid, 0, strlen($b64uuid) - 2);

    return $b64uuid;
}

function getGUID() {
    if (function_exists('com_create_guid')) {
        return com_create_guid();

    } else {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8) . $hyphen
            .substr($charid, 8, 4) . $hyphen
            .substr($charid,12, 4) . $hyphen
            .substr($charid,16, 4) . $hyphen
            .substr($charid,20,12)
            .chr(125);// "}"

        return $uuid;
    }
}

if (!function_exists('WriteLog')) {
    function WriteLog($a = null, $b = null, $c = null, $d = null) {
        return 1;
    }
}