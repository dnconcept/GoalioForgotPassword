<?php

namespace GoalioForgotPassword\Controller;

use GoalioForgotPassword\Options\ForgotOptionsInterface;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use GoalioForgotPassword\Service\Password as PasswordService;
use GoalioForgotPassword\Options\ForgotControllerOptionsInterface;
use ZfcUser\Service\User as ZfcUserService;

class ForgotController extends AbstractActionController
{
    /** @var ZfcUserService */
    protected $userService;

    /** @var PasswordService */
    protected $passwordService;

    /** @var Form */
    protected $forgotForm;

    /** @var Form */
    protected $resetForm;

    /**
     * ForgotController constructor.
     * @param ZfcUserService $userService
     * @param PasswordService $passwordService
     */
    public function __construct(ZfcUserService $userService, PasswordService $passwordService)
    {
        $this->userService = $userService;
        $this->passwordService = $passwordService;
    }

    public function forgotAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute('zfcuser');
        }
        $this->passwordService->cleanExpiredForgotRequests();
        $redirectUrl = $this->url()->fromRoute('zfcuser/forgotpassword');
        $prg = $this->prg($redirectUrl, true);

        $form = $this->getForgotForm();
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response) {
            // returned a response to redirect us
            return $prg;
        } elseif ($prg === false) {
            // this wasn't a POST request, but there were no params in the flash messenger
            // probably this is the first time the form was loaded
            // Render the form
            return array(
                'forgotForm' => $form,
            );
        }

        $form->setData($prg);
        if ($form->isValid()) {

            $email = $form->get('email')->getValue();
            $user = $this->userService->getUserMapper()->findByEmail($email);

            //only send request when email is found
            if ($user != null) {
                $this->passwordService->sendProcessForgotRequest($user->getId(), $email);
            }

            $vm = new ViewModel(array('email' => $email));
            $vm->setTemplate('goalio-forgot-password/forgot/sent');
            return $vm;
        } else {
            return array(
                'forgotForm' => $form,
            );
        }

    }

    public function resetAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute('zfcuser');
        }

        $this->passwordService->cleanExpiredForgotRequests();

        $form = $this->getResetForm();

        $userId = $this->params()->fromRoute('userId', null);
        $token = $this->params()->fromRoute('token', null);

        $passwordRequest = $this->passwordService->getPasswordMapper()->findByUserIdRequestKey($userId, $token);

        //no request for a new password found
        if ($passwordRequest === null || $passwordRequest == false) {
            return $this->redirect()->toRoute('zfcuser/forgotpassword');
        }

        $user = $this->userService->getUserMapper()->findById($userId);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid() && $user !== null) {
                $this->passwordService->resetPassword($passwordRequest, $user, $form->getData());

                $vm = new ViewModel(array('email' => $user->getEmail()));
                $vm->setTemplate('goalio-forgot-password/forgot/passwordchanged');
                return $vm;
            }
        }
        // Render the form
        return new ViewModel(array(
            'resetForm' => $form,
            'userId' => $userId,
            'token' => $token,
            'email' => $user->getEmail(),
        ));
    }

    /**
     * @param ZfcUserService $userService
     * @return $this
     */
    public function setUserService(ZfcUserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    public function setPasswordService(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
        return $this;
    }

    /**
     * Getters/setters for DI stuff
     */

    public function getForgotForm()
    {
        if (!$this->forgotForm) {
            $this->setForgotForm($this->getServiceLocator()->get('goalioforgotpassword_forgot_form'));
        }
        return $this->forgotForm;
    }

    public function setForgotForm(Form $forgotForm)
    {
        $this->forgotForm = $forgotForm;
        return $this;
    }

    public function getResetForm()
    {
        if (!$this->resetForm) {
            $this->setResetForm($this->getServiceLocator()->get('goalioforgotpassword_reset_form'));
        }
        return $this->resetForm;
    }

    public function setResetForm(Form $resetForm)
    {
        $this->resetForm = $resetForm;
        return $this;
    }

}
