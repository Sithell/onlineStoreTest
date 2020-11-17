<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

include '../lib/utils.php';
include '../routes/auth.php';
include '../routes/getToken.php';
include '../routes/addProduct.php';
include '../routes/getProduct.php';
include '../routes/getUser.php';
include '../routes/getProductsList.php';
include '../routes/getUserProducts.php';
include '../routes/getMyProducts.php';
include '../routes/updateMyRecord.php';
include '../routes/deleteProduct.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = 'root';
$config['db']['dbname'] = 'online_store';

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->get('/auth', function (Request $request, Response $response, array $args) {
    global $container;
    auth($container['db'], $request->getQueryParams()['phone_number']);
    $response->getBody()->write("Сообщение с кодом подтверждения отправлено на указанный номер");
    return $response;
});
$app->get('/get_token', function (Request $request, Response $response, array $args) {
    global $container;
    $phone_number = $request->getQueryParams()['phone_number'];
    $verification_code = $request->getQueryParams()['verification_code'];
    $response->getBody()->write(json_encode(get_token($container['db'], $phone_number, $verification_code)));
    return $response;
});
$app->get('/add_product', function (Request $request, Response $response, array $args) {
    global $container;
    $response->getBody()->write(json_encode(add_product($container['db'],
        $request->getQueryParams()['token'],
        $request->getQueryParams()['name'],
        $request->getQueryParams()['description'],
        $request->getQueryParams()['price']
    )));
    return $response;
});
$app->get('/get_product', function (Request $request, Response $response, array $args) {
    global $container;
    $response->getBody()->write(json_encode(get_product($container['db'],
        $request->getQueryParams()['id']
    )));
    return $response;
});
$app->get('/get_user', function (Request $request, Response $response, array $args) {
    global $container;
    $response->getBody()->write(json_encode(get_user($container['db'],
        $request->getQueryParams()['id']
    )));
    return $response;
});
$app->get('/get_products_list', function (Request $request, Response $response, array $args) {
    global $container;
    if (is_null($request->getQueryParams()['start'])) {
        $start = 0;
    }
    else {
        $start = $request->getQueryParams()['start'];
    }
    if (is_null($request->getQueryParams()['per_page'])) {
        $per_page = 5;
    }
    else {
        $per_page = $request->getQueryParams()['per_page'];
    }
    $response->getBody()->write(json_encode(get_products_list($container['db'],
        $per_page,
        $start
    )));
    return $response;
});
$app->get('/get_user_products', function (Request $request, Response $response, array $args) {
    global $container;
    if (is_null($request->getQueryParams()['start'])) {
        $start = 0;
    }
    else {
        $start = $request->getQueryParams()['start'];
    }
    if (is_null($request->getQueryParams()['per_page'])) {
        $per_page = 5;
    }
    else {
        $per_page = $request->getQueryParams()['per_page'];
    }
    $response->getBody()->write(json_encode(get_user_products($container['db'],
        $request->getQueryParams()['id'],
        $per_page,
        $start
    )));
    return $response;
});
$app->get('/get_my_products', function (Request $request, Response $response, array $args) {
    global $container;
    if (is_null($request->getQueryParams()['start'])) {
        $start = 0;
    }
    else {
        $start = $request->getQueryParams()['start'];
    }
    if (is_null($request->getQueryParams()['per_page'])) {
        $per_page = 5;
    }
    else {
        $per_page = $request->getQueryParams()['per_page'];
    }
    $response->getBody()->write(json_encode(get_my_products($container['db'],
        $request->getQueryParams()['token'],
        $per_page,
        $start
    )));
    return $response;
});
$app->get('/update_my_record', function (Request $request, Response $response, array $args) {
    global $container;
    $response->getBody()->write($response->getBody()->write(json_encode(update_my_record($container['db'],
        $request->getQueryParams()['token'],
        $request->getQueryParams()
    ))));
    return $response;
});
$app->get('/delete_product', function (Request $request, Response $response, array $args) {
    global $container;
    $response->getBody()->write($response->getBody()->write(json_encode(delete_product($container['db'],
        $request->getQueryParams()['token'],
        $request->getQueryParams()['id']
    ))));
    return $response;
});
$app->run();
