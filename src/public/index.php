<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

include '../lib/config.php';

include '../lib/util.php';
include '../lib/error.php';

include '../app/ProductAttribute.php';
include '../app/Category.php';
include '../app/Product.php';
include '../app/User.php';


$app = new \Slim\App(['settings' => CONFIG]);

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

$product_attribute = new ProductAttribute($container['db']);
$category = new Category($container['db']);
$product = new Product($container['db'], $category);
$user = new User($container['db']);

$methods = [
    ['post', '/auth', $user, 'add', ['phone_number']],
    ['get', '/get_token', $user, 'get_token', ['phone_number', 'verification_code']],
    ['get', '/get_product', $product, 'get', ['id']],
    ['get', '/get_user', $user, 'get', ['id']],
    ['get', '/get_products_list', $product, 'get_all', ['start', 'per_page']],
    ['get', '/get_user_products', $user, 'get_products', ['id', 'start', 'per_page']],
    ['get', '/get_my_products', $user, 'get_products_by_token', ['token', 'start', 'per_page']],
    ['get', '/get_my_record', $user, 'get_by_token', ['token']],
    ['post', '/update_my_record', $user, 'set', ['token', 'name']],
    ['post', '/delete_product', $product, 'delete', ['id', 'token']],
    ['post', '/add_product', $product, 'add', ['token', 'name', 'description', 'price']],
    ['get', '/get_category_id', $category, 'get_id', ['path']],
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
            return $response
                ->withHeader("Content-Type", "application/json; charset = utf-8")
                ->write(json_encode(
                    call_user_func_array(array($methods[$i][2], $methods[$i][3]), $parameter_list)
                ));
        }
    );
}

$app->run();
