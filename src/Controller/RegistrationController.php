<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository, LoggerInterface $logger): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return new JsonResponse(["message" => "No user found"], 400);
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return new JsonResponse(["message" => "No user found"], 402);
        }

        $uri = $request->getUri();

	    $logger->info("¤¤¤¤¤¤¤¤¤¤¤¤¤¤¤   Verification request. URI   ¤¤¤¤¤¤¤¤¤¤¤¤¤¤");
	    $logger->info($uri);
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($uri, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            return new JsonResponse(["message" => "An exception occured", "exception" => $exception->getReason()], 403);
        }

        return new JsonResponse(["message" => "Your email address has been verified."], 200);
    }
}
