<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicController extends AbstractController
{
    #[Route('/privacy-policy', name: 'privacy_policy', methods: ['GET'])]
    public function PrivacyPolicy(): Response
    {
        return $this->render('public/privacy_policy.html.twig', [
            'active' => 'none',
        ]);
    }
}
