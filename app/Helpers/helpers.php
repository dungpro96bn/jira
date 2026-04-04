<?php

function random () {
    $min = 1;
    $max = 999999999;
    $randomNumber = rand($min, $max);
    echo $randomNumber;
}

function title_page(){
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $currentUrl = $scheme . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $url = $currentUrl;
    $parsedUrl = parse_url($url);
    $path = $parsedUrl['path'];

    if($path == '/'){
        echo "Create Tasks On Jira";
    } elseif ($path == '/login'){
        echo "Login";
    } elseif ($path == '/board'){
        echo "Board";
    } elseif ($path == '/register'){
        echo "Register";
    } elseif ($path == '/create-task'){
        echo "Create Task";
    } elseif ($path == '/users'){
        echo "Users";
    } elseif ($path == '/user-new'){
        echo "Add New User";
    } elseif ($path == '/summary'){
        echo "Summary";
    } elseif ($path == '/dashboard'){
        echo "Dashboard";
    }

}