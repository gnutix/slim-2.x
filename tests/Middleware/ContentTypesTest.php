<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011-2017 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     2.6.4
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class ContentTypesTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        ob_start();
    }

    protected function tearDown(): void
    {
        ob_end_clean();
    }

    /**
     * Test parses JSON
     */
    public function testParsesJson(): void
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json',
            'CONENT_LENGTH' => 13,
            'slim.input' => '{"foo":"bar"}'
        ));
        $s = new \Slim\Slim();
        $s->add(new \Slim\Middleware\ContentTypes());
        $s->run();
        /** @var mixed[] $body */
        $body = $s->request()->getBody();
        $this->assertTrue(is_array($body));
        $this->assertEquals('bar', $body['foo']);
    }

    /**
     * Test ignores JSON with errors
     */
    public function testParsesJsonWithError(): void
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json',
            'CONENT_LENGTH' => 12,
            'slim.input' => '{"foo":"bar"' //<-- This should be incorrect!
        ));
        $s = new \Slim\Slim();
        $s->add(new \Slim\Middleware\ContentTypes());
        $s->run();
        $body = $s->request()->getBody();
        $this->assertTrue(is_string($body));
        $this->assertEquals('{"foo":"bar"', $body);
    }

    /**
     * Test parses XML
     */
    public function testParsesXml(): void
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/xml',
            'CONENT_LENGTH' => 68,
            'slim.input' => '<books><book><id>1</id><author>Clive Cussler</author></book></books>'
        ));
        $s = new \Slim\Slim();
        $s->add(new \Slim\Middleware\ContentTypes());
        $s->run();
        $body = $s->request()->getBody();
        $this->assertInstanceOf('SimpleXMLElement', $body);
        $this->assertEquals('Clive Cussler', (string) $body->book->author);
    }

    /**
     * Test ignores XML with errors
     */
    public function testParsesXmlWithError(): void
    {
	libxml_use_internal_errors(true);
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/xml',
            'CONTENT_LENGTH' => 68,
            'slim.input' => '<books><book><id>1</id><author>Clive Cussler</book></books>' //<-- This should be incorrect!
        ));
        $s = new \Slim\Slim();
        $s->add(new \Slim\Middleware\ContentTypes());
        $s->run();
        $body = $s->request()->getBody();
        $this->assertTrue(is_string($body));
        $this->assertEquals('<books><book><id>1</id><author>Clive Cussler</book></books>', $body);
    }

    /**
     * Test parses CSV
     */
    public function testParsesCsv(): void
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'text/csv',
            'CONTENT_LENGTH' => 44,
            'slim.input' => "John,Doe,000-111-2222\nJane,Doe,111-222-3333"
        ));
        $s = new \Slim\Slim();
        $s->add(new \Slim\Middleware\ContentTypes());
        $s->run();
        /** @var mixed[] $body */
        $body = $s->request()->getBody();
        $this->assertTrue(is_array($body));
        $this->assertEquals(2, count($body));
        $this->assertEquals('000-111-2222', $body[0][2]);
        $this->assertEquals('Doe', $body[1][1]);
    }

    /**
     * Test parses request body based on media-type only, disregarding
     * any extra content-type header parameters
     */
    public function testParsesRequestBodyWithMediaType(): void
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json; charset=ISO-8859-4',
            'CONTENT_LENGTH' => 13,
            'slim.input' => '{"foo":"bar"}'
        ));
        $s = new \Slim\Slim();
        $s->add(new \Slim\Middleware\ContentTypes());
        $s->run();
        /** @var mixed[] $body */
        $body = $s->request()->getBody();
        $this->assertTrue(is_array($body));
        $this->assertEquals('bar', $body['foo']);
    }
}
