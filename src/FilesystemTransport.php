<?php

namespace AlphaSoft\Messenger\FilesystemTransport;

use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class FilesystemTransport implements TransportInterface
{
    const MESSAGE_EXTENSION = '.message';
    private string $directory;

    private bool $log;
    private SerializerInterface $serializer;


    public function __construct(string $directory, bool $log = false, SerializerInterface $serializer = null)
    {
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        $this->directory = rtrim($directory, '/') . '/';
        $this->serializer = $serializer ?? new PhpSerializer();
        $this->log = $log;
    }

    public function send(Envelope $envelope): Envelope
    {

        $serializedMessage = $this->serializer->encode($envelope);

        $id = $this->generateUniqueId();
        $fileName = $this->generateFilenameById($id);

        $result = @file_put_contents($fileName, json_encode($serializedMessage), LOCK_EX);
        if ($result === false) {
            throw new TransportException(sprintf('Could not write message to file "%s"', $fileName));
        }

        return $envelope->with(new TransportMessageIdStamp($id));
    }

    public function get(): iterable
    {
        $files = glob($this->directory . '*'.self::MESSAGE_EXTENSION);
        usort($files, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        foreach ($files as $filename) {
            $content = @file_get_contents($filename);
            if ($content === false) {
                throw new TransportException(sprintf('Could not read message from file "%s"', $filename));
            }

            $data = @json_decode($content, true);
            if (null === $data || false === $data) {
                throw new TransportException(sprintf('Invalid JSON in file "%s"', $filename));
            }

            $envelope = $this->serializer->decode($data);
            $id = basename($filename, self::MESSAGE_EXTENSION);
            return [$envelope->with(new TransportMessageIdStamp($id))];
        }

        return [];
    }

    public function ack(Envelope $envelope): void
    {
        $filename = $this->getFileForEnvelope($envelope);
        if (file_exists($filename)) {
            unlink($filename);
            $this->logProcessed($envelope);
        }
    }

    public function reject(Envelope $envelope): void
    {
        $filename = $this->getFileForEnvelope($envelope);
        if ($filename && file_exists($filename)) {
            unlink($filename);
            $this->logFailed($envelope);
        }
    }
    private function getFileForEnvelope(Envelope $envelope): ?string
    {
        $stamp = $envelope->last(TransportMessageIdStamp::class);
        if (!$stamp instanceof TransportMessageIdStamp) {
            throw new \LogicException('No TransportMessageIdStamp found on the Envelope.');
        }

        $filename = $this->generateFilenameById($stamp->getId());
        if (!file_exists($filename)) {
            return null;
        }

        return $filename;
    }

    private function logProcessed(Envelope $envelope): void
    {
        if (!$this->log) {
            return;
        }

        $logFile = $this->directory . sprintf('%s.%s.log', 'processed', date('Y-m-d'));
        $stamp = $envelope->last(TransportMessageIdStamp::class);
        $logEntry = json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'message_id' => $stamp ? $stamp->getId() : 'unknown',
                'message_type' => get_class($envelope->getMessage()),
                'status' => 'processed'
            ]) . PHP_EOL;

        $result = @file_put_contents($logFile, $logEntry, FILE_APPEND);
        if ($result === false) {
            error_log( sprintf('ERROR : Could not write log entry to file "%s"', $logFile));
            error_log($logEntry);
        }

    }

    private function logFailed(Envelope $envelope): void
    {
        if (!$this->log) {
            return;
        }

        $logFile = $this->directory . sprintf('%s.%s.log', 'failed', date('Y-m-d'));
        $stamp = $envelope->last(TransportMessageIdStamp::class);
        $logEntry = json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'message_id' => $stamp ? $stamp->getId() : 'unknown',
                'message_type' => get_class($envelope->getMessage()),
                'status' => 'failed'
            ]) . PHP_EOL;

        $result = @file_put_contents($logFile, $logEntry, FILE_APPEND);
        if ($result === false) {
            error_log( sprintf('ERROR : Could not write log entry to file "%s"', $logFile));
            error_log($logEntry);
        }
    }

    private function generateUniqueId(): string
    {
        do {
            $id = uniqid(date("Ymd_His_") . gettimeofday()["usec"]);
            $fileName = $this->generateFilenameById($id);
        } while (file_exists($fileName));

        return $id;
    }

    private function generateFilenameById(string $id): string
    {
        return sprintf("%s%s%s", $this->directory, $id, self::MESSAGE_EXTENSION);
    }
}
