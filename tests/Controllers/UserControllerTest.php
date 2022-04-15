<?php
declare(strict_types = 1);

namespace Controllers;

require_once __DIR__ . "/../TestCase.php";

use BadRequest;
use NotFound;
use TestCase;
use Unauthorized;

/**
 * Test class for AdminController
 */
class UserControllerTest extends TestCase
{
    /**
     * @var UserController
     */
    private UserController $userController;

    /**
     * @var string user_id
     */
    private string $user_id;

    /**
     * Construct AdminController for tests
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->userController = new UserController();
        $GLOBALS["pdo"]       = $this->userController->database()->getPdo();
    }

    /**
     * Test getUsers function
     * Usage: GET /users | Scope: admin, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testGetUsers()
    {
        // Call function
        $request = $this->createRequest("GET", "/users");
        $result = $this->userController->getUsers($request, $this->response);

        // Check if request = database and http code is correct
        self::assertSame(
            json_encode(
                $GLOBALS["pdo"]
                    ->query("SELECT user_id, email, first_name, last_name FROM users ORDER BY first_name LIMIT 300;")
                    ->fetchAll()
            ),
            $result->getBody()->__toString()
        );
        self::assertSame(200, $result->getStatusCode());
    }

    /**
     * Test getUsers function without permission
     * Usage: GET /users | Scope: admin, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testGetUsersWithoutScope()
    {
        // Change scope
        $GLOBALS["session"]["scope"] = "app";

        // Check if exception is thrown
        $this->expectException(Unauthorized::class);
        $this->expectExceptionMessage("User doesn't have the permission");

        // Call function
        $request = $this->createRequest("GET", "/users");
        $this->userController->getUsers($request, $this->response);
    }

    /**
     * Test getUser function
     * Usage: GET /users/{user_id} | Scope: admin, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testGetUser()
    {
        // Call function
        $request = $this->createRequest("GET", "/users/" . $this->user_id);
        $result = $this->userController->getUser($request, $this->response, ["user_id" => $this->user_id]);

        // Check if request = database and http code is correct
        self::assertSame(
            json_encode(
                $GLOBALS["pdo"]
                    ->query("SELECT email, first_name, last_name, device, created_at, updated_at FROM users WHERE user_id = '$this->user_id' LIMIT 1;")
                    ->fetch()
            ),
            $result->getBody()->__toString()
        );
        self::assertSame(200, $result->getStatusCode());
    }

    /**
     * Test getUser function without permission
     * Usage: GET /users/{user_id} | Scope: admin, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testGetUserWithoutScope()
    {
        // Change scope
        $GLOBALS["session"]["scope"] = "app";

        // Check if exception is thrown
        $this->expectException(Unauthorized::class);
        $this->expectExceptionMessage("User doesn't have the permission");

        // Call function
        $request = $this->createRequest("GET", "/users/" . $this->user_id);
        $this->userController->getUser($request, $this->response, ["user_id" => $this->user_id]);
    }

    /**
     * Test getUser function without params
     * Usage: GET /users/{user_id} | Scope: admin, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testGetUserWithoutParams()
    {
        // Check if exception is thrown
        $this->expectException(BadRequest::class);
        $this->expectExceptionMessage("Missing value in array");

        // Call function
        $request = $this->createRequest("GET", "/users/");
        $this->userController->getUser($request, $this->response, []);
    }

    /**
     * Test getUser function with bad ID
     * Usage: GET /users/{user_id} | Scope: admin, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testGetUserWithBadID()
    {
        // Check if exception is thrown
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage("Nothing was found in the database");

        // Call function
        $request = $this->createRequest("GET", "/users/00000000-0000-0000-0000-000000000000");
        $this->userController->getUser($request, $this->response, ["user_id" => "00000000-0000-0000-0000-000000000000"]);
    }

    /**
     * Test addUser function
     * Usage: POST /admins | Scope: app, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testAddUser()
    {
        // Fields
        $GLOBALS["body"] = [
            "email"            => "test_add2@example.com",
            "first_name"       => "Test_add_user2",
            "last_name"        => "User_add2"
        ];

        // Call function
        $request = $this->createRequest("POST", "/users", $GLOBALS["body"]);
        $result = $this->userController->addUser($request, $this->response);

        // Fetch new user
        $new_user = $GLOBALS["pdo"]
            ->query("SELECT email, first_name, last_name FROM users WHERE email = '{$GLOBALS["body"]["email"]}' LIMIT 1;")
            ->fetch();

        // Remove new user
        $GLOBALS["pdo"]
            ->prepare("DELETE FROM users WHERE email = '{$GLOBALS["body"]["email"]}';")
            ->execute();

        // Check if request = database and http code is correct
        self::assertSame($new_user, $GLOBALS["body"]);
        $this->assertHTTPCode($result, 201, "Created");
    }

    /**
     * Test addUser function without permission
     * Usage: POST /admins | Scope: app, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testAddAdminWithoutScope()
    {
        // Change scope
        $GLOBALS["session"]["scope"] = "admin";

        // Check if exception is thrown
        $this->expectException(Unauthorized::class);
        $this->expectExceptionMessage("User doesn't have the permission");

        // Call function
        $request = $this->createRequest("POST", "/users");
        $this->userController->addUser($request, $this->response);
    }

    /**
     * Test addUser function without params
     * Usage: POST /users | Scope: app, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testAddUserWithoutParams()
    {
        // Check if exception is thrown
        $this->expectException(BadRequest::class);
        $this->expectExceptionMessage("Missing value in array");

        // Call function
        $request = $this->createRequest("POST", "/users", $GLOBALS["body"] = []);
        $this->userController->addUser($request, $this->response);
    }

    /**
     * Test addUser function with missing params
     * Usage: POST /users | Scope: app, super_admin
     *
     * @throws NotFound|BadRequest|Unauthorized
     */
    public function testAddUserWithMissingParams()
    {
        // Fields
        $GLOBALS["body"] = [
            "email" => "test_add2@example.com"
        ];

        // Check if exception is thrown
        $this->expectException(BadRequest::class);
        $this->expectExceptionMessage("Missing value in array");

        // Call function
        $request = $this->createRequest("POST", "/users", $GLOBALS["body"]);
        $this->userController->addUser($request, $this->response);
    }

    /**
     * SetUp parameters before execute tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user_id = $GLOBALS["pdo"]
            ->query("INSERT INTO users (email, first_name, last_name, device) VALUES ('test_user@example.com', 'Test', 'User', 'Android 10') RETURNING user_id;")
            ->fetchColumn();
    }

    /**
     * Clean parameters after execute tests
     */
    protected function tearDown(): void
    {
        $GLOBALS["pdo"]
            ->prepare("DELETE FROM users WHERE user_id = '$this->user_id';")
            ->execute();

        parent::tearDown();
    }
}