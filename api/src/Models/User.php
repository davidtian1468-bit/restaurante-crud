<?php

class User {

    public function __construct(private PDO $db) {}

    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT id, name, email, created_at FROM users ORDER BY id DESC"
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, created_at FROM users WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function emailExists(string $email, ?int $exceptId = null): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?"
        );
        $stmt->execute([$email, $exceptId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare(
                "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?"
            );
            $stmt->execute([
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $id
            ]);
            return;
        }

        $stmt = $this->db->prepare(
            "UPDATE users SET name = ?, email = ? WHERE id = ?"
        );
        $stmt->execute([$data['name'], $data['email'], $id]);
    }

    public function delete(int $id): int {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}