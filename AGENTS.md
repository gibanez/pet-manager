# Convenciones base — backend PHP + Slim 4 + DDD

> **Qué es este archivo.** Base compartida de convenciones para proyectos PHP con Slim 4 y DDD liviano.
> OpenCode lo carga automáticamente como instrucciones del proyecto (OpenCode V2 lee `AGENTS.md`, **no** `CLAUDE.md`).
> Para reutilizarlo en otro proyecto: copiá este `AGENTS.md` a la raíz del proyecto, o referencialo desde el
> `opencode.jsonc` del proyecto con `"instructions": ["~/Documents/php-ddd-base/AGENTS.md"]`.
> Ver `README.md` de este repo para las tres formas de uso.

API REST en PHP con Slim 4. Código organizado por módulos con esquema DDD liviano (Domain / Application / Infrastructure). Este documento describe **cómo se construye el backend** y las reglas a seguir al generar código nuevo.

> Convenciones: `{Root}` = namespace raíz del proyecto (mapeado a `src/` por PSR-4). `{Modulo}` / `{App}` = nombres PascalCase. Todo el código y los mensajes de error de la API están en inglés; la documentación, en español.

## Stack y restricciones

- **PHP 7.4** (verificar con `php -v` al arrancar un proyecto: la elección de versiones de paquetes depende de eso). Sin `enum` (usar constantes de clase), sin union types, sin named arguments, sin propiedades tipadas (se tipa con docblocks `/** @var string */`). Sí se usan tipos en parámetros/retornos de métodos (`?string`, `: void`, `: array`).
- Paquetes (`composer.json`):
  - `slim/slim ^4` + `nyholm/psr7` + `nyholm/psr7-server` (Slim 4 no trae implementación PSR-7).
  - `php-di/php-di ^6` — **v6, no v7** (v7 pide PHP 8; v5 no implementa PSR-11 y Slim 4 lo rechaza). El helper es `DI\autowire()`.
  - `ramsey/uuid ^4.2` — ids.
  - `robmorgan/phinx ^0.14` — migraciones.
  - `lcobucci/jwt ^4` — JWT. **No usar `firebase/php-jwt`**: todas sus versiones compatibles con PHP 7.4 (<7.0) tienen una advisory sin parche (CVE-2025-45769) y Composer las bloquea.
  - dev: `pestphp/pest ^1.23` (requiere `"config": {"allow-plugins": {"pestphp/pest-plugin": true}}`).
- Composer bloquea paquetes con advisories de seguridad: si una instalación falla por eso, buscar una alternativa en vez de desactivar el chequeo.
- Si `composer require` elige una versión que exige PHP 8, instalar la última compatible con 7.4 de forma explícita (`composer require "paquete:^X"`).

## Estructura de carpetas

```
public/                      # document root: index.php (entry point), .htaccess, media/ (uploads)
src/
    Apps/
        routes.php           # loader: require de las rutas de cada app
        definitions.php      # loader: array_merge de las definiciones de DI (Shared + cada app)
        {App}/               # una "app" = un bloque de API (ej. Api); rutas bajo /api
            routes.php       #   $app->group('/api', ...) + require de las rutas de cada módulo
            definitions.php  #   array_merge de las definiciones de cada módulo
            {Modulo}/
                Domain/
                Application/
                Infrastructure/
    Shared/                  # infraestructura transversal (Config/Env, Http/JsonResponseTrait, Persistence/SqliteConnection, Container/definitions)
    tests/                   # espejo de src/Apps (ver Tests)
database/migrations/         # migraciones Phinx
storage/                     # base SQLite y archivos no públicos
phinx.php  phpunit.xml  composer.json  .env
```

Namespace: `{Root}\Apps\{App}\{Modulo}\{Capa}\...`; compartido: `{Root}\Shared\...`; tests: `{Root}\Tests\...`.

### Capas de un módulo

