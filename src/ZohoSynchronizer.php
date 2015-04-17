<?php
namespace Wabel\Zoho\CRM\Sync;

use Wabel\Zoho\CRM\AbstractZohoDao;
use Wabel\Zoho\CRM\ZohoBeanInterface;

/**
 * This class is in charge of synchronizing one table of your database with Zoho records.
 */
class ZohoSynchronizer
{

    /**
     * @var AbstractZohoDao
     */
    private $dao;

    /**
     * @var MappingInterface
     */
    private $mapper;

    /**
     * @param AbstractZohoDao  $dao    The DAO to synchronize
     * @param MappingInterface $mapper The mapper used for the synchronization
     */
    public function __construct(AbstractZohoDao $dao, MappingInterface $mapper)
    {
        $this->dao = $dao;
        $this->mapper = $mapper;
    }

    /**
     * Syncs all records between Zoho and your application.
     *
     * This will call both "getZohoBeansInApp" and "sendAppBeansToZoho"
     */
    public function sync()
    {
        $this->getZohoBeansInApp();
        $this->sendAppBeansToZoho();
    }

    /**
     * Sends modified beans to Zoho.
     */
    public function sendAppBeansToZoho()
    {
        $appBeans = $this->mapper->getBeansToSynchronize();

        foreach($appBeans as $appBean) {
            $zohoBeans[] = $this->mapper->toZohoBean($appBean);
        }
        /* @var $zohoBeans ZohoBeanInterface[] */

        $this->dao->save($zohoBeans);

        foreach ($appBeans as $key => $appBean) {
            $zohoBean = $zohoBeans[$key];
            $modifiedTime = $zohoBean->getModifiedTime();
            if ($modifiedTime === null) {
                $modifiedTime = $zohoBean->getCreatedTime();
            }
            $this->mapper->onSyncToZohoComplete($appBean, $zohoBean->getZohoId(), $modifiedTime);
        }
    }

    /**
     * Gets modified beans from Zoho into the application.
     */
    public function getZohoBeansInApp() {

        $lastZohoModificationDate = $this->mapper->getLastZohoModificationDate();

        $zohoBeans = $this->dao->getRecords(null, null, $lastZohoModificationDate);

        $appBeans = array_map(array($this->mapper, "toApplicationBean"), $zohoBeans);

        return $appBeans;
    }
}
