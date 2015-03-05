<?php
namespace Wabel\Zoho\CRM\Sync;

require 'ContactApplicationBean.php';
require 'ContactMapper.php';

use TestNamespace\ContactZohoDao;
use Wabel\Zoho\CRM\Service\EntitiesGeneratorService;
use Wabel\Zoho\CRM\ZohoClient;

class ZohoSynchronizerTest extends \PHPUnit_Framework_TestCase {

    public function getZohoClient() {
        return new ZohoClient($GLOBALS['auth_token']);
    }

    public function getEntitiesGeneratorService()
    {
        return new EntitiesGeneratorService($this->getZohoClient());
    }

    public function testSync() {
        $generator = $this->getEntitiesGeneratorService();
        $generator->generateModule('Contacts', 'Contacts', 'Contact', __DIR__.'/generated/', 'TestNamespace');

        require(__DIR__.'/generated/Contact.php');
        require(__DIR__.'/generated/ContactZohoDao.php');

        $contactZohoDao = new ContactZohoDao($this->getZohoClient());

        $contacts = [
            new ContactApplicationBean(1, "Test1", "Test", "test@yopmail.com", "0123456789"),
            new ContactApplicationBean(2, "Test2", "Test", "test2@yopmail.com", "0123456789"),
            new ContactApplicationBean(3, "Test3", "Test", "test3@yopmail.com", "0123456789"),
            new ContactApplicationBean(4, "Test4", "Test", "test4@yopmail.com", "0123456789"),
            new ContactApplicationBean(5, "Test5", "Test", "test5@yopmail.com", "0123456789"),
        ];

        $mapper = new ContactMapper();
        $mapper->setTestContacts($contacts);


        // Let's start by removing past inserted clients:
        $pastContacts = $contactZohoDao->searchRecords('(First Name:Test)');
        foreach ($pastContacts as $pastContact) {
            $contactZohoDao->delete($pastContact->getZohoId());
        }

        $zohoSynchronizer = new ZohoSynchronizer($contactZohoDao, $mapper);

        $zohoSynchronizer->sync();

        sleep(120);

        $newContacts = $contactZohoDao->searchRecords('(First Name:Test)');
        $this->assertCount(5, $newContacts);
        // The ZohoID should be set in all fields:
        foreach ($contacts as $contact) {
            $this->assertNotEmpty($contact->getZohoId());
        }
    }
}
