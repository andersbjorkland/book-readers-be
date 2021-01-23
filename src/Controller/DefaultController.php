<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DefaultController extends AbstractController
{
	/**
	 * @Route("/user", name="default")
	 */
	public function index(Request $request, HttpClientInterface $client, LoggerInterface $logger): Response
	{
		$user = $this->getUser();
		if ($user) {
			$books = $this->getToRead($client);
			return new JsonResponse(["message" => "Successful", "toRead" => $books]);
		}
		$logger->info($request);
		$logger->info($this->getUser());

		return new JsonResponse(["message" => "JIPPI!!!"]);
	}

    /**
     * @Route("/user/auth", name="checkToken")
     */
    public function checkToken(Request $request, HttpClientInterface $client, LoggerInterface $logger): Response
    {
    	$user = $this->getUser();
    	if ($user) {
    		// Update authentication credentials
		    $payload = [
			    "user" => $user->getUsername(),
			    "exp"  => (new \DateTime())->modify("+1 day")->getTimestamp(),
		    ];

		    $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
		    $token = sprintf('Bearer %s', $jwt);

		    return new JsonResponse([
		    	"message" => "Successful",
			    'token' => $token,
			    'user' => $user->getUsername()
		    ], 200);
	    }

        return new JsonResponse(["message" => "Something went wrong. Could not resolve user from token."], Response::HTTP_NOT_ACCEPTABLE);
    }

	/**
	 * @Route("/user/unregister", name="unregister")
	 */
	public function unregister(Request $request, LoggerInterface $logger): Response
	{
		$user = $this->getUser();
		if ($user) {
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->remove($user);
			$entityManager->flush();

			return new JsonResponse([
				"message" => "Unregistered Successfully",
			], 204);
		}

		return new JsonResponse(["message" => "Something went wrong. Could not resolve user from token."], 404);
	}

	/**
	 * @Route("/user/to-read", name="addToRead", methods={"POST"})
	 */
    public function addToRead(Request $request, HttpClientInterface $client, BookRepository $bookRepository, LoggerInterface  $logger) : Response
    {
    	$user = $this->getUser();
	    $logger->info($request);
	    $data = json_decode($request->getContent(), true);
	    $id = $data["volumeId"];
	    $logger->info("Adding to-read with volumeId: " . $id);

	    $entityManager = $this->getDoctrine()->getManager();
	    $book = $bookRepository->findOneByVolumeId($id);
	    if (!$book) {
	    	$book = new Book();
	    	$book->setVolumeId($id);

	    	$entityManager->persist($book);
	    }

	    $user->addToRead($book);
	    $entityManager->persist($user);
	    $entityManager->flush();

	    return new JsonResponse(["message" => "Added book to user's to-read list"], Response::HTTP_CREATED);
    }

	/**
	 * @Route("/user/to-read", name="removeToRead", methods={"DELETE"})
	 */
    public function removeToRead(Request $request, HttpClientInterface $client, BookRepository $bookRepository) : Response
    {
    	$user = $this->getUser();
	    $data = json_decode($request->getContent(), true);
	    $id = $data["volumeId"];
	    if (!$id) {
		    return new JsonResponse(["message" => "Expected a value for key 'volumeId'."], Response::HTTP_FAILED_DEPENDENCY);
	    }

	    $book = $bookRepository->findOneByVolumeId($id);
	    if (!$book) {
		    return new JsonResponse(["message" => "No book found"], Response::HTTP_FAILED_DEPENDENCY);
	    }

	    $user->removeToRead($book);
	    $entityManager = $this->getDoctrine()->getManager();
	    $entityManager->persist($user);
	    $entityManager->flush();

	    return new JsonResponse(["message" => "Book was removed."], Response::HTTP_ACCEPTED);
    }

    protected function getToRead(HttpClientInterface $client)
    {
	    $user = $this->getUser();
	    if ($user) {
		    $books = [];
		    $toRead = $user->getToRead();
		    for ($i = 0; $i < count($toRead); $i++) {
			    $response = $client->request(
				    'GET',
				    'https://www.googleapis.com/books/v1/volumes/' . $toRead[$i]->getVolumeId()
			    );
			    $books[] = $response->toArray();
		    }
		    return $books;
	    }
    }
}
