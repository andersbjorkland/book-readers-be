<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BookApiController extends AbstractController
{
    /**
     * @Route("/api/{query}", name="book_api")
     */
    public function index(string $query, HttpClientInterface $client): Response
    {
        $response = $client->request(
            'GET',
            'https://www.googleapis.com/books/v1/volumes?q=' . $query
        );
        return $this->json($response->toArray());
    }
}
