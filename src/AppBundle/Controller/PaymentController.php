<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Transaction;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\StripeObject;
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

        $event = $this->stripeWebhookAction($request);

        dump($event); exit;

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

    /**
     * @param Request $request
     * @return StripeObject
     * @throws Exception
     */
    public function stripeWebhookAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new Exception('Bad JSON body from Stripe!');
        }

        $eventId = $data['id'];

        // Actions depending on webhooks
//        if ($data['type'] === 'invoice.payment_succeeded') {
//            //To do something
//         }

        $stripeEvent = $this->findEvent($eventId);

        return $stripeEvent;
    }

    /**
     * @param $eventId
     * @return StripeObject
     */
    public function findEvent($eventId)
    {
        return Event::retrieve($eventId);
    }

    /**
     * @param $stripeSubscriptionId
     * @return \AppBundle\Entity\Subscription|object|null
     * @throws Exception
     */
    private function findSubscription($stripeSubscriptionId)
    {
        $subscription = $this->getDoctrine()
            ->getRepository('AppBundle:Subscription')
            ->findOneBy([
                'stripeSubscriptionId' => $stripeSubscriptionId
            ]);
        if (!$subscription) {
            throw new Exception(
                'Somehow we have no subscription id ' . $stripeSubscriptionId
            );
        }
        return $subscription;
    }

}