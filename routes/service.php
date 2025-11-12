<?php
    declare(strict_types=1);

    function registerService(AltoRouter $router) :void {
        $router->map('GET', "/service",'ServiceController#index');
        $router->map('POST', "/service", 'ServiceController#create');
        $router->map('PUT', "/service/[a:service_id]",'ServiceController#update');
        $router->map('DELETE',"/service/[a:service_id]", 'ServiceController#delete');
    }
?>