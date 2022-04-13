<?php
declare(strict_types = 1);

namespace Controllers;

require_once __DIR__ . "/../TestCase.php";

use TestCase;

/**
 * Test class for BaseController
 */
class BaseControllerTest extends TestCase
{
    /**
     * @var BaseController
     */
    private BaseController $baseController;

    /**
     * Construct BaseController for tests
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->baseController = new BaseController();
        $GLOBALS["pdo"]       = $this->baseController->database()->getPdo();
    }

    /**
     * Test basePath function
     * Usage: GET / | Scope: none
     */
    public function testGetActions()
    {
        // Call function
        $request = $this->createRequest("GET", "/actions");
        $result  = $this->baseController->basePath($request, $this->response);

        // Check if request = database and http code is correct
        self::assertSame(
            json_encode([
                "version"     => "v0.1",
                "title"       => "Cerealis API",
                "description" => "Enterprise Resource Management API",
                "host"        => "https://mspr.minarox.fr",
                "base_path"    => "/api"
            ]),
            $result->getBody()->__toString()
        );
        self::assertSame(200, $result->getStatusCode());
    }
}