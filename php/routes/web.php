<?php
declare(strict_types=1);

function registerRoutes(): Router
{
    $router = new Router();

    $controller = new HomeController();

    $homeHandler = static fn (): array => $controller->index();
    $designsHandler = static fn (): array => $controller->designsIndex();
    $contactHandler = static fn (): array => $controller->contactIndex();
    $contactSubmitHandler = static fn (): array => $controller->submitContact();

    $router->get('/', $homeHandler);
    $router->get('/index.php', $homeHandler);
    $router->get('/public', $homeHandler);
    $router->get('/public/index.php', $homeHandler);
    $router->get('/designs', $designsHandler);
    $router->get('/contact', $contactHandler);
    $router->map('POST', '/contact', $contactSubmitHandler);

    return $router;
}
