<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;

class MessageController extends AbstractController
{

    #[Route('/api/messages/parent/{parentId}', name: 'messages_by_parent', methods: ['GET'], requirements: ['parentId' => '\d+'])]
    public function getMessagesByParent(
        int $parentId,
        MessageRepository $messageRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        // Récupérer les messages ayant ce `parent_id`, triés du plus ancien au plus récent
        $messages = $messageRepository->findBy(
            ['parent' => $parentId],
            ['datePoste' => 'ASC'] // Tri ASC pour du plus ancien au plus récent
        );

        // Sérialisation des données
        $jsonData = $serializer->serialize($messages, 'json', ['groups' => ['message:list']]);

        return new JsonResponse($jsonData, JsonResponse::HTTP_OK, [], true);
    }
}
