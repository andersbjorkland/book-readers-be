<?php


namespace App\Service;


use Symfony\Contracts\HttpClient\HttpClientInterface;

class BookDataFetcher {

	private $client;

	public function __construct(HttpClientInterface $client) {
		$this->client = $client;
	}

	public function getBookData($volumeId)
	{
		$response = $this->client->request(
			'GET',
			'https://www.googleapis.com/books/v1/volumes/' . $volumeId
		);
		return $response->toArray();
	}

}