<?php
class UserController {

    public function __construct(private User $user) {}

    /* ---------- Helpers ---------- */

    private function json(int $status, mixed $payload): void {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    /** Lee el body JSON. Devuelve null si está vacío, mal formado o no es un objeto. */
    private function readPayload(): ?array {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Validación básica de campos obligatorios.
     * En creación: name, email y password son obligatorios.
     * En actualización: name y email obligatorios; password opcional.
     * Devuelve un arreglo [campo => mensaje]; vacío si todo es válido.
     */
    private function validate(array $data, bool $passwordRequired): array {
        $errors = [];

        $name = $data['name'] ?? null;
        if (!is_string($name) || trim($name) === '') {
            $errors['name'] = 'El nombre es obligatorio';
        } elseif (mb_strlen(trim($name)) > 100) {
            $errors['name'] = 'El nombre no puede superar 100 caracteres';
        }

        $email = $data['email'] ?? null;
        if (!is_string($email) || trim($email) === '') {
            $errors['email'] = 'El correo es obligatorio';
        } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El correo no tiene un formato válido';
        } elseif (mb_strlen(trim($email)) > 150) {
            $errors['email'] = 'El correo no puede superar 150 caracteres';
        }

        $password = $data['password'] ?? null;
        $hasPassword = $password !== null && $password !== '';
        if ($passwordRequired && !$hasPassword) {
            $errors['password'] = 'La contraseña es obligatoria';
        } elseif ($hasPassword) {
            if (!is_string($password) || strlen($password) < 6) {
                $errors['password'] = 'Mínimo 6 caracteres';
            } elseif (strlen($password) > 72) {   // límite real de bcrypt
                $errors['password'] = 'Máximo 72 caracteres';
            }
        }

        return $errors;
    }

    private function sanitize(array $data): array {
        return [
            'name'     => trim($data['name']),
            'email'    => strtolower(trim($data['email'])),
            'password' => $data['password'] ?? null,
        ];
    }

    private function badRequest(array $campos): void {
        $this->json(400, ['error' => 'Datos inválidos', 'campos' => $campos]);
    }

    /* ---------- Endpoints ---------- */

    // GET /api/users
    public function index(): void {
        $this->json(200, $this->user->getAll());
    }

    // GET /api/users/{id}
    public function show(int $id): void {
        $user = $this->user->getById($id);
        if (!$user) {
            $this->json(404, ['error' => 'Usuario no encontrado']);
            return;
        }
        $this->json(200, $user);
    }

    // POST /api/users
    public function store(): void {
        $data = $this->readPayload();
        if ($data === null) {
            $this->json(400, ['error' => 'El cuerpo debe ser un JSON válido']);
            return;
        }

        $errors = $this->validate($data, passwordRequired: true);
        if ($errors) { $this->badRequest($errors); return; }

        $data = $this->sanitize($data);

        if ($this->user->emailExists($data['email'])) {
            $this->badRequest(['email' => 'Ya existe un usuario con ese correo']);
            return;
        }

        try {
            $id = $this->user->create($data);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {   // UNIQUE (carrera entre dos peticiones)
                $this->badRequest(['email' => 'Ya existe un usuario con ese correo']);
                return;
            }
            throw $e;                           // → 500 en index.php
        }

        $this->json(201, [
            'mensaje' => 'Usuario creado',
            'id'      => $id,
            'usuario' => $this->user->getById($id),
        ]);
    }

    // PUT /api/users/{id}
    public function update(int $id): void {
        if (!$this->user->getById($id)) {
            $this->json(404, ['error' => 'Usuario no encontrado']);
            return;
        }

        $data = $this->readPayload();
        if ($data === null) {
            $this->json(400, ['error' => 'El cuerpo debe ser un JSON válido']);
            return;
        }

        $errors = $this->validate($data, passwordRequired: false);
        if ($errors) { $this->badRequest($errors); return; }

        $data = $this->sanitize($data);

        if ($this->user->emailExists($data['email'], $id)) {
            $this->badRequest(['email' => 'Ya existe un usuario con ese correo']);
            return;
        }

        try {
            $this->user->update($id, $data);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->badRequest(['email' => 'Ya existe un usuario con ese correo']);
                return;
            }
            throw $e;
        }

        $this->json(200, [
            'mensaje' => 'Usuario actualizado',
            'id'      => $id,
            'usuario' => $this->user->getById($id),
        ]);
    }

    // DELETE /api/users/{id}
    public function destroy(int $id): void {
        if ($this->user->delete($id) === 0) {
            $this->json(404, ['error' => 'Usuario no encontrado']);
            return;
        }
        $this->json(200, ['mensaje' => 'Usuario eliminado', 'id' => $id]);
    }
}
