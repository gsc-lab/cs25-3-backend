<?php
declare(strict_types=1);

function studentRegister(AltoRouter $router): void {
    $router->map('GET',    "/students",                'StudentsController#index');
    $router->map('GET',    "/students/[i:std_id]",     'StudentsController#show');
    $router->map('POST',   "/students",                'StudentsController#create');
    $router->map('PUT',    "/students/[i:std_id]",     'StudentsController#update');
    $router->map('PATCH',  "/students/[i:std_id]",     'StudentsController#update');
    $router->map('DELETE', "/students/[i:std_id]",     'StudentsController#delete');
}
