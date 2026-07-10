<?php

namespace App\Controller;

use App\Service\Document\LocalizedDocumentProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

final class DocumentController extends AbstractController
{
    #[Route('/documents/{type}', name: 'app_document_view', requirements: ['type' => 'rules|parental_authorization|image_rights'], methods: ['GET'])]
    public function view(string $type, Request $request, LocalizedDocumentProvider $documentProvider): Response
    {
        $document = $documentProvider->getDocument($type, $request->getLocale());

        if (null === $document) {
            throw $this->createNotFoundException('Document not available.');
        }

        $response = new BinaryFileResponse($document->absolutePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $document->filename);

        return $response;
    }
}
