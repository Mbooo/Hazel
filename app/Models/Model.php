<?php

namespace App\Models;

use Exception;
use PDOException;
use PDO;
use App\DatabaseConnection;

class Model extends DatabaseConnection
{
    protected $db = null;
    protected $tableName = null;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
        $this->db = $this->connect();
    }

    public function find(?int $limit = null)
    {
        if (!$this->tableName) {
            throw new Exception("Table Name was not specified", 1);
        }

        $sql = "SELECT * FROM $this->tableName";


        try {
            if (!$this->db) {

                echo "NO DB";
            }

            $statement = $this->db->prepare($sql);

            if ($limit !== null) {
                $statement->bindParam(":lim", $limit, PDO::PARAM_INT); // If we only want to get a certain amount of data
            }

            $statement->execute();
            return $statement->fetchAll();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findOne(int $id)
    {
        if (!$this->tableName || $id) {
            return false;
        }

        $sql = "SELECT * from $this->tableName
        where id = :id";

        try {
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":id", $id, PDO::PARAM_INT);

            $statement->execute();
            return $statement->fetch();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
