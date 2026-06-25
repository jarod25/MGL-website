<?php

namespace App\Controller\Admin;

use App\Entity\Subscription;
use App\Form\Admin\SubscriptionAdminType;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_ADMIN')]
final class SubscriptionAdminController extends AbstractController
{
    #[Route('/admin/lan/subscriptions', name: 'app_admin_lan_subscription_index', methods: ['GET'])]
    public function index(SubscriptionRepository $subscriptionRepository): Response
    {
        return $this->render('admin/lan/subscription/index.html.twig', [
            'subscriptions' => $subscriptionRepository->findBy([], ['durationDays' => 'ASC', 'price' => 'ASC', 'name' => 'ASC']),
        ]);
    }

    #[Route('/admin/lan/subscriptions/{id}/edit', name: 'app_admin_lan_subscription_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Subscription $subscription,
        Request $request,
        SubscriptionRepository $subscriptionRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response {
        $form = $this->createForm(SubscriptionAdminType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $tierId = $subscription->getHelloAssoTierId();
            if (is_string($tierId)) {
                $tierId = trim($tierId);
                $subscription->setHelloAssoTierId($tierId === '' ? null : $tierId);
            }

            if ($form->isValid()) {
                $tierId = $subscription->getHelloAssoTierId();
                if ($tierId !== null) {
                    $existingSubscription = $subscriptionRepository->findOneByHelloAssoTierId($tierId);
                    if ($existingSubscription instanceof Subscription && $existingSubscription->getId() !== $subscription->getId()) {
                        $form->get('helloAssoTierId')->addError(new FormError($translator->trans('admin.subscription.error.duplicate_tier_id')));
                    }
                }
            }

            if ($form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.subscription.flash.updated'));

                return $this->redirectToRoute('app_admin_lan_subscription_index');
            }
        }

        return $this->render('admin/lan/subscription/edit.html.twig', [
            'subscription' => $subscription,
            'form' => $form->createView(),
        ]);
    }
}
