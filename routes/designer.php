<?php

declare(strict_types=1);

function registerDesigner(AltoRouter $router):void{
    $router->map("GET", '/designer', "DesignerController#index"); // Designer정보 전체 보기
    $router->map("GET", '/designer/[a:designer_id]', "DesignerController#show"); // 해당하는 Designer정보 보기 
    $router->map("POST", '/designer', "DesignerController#create"); // 작성하기
    $router->map("PUT", '/designer/[a:designer_id]', "DesignerController#update"); // 해당하는 Designer정보 수정
    $router->map("DELETE", '/designer/[a:designer_id]', "DesignerController#delete"); // 해당하는 Designer정보 삭제
}
?>