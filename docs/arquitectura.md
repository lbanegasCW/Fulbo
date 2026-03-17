# Arquitectura Fulbo

## Enfoque general
- **MVC limpio con capas**: Controladores (HTTP), Servicios (reglas de negocio), Repositorios (persistencia), Vistas (render HTML), Helpers/Middlewares (cross-cutting).
- **Sin dependencias runtime externas**: compatible con hosting PHP tradicional.
- **Router simple** con rutas declarativas y middlewares por endpoint.

## Flujo principal
1. `public/index.php` carga entorno, sesion segura y autoload.
2. `routes/web.php` registra endpoints.
3. Router ejecuta middlewares (`Auth`, `Admin`, `CSRF`).
4. Controlador valida entrada y delega al servicio.
5. Servicio orquesta reglas complejas (sorteo, turnos, fixture, desempate).
6. Repositorios usan PDO + prepared statements.
7. Vista responde HTML mobile-first.

## Escalabilidad futura
- Extraer servicios a API REST (`/api/*`) sin romper vistas actuales.
- Reemplazar router por uno más avanzado manteniendo capas internas.
- Migrar repositorios a Query Builder/ORM si crece el dominio.
- Separar frontend en SPA opcional, conservando backend y esquema actual.
