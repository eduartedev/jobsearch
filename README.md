# JobSearch

UI local (Laravel + Vue/Inertia) para búsqueda de empleo con proveedor de IA híbrido: **Ollama** o **Claude**.

Inspirado en [ai-job-search](https://github.com/ai-job-search-dev/ai-job-search).

## Requisitos

- DevKit en `E:\devkit` (PHP 8.3+, Node, Composer, MariaDB/SQLite)
- Ollama (opcional, local) y/o API key de Anthropic (Claude)

## Setup (DevKit)

```powershell
cd E:\devkit\www\jobsearch
php artisan migrate
npm install
npm run build
```

Abre `https://jobsearch.test:8443`, regístrate y ve a **IA** para elegir proveedor/modelo.

## Seguridad

- La API key de Claude se guarda cifrada por usuario y no se reexpone en la UI.
