# Sistema de Pagos - IESTP

Proyecto de gestión de pagos y solicitudes para el IESTP. Contiene APIs en PHP, vistas para distintos roles (admin, bienestar, dirección, usuario) y scripts SQL para inicializar la base de datos.

## Resumen

Este repo implementa un sistema básico de pagos y trámites institucionales en PHP pensado para ejecutarse en un entorno local con XAMPP o similar. Incluye:

- Endpoints de autenticación y pagos (`api/`)
- Páginas públicas y vistas para distintos roles (`views/` y `public/`)
- Conexión a base de datos mediante `config/conexion.php`
- Scripts SQL en la carpeta `sql/` para poblar la base de datos

## Requisitos

- PHP 7.x o 8.x
- MySQL / MariaDB
- XAMPP (recomendado para desarrollo local)
- Navegador moderno

## Instalación rápida (local)

1. Clonar o copiar el proyecto dentro del directorio de htdocs de XAMPP. Ejemplo:

   - Colocar la carpeta en `C:\xampp\htdocs\Sistema-de-pagos-IESTP-`

2. Crear la base de datos en MySQL e importar los scripts SQL que se encuentran en la carpeta `sql/`:

   - `sql/integrado_db.sql` (base principal)
   - `sql/Inserciones-usuarios.sql` (usuarios de ejemplo)
   - `sql/sistema-pago-descuentos.sql` (datos relacionados a pagos/descuentos)

3. Configurar la conexión a la base de datos en `config/conexion.php` (host, usuario, contraseña, nombre de base de datos).

4. Asegurarse de que el servidor Apache y MySQL estén corriendo en XAMPP.

5. Abrir en el navegador: `http://localhost/Sistema-de-pagos-IESTP-/` y acceder a las vistas o a `login.html`.

## Estructura de carpetas

La siguiente arquitectura muestra las carpetas y archivos principales del proyecto:

```
conexcionsftp.txt
index.html
tablasusadas.txt
api/
    verificar_token.php
    auth/
        login.php
    pagos/
        index.php
    resoluciones/
        index.php
    solicitudes/
        index.php
config/
    conexion.php
img/
models/
    model_login.php
public/
    auth.php
    login.html
    img/
    js/
        login.js
sql/
    Inserciones-usuarios.sql
    integrado_db.sql
    sistema-pago-descuentos.sql
views/
    dashboard-admin.php
    dashboard-bienestar.php
    dashboard-direccion.php
    dashboard-usuario.php
    assets/
        img/
    includes/
        admin/
            admin-bienestar.html
            admin-direccion.html
            admin-notificaciones.html
            admin-usuarios.html
            panel-admin.php
        bienestar/
            registro-bienestar-estudiantil.html
            reportes-bienestar-estudiantil.html
            solicitud-bienestar-estudiantil.html
        direccion/
            direccion-resolucion.html
            reportes-direccion.html
            solicitud-registro.html
        usuario/
            comprobantes.html
            notificaciones.html
            pagos.html
            usuario-metodo-pago.html
            usuario-solicitud.html
```

## API principales

- `api/auth/login.php` : Autenticación de usuarios.
- `api/verificar_token.php` : Verifica tokens (si aplica autorización por token).
- `api/pagos/index.php` : Punto de entrada para operaciones de pagos.
- `api/resoluciones/index.php` : Endpoints para resoluciones.
- `api/solicitudes/index.php` : Gestión de solicitudes.

Revisar `models/model_login.php` para entender la lógica del login.

## Configuración sugerida

- Proteger `config/` y archivos sensibles en despliegues reales.
- Usar contraseñas seguras y, si es posible, variables de entorno para credenciales.

## Notas para desarrolladores

- Para añadir un nuevo endpoint, crear el archivo correspondiente en `api/` y seguir el patrón existente.
- Las vistas están en `views/` y pueden incluir archivos de `includes/` para componentes comunes.
- Mantener los scripts SQL sincronizados con cualquier cambio en el esquema.

## Próximos pasos recomendados

- Documentar la API con ejemplos de request/response.
- Añadir validación y manejo de errores más robusto en los endpoints.
- Implementar control de acceso por roles (si no está completo).

## Contacto

Si necesitas ayuda con la instalación o quieres colaborar, abre un issue o contacta al mantenedor del repositorio.

---

Archivo generado automáticamente: `README.md` para el proyecto `Sistema-de-pagos-IESTP-`.
