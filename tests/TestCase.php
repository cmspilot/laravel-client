<?php

namespace OnePilot\Client\Tests;

use OnePilot\Client\Classes\FakePackageDetector;
use OnePilot\Client\ClientServiceProvider;
use OnePilot\Client\Contracts\PackageDetector;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $privateKey = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected $timestamp;
    protected $hash;

    public function setUp(): void
    {
        parent::setUp();

        config(['onepilot.private_key' => $this->privateKey]);

        $this->setTimestamp();

        $this->app->bind(PackageDetector::class, FakePackageDetector::class);

        FakePackageDetector::setPackagesFromPath(__DIR__ . '/data/composer/installed-packages-light.json');
        FakePackageDetector::generatePackagesConstraints();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ClientServiceProvider::class,
        ];
    }

    /**
     * Set timestamp and regenerate hash
     *
     * @param null $timestamp
     */
    protected function setTimestamp($timestamp = null)
    {
        $this->timestamp = $timestamp ?? time();

        $this->hash = $this->generateAuthenticationHash($this->privateKey, $this->timestamp);
    }

    /**
     * @param string $privateKey
     * @param int    $timestamp
     *
     * @return string Hash
     */
    protected function generateAuthenticationHash($privateKey, $timestamp)
    {
        return hash_hmac('sha256', $timestamp, $privateKey);
    }

    protected function authenticationHeaders()
    {
        return [
            'hash'  => $this->hash,
            'stamp' => $this->timestamp,
        ];
    }
}
