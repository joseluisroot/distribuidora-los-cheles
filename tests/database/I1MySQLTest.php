<?php
use App\Services\AccessService;
use CodeIgniter\Test\CIUnitTestCase;

/** Explicit opt-in. Creates only random local schemas, never uses the configured commercial database. */
final class I1MySQLTest extends CIUnitTestCase
{
    public function testMigrationLockContentionAndFixtureBackupRestore(): void
    {
        if (getenv('I1_MYSQL_TESTS') !== '1') $this->markTestSkipped('Opt-in local MySQL verification.');
        $config = config('Database')->default;
        $this->assertContains($config['hostname'], ['localhost', '127.0.0.1', '::1']);
        $this->assertSame('MySQLi', $config['DBDriver']);
        $this->assertEmpty($config['DSN']);
        $config['database'] = '';
        $config['DBPrefix'] = '';
        $config['pConnect'] = false;
        $config['DBDebug'] = true;
        $config['failover'] = [];
        $admin = \Config\Database::connect($config, false);
        $created = [];
        $connections = [];
        try {
            foreach (['source', 'restore'] as $kind) {
                $name = 'i1_verify_' . bin2hex(random_bytes(8)) . '_' . $kind;
                $admin->query('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
                $created[] = $name;
                $connections[$kind] = \Config\Database::connect(array_replace($config, ['database' => $name]), false);
            }
            $source = $connections['source'];
            $forge = \Config\Database::forge($source);
            foreach ([
                '2025-09-29-193005_CreateRoles' => 'CreateRoles',
                '2025-09-29-193106_CreateUsers' => 'CreateUsers',
                '2026-08-28-000001_CreateAccessControl' => 'CreateAccessControl',
                '2026-08-28-000003_EnsureTransactionalSecurityTables' => 'EnsureTransactionalSecurityTables',
            ] as $file => $class) {
                require_once APPPATH . 'Database/Migrations/' . $file . '.php';
                $class = 'App\\Database\\Migrations\\' . $class;
                (new $class($forge))->up();
            }
            $source->table('roles')->insert(['id' => 1, 'name' => 'test']);
            foreach ([1, 2] as $id) $source->table('users')->insert([
                'id' => $id, 'role_id' => 1, 'name' => 'Fixture '.$id,
                'email' => 'fixture'.$id.'@example.test', 'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            ]);
            foreach (['access.manage', 'prices.manage'] as $permission) $source->table('access_grants')->insert([
                'subject_type' => 'user', 'subject_id' => 1, 'permission' => $permission, 'scope' => 'global', 'effect' => 'allow',
            ]);
            $competitor = $connections['competitor'] = \Config\Database::connect(array_replace($config, ['database' => $created[0]]), false);
            $competitor->query('SET SESSION innodb_lock_wait_timeout = 1');
            $competitor->transException(true);
            $this->assertNotSame($source->query('SELECT CONNECTION_ID() AS id')->getRow()->id, $competitor->query('SELECT CONNECTION_ID() AS id')->getRow()->id);
            $this->assertSame('InnoDB', $source->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='access_lock'")->getRow()->ENGINE);
            $source->transBegin();
            $source->table('access_lock')->where('id', 1)->set('version', 'version + 1', false)->update();
            $blocked = false;
            try {
                (new AccessService($competitor))->change(1, 'user', 2, 'prices.manage', 'global', 'allow', 'Concurrency fixture');
            } catch (\Throwable $error) {
                $blocked = str_contains(strtolower($error->getMessage()), 'lock wait timeout');
                if (!$blocked) throw $error;
            } finally { $source->transRollback(); }
            $this->assertTrue($blocked, 'Concurrent mutation must not bypass the lock.');
            $this->assertSame(0, $source->table('access_audit')->countAllResults());
            // Fresh connection avoids carrying the deliberately failed transaction status.
            $access = new AccessService($source);
            $access->change(1, 'user', 2, 'prices.manage', 'global', 'allow', 'Fixture grant');
            $this->assertTrue($access->can(2, 'prices.manage'));
            $access->change(1, 'user', 2, 'prices.manage', 'global', null, 'Fixture revoke');
            $this->assertFalse($access->can(2, 'prices.manage'));
            $source->query('ALTER TABLE access_lock ENGINE=MyISAM');
            try {
                $access->requireTransactionalStorage();
                $this->fail('Unsafe storage must prevent mutations.');
            } catch (\DomainException $expected) {
                $this->assertStringContainsString('InnoDB', $expected->getMessage());
            }
            (new \App\Database\Migrations\EnsureTransactionalSecurityTables($forge))->up();
            $access->requireTransactionalStorage();

            // Logical backup in memory: schema plus rows; no real user data or secret files.
            $restore = $connections['restore'];
            foreach (['roles', 'users', 'access_lock', 'access_grants', 'access_audit'] as $table) {
                $ddl = array_values($source->query('SHOW CREATE TABLE `'.$table.'`')->getRowArray())[1];
                $rows = $source->table($table)->orderBy('id')->get()->getResultArray();
                $restore->query($ddl);
                if ($rows) $restore->table($table)->insertBatch($rows);
                $this->assertSame($rows, $restore->table($table)->orderBy('id')->get()->getResultArray());
            }
            $migration = new \App\Database\Migrations\CreateAccessControl($forge);
            $migration->down();
            $source->resetDataCache();
            $this->assertFalse($source->tableExists('access_grants'));
            $this->assertNotContains('auth_version', $source->getFieldNames('users'));
            $migration->up();
            $source->resetDataCache();
            $this->assertTrue($source->tableExists('access_audit'));
            $this->assertSame(2, $source->table('users')->countAllResults());
        } finally {
            foreach ($connections as $connection) $connection->close();
            foreach ($created as $name) {
                if (!preg_match('/^i1_verify_[a-f0-9]{16}_(source|restore)$/D', $name)) throw new \RuntimeException('Unsafe cleanup target.');
                $admin->query('DROP DATABASE `' . $name . '`');
            }
            $admin->close();
        }
    }
}
