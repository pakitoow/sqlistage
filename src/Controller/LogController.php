<?php

namespace App\Controller;

use App\Entity\LogItem;
use App\Entity\LogSet;
use App\Form\LogUploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    #[Route('/log', name: 'app_log')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LogUploadType::class);
        $form->handleRequest($request);

        $logs = [];

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $file */
            $file = $form->get('uploadFile')->getData();

            if ($file) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFilename = uniqid('', true) . '.' . $file->guessExtension();

                try {
                    $file->move($uploadDir, $newFilename);
                    $filePath = $uploadDir . '/' . $newFilename;

                    // Parse log file
                    $logs = $this->parseLogFile($filePath);

                    // Create LogSet entity
                    $logSet = new LogSet();
                    $logSet->setFilename($newFilename);

                    // Persist LogItem entities
                    foreach ($logs as $logData) {
                        $logItem = new LogItem();
                        $logItem->setLineNumber($logData['line_number']);

                        if ($logData['datetime'] instanceof \DateTimeInterface) {
                            $logItem->setTimestamp($logData['datetime']);
                        } else {
                            try {
                                $logItem->setTimestamp(new \DateTime($logData['datetime']));
                            } catch (\Exception $e) {
                                $logItem->setTimestamp(new \DateTime());
                            }
                        }

                        $logItem->setSeverity($logData['level']);  // Make sure your LogItem entity has setSeverity()
                        $logItem->setMessage($logData['message']);
                        $logItem->setRawLine($logData['raw_line']);
                        $logItem->setParsed($logData['parsed']);
                        $logItem->setLogSet($logSet);

                        $em->persist($logItem);
                        $logSet->addLogItem($logItem);
                    }

                    $em->persist($logSet);
                    $em->flush();

                    $this->addFlash('success', 'Fichier uploadé, parsé et sauvegardé avec succès.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Aucun fichier sélectionné.');
            }
        }

        return $this->render('log/index.html.twig', [
            'form' => $form->createView(),
            'logs' => $logs,
        ]);
    }

    private function parseLogFile(string $filePath): array
    {
        $logs = [];
        $lineNumber = 0;

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->addFlash('error', 'Impossible d\'ouvrir le fichier.');
            return $logs;
        }

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parsedLine = $this->parseLogLine($line, $lineNumber);
            if ($parsedLine) {
                $logs[] = $parsedLine;
            }
        }

        fclose($handle);
        return $logs;
    }

    private function parseLogLine(string $line, int $lineNumber): ?array
    {
        $patterns = [
            // Format: [timestamp] LEVEL message
            '/^\[(.+?)\]\s+(ERROR|WARNING|INFO|DEBUG|CRITICAL|NOTICE|ALERT|EMERGENCY)\s+(.+)$/i',

            // Format: [timestamp] [level] message
            '/^\[(.+?)\]\s*\[(.+?)\]\s*(.+)$/',

            // Format: timestamp LEVEL message (ISO format)
            '/^(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(ERROR|WARNING|INFO|DEBUG|CRITICAL|NOTICE|ALERT|EMERGENCY)\s+(.+)$/i',

            // Format: [timestamp] PHP Fatal error|Warning|Notice|Error: message
            '/^\[(.+?)\]\s*PHP\s+(Fatal\s+error|Warning|Notice|Error):\s*(.+)$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                $timestamp = trim($matches[1]);
                $level = strtoupper(trim($matches[2]));
                $message = trim($matches[3]);

                try {
                    $datetime = new \DateTime($timestamp);
                } catch (\Exception $e) {
                    $datetime = new \DateTime(); // fallback to now
                }

                return [
                    'line_number' => $lineNumber,
                    'datetime' => $datetime,
                    'level' => $this->normalizeLogLevel($level),
                    'message' => $message,
                    'raw_line' => $line,
                    'parsed' => true,
                ];
            }
        }

        // No pattern matched → unknown format
        return [
            'line_number' => $lineNumber,
            'datetime' => new \DateTime(), // fallback now
            'level' => 'UNKNOWN',
            'message' => $line,
            'raw_line' => $line,
            'parsed' => false,
        ];
    }

    private function normalizeLogLevel(string $level): string
    {
        $levelMap = [
            'EMERGENCY' => 'EMERGENCY',
            'ALERT' => 'ALERT',
            'CRITICAL' => 'CRITICAL',
            'ERROR' => 'ERROR',
            'WARNING' => 'WARNING',
            'NOTICE' => 'NOTICE',
            'INFO' => 'INFO',
            'DEBUG' => 'DEBUG',
            'FATAL ERROR' => 'CRITICAL',
            'FATAL' => 'CRITICAL',
            'WARN' => 'WARNING',
            'ERR' => 'ERROR',
        ];

        return $levelMap[$level] ?? strtoupper($level);
    }
}
