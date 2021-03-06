<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Transaction;
use Exception;
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
     * @throws Exception
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


        // Instantiate SubscriptionDb
        $subscriptionDb = new \AppBundle\Entity\Subscription();

        // Add subscriptionDb to Db
        $subscriptionDb->setSubscriptionId($subscription->id);
        $subscriptionDb->setProduct($plan->id);
        $subscriptionDb->setAmount($plan->amount);
        $subscriptionDb->setCurrency($plan->currency);
        $subscriptionDb->setStatus($subscription->status);
        $subscriptionDb->setCustomerId($subscription->customer);
        $subscriptionDb->setCurrentPeriodEnd($subscription->current_period_end);
        $subscriptionDb->setUser($this->getUser());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($subscriptionDb);
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
     * @throws Exception
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
     * @Route("payment/subscriptions", name="subscriptions")
     */
    public function showSubscriptionsAction()
    {

        // Get Subscriptions
        $subscriptions = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Subscription')
            ->findAll();


        return $this->render('@App/payment/showSubscriptions.html.twig', [
            'subscriptions' => $subscriptions,
        ]);
    }


}