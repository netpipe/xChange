<?php
//php-url-shortener
require_once "config.php";
//https://dev.to/manuthecoder/really-simple-encryption-in-php-3kk9
define("encryption_method", "AES-128-CBC");
define("key", "test");
function encrypt($data) {
    $key = key;
    $plaintext = $data;
    $ivlen = openssl_cipher_iv_length($cipher = encryption_method);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
    return $ciphertext;
}
function decrypt($data) {
    $key = key;
    $c = base64_decode($data);
    $ivlen = openssl_cipher_iv_length($cipher = encryption_method);
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len = 32);
    $ciphertext_raw = substr($c, $ivlen + $sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    if (hash_equals($hmac, $calcmac))
    {
        return $original_plaintext;
    }
}


function encryptAES($plaintext, $password) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return $iv . $hash . $ciphertext;
}

function decryptAES($ivHashCiphertext, $password) {
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}


function getUniqueRandomString($length) : string {

    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    if (!randomStringIsUnique($randomString)) {
        return getUniqueRandomString($length);
    }
    return $randomString;
}

function randomStringIsUnique($key) : bool {

    $conn = getConnection();
    $sql = "SELECT key FROM link WHERE key = :key";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':key', $key);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['key'] === $key) {
            return false;
        }
    }
    return true;
}

function goToUrl($key) {

    $conn = getConnection();
    $sql = "SELECT * FROM link WHERE key = :key LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':key', $key);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($row['key']) && $row['key'] === $key) {

        $sql = "UPDATE link Set last_access = :last_access, hits = hits + 1 WHERE key = :key";
        $stmt = $conn->prepare($sql);
        $last_access = date('Y-m-d H:i:s');
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':last_access', $last_access);
        $stmt->execute();
        header("Location: " . str_replace('||', 'a', decrypt($row['url'])), true, 302);
      //  header("Location: " . decrypt($row['url']), true, 302);
        exit(); // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    }

}

function urlAlreadyShortened($url) : bool {

    $conn = getConnection();
    $sql = "SELECT key, url FROM link WHERE url = :url";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':url', $url);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['url'] === $url) {
            header("Location: " . BASE_URL . "/php-url-shortener-main/index.php?slug=" . $row['key']);
            exit();
        }
    }
    return false;
}

function urlIsCorrect($url) : bool {

    $startsWithHttp = preg_match("/^(https?:\/\/)(\S*)$/m", trim($url));
    return $startsWithHttp === 1;
}

function slugMeetsRequirements($slug) : bool {

    return strlen(trim($slug, "/")) >= SLUG_LEN;
}

function getConnection() : PDO {

    try {
        $conn = new PDO("sqlite:" . __DIR__ . "/data.sqlite");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;

    } catch (PDOException $e) {
        throw new RuntimeException("Can not connect.");
    }
}

function addUrlToDatabase($u) : string {

    $key = getUniqueRandomString(SLUG_LEN);
    $url = encrypt(substr($u, 0, 2048));
    $created_at = date('Y-m-d H:i:s');

    $conn = getConnection();
    $sql = "INSERT INTO link (key, url, created_at, lifetime, last_access, hits) 
            VALUES (:key, :url, :created_at, null, null, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':key', $key);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':created_at', $created_at);
    $stmt->execute();

    return $key;
}
