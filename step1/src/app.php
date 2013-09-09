<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

GameRocket_Configuration::environment('production');
GameRocket_Configuration::apikey('<your_apikey>');
GameRocket_Configuration::secretkey('<your_secretkey>');

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views'
));

$app->get("/", function() use($app) {
    return $app->redirect("/mine");
});

$app->get("/mine", function() use($app) {
    $result = GameRocket_Action::run('on-load-mine-page', array(
        'player' => "<player_id>"
    ));
    
    if ($result->success) {
        $app['current_gold'] = $result->map->data['current_gold'];
        $app['current_production'] = $result->map->data['current_production'];
        $app['next_level_price'] = $result->map->data['next_level_price'];
        $app['next_level_production'] = $result->map->data['next_level_production'];
    } else {
        return new Response("<h1>Error: " . $result->error_description . "</h1>", 200);
    }
    
    return $app["twig"]->render("mine.twig");
});

$app->post("/upgrade_mine", function() use($app) {
    $result = GameRocket_Action::run('upgrade-mine', array(
        'player' => "<player_id>"
    ));
    
    if ($result->success) {
        if ($result->map->data['status'] === 'error') {
            return new Response("<h1>Error: " . $result->map->data['message'] . "</h1>", 200);
        }
    } else {
        return new Response("<h1>Error: " . $result->error_description . "</h1>", 200);
    }
    
    return $app->redirect("/mine");
});

return $app;
