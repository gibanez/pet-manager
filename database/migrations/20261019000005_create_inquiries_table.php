<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateInquiriesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('inquiries', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('name', 'string', ['limit' => 120])
            ->addColumn('email', 'string', ['limit' => 190])
            ->addColumn('message', 'text')
            ->addColumn('created_at', 'datetime')
            ->create();
    }
}
