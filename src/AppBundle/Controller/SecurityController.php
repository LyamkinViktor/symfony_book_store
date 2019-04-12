<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginForm;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render(
            '@App/security/login.html.twig',
            array(
                'form' => $form->createView(),
                'error' => $error
            )
        );

    }

    /**
     * @Route("/logout", name="logout")
     * @throws Exception
     */
    public function logoutAction()
    {
        throw new Exception('exit');
    }
}