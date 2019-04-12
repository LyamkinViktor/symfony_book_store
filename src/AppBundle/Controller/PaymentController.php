<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    /**
     * @Route("/payment/", name="payment")
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
     * @Route("payment/charge/{username}&{pid}", name="charge")
     * @param Request $request
     * @param $username
     * @param $pid
     * @return RedirectResponse
     */
    public function chargeAction(Request $request, $username, $pid)
    {
        if (empty($request->request->get('stripeToken'))) {
            return $this->redirectToRoute('payment');
        } else {
            $token = $request->request->get('stripeToken');
        }

        //Set secret key
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));


        //Create Customer in stripe
        /** @var Customer $customer */
        $customer = Customer::create([
            'email' => $username,
            'source' => $token,
        ]);

        $plans = [
            'month' => 'Month_subscription',
            'year' => 'Year_subscription'
        ];

        //Create Subscription
        /** @var Subscription $subscription */
        $subscription = Subscription::create([
            "customer" => $customer->id,
            "items" => [
                [
                    "plan" => $plans[$pid],
                ],
            ]
        ]);

        // Plan of subscription
        $plan = $subscription->plan;

        //convert UNIX timestamps to date strings
        //$currentPeriodEnd = gmdate("Y-m-d | H:i:s", $subscription->current_period_end);

        // Instantiate Transaction
        $transaction = new Transaction();


        // Add transaction to Db
        $transaction->setId($subscription->id);
        $transaction->setProduct($plan->id);
        $transaction->setAmount($plan->amount);
        $transaction->setCurrency($plan->currency);
        $transaction->setStatus($subscription->status);
        $transaction->setCustomerId($subscription->customer);
        $transaction->setCurrentPeriodEnd($subscription->current_period_end);
        $transaction->setUser($this->getUser());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($transaction);
        $entityManager->flush();

        // Add a message, redirect to success
        $this->addFlash('success', 'Payment successful!');
        return $this->redirectToRoute('success', [
            'tid' => $subscription->id,
            'product' => $plan->id,
        ]);


    }

    /**
     * @Route("payment/success", name="success")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function success(Request $request)
    {

        if (!empty($request->query->get('tid') && !empty($request->query->get('product')))) {

            $tid = $request->query->get('tid');
            $product = $request->query->get('product');

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