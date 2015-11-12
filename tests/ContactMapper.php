<?php
namespace Wabel\Zoho\CRM\Sync;

use TestNamespace\Contact;
use Wabel\Zoho\CRM\Exception\ZohoCRMException;
use Wabel\Zoho\CRM\ZohoBeanInterface;

class ContactMapper implements MappingInterface
{

    private $testContacts;

    /**
     * @return array
     */
    public function getTestContacts()
    {
        return $this->testContacts;
    }

    /**
     * @param array $testContacts
     */
    public function setTestContacts($testContacts)
    {
        $this->testContacts = $testContacts;
    }

    /**
     * Returns a Zoho Bean based on the bean passed in argument
     *
     * @param  object            $applicationBean
     * @return ZohoBeanInterface
     */
    public function toZohoBean($applicationBean)
    {
        if (!$applicationBean instanceof ContactApplicationBean) {
            throw new ZohoCRMException("Expected ContactApplicationBean");
        }
        $zohoBean = new Contact();
        $zohoBean->setLastName($applicationBean->getLastName());
        $zohoBean->setFirstName($applicationBean->getFirstName());
        $zohoBean->setEmail($applicationBean->getEmail());
        $zohoBean->setPhone($applicationBean->getPhone());
        $zohoBean->setZohoId($applicationBean->getZohoId());
        if ($applicationBean->getZohoLastModificationDate()) {
            $zohoBean->setModifiedTime($applicationBean->getZohoLastModificationDate());
        }

        return $zohoBean;
    }

    /**
     * Returns a Zoho Bean based on the bean passed in argument
     *
     * @param  ZohoBeanInterface $zohoBean
     * @return object
     */
    public function toApplicationBean(ZohoBeanInterface $zohoBean)
    {
        if (!$zohoBean instanceof Contact) {
            throw new ZohoCRMException("Expected Contact");
        }
        $applicationBean = new ContactApplicationBean();
        $applicationBean->setLastName($zohoBean->getLastName());
        $applicationBean->setFirstName($zohoBean->getFirstName());
        $applicationBean->setEmail($zohoBean->getEmail());
        $applicationBean->setPhone($zohoBean->getPhone());
        $applicationBean->setZohoId($zohoBean->getZohoId());
        $applicationBean->setZohoLastModificationDate($zohoBean->getModifiedTime());

        return $applicationBean;
    }

    /**
     * Stores the ZohoID in the bean passed in parameter.
     *
     * @param object $applicationBean
     * @param string $zohoId
     */
    public function onSyncToZohoComplete($applicationBean, $zohoId, \DateTime $date = null)
    {
        if (!$applicationBean instanceof ContactApplicationBean) {
            throw new ZohoCRMException("Expected ContactApplicationBean");
        }
        $applicationBean->setZohoId($zohoId);
        $applicationBean->setZohoLastModificationDate($date);
    }

    /**
     * Returns an array of application beans that have been modified since the last synchronisation with Zoho.
     *
     * @return \object[]
     */
    public function getBeansToSynchronize()
    {
        return $this->testContacts;
    }

    /**
     * This function should return the last Zoho modification date of a record saved in YOUR database.
     * The date must be returned as a \DateTime object.
     * Note: when a Zoho bean is inserted, the last modification date is passed to the `onSyncToZohoComplete`.
     * You should store that date in the database.
     *
     * @return \DateTime
     */
    public function getLastZohoModificationDate() {
        $now = new \DateTime();
        return $now->sub(new \DateInterval("P1D"));
    }

    /**
     * Function called when an application bean was deleted in Zoho.
     *
     * @param string $zohoId
     */
    public function onDeletedInZoho($zohoId)
    {
        // Does nothing
    }

    /**
     * Function called when an error was thrown by Zoho while trying to update a record.
     *
     * @param object $applicationBean
     * @param string $zohoId
     * @param ZohoCRMException $exception The exception representing the error regarding this record.
     */
    public function onSyncToZohoError($applicationBean, $zohoId, ZohoCRMException $exception)
    {
        // Does nothing
    }

    /**
     * Function called when the record in Zoho was merged with another record. Unfortunately, there is no way to
     * return the new ZohoId.
     *
     * @param object $applicationBean
     * @param string $zohoId
     */
    public function onMerged($applicationBean, $zohoId)
    {
        // Does nothing
    }
}
