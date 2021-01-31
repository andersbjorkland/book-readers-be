<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BookApiController extends AbstractController
{
    /**
     * @Route("/book-api/details/{id}", name="book_details")
     */
    public function details(string $id, HttpClientInterface $client, BookRepository $bookRepository): Response
    {
    	$book = $bookRepository->findOneByVolumeId($id);
    	if (!$book) {
		    $response = $client->request(
			    'GET',
			    'https://www.googleapis.com/books/v1/volumes/' . $id
		    );

		    return $this->json( $response->toArray() );
	    }

    	return $this->json($book->getData());
    }

    /**
     * @Route("/book-api/images/{path}")
     */
    public function images(string $path, HttpClientInterface $client): Response
    {
        $target = "https://books.google.com/books/content?id=" . $path;

        $contents = file_get_contents($target);

        return new Response($contents, 200, ['Content-type'=>'image/jpeg']);
    }

    /**
     * @Route("/book-api/{query}", name="book_api")
     */
    public function index(string $query, HttpClientInterface $client): Response
    {
        $apiToken = $this->getParameter('app.api_token');
        $apiKeyValue = $apiToken ? "&key=" . $apiToken : "";

        $response = $client->request(
            'GET',
            'https://www.googleapis.com/books/v1/volumes?q=' . $query . $apiKeyValue
        );
        return $this->json($response->toArray());
    }
}
