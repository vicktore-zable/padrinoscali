<?php

use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private string $logDir;

    protected function setUp(): void
    {
        $this->logDir = sys_get_temp_dir() . '/aratio_test_logs';
        if (!is_dir($this->logDir)) mkdir($this->logDir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->logDir . '/aratio-*.log') as $f) unlink($f);
        rmdir($this->logDir);
    }

    public function testLogCreatesFile(): void
    {
        $logger = new Logger($this->logDir, Logger::DEBUG);
        $logger->info('Test message');

        $files = glob($this->logDir . '/aratio-*.log');
        $this->assertCount(1, $files);
        $content = file_get_contents($files[0]);
        $this->assertStringContainsString('INFO', $content);
        $this->assertStringContainsString('Test message', $content);
    }

    public function testLogLevels(): void
    {
        $logger = new Logger($this->logDir, Logger::WARNING);
        $logger->debug('Should not appear');
        $logger->warning('Should appear');

        $content = file_get_contents(glob($this->logDir . '/aratio-*.log')[0]);
        $this->assertStringNotContainsString('Should not appear', $content);
        $this->assertStringContainsString('Should appear', $content);
    }

    public function testChannel(): void
    {
        $logger = new Logger($this->logDir, Logger::DEBUG);
        $logger->channel('test')->info('Channel message');

        $content = file_get_contents(glob($this->logDir . '/aratio-*.log')[0]);
        $this->assertStringContainsString('[test]', $content);
    }

    public function testSingleton(): void
    {
        $a = Logger::getInstance();
        $b = Logger::getInstance();
        $this->assertSame($a, $b);
    }

    public function testContextInLog(): void
    {
        $logger = new Logger($this->logDir, Logger::DEBUG);
        $logger->info('With context', ['user_id' => 42, 'action' => 'test']);

        $content = file_get_contents(glob($this->logDir . '/aratio-*.log')[0]);
        $this->assertStringContainsString('"user_id"', $content);
    }
}