- **Domain**: entidades, value objects, interfaces de repositorio, excepciones de dominio y reglas de negocio puras. No depende de Slim, de PDO ni de nada de `Infrastructure`. Únicas dependencias externas permitidas: funciones nativas (`password_hash`/`password_verify`) y `Ramsey\Uuid\Uuid` para generar ids.
- **Application**: casos de uso. **Un directorio por caso de uso** con un `Command`/`Query` (DTO de propiedades públicas con constructor tipado) y un `Handler` con un método `handle(...)`. Depende de `Domain`, nunca de `Infrastructure`.
- **Infrastructure**: implementaciones concretas.
  - `Http/{Modulo}Controller.php`: un método por acción REST (`create`, `list`, `show`, `update`, `delete`). Recibe `Request`/`Response` PSR-7, valida campos obligatorios (422), normaliza entradas (`''` → `null`, strings → bool), llama al handler y **traduce excepciones de dominio a HTTP** (404 no existe, 409 conflicto/duplicado, 422 validación, 401 auth). Respuestas siempre JSON vía `JsonResponseTrait`; los borrados devuelven 204 sin cuerpo.
  - `Persistence/Sqlite{Modulo}Repository.php`: implementa `{Modulo}RepositoryInterface`.
  - `routes.php`: solo registra rutas apuntando a `[Controller::class, 'metodo']`.
  - `definitions.php`: solo si hay algo que no se pueda autowirear (típicamente el bind interface → implementación).

### Estilo de las entidades

- Propiedades privadas con docblock, constructor privado, factories estáticas `create(...)` (genera el id, estado inicial) y `fromPersistence(...)` (rehidrata), getters sin prefijo (`name()`), `toArray()` para JSON, y métodos con intención (`rename()`, `activate()`, `deactivate()`, `updateDetails(...)`) en vez de setters.
- Estados como constantes (`STATUS_ACTIVE`, `STATUS_INACTIVE`).
- Excepciones de dominio con constructores nombrados: `NotFoundException::withId($id)`.
- Si una regla depende de "hoy", el método de dominio recibe la fecha (`isVisibleOn(string $today)`); la fecha la pasa el controller. Así la regla es testeable sin reloj.

## Ids: siempre UUID

Todas las entidades usan UUID v4 (string): `Uuid::uuid4()->toString()` dentro del `create()` de la entidad, **nunca** en `Infrastructure`. Columnas `VARCHAR(36)` como clave primaria. Como el id viene del dominio, `save()` del repositorio hace upsert:

```sql
INSERT INTO tabla (id, ...) VALUES (:id, ...)
ON CONFLICT(id) DO UPDATE SET campo = excluded.campo, ...
```

Otras convenciones de repositorio: booleanos como `1`/`0` y se rehidratan con `(bool)`; listados `ORDER BY rowid ASC`; `findById` devuelve `?Entidad`; para "existe por X excluyendo este id" usar `existsByX(string $x, ?string $excludeId = null)`.

## Inyección de dependencias (PHP-DI, autowiring)

`public/index.php` arma el contenedor y se lo pasa a Slim: `ContainerBuilder` → `addDefinitions(require src/Apps/definitions.php)` → `build()` → `AppFactory::setContainer($container)`. **No se instancia nada a mano** en rutas, controllers ni handlers: las clases con constructor tipado se resuelven solas. Slim resuelve `[Controller::class, 'metodo']` y los middlewares por nombre de clase contra el contenedor.

Solo se declara lo que no se puede inferir:

```php
// Infrastructure/definitions.php del módulo
use function DI\autowire;
return [ {Modulo}RepositoryInterface::class => autowire(Sqlite{Modulo}Repository::class) ];

// Shared/Infrastructure/Container/definitions.php — recursos compartidos
use function DI\factory;
return [ \PDO::class => factory(function () { return SqliteConnection::create(); }) ];
```

Las definiciones se agregan **en cascada**, igual que las rutas: `src/Apps/definitions.php` (Shared + apps) → `src/Apps/{App}/definitions.php` (`array_merge(require ...)` de cada módulo). Un módulo nuevo suma una línea ahí; **nunca se toca `public/index.php`**. Cuidar la coma del último `require` dentro del `array_merge`.

