<?php

declare(strict_types=1);

function registerNews(AltoRouter $router) :void {
    $router->map("GET", '/news', "NewsController#index"); // news 전체 보기
    $router->map("GET", '/news/[a:news_id]', "NewsController#show"); // news 상세 보기
    $router->map("POST", '/news', "NewsController#create"); // news작성
    $router->map("PUT", '/news/[a:news_id]', "NewsController#update"); // news수정 
    $router->map("DELETE", '/news/[a:news_id]', "NewsController#delete"); // news 삭게하기    
}

?>