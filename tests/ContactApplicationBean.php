<?php
namespace Wabel\Zoho\CRM\Sync;

class ContactApplicationBean
{

    private $id;
    private $lastName;
    private $firstName;
    private $email;
    private $phone;
    private $zohoId;
    private $zohoLastModificationDate;

    public function __construct($id = null, $lastName = null, $firstName = null, $email = null, $phone = null, $zohoId = null, $zohoLastModificationDate = null)
    {
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->phone = $phone;
        $this->zohoId = $zohoId;
        $this->zohoLastModificationDate = $zohoLastModificationDate;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getZohoId()
    {
        return $this->zohoId;
    }

    /**
     * @param mixed $zohoId
     */
    public function setZohoId($zohoId)
    {
        $this->zohoId = $zohoId;
    }

    /**
     * @return mixed
     */
    public function getZohoLastModificationDate()
    {
        return $this->zohoLastModificationDate;
    }

    /**
     * @param mixed $zohoLastModificationDate
     */
    public function setZohoLastModificationDate($zohoLastModificationDate)
    {
        $this->zohoLastModificationDate = $zohoLastModificationDate;
    }
}
