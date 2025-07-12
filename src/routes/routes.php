<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../contollers/AuthController.php';
require_once __DIR__ . '/../contollers/UserController.php';

function route($path) {
    $authController = new AuthController();
    $userController = new UserController();

    // Remove query parameters (e.g., ?key=value)
    $path = parse_url($path, PHP_URL_PATH);

    switch ($path) {
        case '/':
        case '/login':
            $authController->showLogin();
            break;
        case '/register':
            $authController->showRegister();
            break;
        case '/login-action':
            $authController->login();
            break;
        case '/register-action':
            $authController->register();
            break;
        case '/logout':
            $authController->logout();
            break;
        case '/dashboard':
            $userController->dashboard();
            break;
        case '/admin':
            $userController->adminDashboard();
            break;
        case '/profile-update':
            $userController->profileUpdate();
            break;
        case '/password-update':
            $userController->passwordUpdate();
            break;
        case '/admin-action':
            $userController->adminAction();
            break;
        default:
            http_response_code(404);
            echo '404 Not Found';
    }
}
?>