## Rutas

- `src/Apps/{App}/routes.php` define `$app->group('/api', function (RouteCollectorProxy $group) { ... })` con un `/ping` de health check y un `require` por módulo; los `routes.php` de cada módulo usan la variable `$group` del scope.
- **Rutas mixtas públicas/protegidas** (el patrón por defecto cuando un front público lee y un panel escribe): lecturas sueltas fuera de todo `group()`, escrituras dentro de un subgrupo con middleware:

```php
$group->get('/things', [ThingController::class, 'list']);          // público
$group->get('/things/{id}', [ThingController::class, 'show']);

$group->group('', function (RouteCollectorProxy $writeGroup) {     // protegido
    $writeGroup->post('/things', [ThingController::class, 'create']);
    $writeGroup->put('/things/{id}', [ThingController::class, 'update']);
    $writeGroup->delete('/things/{id}', [ThingController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
```

- Un controller reutilizado por varias URLs recibe contexto con `->setArgument('clave', 'valor')` en la ruta (llega en `$args` junto con los placeholders). Útil para módulos polimórficos.
- `public/index.php` agrega `addBodyParsingMiddleware()` (sin eso `getParsedBody()` es `null`) y el middleware de errores. **En producción `displayErrorDetails` debe ser `false`** (filtra rutas y stack traces); lo ideal es leerlo de una variable de entorno (`APP_DEBUG`).
- Fijar la zona horaria en el bootstrap: `date_default_timezone_set(Env::get('APP_TIMEZONE', '<zona>'))`. Los hosts compartidos corren en UTC y cualquier regla de "hoy" (vigencias, turnos) se corre horas.

## `src/Shared/` (a copiar al arrancar)

- `Config/Env.php`: lee `.env` (`KEY=VALUE`, ignora vacías y `#`), cacheado en una propiedad estática. `Env::get($key, $default = null)` y `Env::rootPath()` (raíz del proyecto, calculada con `dirname(__DIR__, N)`).
- `Http/JsonResponseTrait.php`: `json(Response $response, array $payload, int $status): Response` — escribe `json_encode`, header `Content-Type: application/json` y status. Los controllers lo usan con `use JsonResponseTrait;`.
- `Persistence/SqliteConnection.php`: `create(): PDO` (singleton) a SQLite; ruta desde `.env` (`DB_PATH`, relativa a la raíz); crea la carpeta si falta; `ERRMODE_EXCEPTION` y `PRAGMA foreign_keys = ON`.
- `Container/definitions.php`: el bind de `\PDO::class` de arriba.

`.env` mínimo: `DB_PATH=storage/database.sqlite`, `JWT_SECRET=<64 hex>`, `JWT_TTL=3600`, `APP_TIMEZONE=...`. **El `.env` y `storage/` nunca deben quedar accesibles por HTTP.**

## Migraciones (Phinx)

