<?php
class Category
{
    private $conn;
    private $table = "categories";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll($includeInactive = true)
    {
        $sql = "SELECT * FROM {$this->table}";
        if (!$includeInactive) {
            $sql .= " WHERE status = 1";
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $status = 1)
    {
        $sql = "INSERT INTO {$this->table} (name, status) VALUES (:name, :status)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', trim($name));
        $stmt->bindValue(':status', (int)$status, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update($id, $name, $status = 1)
    {
        $sql = "UPDATE {$this->table} SET name = :name, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->bindValue(':name', trim($name));
        $stmt->bindValue(':status', (int)$status, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

