<?php

namespace GoalioForgotPasswordTest\Service;

use GoalioForgotPassword\Service\Password;
use GoalioForgotPasswordTest\Bootstrap;

/**
 * Class PasswordTest
 * @author Nicolas Desprez <contact@dnconcept.fr>
 * @package GoalioForgotPasswordTest\Service
 */
class PasswordTest extends \PHPUnit_Framework_TestCase
{

    /** @var  Password */
    private $passwordService;

    /** @var \Zend\Db\Adapter\Adapter $adapter */
    private $adapter;

    private function initDatabase()
    {
        $serviceLocator = Bootstrap::getServiceManager();
        $this->adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
        $this->dbQuery(file_get_contents(TEST_DIR . '/schema.sqlite.sql'));
    }

    protected function setUp()
    {
        $this->initDatabase();
        $serviceLocator = Bootstrap::getServiceManager();
        $password_mapper = $serviceLocator->get('goalioforgotpassword_password_mapper');
        $zfcuser_options = $this->getMock(\ZfcUser\Options\PasswordOptionsInterface::class);
        $zfcuser_options->expects($this->any())
            ->method('getPasswordCost')
            ->willReturn(10);
        $module_options = $serviceLocator->get('goalioforgotpassword_module_options');
        $this->passwordService = new Password($password_mapper, $zfcuser_options, $module_options);
    }

    public function testRemove_WillReturnFalse_IfUserDoesnotExists()
    {
        $passWordEntity = new \GoalioForgotPassword\Entity\Password();
        $passWordEntity->setRequestKey('test');
        $this->assertFalse($this->passwordService->remove($passWordEntity));
    }

    private function dbQuery($sql)
    {
        return $this->adapter->createStatement($sql)->execute();
    }

    public function testRemove_WillReturnTrue_IfUserExists()
    {
        $passWordEntity = new \GoalioForgotPassword\Entity\Password();
        $passWordEntity
            ->setUserId(10)
            ->setRequestKey('test');
        $now = date('Y-m-d');
        $sql = "INSERT INTO user_password_reset('user_id', 'request_key', 'request_time') VALUES(10, 'test', $now)";
        $this->dbQuery($sql);
        $this->assertTrue($this->passwordService->remove($passWordEntity));
        $this->assertEquals(0, $this->dbQuery("SELECT * FROM user_password_reset")->count());
    }

}
