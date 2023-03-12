<?php

namespace whatsAppBot;


use db\DB;

class WhatsAppBot
{
    public static $dbConnection;

    public function __construct()
    {
        self::$dbConnection = new DB();
    }

    public function insertUser($tableName, $phone, $message)
    {
        self::$dbConnection->insertDB($tableName, ["phone" => $phone, "message" => $message]);
    }

    public function updateLinks($tableName, $links,$where)
    {
        self::$dbConnection->updateDB($tableName, ["links"=>$links],['phone'=>$where]);
    }

    public function selectUser($tableName)
    {
        return self::$dbConnection->selectDB($tableName,'*');
    }

    public function deleteUser($tableName,$where)
    {
        self::$dbConnection->deleteDB($tableName,['phone'=>$where]);
    }

}