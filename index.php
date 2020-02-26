<?php

require_once __DIR__."/vendor/autoload.php";

use Controllers\PhoneBookController;

$jsonConfig = file_get_contents(__DIR__."/config.json");
$router = new AltoRouter();
$controller = new PhoneBookController;

$router->setBasePath('zipdev/');

$router->map('GET', '/', function () {
    echo "OK";
});

$router->map('GET', '/api/v1/contacts', function () use ($controller) {
    $name = isset($_GET['name']) ? $_GET['name'] : null;
    $surname = isset($_GET['surname']) ? $_GET['surname'] : null;
    echo $controller->getContacts($name, $surname);
});

$router->map('GET', '/api/v1/contacts/[i:id]', function ($id) use ($controller) {
    echo $controller->getContactByID($id);
});

$router->map('POST', '/api/v1/contacts', function () use ($controller) {
    $newContact = json_decode(file_get_contents('php://input'));
    echo $controller->saveContact($newContact);
});

$router->map('PUT', '/api/v1/contacts/[i:id]', function ($id) use ($controller) {
    $updatedContact = json_decode(file_get_contents('php://input'));
    echo $controller->updateContact($id, $updatedContact);
});

$router->map('DELETE', '/api/v1/contacts/[i:id]', function ($id) use ($controller) {
    echo $controller->deleteContact($id);
});

$match = $router->match();

if( is_array($match) && is_callable( $match['target'] ) ) {
    header('Content-type:application/json;charset=utf-8');
    try {
        call_user_func_array( $match['target'], $match['params'] ); 
    } catch(Exception $ex) {
        $error = new stdClass;
        $error->message = "Some error ocurred!";
        $error->devMessage = $ex->getMessage();
        echo json_encode($error);
    }
} else {
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
