<?php


namespace App\Controller;

use App\Dto\DocumentsInputDto;
use App\Service\PrepareDocumentService;
use PhpOffice\PhpWord\Exception\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class DocumentsController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     * @throws \HttpException
     * @throws Exception
     */
    public function __invoke(Request $request, string $projectDir, LoggerInterface $logger): StreamedResponse
    {

        $serializer = new PropertyNormalizer();
        $dto = $serializer->denormalize(json_decode($request->getContent(), true), DocumentsInputDto::class);

        $logger->info("Template processing with params: {dto}", compact('dto'));

        $service = new PrepareDocumentService($logger);

        return $service->prepareResponse($projectDir, $dto);
    }
}
