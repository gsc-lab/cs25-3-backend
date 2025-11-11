<?php
declare(strict_types=1);

require_once __DIR__.'/users.php';
require_once __DIR__.'/news.php';
require_once __DIR__.'/service.php';
require_once __DIR__.'/designer.php';

function registerAllRoutes(AltoRouter $router): void {
    $router->map('GET', '/', fn() => 'Home Page');
    registerUsers($router);
    registerNews($router);
    registerService($router);
    registerDesigner($router);
}
