<?php
    declare(strict_types=1);

    function registerService(AltoRouter $router) :void {
        $router->map('GET', "/service",'ServiceController#index');
        $router->map('POST', "/service/create", 'ServiceController#create');
        $router->map('PUT', "/service/update/[a:service_id]",'ServiceController#update');
        $router->map('DELETE',"/service/delete/[a:service_id]", 'ServiceController#delete');
    }
?>