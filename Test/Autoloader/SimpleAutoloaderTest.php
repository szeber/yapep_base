<?php

namespace YapepBase\Autoloader;

/**
 * Test class for SimpleAutoloader.
 * Generated by PHPUnit on 2011-12-12 at 10:08:43.
 */
class SimpleAutoloaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var SimpleAutoloader
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new SimpleAutoloader;
    }

    public function testLoad() {
        $this->object->addClassPath(BASE_DIR);
        $this->assertTrue($this->object->load('\\YapepBase\\Test\\Mock\\Autoloader\\ClassMock'));
        $this->assertTrue($this->object->load('\\YapepBase\\Test\\Mock\\Autoloader\\InterfaceMock'));
        $this->assertFalse($this->object->load('\\YapepBase\\Test\\Mock\\Autoloader\\NonexistentClassMock'));
        $this->assertFalse($this->object->load('\\YapepBase\\Test\\Mock\\Autoloader\\EmptyMock'));
    }

    protected function loadDirectory($path, $root) {
        if (($dh = opendir($path)) !== false) {
            while (($entry = readdir($dh)) !== false) {
                if (!preg_match('/(^[a-z]+\.php$|^Test$|Test\.php$|^\.$|^\.\.$)/', $entry)) {
                    $newpath = $path . '/' . $entry;
                    if (is_dir($newpath)) {
                        $this->loadDirectory($newpath, $root);
                    } else if (is_file($newpath) && preg_match('/\.php/', $newpath)) {
                        $name = trim(str_replace('.php', '', str_replace($root, '', $newpath)), DIRECTORY_SEPARATOR);
                        $namespacedclass = strtr($name, DIRECTORY_SEPARATOR, '\\');
                        $legacyclass = strtr($name, DIRECTORY_SEPARATOR, '_');
                        try {
                            $namespacedres = $this->object->load($namespacedclass);
                        } catch (\ErrorException $e) {
                            $this->assertEquals(\E_USER_WARNING, $e->getSeverity());
                            $namespacedres = false;
                        }
                        try {
                            $legacyres = $this->object->load($legacyclass);
                        } catch (\ErrorException $e) {
                            $this->assertEquals(\E_USER_WARNING, $e->getSeverity());
                            $legacyres = false;
                        }
                        $this->assertTrue($namespacedres || $legacyres, 'Neither ' . $namespacedclass . ' nor '
                            . $namespacedclass . ' were found in ' . $newpath);
                    }
                }
            }
        }
    }

    /**
     * This test loads everything so the code coverage is accurate.
     * @depends testLoad
     */
    public function testLoadEverything() {
        $classroot = BASE_DIR . DIRECTORY_SEPARATOR . 'YapepBase';
        $this->object->setClassPath(array(BASE_DIR));
        $this->loadDirectory($classroot, BASE_DIR);
    }
}
