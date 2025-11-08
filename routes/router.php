<?php
declare(strict_types=1);

require_once __DIR__.'/students.php';

function registerAllRoutes(AltoRouter $router): void {
    $router->map('GET', fn() => 'Home Page');

    registerStudents($router);
    // registerCourses($router);
    // registerEnrollments($router);
}