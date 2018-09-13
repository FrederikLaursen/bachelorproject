<?php
namespace API;

class DatabaseHandler extends \PDO
{
    protected $pdo;
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:../TestDB.db');
    }

    public function selectQuery($selectStatement)
    {

        $result = $this->pdo->query($selectStatement);
        return $result;
    }

}