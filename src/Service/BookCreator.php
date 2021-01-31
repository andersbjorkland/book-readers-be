<?php


namespace App\Service;


use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookCreator {

	private $bookDataFetcher;
	private $bookRepository;
	private $entityManager;

	public function __construct(
		BookDataFetcher $bookDataFetcher,
		BookRepository $bookRepository,
		EntityManagerInterface $entityManager) {

		$this->bookDataFetcher = $bookDataFetcher;
		$this->bookRepository = $bookRepository;
		$this->entityManager = $entityManager;

	}

	public function getBook($id) : Book
	{
		$book = $this->bookRepository->findOneByVolumeId($id);
		if (!$book) {
			$book = new Book();
			$book->setVolumeId($id);
			$bookData = $this->bookDataFetcher->getBookData($id);
			$book->setData($bookData);

			$this->entityManager->persist($book);
			$this->entityManager->flush();

		}

		return $book;
	}

}