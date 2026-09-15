# Stack

Backend for Qlinqs (see `qlinqs.md`). API-only — no Blade views, no web
routes, no frontend build tooling.

## Technologies (versions actually installed)

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | 8.5.10 (in Docker image `php:8.5-cli-alpine`) / 8.5.5 on host |
| Framework | Laravel | v13.31.0 |
| Auth | Laravel Sanctum | v4.3.3 |
| Database | PostgreSQL | 17.11 (image `postgres:17-alpine`) |
| Test runner | PestPHP | v5.2.0 (+ `pestphp/pest-plugin-laravel` v5) |
| API docs | Scramble (dedoc/scramble) | v0.13.43 |
| AI/dev tooling | Laravel Boost | v2.9.0 |
| Containers | Docker Compose | via Colima |
| Package manager | Composer | 2.9.7 |

Full pinned versions live in `composer.lock`.

## Containers

Defined in `docker-compose.yml` (project name `qlinqs-core-api`):

- `qlinqs-core-api-app` — PHP 8.5 CLI (Alpine) built from `Dockerfile`, runs
  `php artisan serve --host=0.0.0.0 --port=8000 --no-reload`, project mounted
  at `/var/www/html`, published on host port **8000**.
  - `--no-reload` is required: without it, Laravel's dev server strips all
    non-whitelisted env vars from the served process on every request,
    which breaks the `DB_HOST=db`/`DB_PORT=5432` override below.
- `qlinqs-core-api-db` — `postgres:17-alpine`, database `qlinqs_core_api`,
  user/password `qlinqs`/`qlinqs`, published on host port **5433** (the host
  already has a Homebrew Postgres on 5432, so this is deliberately offset).

Inside the `app` container, `DB_HOST=db` / `DB_PORT=5432` are injected via
`docker-compose.yml`'s `environment:` block (overriding `.env`, which is
written for host-side tools connecting to `127.0.0.1:5433`).

## Commands

### Docker

```sh
colima start                 # if the Docker daemon is unreachable
docker compose build         # build the app image
docker compose up -d         # start app + db in the background
docker compose ps            # check status/ports
docker compose logs -f app   # tail app logs
docker compose down          # stop and remove containers (keeps the db volume)
```

### Artisan (run inside the container)

```sh
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app php artisan tinker
```

Host-side `php artisan ...` also works for anything that doesn't need the
containerized Postgres (host `.env` points `DB_HOST`/`DB_PORT` at
`127.0.0.1:5433`, which is the port Docker Compose publishes).

### Tests (PestPHP)

Runs on the host against an in-memory SQLite DB (`phpunit.xml`), no Docker
required:

```sh
./vendor/bin/pest
# or
php artisan test
```

### API docs (Scramble)

With the `app` container running:

- UI: http://localhost:8000/docs/api
- Raw OpenAPI JSON: http://localhost:8000/docs/api.json

### Laravel Boost

Installed as a dev dependency with guidelines, skills, and MCP server wired
up for Claude Code (`.mcp.json`, `.claude/skills/*`, `CLAUDE.md`). The MCP
server itself runs on demand via:

```sh
php artisan boost:mcp
```

No separate process needs to stay running — Claude Code launches it through
the MCP config in `.mcp.json`.
