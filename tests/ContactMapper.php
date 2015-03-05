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
    public function onSyncToZohoComplete($applicationBean, $zohoId, \DateTime $date)
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
     * Filters the list of Zoho beans to store in database.
     *
     *
     * @param  array $zohoBeans
     * @return array The filtered list of Zoho beans.
     */
    public function filterZohoBeans(array $zohoBeans)
    {
        return $zohoBeans;
    }
}
