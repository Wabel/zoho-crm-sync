<?php
namespace Wabel\Zoho\CRM\Sync;

use Wabel\Zoho\CRM\AbstractZohoDao;
use Wabel\Zoho\CRM\ZohoBeanInterface;

/**
 * This class is in charge of synchronizing one table of your database with Zoho records.
 */
class ZohoSynchronizer {

    /**
     * @var AbstractZohoDao
     */
    private $dao;

    /**
     * @var MappingInterface
     */
    private $mapper;


    /**
     * @param AbstractZohoDao $dao The DAO to synchronize
     * @param MappingInterface $mapper The mapper used for the synchronization
     */
    public function __construct(AbstractZohoDao $dao, MappingInterface $mapper)
    {
        $this->dao = $dao;
        $this->mapper = $mapper;
    }

    /**
     * Syncs all records between Zoho and your application.
     */
    public function sync() {
        $this->sendAppBeansToZoho();
    }

    /**
     * Sends modified beans to Zoho.
     */
    protected function sendAppBeansToZoho() {
        // TODO: fix the date!
        $contactAppBeans = $this->mapper->getBeansToSynchronize();

        $contactZohoBeans = array_map(array($this->mapper, "toZohoBean"), $contactAppBeans);
        /* @var $contactZohoBeans ZohoBeanInterface[] */

        $this->dao->save($contactZohoBeans);

        foreach ($contactAppBeans as $key => $contactAppBean) {
            $contactZohoBean = $contactZohoBeans[$key];
            $modifiedTime = $contactZohoBean->getModifiedTime();
            if ($modifiedTime === null) {
                $modifiedTime = $contactZohoBean->getCreatedTime();
            }
            $this->mapper->onSyncToZohoComplete($contactAppBean, $contactZohoBean->getZohoId(), $modifiedTime);
        }
    }
}
