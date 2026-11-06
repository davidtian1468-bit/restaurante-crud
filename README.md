# Restaurante CRUD — API REST en PHP + MySQL

API REST sin frameworks (PHP 8 + PDO + MySQL) con dos recursos:

- **Productos** (`/api/products`) — arquitectura base del proyecto `api-products`.
- **Usuarios** (`/api/users`) — CRUD completo del recurso solicitado en la práctica.

## Arquitectura

```
api/
├── public/index.php                  # Front controller + enrutamiento
├── src/
│   ├── Config/Database.php           # Conexión PDO
│   ├── Models/User.php               # Acceso a datos de la tabla users
│   └── Controllers/
│       ├── ProductController.php
│       └── UserController.php        # Validación + respuestas JSON + códigos HTTP
├── restaurante.sql                   # BD + tabla productos
├── usuarios.sql                      # Tabla users
└── postman/                          # Colección de Postman lista para importar
web/                                  # Front (admin.html, usuarios.html)
```

## Requisitos

- PHP **8.0 o superior** con las extensiones `pdo_mysql` y `mbstring` (vienen activas en XAMPP).
- MySQL 5.7+ / MariaDB 10.3+.

## 1. Instalación de la base de datos

Con MySQL corriendo (usuario `root` sin contraseña por defecto en XAMPP):

```bash
mysql -u root < api/restaurante.sql
mysql -u root < api/usuarios.sql
```

> En XAMPP también puedes usar **phpMyAdmin → Importar** y cargar primero
> `restaurante.sql` y luego `usuarios.sql`.

Esto crea la base `restaurante` con las tablas `productos` y `users`:

| Campo        | Tipo                | Notas                      |
|--------------|---------------------|----------------------------|
| `id`         | INT UNSIGNED, PK AI |                            |
| `name`       | VARCHAR(100)        | obligatorio                |
| `email`      | VARCHAR(150)        | obligatorio, **único**     |
| `password`   | VARCHAR(255)        | se guarda con `password_hash` (bcrypt) |
| `created_at` | TIMESTAMP           | default `CURRENT_TIMESTAMP`|

## 2. Configuración

Por defecto la API se conecta a `127.0.0.1:3306`, base `restaurante`, usuario `root`, sin contraseña.
Si tu configuración es distinta, define variables de entorno antes de levantar el servidor
(o edita `api/src/Config/Database.php`):

`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`  
`APP_DEBUG=1` (opcional) incluye el detalle de los errores 500 en la respuesta.

## 3. Levantar el servicio

```bash
cd api
php -S localhost:8080 -t public
```

En Windows con XAMPP, si `php` no está en el PATH:

```bat
cd api
C:\xampp\php\php.exe -S localhost:8080 -t public
```

La API queda en **http://localhost:8080**. Para ver el front, en otra terminal:
`php -S localhost:5500 -t web` y abre http://localhost:5500/usuarios.html

## 4. Endpoints de usuarios

| Método | Ruta              | Descripción            | Éxito |
|--------|-------------------|------------------------|-------|
| GET    | `/api/users`      | Listar todos           | 200   |
| GET    | `/api/users/{id}` | Obtener por ID         | 200   |
| POST   | `/api/users`      | Crear                  | 201   |
| PUT    | `/api/users/{id}` | Actualizar             | 200   |
| DELETE | `/api/users/{id}` | Eliminar               | 200   |

### Códigos de estado

| Código | Cuándo                                                                 |
|--------|------------------------------------------------------------------------|
| 200    | Consulta, actualización o eliminación correcta                         |
| 201    | Usuario creado                                                         |
| 400    | JSON inválido, campos obligatorios vacíos, correo inválido o duplicado, contraseña < 6 caracteres |
| 404    | El usuario no existe, o la ruta no existe                              |
| 500    | Error interno (p. ej. no hay conexión con la base de datos)            |

La contraseña **nunca** se devuelve en las respuestas.

### Ejemplos (curl)

**Crear**
```bash
curl -i -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Ana Torres","email":"ana@example.com","password":"secreto123"}'
```
```json
HTTP/1.1 201 Created
{
  "mensaje": "Usuario creado",
  "id": 1,
  "usuario": { "id": 1, "name": "Ana Torres", "email": "ana@example.com", "created_at": "2026-11-05 10:30:00" }
}
```

**Listar**
```bash
curl -i http://localhost:8080/api/users
```

**Obtener por ID**
```bash
curl -i http://localhost:8080/api/users/1
```

**Actualizar** (`password` es opcional; si se omite se conserva la actual)
```bash
curl -i -X PUT http://localhost:8080/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Ana T. Rojas","email":"ana.rojas@example.com"}'
```

**Eliminar**
```bash
curl -i -X DELETE http://localhost:8080/api/users/1
```

**Error de validación (400)**
```bash
curl -i -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"","email":"no-es-correo"}'
```
```json
{
  "error": "Datos inválidos",
  "campos": {
    "name": "El nombre es obligatorio",
    "email": "El correo no tiene un formato válido",
    "password": "La contraseña es obligatoria"
  }
}
```

**No encontrado (404)**
```bash
curl -i http://localhost:8080/api/users/9999
```
```json
{ "error": "Usuario no encontrado" }
```

## 5. Probar con Postman

1. Postman → **Import** → selecciona `api/postman/Restaurante-Users.postman_collection.json`.
2. Con el servidor levantado, ejecuta las peticiones en orden (carpeta *Users*).
   La variable `{{base_url}}` vale `http://localhost:8080` y `{{user_id}}` se llena sola al ejecutar *Crear usuario*.
3. Cada petición trae tests que validan el código de estado esperado
   (puedes correr toda la colección con **Run collection**).
