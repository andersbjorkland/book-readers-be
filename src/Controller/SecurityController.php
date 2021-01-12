<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request, LoggerInterface $logger)
    {
        $user = $this->getUser();
        $logger->info("################################################################");
        $logger->info($request->getContent());
        $logger->info("################################################################");


        $response = new JsonResponse(['username' => $user->getUsername(), 'roles' => $user->getRoles(), 'message' => 'Success']);

        return $response;
    }
}