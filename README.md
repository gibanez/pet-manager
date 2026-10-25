# php-ddd-base

Repo base para proyectos **PHP 7.4 + Slim 4 + DDD liviano** (Domain / Application / Infrastructure), pensado para usarse con **OpenCode** y para **forkear** en cada proyecto nuevo.

OpenCode **no lee `CLAUDE.md`**: lee **`AGENTS.md`**. Por eso las convenciones genéricas viven en `AGENTS.md`, y lo específico de cada proyecto vive en `proyect.md`.

> **Sin preguntas de arranque.** La base es 100% genérica: no pide ni necesita el nombre del proyecto (usa `{Root}` y `{Modulo}` como placeholders). Al abrir un fork, OpenCode carga `AGENTS.md` + `proyect.md` en silencio.

## Qué contiene

| Archivo | Rol | ¿Se edita por proyecto? |
| --- | --- | --- |
| `AGENTS.md` | Convenciones genéricas del stack (arquitectura, DI, rutas, migraciones, tests, deploy). | No (o casi nunca; los cambios se comparten a todos los forks). |
| `proyect.md` | Documentación específica: módulos, decisiones propias, deploy, gotchas. | **Sí**, en cada fork. |
| `opencode.jsonc` | Le dice a OpenCode que cargue `proyect.md` además de `AGENTS.md`. | No. |
| `README.md` | Este documento. | No. |
| `.gitignore` | Ignora `vendor/`, `.env`, `storage/*.sqlite`, builds, etc. | No. |
| `composer.json` | Dependencias (Slim 4, PHP-DI 6, Phinx, lcobucci/jwt, Pest). | No. |
| `public/` | Entry point de Slim, sitio, CMS y assets. | No. |
| `src/Apps/` | API (`Auth`, `Admin`, `Page`, `Inquiry`) y sitio. | No. |
| `database/migrations/` | Esquema y semillas (admin y páginas). | No. |

### Regla de oro

- **`AGENTS.md`** = cómo se construye *cualquier* proyecto de este stack. Estable.
- **`proyect.md`** = qué es *este* proyecto y qué hizo distinto. Se completa al crear el fork. Si una decisión de `proyect.md` contradice a `AGENTS.md`, gana `proyect.md` (se carga después).

## Cómo lo carga OpenCode

1. `~/.config/opencode/AGENTS.md` (global, si existe): aplica a todos los proyectos.
2. `<proyecto>/AGENTS.md`: se carga solo.
3. `<proyecto>/opencode.jsonc` → `"instructions": ["proyect.md"]`: carga la doc del proyecto.
4. `AGENTS.md` anidados en subcarpetas: se cargan cuando el agente lee esa zona.

## Correr el MVP (local)

Requisitos: PHP 8.x (el objetivo del código es 7.4), Composer y las extensiones `pdo_sqlite`, `mbstring`, `openssl`.

```powershell
cd C:\Users\German\Documents\php-ddd-base
composer install
Copy-Item .env.example .env      # y generá un JWT_SECRET de 64 hex
vendor/bin/phinx migrate
php -S localhost:9980 -t public
```

Luego abrí:

| URL | Qué es |
| --- | --- |
| `http://localhost:9980/` | Home (contenido desde el CMS) |
| `http://localhost:9980/about` | About us |
| `http://localhost:9980/contact` | Contacto (guarda en `inquiries`) |
| `http://localhost:9980/admin/` | CMS — `admin@example.com` / `admin1234` |
| `http://localhost:9980/api/ping` | Health check |

### Rutas de la API

| Método | Ruta | Auth |
| --- | --- | --- |
| `GET` | `/api/ping` | público |
| `POST` | `/api/auth/login` | público |
| `GET` | `/api/public/pages/{slug}` | público |
| `GET` | `/api/pages` | JWT |
| `GET` | `/api/pages/{id}` | JWT |
| `POST` | `/api/pages` | JWT |
| `PUT` | `/api/pages/{id}` | JWT |
| `DELETE` | `/api/pages/{id}` | JWT |
| `POST` | `/api/inquiries` | público |
| `GET` | `/api/inquiries` | JWT |

Tests: `vendor/bin/pest`.

## Crear un proyecto desde esta base

### Opción A — Fork en GitHub (recomendada)

1. Subí esta base una vez (ver abajo).
2. En GitHub, **Fork** del repo (o *Use this template*).
3. Cloná el fork con el nombre del proyecto nuevo:

   ```powershell
   git clone https://github.com/<tu-usuario>/php-ddd-base.git mi-proyecto
   cd mi-proyecto
   ```

4. Completá `proyect.md` (nombre, módulos, deploy, etc.).
5. Arrancá el backend según el checklist de `AGENTS.md`.
6. `git remote rename origin upstream` si querés mantener el fork enlazado a la base.

### Opción B — Copia local

```powershell
Copy-Item -Recurse C:\Users\German\Documents\php-ddd-base C:\xampp\htdocs\mi-proyecto
Remove-Item C:\xampp\htdocs\mi-proyecto\.git -Recurse -Force -ErrorAction SilentlyContinue
cd C:\xampp\htdocs\mi-proyecto
git init
```

Después completá `proyect.md` y seguí el checklist de `AGENTS.md`.

## Subir esta base a git (una sola vez)

```powershell
cd C:\Users\German\Documents\php-ddd-base
git init
git add .
git commit -m "Base PHP 7.4 + Slim 4 + DDD (convenciones y estructura)"
git branch -M main
git remote add origin https://github.com/<tu-usuario>/php-ddd-base.git
git push -u origin main
```

En GitHub conviene marcarlo como **Template repository** (Settings → Template repository) para que aparezca el botón *Use this template* además del fork.

## Migrar un proyecto que ya existe

1. `AGENTS.md` genérico: copialo o referencialo desde `opencode.jsonc` (`"instructions": ["~/Documents/php-ddd-base/AGENTS.md"]`).
2. La doc propia del proyecto (antes `CLAUDE.md`): renombrala a `proyect.md` y agregala a `instructions`.
3. Cualquier archivo que se llamara `CLAUDE*.md`: renombralo (OpenCode no los lee).
