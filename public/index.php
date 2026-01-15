<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SellNow\Config\Database;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

session_start();

// Basic Twig Setup (Global-ish)
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['debug' => true]);
$twig->addGlobal('session', $_SESSION);

// Database Connection
$db = Database::getInstance()->getConnection();

// Basic Routing (Switch Statement)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Simple helper for redirection
function redirect($url)
{
    header("Location: {$url}");
    exit;
}

// Controller factory (reduces duplication)
function controller (string $class, $twig, $db) 
{
    $fqcn = "\\SellNow\\Controllers\\{$class}";
    return new $fqcn($twig, $db);
}

// Router
switch ($uri) {

    case '/':
        echo $twig->render('layouts/base.html.twig', [
            'content' => "<h1>Welcome</h1><a href='/login'>Login</a>"
        ]);
        break;

    case '/login':
        $auth = controller('AuthController', $twig, $db);
        $method === 'POST' ? $auth->login() : $auth->loginForm();
        break;

    case '/register':
        $auth = controller('AuthController', $twig, $db);
        $method === 'POST' ? $auth->register() : $auth->registerForm();
        break;

    case '/logout':
        session_destroy();
        redirect('/');
        break;

    case '/dashboard':
        controller('AuthController', $twig, $db)->dashboard();
        break;

    case '/products/add':
        $product = controller('ProductController', $twig, $db);
        $method === 'POST' ? $product->store() : $product->create();
        break;

    case '/cart':
        controller('CartController', $twig, $db)->index();
        break;

    case '/cart/add':
        controller('CartController', $twig, $db)->add();
        break;

    case '/cart/clear':
        controller('CartController', $twig, $db)->clear();
        break;

    case '/checkout':
        controller('CheckoutController', $twig, $db)->index();
        break;

    case '/checkout/process':
        controller('CheckoutController', $twig, $db)->process();
        break;

    case '/payment':
        controller('CheckoutController', $twig, $db)->payment();
        break;

    case '/checkout/success':
        controller('CheckoutController', $twig, $db)->success();
        break;

    default:
        /*
         * Dynamic public routes:
         * /{username}
         * /{username}/products
         */
        $parts = explode('/', trim($uri, '/'));

        if (count($parts) == 1 && $parts[0] !== '') {
            controller('PublicController', $twig, $db)->profile($parts[0]);
            break;
        }

        if (count($parts) == 2 && $parts[1] == 'products') {
            redirect('/' . $parts[0]);
        }

        http_response_code(404);
        echo "404 Not Found";
        break;
}

