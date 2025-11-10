<?php
declare(strict_types=1);

require_once __DIR__.'/users.php';
require_once __DIR__.'/news.php';

function registerAllRoutes(AltoRouter $router): void {
    $router->map('GET', '/', fn() => 'Home Page');
    registerUsers($router);
    registerNews($router);
}