- **Ningún repositorio crea tablas.** Todo el esquema vive en `database/migrations/`, una migración por tabla/cambio (`vendor/bin/phinx create CreateXTable`).
- `phinx.php` lee `DB_PATH` del mismo `.env` y usa el adapter `sqlite` con `'suffix' => ''` (si no, Phinx le agrega `.sqlite3`).
- Comandos: `vendor/bin/phinx migrate | rollback | status`.
- Tablas con id UUID: `$this->table('x', ['id' => false, 'primary_key' => 'id'])` + `addColumn('id', 'string', ['limit' => 36])`. FKs: `addForeignKey('col', 'otra', 'id', ['delete' => 'SET_NULL'|'CASCADE', 'update' => 'CASCADE'])`.
- **Trampas conocidas:**
  - Dos `phinx create` en el mismo segundo producen el **mismo timestamp** y chocan: renombrar el archivo de una (el nombre de clase no cambia).
  - SQLite no altera FKs/columnas: Phinx reconstruye la tabla en `changeColumn`/`removeColumn`/`addForeignKey` sobre tabla existente. Funciona, pero con `foreign_keys` activo **el orden importa**: antes de borrar o reconstruir una tabla referenciada, repuntar o reconstruir primero las tablas hijas (si no, un `ON DELETE CASCADE` borra datos). En cambios con datos, usar SQL explícito en `up()` con `$this->execute(...)`: crear tabla nueva → copiar → borrar vieja → renombrar → recrear índices.
  - Validar precondiciones **antes** de escribir (ej. contar filas huérfanas y lanzar excepción): Phinx envuelve la migración en una transacción y la excepción hace rollback.
  - Migraciones de datos irreversibles: `down()` lanza `Phinx\Migration\IrreversibleMigrationException`. Antes de correrlas, copiar `storage/database.sqlite`.
  - No editar migraciones ya aplicadas: agregar una nueva (el historial queda en `phinxlog`).
  - Verificar después con `PRAGMA foreign_key_check` y contando filas.
  - **Quitar una columna de una tabla "padre" (con hijas `ON DELETE CASCADE`) no se hace**: el `SQLite` de PHP 7.4 es 3.31 (`DROP COLUMN` llegó en 3.35) y reconstruir la tabla dentro de la transacción de Phinx borraría en cascada a las hijas (`PRAGMA foreign_keys=OFF` no tiene efecto dentro de una transacción). En su lugar: crear la tabla nueva, **copiar los datos con `INSERT ... SELECT`** y dejar las columnas viejas **sin usar** (nullable, sin leer ni escribir, anotado en la migración).
- Hosting sin SSH (ej. InfinityFree): no se puede correr Phinx en el server; se sube el `database.sqlite` ya migrado (pisa el del server).

## Tests (Pest)

- Viven en `src/tests/`, espejo de `src/Apps/{App}/{Modulo}/{Capa}/`; namespace `{Root}\Tests\...` vía `autoload-dev`; `phpunit.xml` apunta el testsuite a `./src/tests` con `suffix="Test.php"`. Correr: `vendor/bin/pest`.
- Un archivo por clase relevante (`LoginHandlerTest.php`). Los **doubles** (repositorios en memoria, fakes de interfaces de Domain) van en `Doubles/` dentro del módulo del test; no terminan en `Test.php`, así que no corren como tests.
- Se testea sin base de datos ni HTTP: handlers con repositorios en memoria, reglas de dominio directamente (fechas, validaciones, normalizadores).
- Las funciones helper declaradas en un archivo de test son **globales**: sus nombres tienen que ser únicos en toda la suite (colisión → error fatal).
- Test de integración manual de la API: levantar `php -S localhost:9980 -t public` y usar `curl`.

## Autenticación (módulo Auth + JWT)

- `Domain/TokenGeneratorInterface` (`generate($subjectId, $email)`), `InvalidCredentialsException`. `Application/Login`: `LoginHandler` busca por email en el repositorio del módulo de usuarios, verifica el password (`password_verify`), chequea que esté activo y genera el token. `Infrastructure`: `AuthController` (`POST /api/auth/login` → 401 si falla), `JwtConfigFactory` (HS256 con `JWT_SECRET`, `JWT_TTL`), `JwtTokenGenerator`, y `JwtAuthMiddleware` (PSR-15).
- El middleware lee `Authorization: Bearer <token>`, valida firma y vigencia (`SignedWith` + `ValidAt`) y responde 401 JSON (`Missing bearer token.` / `Invalid or expired token.`). Si es válido, deja el id del sujeto en `$request->getAttribute('...')`. Necesita un `ResponseFactoryInterface` bindeado (`Nyholm\Psr7\Factory\Psr17Factory`) en el contenedor.
- Se protege agregando el middleware **por nombre de clase** al subgrupo de rutas (ver Rutas).
- Rotar `JWT_SECRET` si el `.env` estuvo expuesto; cambiar passwords semilla antes de publicar.

