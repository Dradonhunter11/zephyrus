<?php namespace Zephyrus\Tests\Application;

use PHPUnit\Framework\TestCase;
use Zephyrus\Application\Configuration;

class ConfigurationTest extends TestCase
{
    public function testReadAllConfigurations()
    {
        Configuration::set(['database' => ['host' => 'localhost'], 'security' => ['encryption_algorithm' => 'aes-256-cbc']]);
        $config = Configuration::getConfigurations();
        self::assertEquals('aes-256-cbc', $config['security']['encryption_algorithm']);
        self::assertEquals('localhost', $config['database']['host']);
        Configuration::set(null);
        $config = Configuration::getConfigurations();
        self::assertEquals('zephyrus_database', $config['database']['hostname']);
        Configuration::set(null);
    }

    public function testFile()
    {
        Configuration::set(null);
        self::assertNull(Configuration::getFile());
        Configuration::getConfiguration('database');
        self::assertEquals('zephyrus_database', Configuration::getFile()->read('database', 'hostname'));
    }

    public function testReadSingleConfiguration()
    {
        Configuration::set(['application' => ['env' => 'dev']]);
        $config = Configuration::getConfiguration('application', 'env');
        self::assertEquals('dev', $config);
        Configuration::set(null);
    }

    public function testReadLocaleConfiguration()
    {
        Configuration::set(['locale' => ['currency' => 'CAD', 'charset' => 'utf8']]);
        $config = Configuration::getLocaleConfiguration();
        $precise = Configuration::getLocaleConfiguration('currency');
        self::assertEquals("CAD", $precise);
        self::assertEquals('utf8', $config['charset']);
        Configuration::set(null);
    }

    public function testReadDatabaseConfiguration()
    {
        Configuration::set(['database' => ['host' => 'localhost', 'charset' => 'utf8']]);
        $config = Configuration::getDatabaseConfiguration();
        $precise = Configuration::getDatabaseConfiguration('host');
        self::assertEquals('localhost', $precise);
        self::assertEquals('utf8', $config['charset']);
        Configuration::set(null);
    }

    public function testReadSessionConfiguration()
    {
        Configuration::set(['session' => ['encryption_enabled' => true, 'refresh_after_interval' => 60]]);
        $config = Configuration::getSessionConfiguration();
        $precise = (bool) Configuration::getSessionConfiguration('encryption_enabled');
        self::assertTrue($precise);
        self::assertEquals('60', $config['refresh_after_interval']);
        Configuration::set(null);
    }

    public function testInvalidConfigurationFile()
    {
        rename(ROOT_DIR . '/config.ini', ROOT_DIR . '/config.ini_test');
        $catch = false;
        try {
            Configuration::set(null);
            Configuration::getConfiguration('session');
        } catch (\RuntimeException $e) {
            $catch = true;
        } finally {
            rename(ROOT_DIR . '/config.ini_test', ROOT_DIR . '/config.ini');
        }
        self::assertTrue($catch);
    }

    public function testInvalidSection()
    {
        $result = Configuration::getConfiguration('invalid');
        self::assertNull($result);
    }

    public function testInvalidSectionField()
    {
        $result = Configuration::getApplicationConfiguration('invalid');
        self::assertNull($result);
    }
}