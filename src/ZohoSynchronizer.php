<?php
namespace Wabel\Zoho\CRM\Sync;

use Wabel\Zoho\CRM\AbstractZohoDao;
use Wabel\Zoho\CRM\Exception\ZohoCRMException;
use Wabel\Zoho\CRM\Exception\ZohoCRMUpdateException;
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
     * @return int Number of records sent to Zoho
     */
    public function sync()
    {
        $lastZohoModificationDate = $this->mapper->getLastZohoModificationDate();

        $this->getZohoBeansInApp($lastZohoModificationDate);
        $this->processDeletedZohoBeans($lastZohoModificationDate);

        return $this->sendAppBeansToZoho();
    }

    /**
     * Sends modified beans to Zoho.
     * @return int Number of records synchronized
     */
    public function sendAppBeansToZoho()
    {
        $appBeans = $this->mapper->getBeansToSynchronize();
        if (!count($appBeans)) {
            return 0;
        }

        foreach ($appBeans as $appBean) {
            $zohoBeans[] = $this->mapper->toZohoBean($appBean);
        }
        /* @var $zohoBeans ZohoBeanInterface[] */

        $failedBeans = new \SplObjectStorage();
        try {
            $this->dao->save($zohoBeans);
        } catch (ZohoCRMUpdateException $updateException) {
            $failedBeans = $updateException->getFailedBeans();
        }

        foreach ($appBeans as $key => $appBean) {
            $zohoBean = $zohoBeans[$key];

            $zohoId = $zohoBean->getZohoId();

            if ($failedBeans->offsetExists($zohoBean)) {
                $exception = $failedBeans->offsetGet($zohoBean);
                if ($exception->getCode() == '401.2') {
                    $this->mapper->onContactMerged($appBean, $zohoId);
                } else {
                    $this->mapper->onSyncToZohoError($appBean, $zohoId, $exception);
                }
            } else {
                $modifiedTime = $zohoBean->getModifiedTime();
                if ($modifiedTime === null) {
                    $modifiedTime = $zohoBean->getCreatedTime();
                }
                $this->mapper->onSyncToZohoComplete($appBean, $zohoId, $modifiedTime);
            }
        }

        return count($appBeans);
    }

    /**
     * Gets modified beans from Zoho into the application.
     * @param \DateTimeInterface $lastZohoModificationDate
     * @return array
     * @throws \Exception
     * @throws \Wabel\Zoho\CRM\Exception\ZohoCRMResponseException
     */
    public function getZohoBeansInApp(\DateTimeInterface $lastZohoModificationDate = null)
    {
        if ($lastZohoModificationDate === null) {
            $lastZohoModificationDate = $this->mapper->getLastZohoModificationDate();
        }

        $zohoBeans = $this->dao->getRecords(null, null, $lastZohoModificationDate);

        $appBeans = array_map(array($this->mapper, "toApplicationBean"), $zohoBeans);

        return $appBeans;
    }

    /**
     * Processes beans removed from Zoho in the application.
     * @param \DateTimeInterface $lastZohoModificationDate
     * @throws \Exception
     * @throws \Wabel\Zoho\CRM\Exception\ZohoCRMResponseException
     */
    public function processDeletedZohoBeans(\DateTimeInterface $lastZohoModificationDate = null)
    {
        if ($lastZohoModificationDate === null) {
            $lastZohoModificationDate = $this->mapper->getLastZohoModificationDate();
        }

        $deletedRecordIds = $this->dao->getDeletedRecordIds($lastZohoModificationDate);

        foreach ($deletedRecordIds as $deletedZohoId) {
            $this->mapper->onDeletedInZoho($deletedZohoId);
        }
    }
}
