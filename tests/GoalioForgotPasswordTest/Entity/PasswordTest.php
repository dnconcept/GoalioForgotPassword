<?php

namespace GoalioForgotPasswordTest\Entity;

use DateTime;
use GoalioForgotPassword\Entity\Password;

/**
 * Class PasswordTest
 * @author Nicolas Desprez <contact@dnconcept.fr>
 * @package GoalioForgotPasswordTest\Entity
 */
class PasswordTest extends \PHPUnit_Framework_TestCase
{

    /** @var  Password */
    private $Password;

    protected function setUp()
    {
        $this->Password = new Password();
    }

    public function testSetUserId_WillReturnInstanceOfPassord()
    {
        $this->assertInstanceOf(Password::class, $this->Password->setUserId(10));
    }

    public function testGetUserId_WillReturn_SameUserIdAsSetter()
    {
        $this->Password->setUserId(10);
        $this->assertEquals(10, $this->Password->getUserId());
    }

    public function testGetRequestTime()
    {
        $this->assertInstanceOf(DateTime::class, $this->Password->getRequestTime());
    }

    public function testSetRequestTime()
    {
        $request = new DateTime('2015-12-01');
        $this->Password->setRequestTime($request);
        $this->assertEquals('2015-12-01', $this->Password->getRequestTime()->format('Y-m-d'));
    }

    public function testGenerateRequestKey()
    {
        $request = new DateTime('2015-12-01');
        $this->Password
            ->setUserId(10)
            ->setRequestTime($request);
        $this->Password->generateRequestKey();
        $this->assertEquals('D5DD7B509F182E8', $this->Password->getRequestKey());
    }

}