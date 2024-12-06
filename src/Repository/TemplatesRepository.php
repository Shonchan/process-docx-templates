<?php

namespace App\Repository;

use App\ApiResource\Templates;
use App\Dto\DocumentsInputDto;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TemplatesRepository
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function getTemplates(string $projectDir): Templates
    {
        $finder = new Finder();

        $finder->in($projectDir."/templates/")->name("/\.docx/")->files();

        $templates = [];
        foreach ($finder as $file) {
            $templates[] = $file->getFilenameWithoutExtension();
        }

        return new Templates($templates);
    }

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function processTemplate(string $projectDir, DocumentsInputDto $dto): string
    {
        $template = $projectDir."/templates/".$dto->template.".docx";

        if (!file_exists($template)) {
            $this->logger->error("Файл шаблона не найден");
            throw new NotFoundHttpException("Файл шаблона не найден");
        }

        $templateProcessor = new TemplateProcessor($template);

        foreach ($dto->data as $key => $value) {
            if (is_string($value)) {
                $templateProcessor->setValue($key, $value);
            }
            if (is_array($value)) {
                $replacements = [];
                $search_key = null;
                foreach ($value as $item) {
                    $it = [];
                    foreach ($item as $k => $v) {
                        if ($search_key === null) {
                            $search_key = $k;
                        }
                        $it[$k] = $v;
                    }
                    $replacements[] = $it;
                }

                $templateProcessor->cloneBlock($key, 0, true, false, $replacements);

                $templateProcessor->cloneRowAndSetValues($search_key, $replacements);
            }
        }

        $save_path = $projectDir."/var/".$dto->template."_processed.docx";
        $templateProcessor->saveAs($save_path);

        return $save_path;
    }
}
