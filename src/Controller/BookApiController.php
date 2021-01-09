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
     * @Route("/api/details/{id}", name="book_details")
     */
    public function details(string $id, HttpClientInterface $client): Response
    {
        $apiToken = $this->getParameter('app.api_token');
        $apiKeyValue = $apiToken ? "&key=" . $apiToken : "";

        $response = $client->request(
            'GET',
            'https://www.googleapis.com/books/v1/volumes/' . $id . $apiKeyValue
        );
        return $this->json($response->toArray());
    }

    /**
     * @Route("/api/{query}", name="book_api")
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
