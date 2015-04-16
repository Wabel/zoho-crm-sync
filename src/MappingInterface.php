<?php
namespace Wabel\Zoho\CRM\Sync;

use Wabel\Zoho\CRM\ZohoBeanInterface;

/**
 * Classes implementing this interface can map Zoho bean to your application beans.
 * This interface needs to be implemented in your application code.
 */
interface MappingInterface
{

    /**
     * Returns a Zoho Bean based on the bean passed in argument
     *
     * @param  object            $applicationBean
     * @return ZohoBeanInterface
     */
    public function toZohoBean($applicationBean);

    /**
     * Returns a Zoho Bean based on the bean passed in argument
     *
     * @param  ZohoBeanInterface $zohoBean
     * @return object
     */
    public function toApplicationBean(ZohoBeanInterface $zohoBean);

    /**
     * Function called when an application bean was succcessfully stored in Zoho.
     * Used to store the ZohoID and last modification date in the bean passed in parameter.
     *
     * @param object    $applicationBean
     * @param string    $zohoId
     * @param \DateTime $modificationDate
     */
    public function onSyncToZohoComplete($applicationBean, $zohoId, \DateTime $modificationDate);

    /**
     * Returns an array of application beans that have been modified since the last synchronisation with Zoho.
     *
     * @return \object[]
     */
    public function getBeansToSynchronize();

    /**
     * This function should return the last Zoho modification date of a record saved in YOUR database.
     * The date must be returned as a \DateTime object.
     * Note: when a Zoho bean is inserted, the last modification date is passed to the `onSyncToZohoComplete`.
     * You should store that date in the database.
     *
     * @return \DateTime
     */
    public function getLastZohoModificationDate();
}
