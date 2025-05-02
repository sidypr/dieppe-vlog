<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Process;

class VideoCompressor
{
    private string $tempDir;

    public function __construct(string $tempDir = '/tmp')
    {
        $this->tempDir = $tempDir;
    }

    public function compress(UploadedFile $file): string
    {
        $inputPath = $file->getPathname();
        $outputPath = $this->tempDir . '/' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Utilisation de FFmpeg pour compresser la vidéo
        $process = new Process([
            'ffmpeg',
            '-i', $inputPath,
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '23',
            '-c:a', 'aac',
            '-b:a', '128k',
            '-movflags', '+faststart',
            $outputPath
        ]);

        $process->setTimeout(3600); // 1 heure maximum
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('La compression de la vidéo a échoué : ' . $process->getErrorOutput());
        }

        return $outputPath;
    }

    public function cleanup(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
} 