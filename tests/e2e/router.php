<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Routeur du serveur PHP intégré : fichiers de public/ servis tels quels, le reste par Symfony
 */

$file = dirname(__DIR__, 2) . '/public' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file($file)) {
    return false;
}
// Le serveur intégré ne recopie pas l'environnement dans $_SERVER, où Symfony lit ses variables (DATABASE_URL en CI)
$_SERVER += getenv();
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__, 2) . '/public/index.php';
require $_SERVER['SCRIPT_FILENAME'];
