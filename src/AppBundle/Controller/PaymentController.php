<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PaymentController extends Controller
{
    /**
     * @Route("/payment", name="payment")
     */
    public function indexAction()
    {
        $lastUsername = $this->get('security.authentication_utils')->getLastUsername();


        if (!empty($lastUsername)) {
            $username = $lastUsername;
        } else {
            return $this->redirectToRoute('login');
        }

        return $this->render('@App/payment/index.html.twig', [
            'username' => $username,
        ]);
    }

    /**
     * @Route("payment/charge/{username}", name="charge")
     * @param $username
     * @return RedirectResponse
     */
    public function chargeAction($username)
    {

        Stripe::setApiKey('sk_test_2kKmRAbpAQf5q60aaNomaDu000Y2RcIDSd');

        //Sanitize POST array
        $POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);

        /*
        $first_name = $POST['first_name'];
        $last_name = $POST['last_name'];
        $email = $POST['email'];
        */

        $email = $username;
        $token = $POST['stripeToken'];



        //Create Customer in stripe

        $customer = Customer::create([
            'email' => $email,
            'source' => $token,
        ]);


        //Charge Customer
        $charge = Charge::create([
            'amount' => 4200,
            'currency' => 'usd',
            'description' => 'Subscription',
            'customer' => $customer->id,
        ]);


        // Instantiate Transaction
        $transaction = new Transaction();

        // Add transaction to Db
        $transaction->setId($charge->id);
        $transaction->setProduct($charge->description);
        $transaction->setAmount($charge->amount);
        $transaction->setCurrency($charge->currency);
        $transaction->setStatus($charge->status);
        $transaction->setCustomerId($charge->customer);
        $transaction->setUser($this->getUser());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($transaction);
        $entityManager->flush();

        // Add a message, redirect to success
        $this->addFlash('success', 'Payment successful!');
        return $this->redirectToRoute('success', [
            'tid' => $charge->id,
            'product' => $charge->description,
        ]);


    }

    /**
     * @Route("payment/success", name="success")
     */
    public function success()
    {
        if (!empty($_GET['tid'] && !empty($_GET['product']))) {
            $GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);

            $tid = $GET['tid'];
            $product = $GET['product'];
        } else {
            return $this->redirectToRoute('payment');
        }

        return $this->render('@App/payment/success.html.twig', [
            'tid' => $tid,
            'product' => $product,
        ]);
    }

    /**
     * @Route("payment/transactions", name="transactions")
     */
    public function showTransactionsAction()
    {

        // Get Transaction
        $transactions = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Transaction')
            ->findAll();


        return $this->render('@App/payment/showTransaction.html.twig', [
            'transactions' => $transactions,
        ]);
    }
}