<?php

namespace AlphaSoft\Messenger\FilesystemTransport;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class FilesystemTransportFactory
{
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        if (!isset($options["directory"])) {
            throw new \LogicException('The "directory" option must be set');
        }

        if (!is_string($options["directory"])) {
            throw new \LogicException('The "directory" option must be a string');
        }

        if (!array_key_exists('log', $options) || !is_bool($options['log'])) {
            $options['log'] = false;
        }

        return new FilesystemTransport($options["directory"], $options['log'], $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'filesystem://');
    }
}