## Dependencias entre módulos

- Regla general: un módulo no depende de otro. Excepciones aceptadas, siempre del lado "consumidor" y **solo contra interfaces de `Domain`** del otro módulo: `Auth` lee el repositorio de usuarios; un módulo que guarda una referencia (`categoryId`, `serviceId`) valida con el repositorio del módulo referenciado en su `Create`/`Update` handler.
- Excepción puntual `Application → Application`: borrar un dueño llama al handler de limpieza del módulo de archivos (ver Media).
- **Nunca la dependencia inversa** (el módulo referenciado no conoce al que lo referencia).
- Dos conceptos con ciclo de vida propio van en módulos separados; algo que no tiene sentido sin su padre (sub-entidad) va dentro del módulo del padre.

## Patrones reutilizables (copiar cuando aplique)

- **Alta rápida de una entidad**: campos obligatorios mínimos en el dominio y el resto `nullable` (también en la migración); `Entidad::createQuick(...)` crea la versión mínima sin password (no puede loguearse hasta completar el perfil) y `create(...)` el alta completa.
- **Vigencia por fechas + destacado**: campos `starts_at`/`ends_at` (`Y-m-d`, nulos = sin límite, ambos inclusivos) y `featured`. La regla vive en `isVisibleOn($today)` del dominio (único lugar); `endsAt < startsAt` o fecha inválida lanza `InvalidDateRangeException` (422). El listado acepta `?visible=1` y `?featured=1` y filtra en el handler usando la regla del dominio; **sin filtros devuelve todo** (para el panel). Cuidar `''` → `null` en el controller.
- **Media polimórfico compartido** (imágenes/videos de varios tipos de entidad): tabla `media(id, owner_type, owner_id, type, path, original_filename, mime_type, position)` **sin FK**. Un `MediaController` único registrado por loop en las rutas de cada dueño (`GET|POST /{recurso}/{id}/media`, `DELETE .../media/{mediaId}`) con `setArgument('ownerType', ...)`. Interface `OwnerExistenceCheckerInterface` en Domain (la implementación en Infrastructure usa los repositorios de los dueños). Borrar un dueño llama a `DeleteOwnerMediaHandler` (borra archivos y filas): al no haber FK, **es la única forma**. Borrar un `Media` suelto exige que pertenezca al dueño de la URL (si no, 404). Archivos en `public/media/{recurso}/{ownerId}/{uuid}.{ext}`; el `type` se **infiere del MIME** con whitelist (jpeg/png/webp/gif → image; mp4/webm/quicktime → video; otro → 422), límite 10 MB; `store()` (depende de PSR-7) vive en la clase concreta de storage, y a `Application` solo se le expone `delete()` mediante `MediaStorageInterface`. Sumar un tipo de dueño nuevo = constante + entrada en el loop de rutas + `case` en el checker + llamada al cleanup en su `Delete*Handler`.
- **Value object de entrada** (ej. teléfono normalizado a una forma canónica): vive en `Domain`, se construye en el **controller** (`Phone::fromInput`, lanza excepción → 422) y entra a `Application` ya normalizado, para que las búsquedas por ese campo siempre comparen la misma forma.

## Entry point, `.htaccess` y deploy (lado backend)

- `public/index.php`: autoload → zona horaria → contenedor → `AppFactory` → body parsing + errores → rutas → `run()`.
- `public/.htaccess` (Apache): `DirectoryIndex index.html index.php`, y reescribir a `index.php` **solo si el pedido no es archivo ni directorio**: `RewriteCond %{REQUEST_FILENAME} !-f` + `RewriteCond %{REQUEST_FILENAME} !-d` + `RewriteRule ^ index.php [QSA,L]`. Sin el `!-d`, el pedido a `/` (un directorio) se salta el `DirectoryIndex`.
- El servidor `php -S localhost:9980 -t public` **no lee `.htaccess`** y manda todo pedido sin archivo a `index.php`; no aplica `DirectoryIndex`.
- Hosting con document root fijo en la raíz del proyecto (ej. InfinityFree: siempre `/htdocs`): `.htaccess` extra **en la raíz** con `RewriteCond %{REQUEST_URI} !^/public/` + `RewriteRule ^(.*)$ public/$1 [L]`. Deja inalcanzables por HTTP `.env`, `storage/`, `vendor/`, `src/`. Supone sitio en la raíz de un dominio y el contenido del proyecto directo en el docroot. No subir `*.zip`/`*.rar` ni backups de la base. Si el hosting deja apuntar el docroot a `public/`, no hace falta.
- Subir `vendor/` sin dependencias de dev (`composer install --no-dev --optimize-autoloader`, en una copia) para reducir cantidad de archivos.

