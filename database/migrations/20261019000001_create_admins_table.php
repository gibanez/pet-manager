<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAdminsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('admins', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('email', 'string', ['limit' => 190])
            ->addColumn('password_hash', 'string', ['limit' => 255])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'active'])
            ->addIndex(['email'], ['unique' => true])
            ->create();
    }
}
