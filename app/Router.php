<?php
declare(strict_types=1);

namespace app;

use Codes\ErrorCode;
use Controllers\BaseController;
use Controllers\SessionController;
use Slim\App;

/**
 * List of route for the app
 * @param App $app
 */
return function (App $app) {
    // CORS Policy
    $app->options("/{routes:.+}", function ($request, $response) {
        return $response;
    });

    $app->get("/", [BaseController::class, "basePath"]);

    $app->post("/login", [SessionController::class, "login"]);

    /**
     * Redirect to 404 if none of the routes match
     */
    $app->map(["GET", "POST", "PUT", "DELETE", "PATCH"], "/{routes:.+}", function ($request, $response) {
        return (new ErrorCode())->methodNotAllowed();
    });
};