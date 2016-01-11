<?php
/**
 * Created by PhpStorm.
 * @author Nicolas Desprez <contact@dnconcept.fr>
 */

namespace GoalioForgotPassword\Controller;

use GoalioForgotPassword\Service\Password;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcUser\Service\User as ZfcUserService;


/**
 * Class ForgotControllerFactory
 * @author Nicolas Desprez <contact@dnconcept.fr>
 * @package GoalioForgotPassword\Controller
 */
class ForgotControllerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface|PluginManager $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        /** @var ZfcUserService $zfcUserService */
        $zfcUserService = $serviceLocator->get('zfcuser_user_service');
        /** @var Password $passwordService */
        $passwordService = $serviceLocator->get('goalioforgotpassword_password_service');
        return new ForgotController($zfcUserService, $passwordService);
    }

}