<?php
namespace Wabel\Zoho\CRM\Sync;

require 'ContactApplicationBean.php';
require 'ContactMapper.php';

use Psr\Log\NullLogger;
use TestNamespace\ContactZohoDao;
use Wabel\Zoho\CRM\Service\EntitiesGeneratorService;
use Wabel\Zoho\CRM\ZohoClient;

class ZohoSynchronizerTest extends \PHPUnit_Framework_TestCase
{

    public function getZohoClient()
    {
        return new ZohoClient($GLOBALS['auth_token']);
    }

    public function getEntitiesGeneratorService()
    {
        return new EntitiesGeneratorService($this->getZohoClient(), new NullLogger());
    }

    protected $firstName;

    public function testSync()
    {
        $generator = $this->getEntitiesGeneratorService();
        $generator->generateModule('Contacts', 'Contacts', 'Contact', __DIR__.'/generated/', 'TestNamespace');

        require __DIR__.'/generated/Contact.php';
        require __DIR__.'/generated/ContactZohoDao.php';

        $contactZohoDao = new ContactZohoDao($this->getZohoClient());
        $firstName = uniqid("Test");
        $this->firstName = $firstName;

        $contacts = [
            new ContactApplicationBean(1, "Test1", $firstName, "test@yopmail.com", "0123456789"),
            new ContactApplicationBean(2, "Test2", $firstName, "test2@yopmail.com", "0123456789"),
            new ContactApplicationBean(3, "Test3", $firstName, "test3@yopmail.com", "0123456789"),
            new ContactApplicationBean(4, "Test4", $firstName, "test4@yopmail.com", "0123456789"),
            new ContactApplicationBean(5, "Test5", $firstName, "test5@yopmail.com", "0123456789"),
        ];

        $mapper = new ContactMapper();
        $mapper->setTestContacts($contacts);

        // Let's start by removing past inserted clients:
        $pastContacts = $contactZohoDao->searchRecords('(First Name:'.$firstName.')');
        foreach ($pastContacts as $pastContact) {
            $contactZohoDao->delete($pastContact->getZohoId());
        }

        // Before calling sync, let's input some test data to sync!
        $contactBean = new \TestNamespace\Contact();
        $contactBean->setFirstName($firstName);
        $contactBean->setLastName("InZohoFirst");
        $contactZohoDao->save($contactBean);

        // Let's wait for the sync of our Zoho user.
        sleep(120);

        $zohoSynchronizer = new ZohoSynchronizer($contactZohoDao, $mapper);

        $appBeans = $zohoSynchronizer->getZohoBeansInApp();

        $found = false;
        foreach ($appBeans as $appBean) {
            $this->assertInstanceOf("Wabel\\Zoho\\CRM\\Sync\\ContactApplicationBean", $appBean);
            if ($appBean->getLastName() == 'InZohoFirst') {
                $found = true;
            }
        }
        if (!$found) {
            $this->fail('Could not find bean from Zoho in app.');
        }


        $zohoSynchronizer->sendAppBeansToZoho();


        sleep(120);

        $newContacts = $contactZohoDao->searchRecords('(First Name:'.$firstName.')');
        $this->assertCount(6, $newContacts);
        // The ZohoID should be set in all fields:
        foreach ($newContacts as $contact) {
            $this->assertNotEmpty($contact->getZohoId());
        }
    }

    protected function tearDown()
    {
        $contactZohoDao = new ContactZohoDao($this->getZohoClient());
        // Let's end by removing past inserted clients:
        $pastContacts = $contactZohoDao->searchRecords('(First Name:'.$this->firstName.')');
        foreach ($pastContacts as $pastContact) {
            $contactZohoDao->delete($pastContact->getZohoId());
        }
    }
}
