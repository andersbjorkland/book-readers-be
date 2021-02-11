<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\CurrentRead;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\CurrentReadRepository;
use App\Repository\FlairRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\BookCreator;
use App\Service\BookDataFetcher;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DefaultController extends AbstractController
{
	/**
	 * @Route("/user", name="default")
	 */
	public function index(Request $request, HttpClientInterface $client, LoggerInterface $logger): Response
	{
		$user = $this->getUser();
		$logger->info($this->getUser());
		if ($user) {
			$toRead = $user->getToRead();
			$toReadArr = [];

			for($i = 0; $i < count($toRead); $i++) {
				$toReadArr[] = $toRead[$i]->getData();
			}

			$currentRead = $user->getCurrentRead();
			$currentReadArr = [];

			for($i = 0; $i < count($currentRead); $i++) {
				$currentReadArr[] = $currentRead[$i]->getBook()->getData();
			}

			$reviews = $user->getReviews();
			$reviewArr = [];
			for($i = 0; $i < count($reviews); $i++) {
				$reviewArr[] = $reviews[$i];
			}

			return new JsonResponse([
				"message" => "Successful",
				"toRead" => $toReadArr,
				"currentRead" => $currentReadArr,
				"reviews" => $reviewArr
			]);
		}
		$logger->info($request);

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
	 * @Route("/user/current-read", name="addCurrentRead", methods={"POST"})
	 */
	public function addCurrentRead(
		Request $request,
		BookCreator $bookCreator,
		CurrentReadRepository $currentReadRepository,
		LoggerInterface  $logger) : Response
	{
		$user = $this->getUser();
		$logger->info($request);
		$data = json_decode($request->getContent(), true);
		$id = $data["volumeId"];

		if(!$id) {
			return new JsonResponse(["message" => "Key volumeId not present"], Response::HTTP_NOT_FOUND);
		}

		$logger->info("Adding to-read with volumeId: " . $id);



		$entityManager = $this->getDoctrine()->getManager();
		$book = $bookCreator->getBook($id);

		if (!$book->getCurrentRead()) {
			$currentRead = $currentReadRepository->findOneByBook($book);
			if (!$currentRead) {
				$currentRead = new CurrentRead();

				$currentRead->setBook( $book );
				$entityManager->persist($currentRead);

				$book->setCurrentRead($currentRead);
				$entityManager->persist($book);
			}
		} else {
			$currentRead = $book->getCurrentRead();
		}

		$user->removeToRead($book);
		$user->addCurrentRead($currentRead);
		$entityManager->persist($user);
		$entityManager->flush();

		return new JsonResponse(["message" => "Added book to user's current read"], Response::HTTP_CREATED);
	}

	/**
	 * @Route("/user/current-read", name="removeCurrentRead", methods={"DELETE"})
	 */
	public function removeCurrentRead(Request $request, HttpClientInterface $client, BookRepository $bookRepository) : Response
	{
		$user = $this->getUser();
		$data = json_decode($request->getContent(), true);
		$id = $data["volumeId"];
		if (!$id) {
			return new JsonResponse(["message" => "Expected a value for key 'volumeId'."], Response::HTTP_FAILED_DEPENDENCY);
		}

		$book = $bookRepository->findOneByVolumeId($id);
		$currentRead = $book->getCurrentRead();
		if (!$book) {
			return new JsonResponse(["message" => "No book found"], Response::HTTP_FAILED_DEPENDENCY);
		}
		if (!$currentRead) {
			return new JsonResponse(["message" => "No book currently as current read."], Response::HTTP_FAILED_DEPENDENCY);
		}

		$user->removeCurrentRead($currentRead);
		$entityManager = $this->getDoctrine()->getManager();
		$entityManager->persist($user);
		$entityManager->flush();

		return new JsonResponse(["message" => "Current read was removed."], Response::HTTP_ACCEPTED);
	}

	/**
	 * @Route("/user/to-read", name="addToRead", methods={"POST"})
	 */
    public function addToRead(
    	Request $request,
	    HttpClientInterface $client,
	    BookCreator $bookCreator,
	    BookRepository $bookRepository,
	    LoggerInterface  $logger
    ) : Response
    {
    	$user = $this->getUser();
	    $logger->info($request);
	    $data = json_decode($request->getContent(), true);
	    $id = $data["volumeId"];
	    $logger->info("Adding to-read with volumeId: " . $id);

	    $entityManager = $this->getDoctrine()->getManager();
	    $book = $bookCreator->getBook($id);

	    $user->addToRead($book);
	    if ($book->getCurrentRead()) {
		    $user->removeCurrentRead($book->getCurrentRead());
	    }
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

	protected function getCurrentRead(HttpClientInterface $client)
	{
		$user = $this->getUser();
		if ($user) {
			$books = [];
			$currentRead = $user->getCurrentRead();
			for ($i = 0; $i < count($currentRead); $i++) {
				$response = $client->request(
					'GET',
					'https://www.googleapis.com/books/v1/volumes/' . $currentRead[$i]->getBook()->getVolumeId()
				);
				$books[] = $response->toArray();
			}
			return $books;
		}
	}

	/**
	 * @Route("/user/review", name="getReview", methods={"GET"})
	 */
	public function getReview(Request $request) : Response
	{
		$user = $this->getUser();
		$reviews = $user->getReviews();
		$message = "";
		if (count($reviews) === 0) {
			$message = "No reviews";
		}
		return new JsonResponse(["message" => $message, "reviews" => json_encode($reviews)]);
	}

	/**
	 * @Route("/user/review", name="addReview", methods={"POST"})
	 */
	public function postReview(
		Request $request,
		LoggerInterface  $logger,
		BookRepository $bookRepository,
		ReviewRepository $reviewRepository,
		FlairRepository $flairRepository) : Response
	{
    	$user = $this->getUser();
		$data = json_decode($request->getContent(), true);
		$volumeId = $data["volumeId"];
		$review = $data["review"];

		$book = $bookRepository->findOneByVolumeId($volumeId);
		$reviewPersisted = $reviewRepository->findOneBy([
			"user" => $user,
			"book" => $book
		]);
		if (!$book) {
			return new JsonResponse(["message" => "No book found"], Response::HTTP_FAILED_DEPENDENCY);
		}

		$logger->info("##############################################");
		$logger->info("Volume: $volumeId");

		$score = $review["score"];
		$impressions = $review["impressions"];
		$shortReview = $review["shortReview"];
		$longReview = $review["longReview"];
		$recommend = $review["recommend"];
		$isDraft = $review["isDraft"];

		if ($reviewPersisted) {
			$review = $reviewPersisted;
		} else {
			$review = new Review();
		}

		$logger->info("Searching for flairs: ");
		$flairs = [];

		for ($i = 0; $i < count($impressions); $i++) {
			$flair = $flairRepository->findOneByFa($impressions[$i]);
			if ($flair) {
				$logger->info($flair->getName());
				$review->addFlair($flair);
			} else {
				$logger->info("Not found with: " . $impressions[$i]);
			}
		}

		if (strlen($shortReview) >= 255) {
			$shortReview = substr($shortReview, 0, 255);
		}

		$review->setBook($book);
		$review->setScore($score);
		$review->setText($longReview);
		$review->setSummary($shortReview);
		$review->setIsDraft($isDraft);
		$review->setWouldRecommend($recommend);
		$review->setUser($user);

		$em = $this->getDoctrine()->getManager();
		if ($reviewPersisted) {
			$em->persist($review);
		} else {
			$user->addReview($review);
			$em->persist($user);
		}

		$em->flush();

		return new JsonResponse(["message" => "Added review", "flairs" => $flairs], Response::HTTP_CREATED);
	}

	/**
	 * @Route("/user/review", name="removeReview", methods={"DELETE"})
	 */
	public function removeReview(
		Request $request,
		HttpClientInterface $client,
		BookRepository $bookRepository,
		ReviewRepository $reviewRepository) : Response
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

		$review = $reviewRepository->findOneBy([
			'book' => $book,
			'user' => $user
		]);
		if (!$review) {
			return new JsonResponse(["message" => "No review found to be removed"], Response::HTTP_FAILED_DEPENDENCY);
		}

		$user->removeReview($review);
		$entityManager = $this->getDoctrine()->getManager();
		$entityManager->persist($user);
		$entityManager->flush();

		return new JsonResponse(["message" => "Review was removed."], Response::HTTP_ACCEPTED);
	}

	/**
	 * @Route("/user/update-password", name="updatePassword", methods={"POST"})
	 */
	public function updatePassword(
		Request $request,
		UserPasswordEncoderInterface $userPasswordEncoder
	) : Response
	{
		$user = $this->getUser();
		$data = json_decode($request->getContent(), true);
		$password = $data["password"];
		$user->setPassword($userPasswordEncoder->encodePassword($user, $password));

		$entityManager = $this->getDoctrine()->getManager();

		$entityManager->persist($user);
		$entityManager->flush();

		return new JsonResponse(["message" => "Updated password"], Response::HTTP_CREATED);
	}
}
