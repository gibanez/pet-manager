<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePagesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('pages', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('slug', 'string', ['limit' => 120])
            ->addColumn('title', 'string', ['limit' => 190])
            ->addColumn('body', 'text')
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'active'])
            ->addIndex(['slug'], ['unique' => true])
            ->create();
    }
}
