<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class LanAdminController extends AbstractController
{
    #[Route('/admin/lan', name: 'app_admin_lan_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/lan/index.html.twig');
    }
}
