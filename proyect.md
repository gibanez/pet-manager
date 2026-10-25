# Proyecto — documentación específica

> **Qué es este archivo.** Documenta **este proyecto en particular**: qué módulos tiene, qué decisiones propias tomó y cómo se despliega. Se completa/edita en cada fork.
> `AGENTS.md` contiene las convenciones **genéricas** del stack (iguales en todos los proyectos): no hace falta tocarlo acá.
> OpenCode carga este archivo automáticamente porque está listado en `opencode.jsonc` (`instructions`).
> Si una decisión de este proyecto contradice a `AGENTS.md`, gana este archivo (se carga después).

## Identidad

- **Nombre:** php-ddd-base
- **Descripción:** base/MVP de backend PHP 7.4 + Slim 4 + DDD liviano, con CMS (login + CRUD de páginas) y sitio público (home / about / contact).
- **Estado:** prototipo funcional (se usa como plantilla para forks).
- **Repositorio:** https://github.com/gibanez/php-ddd-base (público)

## Stack

- **PHP:** objetivo 7.4 (`config.platform` en `composer.json`); probado localmente en PHP 8.5.
- **Base de datos:** SQLite (`storage/database.sqlite`), migrada con Phinx.
- **Frontends:** HTML + CSS + JS sin build (sitio en `public/`, CMS en `public/admin/`).
- **Hosting / deploy:** `php -S localhost:9980 -t public` para desarrollo; Apache con `public/.htaccess` para producción.

## Módulos

| Módulo | Ruta base | Entidad principal | Estado |
| --- | --- | --- | --- |
| `Auth` | `POST /api/auth/login` | – | listo (JWT HS256) |
| `Admin` | – | `Admin` | listo (repositorio + seed) |
| `Page` | `/api/pages` (protegido) y `/api/public/pages/{slug}` | `Page` | listo (CRUD + lectura pública) |
| `Inquiry` | `POST /api/inquiries` y `GET /api/inquiries` (protegido) | `Inquiry` | listo |

Rutas de salud: `GET /api/ping`.

## Autenticación

- `POST /api/auth/login` recibe `{ email, password }` y devuelve `{ token, admin }`.
- Token JWT HS256 firmado con `JWT_SECRET`, con `sub` = id del admin y vencimiento `JWT_TTL`.
- `JwtAuthMiddleware` protege las rutas de escritura de `Page` y el listado de `Inquiry`.
- El CMS guarda el token en `localStorage` (`cms_token`) y lo manda como `Authorization: Bearer …`.
- Usuario semilla de desarrollo: `admin@example.com` / `admin1234`.

## Frontends / apps

- **Sitio** (`src/Apps/Site/Infrastructure/Views`): `home.html`, `about.html`, `contact.html`, servidos por `SiteController`. Home carga `GET /api/public/pages/home`; el contacto hace `POST /api/inquiries`.
- **CMS** (`public/admin/`): página estática con `admin.js`. Login, listado, alta/edición y borrado de páginas.
- Assets del sitio en `public/assets/` (`site.css`, `site.js`).

## Base de datos y migraciones propias

- `admins`: `id` (UUID), `name`, `email` único, `password_hash`, `status`.
- `pages`: `id` (UUID), `slug` único, `title`, `body`, `status`.
- `inquiries`: `id` (UUID), `name`, `email`, `message`, `created_at`.
- Semillas: un admin de desarrollo y las páginas `home`, `about`, `contact`.
- Comandos: `vendor/bin/phinx migrate`, `vendor/bin/phinx rollback`, `vendor/bin/phinx status`.

## Decisiones y patrones propios

- El CMS es **vanilla JS** a propósito: cero build, se abre en `/admin/` y consume la API con `fetch`.
- El sitio también es estático salvo la home, que se hidrata desde la API.
- El slug se normaliza en el controller (`strtolower`, espacios → `-`, solo `[a-z0-9-]`).
- `Page::STATUS_ACTIVE` / `STATUS_INACTIVE`; la ruta pública devuelve 404 si la página está inactiva.
- `ext-sodium` se declara en `config.platform` de Composer porque el PHP local no la trae; HS256 no la usa.

## Deploy

1. `composer install --no-dev --optimize-autoloader` (con `--ignore-platform-req=ext-sodium` si hiciera falta).
2. Apuntar el document root a `public/` (o usar el `.htaccess` raíz descripto en `AGENTS.md`).
3. Generar `.env` con `JWT_SECRET` propio y `APP_DEBUG=false`.
4. Subir `storage/database.sqlite` ya migrado (o correr `phinx migrate` en el server).

## Gotchas

- `lcobucci/jwt 4.3`: `ValidAt` recibe un `Lcobucci\Clock\Clock`, no un `DateTimeImmutable`; usar `new ValidAt(SystemClock::fromUTC())`.
- En el PHP local de esta máquina falta `ext-zip`: Composer cae a clonar cada paquete (lento). Correr `php -d extension=zip C:\composer\composer.phar install` o habilitar `extension=zip` en el `php.ini`.
- `php -S` no lee `.htaccess`: las rutas caen al `index.php` por el fallback del servidor embebido.
- El sitio vive en `public/`; el CMS en `public/admin/`. No moverlos sin ajustar las rutas de los assets.
- `APP_DEBUG=true` muestra el detalle de las excepciones: apagarlo en producción.
- Si se cambia el usuario semilla, actualizar también la leyenda del login del CMS.

## Pendientes

- [ ] Marcar el repo como template en GitHub (Settings → Template repository).
- [ ] Agregar tests de integración HTTP (hoy hay tests de dominio/aplicación con Pest).
- [ ] Sumar un módulo de usuarios/administradores con CRUD (hoy el admin solo se siembra).
- [ ] Reemplazar el front vanilla por Astro/Vue si el proyecto lo requiere.
