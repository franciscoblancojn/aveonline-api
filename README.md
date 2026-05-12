# Aveonline API

Plugin de WordPress que crea endpoints REST API para integrar con **Aveonline**, plataforma de envíos y logística colombiana. Permite cotizar tarifas de envío con múltiples transportadoras en paralelo desde el checkout de WooCommerce.

## Requisitos

- WordPress 5.0+
- PHP 7.4+
- Extensión cURL de PHP
- WooCommerce (opcional, para integración de guías y pedidos)

## Instalación

1. Descargar el plugin y subirlo a `/wp-content/plugins/aveonline-api/`
2. Activar el plugin desde el panel de administración de WordPress
3. Ir a **Aveonline Api** en el menú de administración
4. Configurar el **Token** de acceso proporcionado por Aveonline

## Configuración

Desde el panel de administración (`Aveonline Api > Configuración`) se debe ingresar el token de autenticación de Aveonline. Este token se almacena en la tabla `wp_options` bajo la clave `AVE_API`.

## Endpoints REST API

### Buscar ciudades

```
GET /wp-json/ave/city/search?search={término}
```

Busca ciudades en la base de datos de Aveonline.

**Parámetros:**
- `search` (string, requerido) — Término de búsqueda

**Respuesta:**
```json
{
  "success": true,
  "data": [{ "id": "...", "nombre": "..." }]
}
```

### Cotizar envío (una transportadora)

```
POST /wp-json/ave/cotizar
```

Obtiene una cotización de una sola transportadora (usa valores fijos internos de `idtransportador`).

### Cotizar envío (múltiples transportadoras en paralelo)

```
POST /wp-json/ave/cotizar-paralelo
```

Obtiene cotizaciones de **múltiples transportadoras en paralelo** usando `curl_multi`. Los resultados se ordenan por precio total ascendente (válidos primero).

**Parámetros del body (JSON):**

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `origen` | string | sí | Código de ciudad origen |
| `destino` | string | sí | Código de ciudad destino |
| `peso` | float | sí | Peso del paquete (kg) |
| `unidades` | int | sí | Número de unidades |
| `valor_declarado` | float | sí | Valor declarado del producto |
| `contraentrega` | bool | no | ¿Requiere contraentrega? |
| `alto` | float | no | Alto del paquete (cm) |
| `largo` | float | no | Largo del paquete (cm) |
| `ancho` | float | no | Ancho del paquete (cm) |

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "status": "ok",
    "message": "cotizaciones encontradas",
    "cotizaciones": [
      {
        "nombreTransportadora": "Servientrega",
        "total": 15000,
        "diasentrega": "3",
        "fletetotal": 14000,
        ...
      }
    ]
  }
}
```

## Actualización automática

El plugin incluye un sistema de auto-actualización vía GitHub. Cuando hay una nueva versión disponible en el repositorio `franciscoblancojn/aveonline-api`, aparece una notificación de actualización en el panel de plugins de WordPress.

## Developer

* Name: Francisco Blanco
* Website: https://franciscoblanco.vercel.app/
* Email: blancofrancisco34@gmail.com

## Repositories

* GitHub: https://github.com/franciscoblancojn/aveonline-shipping

## Licencia

GPLv2 o posterior
