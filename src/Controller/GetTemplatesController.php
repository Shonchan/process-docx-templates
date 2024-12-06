<?php


namespace App\Controller;

use App\Repository\TemplatesRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetTemplatesController extends AbstractController
{
    public function __invoke(LoggerInterface $logger, Request $request, string $projectDir): JsonResponse
    {
        $content = (new TemplatesRepository())->getTemplates($projectDir);
        $logger->info("Api.templates response: {content}", compact('content'));

        return new JsonResponse($content, Response::HTTP_OK);
    }
}
