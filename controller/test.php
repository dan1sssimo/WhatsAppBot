<?php

namespace testController;

use whatsAppBot\WhatsAppBot;

class Test
{
    protected $dbModel;

    public function __construct()
    {
        $this->dbModel = new WhatsAppBot();
    }

    public function addUser($number, $message)
    {
        $this->dbModel->insertUser("users", $number, $message);
    }


    public function newLinks($links, $where)
    {
        $this->dbModel->updateLinks("users", $links, $where);
    }

    public function delete($where)
    {
        $this->dbModel->deleteUser("users", $where);
    }

    public function fetchUser()
    {
        return $this->dbModel->selectUser("users");
    }

}