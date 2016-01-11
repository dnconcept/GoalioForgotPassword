<?php

namespace GoalioForgotPassword\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ServicePasswordFactory
 * @author Nicolas Desprez <contact@dnconcept.fr>
 * @package GoalioForgotPassword\Factory
 */
class PasswordFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $password_mapper = $serviceLocator->get('goalioforgotpassword_password_mapper');
        $zfcuser_options = $serviceLocator->get('zfcuser_module_options');
        $module_options = $serviceLocator->get('goalioforgotpassword_module_options');
        return new Password($password_mapper, $zfcuser_options, $module_options);
    }

}