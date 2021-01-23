<?php


namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtAuthenticator extends AbstractGuardAuthenticator {
	private $em;
	private $params;
	private $logger;
	private $headerKey;

	public function __construct(EntityManagerInterface $em, ContainerBagInterface $params, LoggerInterface $logger)
	{
		$this->em = $em;
		$this->params = $params;
		$this->logger = $logger;
	}

	public function start( Request $request, AuthenticationException $authException = null ) {
		$data = [
			'message' => 'Authentication Required'
		];
		$this->logger->debug("Authenticating not accessed. " . $request->getSchemeAndHttpHost());
		return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
	}

	public function supports( Request $request ) {
		$supports = false;
		if ($request->headers->has('Authorization')) {
			$this->headerKey = 'Authorization';
			$supports = true;
		}

		if ($request->headers->has('authorization')) {
			$this->headerKey = 'authorization';
			$supports = true;
		}

		return $supports;
	}

	public function getCredentials( Request $request ) {
		$authorizationHeader = $request->headers->get($this->headerKey);
		$this->logger->error("Checking credentials: ");
		$this->logger->error($authorizationHeader);

		return $authorizationHeader;
	}

	public function getUser( $credentials, UserProviderInterface $userProvider ) {
		try {
			$credentials = str_replace('Bearer ', '', $credentials);
			$jwt = (array) JWT::decode(
				$credentials,
				$this->params->get('jwt_secret'),
				['HS256']
			);
			return $this->em->getRepository(User::class)
			                ->findOneBy([
				                'email' => $jwt['user'],
			                ]);
		}catch (\Exception $exception) {
			throw new AuthenticationException($exception->getMessage());
		}
	}

	public function checkCredentials( $credentials, UserInterface $user ) {
		return null !== $user;
	}

	public function onAuthenticationFailure( Request $request, AuthenticationException $exception ) {
		$this->logger->error("Authentication failed. " . $exception->getMessage());

		return new JsonResponse([
			'message' => $exception->getMessage()
		], Response::HTTP_UNAUTHORIZED);
	}

	public function onAuthenticationSuccess( Request $request, TokenInterface $token, string $providerKey ) {
		return ;
	}

	public function supportsRememberMe() {
		return false;
	}
}