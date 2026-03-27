<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use App\Routes\Web;

require_once __DIR__ . '/../app/Helpers/adf.php';

/*
|--------------------------------------------------------------------------
| Clean URI
|--------------------------------------------------------------------------
*/

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/*
   Remove trailing slash (trừ root)
*/
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

/*
|--------------------------------------------------------------------------
| Handle root "/"
|--------------------------------------------------------------------------
*/

if ($uri === '/') {
    if (isset($_SESSION['user'])) {
        header('Location: /board');
    } else {
        header('Location: /login');
    }
    exit;
}

/*
|--------------------------------------------------------------------------
| Run Router
|--------------------------------------------------------------------------
*/

Web::route($uri);