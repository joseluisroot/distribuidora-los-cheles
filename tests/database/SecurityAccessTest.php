<?php
use App\Services\AccessService;
use App\Models\PasswordResetTokenModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

final class SecurityAccessTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private $connection;
    private $testForge;
    private array $fixtureMigrations = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = db_connect('tests');
        $this->assertSame('SQLite3', $this->connection->DBDriver);
        $this->assertSame(':memory:', $this->connection->database);
        $this->testForge = \Config\Database::forge($this->connection);
        foreach ([
            '2025-09-29-193005_CreateRoles' => 'CreateRoles',
            '2025-09-29-193106_CreateUsers' => 'CreateUsers',
            '2025-09-29-193114_CreatePasswordResetTokens' => 'CreatePasswordResetTokens',
            '2025-10-07-201241_AddUseragentAndIpToTokent' => 'AddUseragentAndIpToTokent',
            '2026-08-28-000001_CreateAccessControl' => 'CreateAccessControl',
        ] as $file => $class) {
            require_once APPPATH . 'Database/Migrations/' . $file . '.php';
            $name = 'App\\Database\\Migrations\\' . $class;
            $migration = new $name($this->testForge);
            $migration->up();
            $this->fixtureMigrations[] = $migration;
        }
        foreach ([1 => 'admin', 2 => 'cliente', 3 => 'vendedor'] as $id => $role) {
            $this->connection->table('roles')->insert(['id' => $id, 'name' => $role]);
        }
        foreach ([1 => 1, 2 => 3, 3 => 2] as $id => $role) {
            $this->connection->table('users')->insert([
                'id' => $id, 'role_id' => $role, 'name' => 'Test ' . $id,
                'email' => 'user' . $id . '@example.test', 'password' => password_hash('Test-password-123', PASSWORD_DEFAULT),
                'is_active' => 1,
            ]);
        }
        session()->remove('user');
    }

    protected function tearDown(): void
    {
        session()->remove('user');
        foreach (['access_audit','access_grants','access_lock','password_reset_tokens','users','roles'] as $table) {
            $this->testForge->dropTable($table, true);
        }
        $this->fixtureMigrations = [];
        parent::tearDown();
    }

    private function grant(string $type, int $id, string $permission, string $effect = 'allow', string $scope = 'global'): void
    {
        $this->connection->table('access_grants')->insert([
            'subject_type' => $type, 'subject_id' => $id, 'permission' => $permission, 'scope' => $scope, 'effect' => $effect,
        ]);
    }

    public function testDenialOverridesRoleAndScopeDoesNotLeak(): void
    {
        $access = new AccessService($this->connection);
        $this->assertFalse($access->can(2, 'prices.manage'));
        $this->grant('role', 3, 'prices.manage', 'allow', 'sucursal:1');
        $this->assertTrue($access->can(2, 'prices.manage', 'sucursal:1'));
        $this->assertFalse($access->can(2, 'prices.manage', 'sucursal:2'));
        $this->assertFalse($access->can(2, 'prices.manage'));
        $this->grant('user', 2, 'prices.manage', 'deny');
        $this->assertFalse($access->can(2, 'prices.manage', 'sucursal:1'));
    }

    public function testAccessMigrationCanRollBackInIsolatedDatabase(): void
    {
        $migration = new \App\Database\Migrations\CreateAccessControl($this->testForge);
        $migration->down();
        $this->assertFalse($this->connection->tableExists('access_grants'));
        $this->connection->resetDataCache();
        $this->assertNotContains('auth_version', $this->connection->getFieldNames('users'));
    }

    public function testOrderDetailsRejectAnotherCustomersIdBeforeReadingLines(): void
    {
        require_once APPPATH . 'Database/Migrations/2025-10-04-043708_CreatePedidos.php';
        (new \App\Database\Migrations\CreatePedidos($this->testForge))->up();
        try {
            $this->connection->table('pedidos')->insert(['id' => 1, 'cliente_id' => 3, 'total' => 10]);
            session()->set('user', ['id' => 2, 'auth_version' => 0]);
            $request = service('incomingrequest');
            $request->setMethod('GET');
            $result = $this->withRequest($request)->controller(\App\Controllers\PedidoController::class)->execute('ver', 1);
            $result->assertStatus(404);
        } finally {
            $this->testForge->dropTable('pedidos', true);
        }
    }

    public function testChangesAreAuditedAndRevocationIsImmediate(): void
    {
        $access = new AccessService($this->connection);
        $this->grant('user', 1, 'access.manage');
        $this->grant('user', 1, 'prices.manage');
        $access->change(1, 'user', 2, 'prices.manage', 'global', 'allow', 'Delegación autorizada');
        $this->assertTrue($access->can(2, 'prices.manage'));
        $access->change(1, 'user', 2, 'prices.manage', 'global', null, 'Cambio de funciones');
        $this->assertFalse($access->can(2, 'prices.manage'));
        $this->assertSame(2, $this->connection->table('access_audit')->countAllResults());
    }

    public function testGlobalLegacyScreenCannotBypassBranchDenial(): void
    {
        $this->grant('role', 3, 'orders.view');
        $this->grant('user', 2, 'orders.view', 'deny', 'sucursal:1');
        $access = new AccessService($this->connection);
        $this->assertFalse($access->can(2, 'orders.view'));
        $this->assertFalse($access->can(2, 'orders.view', 'sucursal:1'));
        $this->assertTrue($access->can(2, 'orders.view', 'sucursal:2'));
    }

    public function testPermissionFilterReflectsGrantAndWithdrawalWithoutNewLogin(): void
    {
        $this->grant('user', 1, 'access.manage');
        $this->grant('user', 1, 'prices.manage');
        session()->set('user', ['id' => 2, 'auth_version' => 0]);
        $request = service('incomingrequest');
        $request->setHeader('Accept', 'text/html');
        $request->removeHeader('X-Requested-With');
        $filter = new \App\Filters\PermissionFilter();
        $this->assertSame(403, $filter->before($request, ['prices.manage'])->getStatusCode());
        $access = new AccessService($this->connection);
        $access->change(1, 'user', 2, 'prices.manage', 'global', 'allow', 'Asignación de funciones');
        $this->assertNull($filter->before($request, ['prices.manage']));
        $access->change(1, 'user', 2, 'prices.manage', 'global', null, 'Retiro de funciones');
        $response = $filter->before($request, ['prices.manage']);
        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Esta sección requiere permiso', $response->getBody());
        $this->assertStringContainsString(site_url('dashboard'), $response->getBody());
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
        $rows = $this->connection->table('access_audit')->orderBy('id')->get()->getResultArray();
        $this->assertCount(2, $rows);
        $this->assertSame('allow', $rows[1]['before_effect']);
        $this->assertNull($rows[1]['after_effect']);
    }

    public function testRemovingRoleGrantPreservesIndividualGrantButDenialBlocksIt(): void
    {
        $this->grant('user', 1, 'access.manage');
        $this->grant('user', 1, 'prices.manage');
        $this->grant('role', 3, 'prices.manage');
        $this->grant('user', 2, 'prices.manage');
        $access = new AccessService($this->connection);
        $access->change(1, 'role', 3, 'prices.manage', 'global', null, 'Retiro de regla del rol');
        $this->assertTrue($access->can(2, 'prices.manage'));
        $access->change(1, 'role', 3, 'prices.manage', 'global', 'deny', 'Restricción del rol');
        $this->assertFalse($access->can(2, 'prices.manage'));
    }

    public function testDeniedJsonAndAjaxRequestsDoNotReceiveHtmlOrPermissionDetails(): void
    {
        session()->set('user', ['id' => 2, 'auth_version' => 0]);
        $request = service('incomingrequest');
        try {
            foreach (['json', 'ajax'] as $mode) {
                $request->setHeader('Accept', $mode === 'json' ? 'application/json' : '*/*');
                $request->setHeader('X-Requested-With', $mode === 'ajax' ? 'XMLHttpRequest' : '');
                $response = (new \App\Filters\PermissionFilter())->before($request, ['costs.view']);
                $this->assertSame(403, $response->getStatusCode());
                $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));
                $this->assertSame(['error' => 'access_denied', 'message' => 'No tienes permiso para realizar esta acción.'], json_decode($response->getBody(), true));
            }
        } finally {
            $request->removeHeader('Accept');
            $request->removeHeader('X-Requested-With');
        }
    }

    public function testCannotDelegateAuthorityYouDoNotHave(): void
    {
        $this->grant('user', 1, 'access.manage');
        $this->expectException(\DomainException::class);
        (new AccessService($this->connection))->change(1, 'user', 2, 'costs.view', 'global', 'allow', 'Test');
    }

    public function testCannotModifyOwnRole(): void
    {
        $this->grant('user', 1, 'access.manage');
        $this->grant('user', 1, 'prices.manage');
        $this->expectException(\DomainException::class);
        (new AccessService($this->connection))->change(1, 'role', 1, 'prices.manage', 'global', 'allow', 'Test');
    }

    public function testFilterRejectsForgedRoleAndExpiredSession(): void
    {
        session()->set('user', ['id' => 2, 'role' => 'admin', 'auth_version' => 0]);
        $response = (new \App\Filters\PermissionFilter())->before(service('request'), ['prices.manage']);
        $this->assertSame(403, $response->getStatusCode());
        $this->connection->table('users')->where('id', 2)->update(['auth_version' => 1]);
        $this->assertNull((new AccessService())->sessionUser());
    }

    public function testResetFindsOwnTokenAndCannotReplay(): void
    {
        $model = new PasswordResetTokenModel($this->connection);
        $first = $model->createToken(2);
        $second = $model->createToken(3);
        $this->assertSame(2, (int) $model->validateToken($first)['user_id']);
        $this->assertSame(3, (int) $model->validateToken($second)['user_id']);
        $this->assertTrue($model->resetPassword($first, 'New-password-123'));
        $this->assertFalse($model->resetPassword($first, 'Replay-password-123'));
        $this->assertNotNull($model->validateToken($second));
        $user = $this->connection->table('users')->where('id', 2)->get()->getRowArray();
        $this->assertSame(1, (int) $user['auth_version']);
        $this->assertTrue(password_verify('New-password-123', $user['password']));
    }

    public function testExpiredAndMalformedResetTokensAreRejected(): void
    {
        $model = new PasswordResetTokenModel($this->connection);
        $token = $model->createToken(2, -1);
        $this->assertNull($model->validateToken($token));
        $this->assertNull($model->validateToken('not-a-token'));
    }

    public function testRegistrationIgnoresSubmittedRole(): void
    {
        $request = service('incomingrequest');
        $request->setMethod('POST');
        $request->setGlobal('post', [
            'name' => 'New customer', 'email' => 'new@example.test', 'password' => 'Customer-password-123',
            'password_confirm' => 'Customer-password-123', 'role_id' => 1, 'auth_version' => 99,
        ]);
        $this->withRequest($request)->controller(\App\Controllers\Auth::class)->execute('doRegister')->assertRedirect();
        $row = $this->connection->table('users')->where('email', 'new@example.test')->get()->getRowArray();
        $this->assertNotNull($row, json_encode(session()->getFlashdata()));
        $this->assertSame(2, (int) $row['role_id']);
        $this->assertSame(0, (int) $row['auth_version']);
    }

    public function testResetMailFailureKeepsGenericResponseWithoutSendingRealEmail(): void
    {
        $mailer = $this->createMock(\CodeIgniter\Email\Email::class);
        $mailer->expects($this->once())->method('setTo')->with('user2@example.test')->willReturnSelf();
        $mailer->method('setSubject')->willReturnSelf();
        $mailer->method('setMessage')->willReturnSelf();
        $mailer->expects($this->once())->method('send')->willReturn(false);
        \Config\Services::injectMock('email', $mailer);
        $request = service('incomingrequest');
        $request->setMethod('POST');
        $request->setGlobal('post', ['email' => 'user2@example.test']);
        $result = $this->withRequest($request)->controller(\App\Controllers\Auth::class)->execute('sendReset');
        $result->assertRedirect();
        $result->assertSessionHas('message', 'Si el correo existe, enviaremos instrucciones.');
    }

    public function testAccessViewDefaultsToAuthenticatedUser(): void
    {
        session()->set('user', ['id' => 1, 'auth_version' => 0]);
        $this->grant('user', 1, 'access.manage');
        $request = service('incomingrequest');
        $request->setMethod('GET');
        $request->setGlobal('get', []);
        $result = $this->withRequest($request)->controller(\App\Controllers\AccessController::class)->execute('index');
        $result->assertOK();
        $html = $result->response()->getBody();
        $this->assertStringContainsString('value="1" selected', $html);
        $this->assertStringContainsString('Administrar accesos</td><td>Permitido', $html);
        $this->assertStringContainsString('Consultar costos</td><td>Denegado', $html);
    }

    public function testAccessViewRejectsUnknownUserInsteadOfShowingMisleadingPermissions(): void
    {
        session()->set('user', ['id' => 1, 'auth_version' => 0]);
        $request = service('incomingrequest');
        $request->setMethod('GET');
        $request->setGlobal('get', ['user_id' => 999]);
        $this->withRequest($request)->controller(\App\Controllers\AccessController::class)->execute('index')->assertStatus(400);
    }

    public function testAccessViewEscapesAuditReasons(): void
    {
        $this->grant('user', 1, 'access.manage');
        $this->grant('user', 1, 'prices.manage');
        (new AccessService())->change(1, 'user', 2, 'prices.manage', 'global', 'allow', '<script>alert(1)</script>');
        $request = service('incomingrequest');
        $request->setMethod('GET');
        $request->setGlobal('get', ['user_id' => 2]);
        $result = $this->withRequest($request)->controller(\App\Controllers\AccessController::class)->execute('index');
        $this->assertStringNotContainsString('<script>alert(1)</script>', $result->response()->getBody());
        $this->assertStringContainsString('&lt;script&gt;', $result->response()->getBody());
        $this->assertStringContainsString('value="2" selected', $result->response()->getBody());
        $this->assertStringContainsString('Modificar precios</td><td>Permitido', $result->response()->getBody());
    }
}
