<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Ramsey\Uuid\Uuid;

final class SeedPages extends AbstractMigration
{
    public function up(): void
    {
        $rows = [];

        foreach ($this->seedPages() as $page) {
            $rows[] = [
                'id' => Uuid::uuid4()->toString(),
                'slug' => $page['slug'],
                'title' => $page['title'],
                'body' => $page['body'],
                'status' => 'active',
            ];
        }

        $this->table('pages')->insert($rows)->save();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM pages WHERE slug IN ('home', 'about', 'contact')");
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function seedPages(): array
    {
        return [
            [
                'slug' => 'home',
                'title' => 'Bienvenido a Base DDD',
                'body' => "Esta home la sirve Slim y el contenido lo administra el CMS.\n\n"
                    . "Ingresá a /admin/ para editarla. El resto de las páginas (about, contact) "
                    . "son vistas estáticas del sitio.",
            ],
            [
                'slug' => 'about',
                'title' => 'Sobre nosotros',
                'body' => 'Página de ejemplo administrada desde el CMS.',
            ],
            [
                'slug' => 'contact',
                'title' => 'Contacto',
                'body' => 'Escribinos y te respondemos a la brevedad.',
            ],
        ];
    }
}
