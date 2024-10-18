<?php

namespace Test\AlphaSoft\Messenger\FilesystemTransport;



use AlphaSoft\Messenger\FilesystemTransport\FilesystemTransport;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

class FilesystemTransportTest extends TestCase
{
    private string $directory;
    private FilesystemTransport $transport;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/test_transport/';
        $this->transport = new FilesystemTransport($this->directory, false, new PhpSerializer());

    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->directory . '*'));
        rmdir($this->directory);
    }

    public function testConstructorCreatesDirectory(): void
    {
        $dir = sys_get_temp_dir() . '/new_transport/';
        new FilesystemTransport($dir);

        $this->assertDirectoryExists($dir);
        rmdir($dir);
    }

    public function testSendWritesMessageToFile(): void
    {
        $envelope = new Envelope(new \stdClass());
        $result = $this->transport->send($envelope);

        $files = glob($this->directory . '*.message');
        $this->assertCount(1, $files);

        $content = json_decode(file_get_contents($files[0]), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('body', $content);

        $this->assertInstanceOf(Envelope::class, $result);
        $this->assertNotNull($result->last(TransportMessageIdStamp::class));
    }

    public function testGetReturnsEnvelope(): void
    {
        $envelope = new Envelope(new \stdClass());
        $result = $this->transport->send($envelope);

        $messages = $this->transport->get();
        $this->assertCount(1, $messages);

        $receivedEnvelope = $messages[0];
        $this->assertInstanceOf(Envelope::class, $receivedEnvelope);
        $this->assertNotNull($receivedEnvelope->last(TransportMessageIdStamp::class));
        $this->assertNotNull($result->last(TransportMessageIdStamp::class));
    }

    public function testAckDeletesFile(): void
    {
        $envelope = new Envelope(new \stdClass());
        $result = $this->transport->send($envelope);

        $this->transport->ack($result);

        $this->assertFileDoesNotExist($this->directory . $result->last( TransportMessageIdStamp::class)->getId() . '.message');
    }

    public function testRejectDeletesFile(): void
    {
        $envelope = new Envelope(new \stdClass());
        $result = $this->transport->send($envelope);

        $this->transport->reject($result);

        $this->assertFileDoesNotExist($this->directory . $result->last( TransportMessageIdStamp::class)->getId() . '.message');
    }

    public function testSendThrowsExceptionIfFileNotWritable(): void
    {
        $envelope = new Envelope(new \stdClass());

        chmod($this->directory, 0444);

        $this->expectException(TransportException::class);
        $this->transport->send($envelope);

        chmod($this->directory, 0777);
    }

    public function testGetThrowsExceptionForInvalidJson(): void
    {
        $id = uniqid();
        file_put_contents($this->directory . $id . '.message', '{invalid json');

        $this->expectException(TransportException::class);
        $this->transport->get();


        unlink($this->directory . $id . '.message');
    }

    public function testGetThrowsExceptionForUnreadableFile(): void
    {
        $envelope = new Envelope(new \stdClass());
        $result = $this->transport->send($envelope);

        $filePath = $this->directory . $result->last( TransportMessageIdStamp::class)->getId() . '.message';


        chmod($filePath, 0000);

        $this->expectException(TransportException::class);
        $this->transport->get();



        chmod($filePath, 0644);
    }

    public function testLogProcessed()
    {
        $transport = new FilesystemTransport($this->directory, true, new PhpSerializer());

        $envelope = new Envelope(new \stdClass());
        $transport->send($envelope);
        $results = $transport->get();
        $transport->ack($results[0]);

        $logfile = $this->directory . sprintf('%s.%s.log', 'processed', date('Y-m-d'));
        $this->assertTrue( file_exists($logfile));
        $content = @file_get_contents($logfile);
        $this->assertIsArray(json_decode($content, true));
    }

    public function testLogFailure()
    {
        $transport = new FilesystemTransport($this->directory, true, new PhpSerializer());

        $envelope = new Envelope(new \stdClass());
        $transport->send($envelope);
        $results = $transport->get();
        $transport->reject($results[0]);

        $logfile = $this->directory . sprintf('%s.%s.log', 'failed', date('Y-m-d'));
        $this->assertTrue( file_exists($logfile));
        $content = @file_get_contents($logfile);
        $this->assertIsArray(json_decode($content, true));
    }
}

