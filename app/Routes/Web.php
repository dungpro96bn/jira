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

            case '/waiting-approval':
                AuthMiddleware::check();

                if ($method === 'GET') {
                    (new AuthController())->waiting();
                }
                break;


            // admin permission
            //===============================
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

            case '/summary':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                if ($method === 'GET') {
                    (new SummaryController())->index();
                }
                break;

            case '/api/summary':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                if ($method === 'GET') {
                    (new SummaryController())->getSummary();
                }
                break;

            case '/api/summary/clear':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                (new SummaryController())->clearCache();
                break;

            case '/api/summary/priority':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                (new SummaryController())->getPriority();
                break;

            case '/api/summary/workload':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                if ($method === 'GET') {
                    (new SummaryController())->getWorkload();
                }
                break;

            case '/api/summary/types':
                AuthMiddleware::check();
                \App\Helpers\Role::check(['admin']);

                if ($method === 'GET') {
                    (new SummaryController())->getTypesTask();
                }
                break;




            /*
            |--------------------------------------------------------------------------
            | TASK
            |--------------------------------------------------------------------------
            */

            case '/create-task':
                AuthMiddleware::checkAccess();

                if ($method === 'GET') {
                    (new TaskController())->index();
                } elseif ($method === 'POST') {
                    (new TaskController())->store();
                }
                break;

            case '/board':
                AuthMiddleware::checkAccess();
                (new \App\Controllers\BoardController())->index();
                break;

            case '/api/board':
                AuthMiddleware::checkAccess();
                (new \App\Controllers\BoardController())->list();
                break;

            case '/api/board/move':
                AuthMiddleware::checkAccess();
                (new \App\Controllers\BoardController())->move();
                break;

            case '/task/detail':
                AuthMiddleware::checkAccess();

                if ($method === 'GET') {
                    (new TaskController())->detail();
                }
                if ($method === 'POST') {
                    (new \App\Controllers\BoardController())->assign();
                }
                break;

            case '/api/board/get-transitions':
                AuthMiddleware::checkAccess();
                (new \App\Controllers\BoardController())->getTransitions();
                break;

            case '/api/board/assign':
                AuthMiddleware::checkAccess();
                (new \App\Controllers\BoardController())->assign();
                break;

            case '/task/update-description':
                AuthMiddleware::checkAccess();

                if ($method === 'POST') {
                    (new TaskController())->updateDescription();
                }
                break;

            case '/task/upload-image':
                AuthMiddleware::checkAccess();

                if ($method === 'POST') {
                    (new TaskController())->uploadImage();
                }
                break;

            case '/attachment-proxy':
                AuthMiddleware::checkAccess();
                (new \App\Controllers\AttachmentController())->proxy();
                break;

            case '/task/labels':
                AuthMiddleware::checkAccess();
                if ($method === 'GET') {
                    (new TaskController())->getLabels();
                }
                break;

            case '/api/task/update-summary':
                AuthMiddleware::checkAccess();
                if ($method === 'POST') {
                    (new TaskController())->updateSummary();
                }
                break;

            case '/task/update-due-date':
                AuthMiddleware::checkAccess();
                (new TaskController())->updateDueDate();
                break;

            case '/task/delete':
                AuthMiddleware::checkAccess();
                (new TaskController())->delete();
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