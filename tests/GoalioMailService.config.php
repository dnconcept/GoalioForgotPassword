<?php

use GoalioForgotPassword\Mapper\Password;
use GoalioForgotPassword\Options\ModuleOptions;

return array(
    'db' => array(
        'driver' => 'Pdo_Sqlite',
        'database' => __DIR__ . '/data/database-test.db',
    ),
    'service_manager' => array(
        'aliases' => array(
            'zfcuser_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
        ),
        'invokables' => array(
            'goalioforgotpassword_password_service' => 'GoalioForgotPassword\Service\Password',
        ),
        'factories' => array(
            'goalioforgotpassword_module_options' => function ($serviceLocator) {
                return new ModuleOptions();
            },
            'Zend\Db\Adapter\Adapter' => function ($serviceLocator) {
                return new \Zend\Db\Adapter\Adapter($serviceLocator->get('config')['db']);
            },
            'goalioforgotpassword_forgot_form' => 'GoalioForgotPassword\Form\Service\ForgotFactory',
            'goalioforgotpassword_reset_form' => 'GoalioForgotPassword\Form\Service\ResetFactory',
            'goalioforgotpassword_password_mapper' => function ($serviceLocator) {
                /** @var ModuleOptions $options */
                $options = $serviceLocator->get('goalioforgotpassword_module_options');
                $mapper = new Password();
                $mapper->setDbAdapter($serviceLocator->get('zfcuser_zend_db_adapter'));
                $entityClass = $options->getPasswordEntityClass();
                $mapper->setEntityPrototype(new $entityClass);
                $mapper->setHydrator(new \GoalioForgotPassword\Mapper\PasswordHydrator());
                return $mapper;
            },
        ),
    ),
);