<?php

namespace GoalioForgotPassword\Service;

use Zend\Mail\Transport\TransportInterface;
use ZfcUser\Options\PasswordOptionsInterface;
use GoalioForgotPassword\Options\ForgotOptionsInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZfcUser\Mapper\UserInterface as UserMapperInterface;
use GoalioForgotPassword\Mapper\Password as PasswordMapper;
use Zend\Crypt\Password\Bcrypt;
use ZfcBase\EventManager\EventProvider;

class Password extends EventProvider implements ServiceManagerAwareInterface
{
    /** @var PasswordMapper */
    protected $passwordMapper;

    protected $userMapper;

    /** @var  ServiceManager */
    protected $serviceManager;

    /** @var  ForgotOptionsInterface */
    protected $options;

    /** @var  PasswordOptionsInterface */
    protected $zfcUserOptions;

    protected $emailRenderer;

    protected $emailTransport;

    /**
     * Password constructor.
     * @param PasswordMapper $passwordMapper
     * @param PasswordOptionsInterface $zfcUserOptions
     * @param ForgotOptionsInterface $options
     */
    public function __construct(PasswordMapper $passwordMapper, PasswordOptionsInterface $zfcUserOptions, ForgotOptionsInterface $options)
    {
        $this->passwordMapper = $passwordMapper;
        $this->zfcUserOptions = $zfcUserOptions;
        $this->options = $options;
    }

    public function cleanExpiredForgotRequests()
    {
        // TODO: reset expiry time from options
        return $this->passwordMapper->cleanExpiredForgotRequests();
    }

    public function cleanPriorForgotRequests($userId)
    {
        return $this->passwordMapper->cleanPriorForgotRequests($userId);
    }

    public function remove($passwordModel)
    {
        return $this->passwordMapper->remove($passwordModel);
    }

    public function sendProcessForgotRequest($userId, $email)
    {
        //Invalidate all prior request for a new password
        $this->cleanPriorForgotRequests($userId);

        $class = $this->options->getPasswordEntityClass();
        /** @var \GoalioForgotPassword\Entity\Password $model */
        $model = new $class;
        $model->setUserId($userId);
        $model->setRequestTime(new \DateTime('now'));
        $model->generateRequestKey();
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('record' => $model, 'userId' => $userId));
        $this->passwordMapper->persist($model);

        $this->sendForgotEmailMessage($email, $model);
    }

    public function sendForgotEmailMessage($to, $model)
    {
        $mailService = $this->getServiceManager()->get('goaliomailservice_message');

        $from = $this->options->getEmailFromAddress();
        $subject = $this->options->getResetEmailSubjectLine();
        $template = $this->options->getResetEmailTemplate();

        $message = $mailService->createTextMessage($from, $to, $subject, $template, array('record' => $model));

        $mailService->send($message);
    }

    public function resetPassword($password, $user, array $data)
    {
        $newPass = $data['newCredential'];

        $bcrypt = new Bcrypt;
        $bcrypt->setCost($this->zfcUserOptions->getPasswordCost());

        $pass = $bcrypt->create($newPass);
        $user->setPassword($pass);

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $user));
        $this->getUserMapper()->update($user);
        $this->remove($password);
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user));

        return true;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceManager()->get('zfcuser_user_mapper');
        }
        return $this->userMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setUserMapper(UserMapperInterface $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }

    public function setMessageRenderer(ViewRenderer $emailRenderer)
    {
        $this->emailRenderer = $emailRenderer;
        return $this;
    }

    public function getMessageTransport()
    {
        if (!$this->emailTransport instanceof TransportInterface) {
            $this->setEmailTransport($this->getServiceManager()->get('goalioforgotpassword_email_transport'));
        }

        return $this->emailTransport;
    }

    public function setMessageTransport(EmailTransport $emailTransport)
    {
        $this->emailTransport = $emailTransport;
        return $this;
    }

    /**
     * @return PasswordMapper
     */
    public function getPasswordMapper()
    {
        return $this->passwordMapper;
    }

}
