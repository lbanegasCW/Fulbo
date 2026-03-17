<!-- Reemplaza la imagen de abajo por tu logo principal -->
<p align="center">
  <img src="docs/logo.png" alt="Fulbo" width="300">
</p>

# Fulbo

PWA para organizar torneos de FC entre amigos, pensada para correr en hosting tradicional con PHP + MySQL.

- Mobile-first (instalable en iOS/Android)
- Panel admin para usuarios, bombos, equipos y torneos
- Flujo completo de torneo: sorteo, eleccion por turnos, fixture, tabla y desempates
- UI unificada para app instalada y version web

## Demo rapido

Credenciales del seed:

- Admin: `admin` / `1234`
- Jugadores: `nico` / `1234`, `maxi` / `1234`
- Usuario pendiente de activacion: `luis` (activar en `/activar`)

## Capturas

| Vista | Captura |
| --- | --- |
| Dashboard | ![Dashboard](docs/screenshots/dashboard.jpg) |
| Torneos | ![Torneos](docs/screenshots/torneos.jpg) |
| Detalle de torneo | ![Detalle](docs/screenshots/detalle_torneo.jpg) |
| Ranking | ![Ranking](docs/screenshots/ranking.jpg) |
| Admin usuarios | ![Admin usuarios](docs/screenshots/admin_usuarios.jpg) |
| Admin torneo | ![Admin torneo](docs/screenshots/admin_torneo.jpg) |

## Stack

- PHP 8+
- MySQL 8+
- Apache (`mod_rewrite`)
- JS vanilla (sin bundler en produccion)

## Arquitectura

Proyecto organizado en capas:

- `app/controllers`: rutas y coordinacion HTTP
- `app/services`: logica de negocio
- `app/repositories`: acceso a datos (PDO)
- `app/middlewares`: auth, admin, csrf
- `views`: vistas separadas por modulo
- `public`: assets y archivos PWA

## Estructura

```text
app/
  controllers/
  services/
  repositories/
  middlewares/
  helpers/
  core/
views/
  layouts/
  auth/
  player/
  admin/
public/
  assets/
    css/
    js/
    img/
  pwa/
config/
routes/
database/
  schema.sql
  seed.sql
```

## Instalacion local

1. Clonar repo.
2. Crear base de datos MySQL.
3. Ejecutar:
   - `database/schema.sql`
   - `database/seed.sql`
4. Copiar `.env.example` a `.env` y completar credenciales.
5. Servir el proyecto con Apache/PHP apuntando al directorio publico.

## Deploy en produccion

1. Subir codigo completo.
2. Configurar `.env` productivo.
3. Verificar permisos y reglas de `.htaccess`.

## Modulos principales

- Auth/activacion con PIN
- Dashboard con proximo torneo
- Torneos (activos + historial)
- Detalle de torneo con fixture por fechas y tabla en vivo
- Ranking anual
- Admin: usuarios, bombos/equipos, crear/editar torneo

## Seguridad

- Hash seguro de PIN (`password_hash` / `password_verify`)
- CSRF en formularios POST
- Sesion segura y control por middleware
- PDO con prepared statements
- Escape HTML en vistas (`e()`)

## PWA y cache

- Manifest: `public/pwa/manifest.json`
- Service worker: `public/pwa/service-worker.js`
- Offline fallback: `public/pwa/offline.html`

Si cambias iconos/assets y queres forzar refresco:

1. Subir assets nuevos.
2. Bump de version del service worker (`CACHE_NAME`).
3. (Opcional recomendado) versionar iconos en el manifest con query string.

## Notas

- Soporta despliegue en subcarpeta con `APP_URL`.
- No requiere procesos background ni Node en produccion.
- Ideal para shared hosting.