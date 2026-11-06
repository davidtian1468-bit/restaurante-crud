<?php
class ProductController {

    public function __construct(private PDO $db) {}

    private function datosValidos(?array $data): bool {
        return isset($data['nombre'], $data['categoria'], $data['descripcion'],
                     $data['precio'], $data['stock'])
            && trim($data['nombre']) !== ''
            && is_numeric($data['precio'])
            && is_numeric($data['stock']);
    }

    public function index(): void {
        $stmt = $this->db->query("SELECT * FROM productos ORDER BY id");
        echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
    }

    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->datosValidos($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos o incorrectos']);
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO productos (nombre, categoria, descripcion, precio, stock)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'], $data['categoria'], $data['descripcion'],
            $data['precio'], $data['stock'],
        ]);

        http_response_code(201);
        echo json_encode(['mensaje' => 'Plato registrado', 'id' => (int) $this->db->lastInsertId()]);
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Plato no encontrado']);
            return;
        }
        echo json_encode($product, JSON_UNESCAPED_UNICODE);
    }

    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->datosValidos($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos o incorrectos']);
            return;
        }

        $stmt = $this->db->prepare(
            "UPDATE productos SET nombre = ?, categoria = ?, descripcion = ?, precio = ?, stock = ? WHERE id = ?"
        );
        $stmt->execute([
            $data['nombre'], $data['categoria'], $data['descripcion'],
            $data['precio'], $data['stock'], $id,
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Plato no encontrado']);
            return;
        }
        echo json_encode(['mensaje' => 'Plato actualizado', 'id' => $id]);
    }

    public function destroy(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Plato no encontrado']);
            return;
        }
        http_response_code(204);
    }
}