## Reglas al generar código nuevo

1. Antes de crear un módulo, mirar si ya existe en `src/Apps/{App}/` y reutilizar su estructura.
2. Un módulo nuevo nace siempre con `Domain`, `Application` e `Infrastructure`.
3. `Domain` sin `use` de `Infrastructure`, Slim ni librerías (salvo `Ramsey\Uuid\Uuid`); `Application` solo contra interfaces de `Domain`.
4. Ids UUID generados en la entidad; upsert en `save()`.
5. Un directorio por caso de uso (`Command|Query` + `Handler`).
6. Nada se instancia a mano: DI por autowiring; el bind de la interface va en `Infrastructure/definitions.php` y se suma un `require` en el `definitions.php` de la app.
7. Las rutas viven en `Infrastructure/routes.php` del módulo y se suman con un `require` en el `routes.php` de la app; nunca en `public/index.php`.
8. Si algunas rutas son públicas y otras no: lecturas sueltas, escrituras en subgrupo con middleware.
9. El esquema solo por migración Phinx; no editar migraciones aplicadas.
10. Cada módulo con lógica de dominio o de aplicación trae tests (con doubles en `Doubles/`).
11. Validaciones de entrada y traducción de excepciones a HTTP solo en el controller.
12. Respetar las restricciones de PHP 7.4 (sin enums, union types ni named args).
13. Dejar documentado en este archivo cualquier patrón o decisión nueva que otro módulo pueda reutilizar.

## Arranque de un proyecto nuevo (checklist)

1. `composer init`; instalar los paquetes de arriba; `autoload` PSR-4 `{Root}\\` → `src/` y `autoload-dev` `{Root}\\Tests\\` → `src/tests/`.
2. Crear `public/index.php` (bootstrap), `public/.htaccess`, `src/Apps/routes.php`, `src/Apps/definitions.php`, `src/Apps/{App}/routes.php` (con `/ping`) y `definitions.php`.
3. Copiar/crear `src/Shared/` (Env, JsonResponseTrait, SqliteConnection, Container/definitions con `PDO` y `ResponseFactoryInterface`).
4. `.env` (`DB_PATH`, `JWT_SECRET`, `JWT_TTL`, `APP_TIMEZONE`, `APP_DEBUG`), `phinx.php`, `phpunit.xml`, carpetas `database/migrations` y `storage`.
5. Primer módulo (ej. el de usuarios administradores) y el módulo `Auth`; primer migración; `vendor/bin/pest`; `php -S localhost:9980 -t public` y probar `/api/ping`.

## Gotchas de desarrollo en Windows (Git Bash / XAMPP)

- `curl` con cuerpos JSON que tienen tildes: la consola manda otra codificación y el body llega inválido (`getParsedBody()` vacío → 422 engañoso). Mandar el JSON desde un archivo UTF-8 (`--data-binary @archivo.json`, generado con `json_encode(..., JSON_UNESCAPED_UNICODE)`).
- No hay `/tmp` escribible en la raíz del disco: usar una carpeta dentro del proyecto para archivos temporales y borrarla al terminar.
- Un `php -S` ya corriendo hay que reiniciarlo (`taskkill //F //IM php.exe`) si se cambian `definitions.php`/`routes.php` y no se ven los cambios.
