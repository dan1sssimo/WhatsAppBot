<?php

namespace db;


use PDO;

class DB
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=localhost;dbname=ninawhatsappbot;charset=UTF8", 'ninawhatsapp', 'K*aLIca}~YP&');
    }

    public function insertDB($tableName, $tableFields)
    {
        $insertDataFiltered = array();
        $filteredFields = implode(', ', array_keys($tableFields));
        foreach ($tableFields as $key => $value)
            $insertDataFiltered [] = "'" . $value . "'";
        $data = implode(', ', $insertDataFiltered);
        $query = "INSERT INTO {$tableName} ({$filteredFields}) VALUES ({$data})";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
    }

    public function deleteDB($table, $where)
    {
        $sql = "DELETE FROM {$table}";
        if(is_array($where) && count($where) > 0) {
            $whereParts = [];
            foreach ($where as $key => $value)
                $whereParts [] = "{$key} = ?";
            $whereStr = implode(' AND ', $whereParts);
            $sql .= ' WHERE '.$whereStr;
        }
        if(is_string($where))
            $sql .= ' WHERE '.$where;
        $sth = $this->pdo->prepare($sql);
        if(is_array($where) && count($where) > 0)
            $sth->execute(array_values($where));
        else
            $sth->execute();
    }

    public function selectDB($table, $fields = "*")
    {
        $fieldsStr = "*";
        if(is_string($fields))
            $fieldsStr= $fields;
        if(is_array($fields))
            $fieldsStr = implode(', ', $fields);
        $sql = "SELECT {$fieldsStr} FROM {$table}";
        $sth = $this->pdo->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }
    public function updateDB($table, $newRow, $where)
    {
        $sql = "UPDATE {$table} SET ";
        $setParts = [];
        $paramsArr = [];
        foreach ($newRow as $key => $value)
        {
            $setParts []= "{$key} = ?";
            $paramsArr []= $value;
        }
        $sql .= implode(', ', $setParts);
        if(is_array($where) && count($where) > 0) {
            $whereParts = [];
            foreach ($where as $key => $value)
            {
                $whereParts [] = "{$key} = ?";
                $paramsArr []= $value;
            }
            $whereStr = implode(' AND ', $whereParts);
            $sql .= ' WHERE '.$whereStr;
        }
        if(is_string($where))
            $sql .= ' WHERE '.$where;
        $sth = $this->pdo->prepare($sql);
        $sth->execute($paramsArr);
    }

}