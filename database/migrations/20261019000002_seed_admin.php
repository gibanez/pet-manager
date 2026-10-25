<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Ramsey\Uuid\Uuid;

final class SeedAdmin extends AbstractMigration
{
    public function up(): void
    {
        $this->table('admins')->insert([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password_hash' => (string) password_hash('admin1234', PASSWORD_BCRYPT),
            'status' => 'active',
        ])->save();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM admins WHERE email = 'admin@example.com'");
    }
}
