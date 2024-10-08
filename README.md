
# Documentación API Linktic

![Arquitectura del Proyecto](https://en.joseiguti.com/img/linktic/arquitectura.png)


## 1. Ejecución local

Para ejecutar el proyecto localmente, siga los siguientes pasos:

1. Clone el repositorio.
2. Ejecute los contenedores utilizando Docker:
   ```bash
   docker compose down -v
   docker compose up --build
   ```
3. Esto creará la base de datos y ejecutará las migraciones junto con los seeds.

## 2. Pruebas unitarias

Para ejecutar las pruebas unitarias del proyecto, utilice el siguiente comando:
```bash
php artisan test
```

## 3. Arquitectura Middleware

El flujo del middleware en el API sigue esta estructura:

1. **Autenticación básica:** Todas las rutas están protegidas por el middleware de autenticación básica. Esto asegura que solo los usuarios autenticados puedan acceder a los servicios.
2. **Capa de caché:** Después de la autenticación, las respuestas de algunas rutas se almacenan en caché para mejorar el rendimiento.
3. **Validación de parámetros:** Antes de crear productos u órdenes, se validan los parámetros recibidos mediante middlewares específicos.

El flujo básico es:
- Primero se verifica la autenticación.
- Si pasa la autenticación, se aplica el caché en las respuestas GET.
- En las solicitudes POST, se validan los parámetros antes de procesar la solicitud.

## 4. Servicios y Autenticación Básica

El API de Linktic utiliza autenticación básica (Basic Auth) definida en el archivo `.env`. Para consumir los servicios es necesario autenticar la solicitud utilizando las credenciales configuradas en el archivo de entorno, es decir, el usuario y la contraseña definidos bajo las variables `BASIC_AUTH_USER` y `BASIC_AUTH_PASSWD`.

### Ejemplo de autenticación básica:

Para consumir los servicios del API es necesario enviar la cabecera de autenticación básica en la solicitud. Aquí un ejemplo:

**Encabezado de autenticación:**
```
Authorization: Basic YWRtaW46c2VjcmV0
```
Donde `YWRtaW46c2VjcmV0` es la codificación base64 del usuario y contraseña `admin:secret`.

### Endpoints:

A continuación se describen los endpoints disponibles, los campos requeridos y los posibles errores.

#### 1. Obtener productos

**Descripción:** Obtiene una lista de todos los productos disponibles.

**Endpoint:** `GET /products`

**Parámetros:** Ninguno.

**Ejemplo de solicitud:**
```
GET /api/products
Authorization: Basic YWRtaW46c2VjcmV0
```

**Respuesta exitosa:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Soporte computadora",
      "price": 25.00,
      "description": "Soporte para computadora y monitor al tiempo",
      "stock": 15,
      "image": "product-one.jpg",
      "created_at": "2024-10-08T07:45:17.000000Z",
      "updated_at": "2024-10-08T07:45:17.000000Z"
    },
    {
      "id": 2,
      "name": "Teclado Gaming",
      "price": 30.00,
      "description": "Teclado gaming de última generación",
      "stock": 25,
      "image": "product-two.jpg",
      "created_at": "2024-10-08T07:45:17.000000Z",
      "updated_at": "2024-10-08T07:45:17.000000Z"
    }
  ]
}
```

#### 2. Crear una orden

**Descripción:** Crea una nueva orden con los productos solicitados.

**Endpoint:** `POST /orders`

**Parámetros requeridos:**
- `customer_name` (string, requerido): Nombre del cliente.
- `customer_email` (string, requerido): Correo electrónico del cliente.
- `products` (array, requerido): Lista de productos, cada producto debe contener:
    - `id` (int, requerido): ID del producto.
    - `quantity` (int, requerido): Cantidad del producto.

**Ejemplo de solicitud:**
```
POST /api/orders
Authorization: Basic YWRtaW46c2VjcmV0
Content-Type: application/json

{
  "customer_name": "Jose Gutierrez",
  "customer_email": "me@joseiguti.com",
  "products": [
    { "id": 1, "quantity": 1 },
    { "id": 2, "quantity": 2 }
  ]
}
```

**Respuesta exitosa:**
```json
{
  "id": 3,
  "customer_name": "Jose Gutierrez",
  "customer_email": "me@joseiguti.com",
  "total_price": 85.00,
  "created_at": "2024-10-08T07:45:17.000000Z",
  "updated_at": "2024-10-08T07:45:17.000000Z",
  "products": [
    { "id": 1, "name": "Soporte computadora", "quantity": 1, "price": 25.00 },
    { "id": 2, "name": "Teclado Gaming", "quantity": 2, "price": 30.00 }
  ]
}
```

**Posibles errores:**

- `422 Unprocessable Entity`: Error de validación. Campos faltantes o datos incorrectos.

**Ejemplo de error de validación:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "customer_name": ["The customer name field is required."],
    "products": ["The products field is required."]
  }
}
```

## 5. Estructura de la base de datos

### Tabla `orders`

| Campo         | Tipo         | Descripción                            |
|---------------|--------------|----------------------------------------|
| id            | bigint       | Identificador único de la orden        |
| customer_name | varchar(255) | Nombre del cliente                     |
| customer_email| varchar(255) | Correo electrónico del cliente         |
| total_price   | decimal(8,2) | Precio total de la orden               |
| created_at    | timestamp    | Fecha de creación                      |
| updated_at    | timestamp    | Fecha de última actualización          |

### Tabla `order_details`

| Campo        | Tipo         | Descripción                            |
|--------------|--------------|----------------------------------------|
| id           | bigint       | Identificador único del detalle        |
| order_id     | bigint       | Identificador de la orden              |
| product_id   | bigint       | Identificador del producto             |
| quantity     | int          | Cantidad del producto                  |
| price        | decimal(8,2) | Precio del producto en la orden        |
| created_at   | timestamp    | Fecha de creación                      |
| updated_at   | timestamp    | Fecha de última actualización          |

### Tabla `products`

| Campo       | Tipo         | Descripción                            |
|-------------|--------------|----------------------------------------|
| id          | bigint       | Identificador único del producto       |
| name        | varchar(255) | Nombre del producto                    |
| price       | decimal(8,2) | Precio del producto                    |
| description | text         | Descripción del producto               |
| stock       | int          | Cantidad de productos en stock         |
| image       | varchar(255) | Nombre de la imagen del producto       |
| created_at  | timestamp    | Fecha de creación                      |
| updated_at  | timestamp    | Fecha de última actualización          |

## 6. Seeds

El sistema inicia con datos iniciales, incluidos productos y una orden de ejemplo. Los productos iniciales son:

- Soporte computadora (precio: 25.00)
- Teclado Gaming (precio: 30.00)
- Silla Gaming (precio: 150.00)

La base de datos se inicializa al ejecutar los seeds junto con las migraciones.

## 7. deploy.yml

El archivo `deploy.yml` es responsable de automatizar el despliegue continuo del proyecto. Cuando se realiza un `git push` en la rama principal, se ejecuta el pipeline, que construye la imagen de Docker, la sube a un registro de contenedores (DigitalOcean) y despliega la aplicación en un droplet.
