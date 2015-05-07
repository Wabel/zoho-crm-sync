<?php
namespace Wabel\Zoho\CRM\Sync;

use Mouf\Mvc\Splash\HtmlResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Wabel\Zoho\CRM\AbstractZohoDao;
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
        $this->getZohoBeansInApp();
        return $this->sendAppBeansToZoho();
    }

    /**
     * Sends modified beans to Zoho.
     * @return int Number of records synchronized
     */
    public function sendAppBeansToZoho()
    {
        $appBeans = $this->mapper->getBeansToSynchronize();
        if(!count($appBeans)) {
            return 0;
        }

        foreach($appBeans as $appBean) {
            $zohoBeans[] = $this->mapper->toZohoBean($appBean);
        }
        /* @var $zohoBeans ZohoBeanInterface[] */

        $failedBeans = new \SplObjectStorage();
        try  {
            $this->dao->save($zohoBeans);
        } catch (ZohoCRMUpdateException $updateException) {
            $failedBeans = $updateException->getFailedBeans();
        }

        foreach ($appBeans as $key => $appBean) {
            $zohoBean = $zohoBeans[$key];

            if ($failedBeans->offsetExists($zohoBean)) {
                $zohoId = null;
                $modifiedTime = null;
            } else {
                $modifiedTime = $zohoBean->getModifiedTime();
                if ($modifiedTime === null) {
                    $modifiedTime = $zohoBean->getCreatedTime();
                }
                $zohoId = $zohoBean->getZohoId();
            }

            $this->mapper->onSyncToZohoComplete($appBean, $zohoId, $modifiedTime);
        }

        return count($appBeans);
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
