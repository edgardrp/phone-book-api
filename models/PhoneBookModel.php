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

    private function getPhones($id, $con)
    {
        $query = "SELECT id, phone_number FROM phones WHERE contact_id = :contactID";
        $statement = $con->prepare($query);
        $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    private function getMailAddress($id, $con)
    {
        $query = "SELECT id, email_address FROM emails WHERE contact_id = :contactID";
        $statement = $con->prepare($query);
        $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getContacts($name, $surname)
    {
        $query = "SELECT c.id, c.name, c.surname
            FROM contacts c ";
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
        foreach ($response as $contact) {
            $contact->phones = $this->getPhones($contact->id, $con);
            $contact->emailAddress = $this->getMailAddress($contact->id, $con);
        }
        return json_encode($response);
    }

    public function getContactByID($id)
    {
        $con = $this->getConnection();
        $query = "SELECT c.id, c.name, c.surname FROM contacts c WHERE c.id = :contactID;";
        $statement = $con->prepare($query);
        $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
        $statement->execute();
        $contact = $statement->fetch();
        if ($contact) {
            $contact->phones = $this->getPhones($id, $con);
            $contact->emailAddress = $this->getMailAddress($id, $con);
            return json_encode($contact);
        }
        else {
            header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            die();
        }
    }

    private function savePhones($id, $phones, $con)
    {
        $query = "INSERT INTO phones (contact_id, phone_number) VALUES (:contactID, :phoneNumber);";
        foreach ($phones as $p) {
            $statement = $con->prepare($query);
            $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
            $statement->bindParam(":phoneNumber", $p, \PDO::PARAM_STR);
            $statement->execute();
        }
    }

    private function saveMails($id, $mails, $con)
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
        $current = json_decode($this->getContactById($id));
        $con = $this->getConnection();
        if ($current->name != $updatedContact->name) {
            $current->name = $updatedContact->name;
        }
        if ($current->surname != $updatedContact->surname) {
            $current->surname = $updatedContact->surname;
        }
        $query = "UPDATE contacts SET name = :name, surname = :surname WHERE id = :id;";
        $statement = $con->prepare($query);
        $statement->bindParam(":name", $current->name, \PDO::PARAM_STR);
        $statement->bindParam(":surname", $current->surname, \PDO::PARAM_STR);
        $statement->bindParam(":id", $id, \PDO::PARAM_INT);
        $statement->execute();
        return json_encode($current);
    }

    private function deletePhones($id, $con)
    {
        $query = "DELETE FROM phones WHERE contact_id = :contactID;";
        $statement = $con->prepare($query);
        $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
        $statement->execute();
    }

    private function deleteEmailAddress($id, $con)
    {
        $query = "DELETE FROM emails WHERE contact_id = :contactID;";
        $statement = $con->prepare($query);
        $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
        $statement->execute();
    }

    public function deleteContact($id)
    {
        $delete = $this->getContactById($id);
        $con = $this->getConnection();
        $this->deletePhones($id, $con);
        $this->deleteEmailAddress($id, $con);
        $query = "DELETE FROM contacts WHERE id = :contactID;";
        $statement = $con->prepare($query);
        $statement->bindParam(":contactID", $id, \PDO::PARAM_INT);
        $statement->execute();
        return $delete;
    }

}
