<?php

namespace App\Service;

use App\Dto\DocumentsInputDto;
use App\Repository\TemplatesRepository;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class PrepareDocumentService
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @throws \HttpException
     * @throws Exception
     */
    public function prepareResponse(string $projectDir, DocumentsInputDto $dto): StreamedResponse
    {
        $file_path = null;
        try {
            $file_path = (new TemplatesRepository($this->logger))->processTemplate($projectDir, $dto);
        } catch (CopyFileException|CreateTemporaryFileException $e) {
            throw new \HttpException($e->getMessage(), $e->getCode());
        }

        if ($file_path) {
            return $this->getResponse($file_path, $projectDir, $dto);
        }

        $this->logger->error("Не удалось обработать шаблон");
        throw new NotFoundHttpException("Не удалось обработать шаблон");
    }

    /**
     * @throws Exception
     */
    private function getResponse(string $file_path, string $projectDir, DocumentsInputDto $dto): StreamedResponse
    {
        $format = "Word2007";
        $header_format = "application/vnd.ms-word";
        $filename = 'test.docx';

        $objReader = IOFactory::createReader($format);
        $phpWord = $objReader->load($file_path);

        $response = new StreamedResponse();
        if ($dto->format === 'pdf') {
            $rendererName = Settings::PDF_RENDERER_MPDF;
            $rendererLibraryPath = realpath($projectDir.'/vendor/mpdf/mpdf');
            Settings::setPdfRenderer($rendererName, $rendererLibraryPath);
            $format = "PDF";
            $header_format = "application/pdf";
            $filename = 'test.pdf';
        }


            $objWriter = IOFactory::createWriter($phpWord, $format);

            $response->setCallback(function () use ($objWriter) {
                $objWriter->save('php://output');
            });
            $response->headers->set('Content-Type', $header_format);
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $filename
            );

        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
