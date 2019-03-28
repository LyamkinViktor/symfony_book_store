<?php

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        //Построение формы
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        //Обработка отправки, пройдет только в POST
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            //Шифрование пароля, также можно сделать через слушатель Doctrine
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            //Сохранение пользователя
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... сделайте любую другую работу - вроде отправки письма и др
            // может, установите "флеш" сообщение об успешном выполнении для пользователя

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            '@App/security/register.html.twig',
            ['form' => $form->createView()]
        );

    }

}