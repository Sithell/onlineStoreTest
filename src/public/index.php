<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

include '../lib/util.php';
include '../lib/error.php';

include '../app/Product.php';
include '../app/User.php';

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

$product = new Product($container['db']);
$user = new User($container['db']);

$methods = [
    ['post', '/auth', $user, 'add', ['phone_number']],
    ['get', '/get_token', $user, 'get_token', ['phone_number', 'verification_code']],
    ['get', '/get_product', $product, 'get', ['id']],
    ['get', '/get_user', $user, 'get', ['id']],
    ['get', '/get_products_list', $product, ['start', 'per_page']],
    ['get', '/get_user_products', $user, ['id', 'start', 'per_page']],
    ['get', '/get_my_products', $user, ['token', 'start', 'per_page']],
    ['get', '/get_my_record', $user, ['token']],
    ['post', '/update_my_record', $user, ['token', 'name']],
    ['post', '/delete_product', $product, ['id', 'token']],
    ['post', '/add_product', $product, 'add', ['token', 'name', 'description', 'price']],
];
for ($i=0; $i < count($methods); $i++) {
    call_user_func(
        array($app, $methods[$i][0]),
        $methods[$i][1],
        function (Request $request, Response $response, array $args) use ($methods, $i) {
            $parameter_list = array();
            foreach ($methods[$i][4] as $parameter) {
                array_push($parameter_list, $request->getQueryParams()[$parameter]);
            }
            $response->getBody()->write(json_encode(
                call_user_func_array(array($methods[$i][2], $methods[$i][3]), $parameter_list)
            ));
            return $response;
        }
    );
}

$app->run();
