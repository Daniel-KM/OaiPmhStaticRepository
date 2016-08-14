<?php

class OaiPmhStaticRepository_UpdateTest extends OaiPmhStaticRepository_Test_AppTestCase
{
    protected $_isAdminTest = true;

    public function setUp()
    {
        parent::setUp();

        // Authenticate and set the current user.
        $this->user = $this->db->getTable('User')->find(1);
        $this->_authenticateUser($this->user);
    }

    public function testFolder()
    {
        // TODO Use the imported folder.
        $this->markTestSkipped(
            __('Test for updates should be done.')
        );
    }
}
