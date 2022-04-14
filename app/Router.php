<?php
declare(strict_types=1);

namespace app;

use Codes\ErrorCode;
use Controllers\AdminController;
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

    // Base controller
    $app->get("/", [BaseController::class, "basePath"]);

    // Session controller
    $app->post("/login", [SessionController::class, "login"]);
    $app->post("/introspect", [SessionController::class, "introspect"]);
    $app->post("/revoke", [SessionController::class, "revoke"]);
    $app->get("/userinfo", [SessionController::class, "userInfo"]);
    $app->put("/userinfo", [SessionController::class, "editUserInfo"]);
    $app->put("/userinfo/password", [SessionController::class, "editPassword"]);
    $app->get("/logout", [SessionController::class, "logout"]);

    // Admin controller
    $app->get("/admins", [AdminController::class, "getAdmins"]);
    $app->get("/admins/{admin_id}", [AdminController::class, "getAdmin"]);

    /**
     * Redirect to 404 if none of the routes match
     */
    $app->map(["GET", "POST", "PUT", "DELETE", "PATCH"], "/{routes:.+}", function ($request, $response) {
        return (new ErrorCode())->methodNotAllowed();
    });
};