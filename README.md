# Facturacion

Aplicacion Laravel para emitir y consultar comprobantes mediante ARCA.

## Requisitos

- Docker Desktop con Docker Compose.
- Credenciales y certificado ARCA para el entorno que se vaya a utilizar.

## Inicio con Docker

Desde la carpeta del proyecto, crea el archivo de entorno si todavia no existe:

```powershell
Copy-Item src/.env.example src/.env
```

En `src/.env`, configura la conexion de base de datos para el servicio `db` de Docker Compose:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=facturacion
DB_USERNAME=laravel
DB_PASSWORD=secret
```

Completa tambien las variables de ARCA y configura las rutas de los certificados
dentro del contenedor. Despues ejecuta:

```powershell
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

La aplicacion queda disponible en <http://localhost:8080>. Para detener los
contenedores sin eliminar los datos de MySQL:

```powershell
docker compose down
```

## Limpiar las caches de Laravel

Desde PowerShell, en la carpeta del proyecto:

```powershell
docker compose exec app php artisan optimize:clear
```

## Configuracion de ARCA

`src/config/services.php` lee estas variables de `src/.env`:

| Variable | Descripcion |
| --- | --- |
| `ARCA_CUIT` | CUIT de la cuenta |
| `ARCA_ACCESS_TOKEN` | Token de acceso |
| `AFIP_PRODUCTION` | Usar `true` para produccion o `false` para pruebas |
| `ARCA_CERT_PATH` | Ruta del certificado dentro del contenedor |
| `ARCA_KEY_PATH` | Ruta de la clave privada dentro del contenedor |

El certificado disponible localmente esta en `src/storage/app/arca/Certificado.pem`.
Si se usa ese archivo, la ruta dentro del contenedor es
`/var/www/html/storage/app/arca/Certificado.pem`. Guarda la clave privada en una
ruta local protegida dentro de `src` y configura `ARCA_KEY_PATH` con su ruta en
el contenedor. No incluyas certificados, claves privadas ni tokens en el
repositorio.

## API de facturacion

Las rutas de la API estan bajo el prefijo `/api/billing`:

| Metodo | Ruta | Funcion |
| --- | --- | --- |
| `GET` | `/api/billing/last-voucher/{ptoVta}/{cbteTipo}` | Consultar el ultimo comprobante |
| `POST` | `/api/billing/invoice` | Crear un comprobante |
| `GET` | `/api/billing/invoice/{id}/pdf` | Descargar el PDF |

## Pruebas

```powershell
docker compose exec app php artisan test
```
