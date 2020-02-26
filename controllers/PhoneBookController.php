<?php

namespace Controllers;
use Models\PhoneBookModel;

class PhoneBookController
{

    private $model = null;

    function __construct() {
        $this->model = new PhoneBookModel;
    }

    public function getContacts($name, $surname)
    {
        return $this->model->getContacts($name, $surname);
    }

    public function getContactByID($id)
    {
        return $this->model->getContactByID($id);
    }

    public function saveContact($newContact)
    {
        return $this->model->saveContact($newContact);
    }

    public function updateContact($id, $updatedContact)
    {
        return $this->model->updateContact($id, $updatedContact);
    }

    public function deleteContact($id)
    {
        return $this->model->deleteContact($id);
    }

}
