<?php

namespace App\Routes;

use App\Controllers\AuthController;
use App\Controllers\TaskController;
use App\Middleware\AuthMiddleware;
use App\Controllers\UserController;
use App\Controllers\SummaryController;

class Web
{
    public static function route($uri)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($uri) {

            /*
            |--------------------------------------------------------------------------
            | AUTH
            |--------------------------------------------------------------------------
            */

            case '/login':
                if ($method === 'GET') {
                    (new AuthController())->showLogin();
                } elseif ($method === 'POST') {
                    (new AuthController())->login();
                }
                break;

            case '/logout':
                AuthMiddleware::check();
                (new AuthController())->logout();
                break;

            case '/register':
                if ($method === 'GET') {
                    (new AuthController())->showRegister();
                } elseif ($method === 'POST') {
                    (new AuthController())->register();
                }
                break;


            case '/users':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                (new UserController())->index();
                break;

            case '/users/store':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                (new UserController())->store();
                break;

            case '/users/update':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                (new UserController())->update();
                break;

            case '/users/delete':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                (new UserController())->delete();
                break;

            case '/users/change-password':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                (new UserController())->changePassword();
                break;

            case '/user-new':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                if ($method === 'GET') {
                    (new UserController())->userNew();
                } elseif ($method === 'POST') {
                    (new UserController())->registerUserNew();
                }
                break;


            /*
            |--------------------------------------------------------------------------
            | TASK
            |--------------------------------------------------------------------------
            */

            case '/create-task':
                AuthMiddleware::check();

                if ($method === 'GET') {
                    (new TaskController())->index();
                } elseif ($method === 'POST') {
                    (new TaskController())->store();
                }
                break;

            case '/board':
                AuthMiddleware::check();
                (new \App\Controllers\BoardController())->index();
                break;

            case '/api/board':
                AuthMiddleware::check();
                (new \App\Controllers\BoardController())->list();
                break;

            case '/api/board/move':
                AuthMiddleware::check();
                (new \App\Controllers\BoardController())->move();
                break;

            case '/task/detail':
                AuthMiddleware::check();

                if ($method === 'GET') {
                    (new TaskController())->detail();
                }
                if ($method === 'POST') {
                    (new \App\Controllers\BoardController())->assign();
                }
                break;

            case '/api/board/get-transitions':
                AuthMiddleware::check();
                (new \App\Controllers\BoardController())->getTransitions();
                break;

            case '/api/board/assign':
                AuthMiddleware::check();
                (new \App\Controllers\BoardController())->assign();
                break;

            case '/task/update-description':
                AuthMiddleware::check();

                if ($method === 'POST') {
                    (new TaskController())->updateDescription();
                }
                break;

            case '/task/upload-image':
                AuthMiddleware::check();

                if ($method === 'POST') {
                    (new TaskController())->uploadImage();
                }
                break;

            case '/attachment-proxy':
                AuthMiddleware::check();
                (new \App\Controllers\AttachmentController())->proxy();
                break;

            case '/task/labels':
                AuthMiddleware::check();
                if ($method === 'GET') {
                    (new TaskController())->getLabels();
                }
                break;

            case '/api/task/update-summary':
                AuthMiddleware::check();
                if ($method === 'POST') {
                    (new TaskController())->updateSummary();
                }
                break;

            case '/task/update-due-date':
                AuthMiddleware::check();
                (new TaskController())->updateDueDate();
                break;

            case '/task/delete':
                AuthMiddleware::check();
                (new TaskController())->delete();
                break;

//            case '/debug-transition':
//                AuthMiddleware::check();
//                (new TaskController())->debugTransition();
//                break;

            case '/summary':
                AuthMiddleware::check();

                if ($method === 'GET') {
                    (new SummaryController())->index();
                }
                break;

            case '/api/summary':
                AuthMiddleware::check();

                if ($method === 'GET') {
                    (new SummaryController())->getSummary();
                }
                break;

            case '/api/summary/clear':
                AuthMiddleware::check();
                (new SummaryController())->clearCache();
                break;


            /*
            |--------------------------------------------------------------------------
            | DEFAULT
            |--------------------------------------------------------------------------
            */

            default:
                http_response_code(404);
                echo "404 - Page Not Found";
                break;
        }


    }
}