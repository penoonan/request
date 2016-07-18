<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace pno\Request\Tests\File\MimeType;

use pno\Request\File\MimeType\MimeTypeGuesser;
use pno\Request\File\MimeType\FileBinaryMimeTypeGuesser;

/**
 * @requires extension fileinfo
 */
class MimeTypeTest extends \PHPUnit_Framework_TestCase
{
    protected $path;

    public function testGuessImageWithoutExtension()
    {
        $this->assertEquals('image/gif', MimeTypeGuesser::getInstance()->guess(__DIR__ . '/../Fixtures/test'));
    }

    public function testGuessImageWithDirectory()
    {
        $this->setExpectedException('pno\Request\File\Exception\FileNotFoundException');

        MimeTypeGuesser::getInstance()->guess(__DIR__ . '/../Fixtures/directory');
    }

    public function testGuessImageWithFileBinaryMimeTypeGuesser()
    {
        $guesser = MimeTypeGuesser::getInstance();
        $guesser->register(new FileBinaryMimeTypeGuesser());
        $this->assertEquals('image/gif', MimeTypeGuesser::getInstance()->guess(__DIR__ . '/../Fixtures/test'));
    }

    public function testGuessImageWithKnownExtension()
    {
        $this->assertEquals('image/gif', MimeTypeGuesser::getInstance()->guess(__DIR__ . '/../Fixtures/test.gif'));
    }

    public function testGuessFileWithUnknownExtension()
    {
        $this->assertEquals('application/octet-stream', MimeTypeGuesser::getInstance()->guess(__DIR__ . '/../Fixtures/.unknownextension'));
    }

    public function testGuessWithIncorrectPath()
    {
        $this->setExpectedException('pno\Request\File\Exception\FileNotFoundException');
        MimeTypeGuesser::getInstance()->guess(__DIR__.'/../Fixtures/not_here');
    }

    public function testGuessWithNonReadablePath()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Can not verify chmod operations on Windows');
        }

        if (!getenv('USER') || 'root' === getenv('USER')) {
            $this->markTestSkipped('This test will fail if run under superuser');
        }

        $path = __DIR__.'/../Fixtures/to_delete';
        touch($path);
        @chmod($path, 0333);

        if (substr(sprintf('%o', fileperms($path)), -4) == '0333') {
            $this->setExpectedException('pno\Request\File\Exception\AccessDeniedException');
            MimeTypeGuesser::getInstance()->guess($path);
        } else {
            $this->markTestSkipped('Can not verify chmod operations, change of file permissions failed');
        }
    }

    public static function tearDownAfterClass()
    {
        $path = __DIR__.'/../Fixtures/to_delete';
        if (file_exists($path)) {
            @chmod($path, 0666);
            @unlink($path);
        }
    }
}
