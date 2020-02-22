<?php

namespace Models;

class PhoneBookModel
{

    private $config;

    function __construct() 
    {
        $jsonConfig = file_get_contents(__DIR__."/../config.json");
        $this->config = json_decode($jsonConfig);
    }

    private function getConnection()
    {
        $connection = new \PDO(
            "mysql:host={$this->config->db->host};dbname={$this->config->db->dbName}",
            "{$this->config->db->user}",
            "{$this->config->db->pwd}",
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ]
        );
        return $connection;
    }

    public function getContacts($name, $surname)
    {
        $query = "SELECT c.name, c.surname, p.phone_number, e.email_address 
            FROM contacts c 
            LEFT JOIN phones p ON p.contact_id = c.id 
            LEFT JOIN emails e ON e.contact_id = c.id";
        if (isset($name) || isset($surname)) {
            $query .= " WHERE ";
            $conditions = null;
            if (isset($name)) {
                $conditions = " name LIKE '%{$name}%' ";
            }
            if (isset($surname)) {
                if (isset($conditions)) {
                    $conditions .= " AND surname LIKE '%{$surname}%'";
                } else { 
                    $conditions = "surname LIKE '%{$surname}%'";
                }
            }
            $query .= $conditions;
        }
        $con = $this->getConnection();
        $statement = $con->prepare($query);
        $statement->execute();
        $response = $statement->fetchAll();
        return json_encode($response);
    }

    public function savePhones($id, $phones, $con)
    {
        $query = "INSERT INTO phones (contact_id, phone_number) VALUES (:contactID, :phoneNumber);";
        foreach ($phones as $p) {
            $statement = $con->prepare($query);
            $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
            $statement->bindParam(":phoneNumber", $p, \PDO::PARAM_STR);
            $statement->execute();
        }
    }

    public function saveMails($id, $mails, $con)
    {
        $query = "INSERT INTO emails (contact_id, email_address) VALUES (:contactID, :mail);";
        foreach ($mails as $m) {
            $statement = $con->prepare($query);
            $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
            $statement->bindParam(":mail", $m, \PDO::PARAM_STR);
            $statement->execute();
        }
    }

    public function saveContact($newContact) 
    {
        $id = null;
        $query = "INSERT INTO contacts (name, surname) VALUES (:name, :surname);";
        $con = $this->getConnection();
        $statement = $con->prepare($query);
        $statement->bindParam(":name", $newContact->name, \PDO::PARAM_STR);
        $statement->bindParam(":surname", $newContact->surname, \PDO::PARAM_STR);
        $statement->execute();
        $id = $con->lastInsertId();
        $this->savePhones($id, $newContact->phones, $con);
        $this->saveMails($id, $newContact->mails, $con);
        $newContact->id = $id;
        return json_encode($newContact);
    }

    public function updateContact($id, $updatedContact)
    {
        return $this->model->updateContact($id, $updatedContact);
    }

}
