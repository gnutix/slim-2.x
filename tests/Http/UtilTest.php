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

class SlimHttpUtilTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test strip slashes when magic quotes disabled
     */
    public function testStripSlashesWithoutMagicQuotes(): void
    {
        $data = "This should have \"quotes\" in it";
        $stripped = \Slim\Http\Util::stripSlashesIfMagicQuotes($data, false);
        $this->assertEquals($data, $stripped);
    }

    /**
     * Test strip slashes from array when magic quotes disabled
     */
    public function testStripSlashesFromArrayWithoutMagicQuotes(): void
    {
        $data = array("This should have \"quotes\" in it", "And this \"too\" has quotes");
        $stripped = \Slim\Http\Util::stripSlashesIfMagicQuotes($data, false);
        $this->assertEquals($data, $stripped);
    }

    /**
     * Test strip slashes when magic quotes enabled
     */
    public function testStripSlashesWithMagicQuotes(): void
    {
        $data = "This should have \"quotes\" in it";
        $stripped = \Slim\Http\Util::stripSlashesIfMagicQuotes($data, true);
        $this->assertEquals('This should have "quotes" in it', $stripped);
    }

    /**
     * Test strip slashes from array when magic quotes enabled
     */
    public function testStripSlashesFromArrayWithMagicQuotes(): void
    {
        $data = array("This should have \"quotes\" in it", "And this \"too\" has quotes");
        $stripped = \Slim\Http\Util::stripSlashesIfMagicQuotes($data, true);
        $this->assertEquals($data = array('This should have "quotes" in it', 'And this "too" has quotes'), $stripped);
    }

    /**
     * Test encrypt and decrypt with valid data
     */
    public function testEncryptAndDecryptWithValidData(): void
    {
        if (!function_exists('mcrypt_list_algorithms')) {
            $this->markTestSkipped(('mcrypt not available.'));
        }
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            // mcrypt is deprecated
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        $data = 'foo';
        $key = 'secret';
        $iv = md5('initializationVector');
        $encrypted = \Slim\Http\Util::encrypt($data, $key, $iv);
        $decrypted = \Slim\Http\Util::decrypt($encrypted, $key, $iv);
        $this->assertEquals($data, $decrypted);
        $this->assertTrue($data !== $encrypted);
    }

    /**
     * Test encrypt when data is empty string
     */
    public function testEncryptWhenDataIsEmptyString(): void
    {
        $data = '';
        $key = 'secret';
        $iv = md5('initializationVector');
        $encrypted = \Slim\Http\Util::encrypt($data, $key, $iv);
        $this->assertEquals('', $encrypted);
    }

    /**
     * Test decrypt when data is empty string
     */
    public function testDecryptWhenDataIsEmptyString(): void
    {
        $data = '';
        $key = 'secret';
        $iv = md5('initializationVector');
        $decrypted = \Slim\Http\Util::decrypt($data, $key, $iv);
        $this->assertEquals('', $decrypted);
    }

    /**
     * Test encrypt when IV and key sizes are too long
     */
    public function testEncryptAndDecryptWhenKeyAndIvAreTooLong(): void
    {
        if (!function_exists('mcrypt_list_algorithms')) {
            $this->markTestSkipped(('mcrypt not available.'));
        }
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            // mcrypt is deprecated
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        $data = 'foo';
        $key = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $iv = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $encrypted = \Slim\Http\Util::encrypt($data, $key, $iv);
        $decrypted = \Slim\Http\Util::decrypt($encrypted, $key, $iv);
        $this->assertEquals($data, $decrypted);
        $this->assertTrue($data !== $encrypted);
    }

    public function testEncodeAndDecodeSecureCookieWithValidData(): void
    {
        if (!function_exists('mcrypt_list_algorithms')) {
            $this->markTestSkipped(('mcrypt not available.'));
        }
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            // mcrypt is deprecated
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        //Prepare cookie value
        $value = 'foo';
        $expires = time() + 86400;
        $secret = 'password';
        $algorithm = 0;
        $mode = 0;
        $encodedValue = \Slim\Http\Util::encodeSecureCookie($value, $expires, $secret, $algorithm, $mode);
        $decodedValue = \Slim\Http\Util::decodeSecureCookie($encodedValue, $secret, $algorithm, $mode);

        //Test secure cookie value
        $parts = explode('|', $encodedValue);
        $this->assertEquals(3, count($parts));
        $this->assertEquals($expires, $parts[0]);
        $this->assertEquals($value, $decodedValue);
    }

    /**
     * Test encode/decode secure cookie with old expiration
     *
     * In this test, the expiration date is purposefully set to a time before now.
     * When decoding the encoded cookie value, FALSE is returned since the cookie
     * will have expired before it is decoded.
     */
    public function testEncodeAndDecodeSecureCookieWithOldExpiration(): void
    {
        if (!function_exists('mcrypt_list_algorithms')) {
            $this->markTestSkipped(('mcrypt not available.'));
        }
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            // mcrypt is deprecated
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        $value = 'foo';
        $expires = time() - 100;
        $secret = 'password';
        $algorithm = 0;
        $mode = 0;
        $encodedValue = \Slim\Http\Util::encodeSecureCookie($value, $expires, $secret, $algorithm, $mode);
        $decodedValue = \Slim\Http\Util::decodeSecureCookie($encodedValue, $secret, $algorithm, $mode);
        $this->assertFalse($decodedValue);
    }

    /**
     * Test encode/decode secure cookie with tampered data
     *
     * In this test, the encoded data is purposefully changed to simulate someone
     * tampering with the client-side cookie data. When decoding the encoded cookie value,
     * FALSE is returned since the verification key will not match.
     */
    public function testEncodeAndDecodeSecureCookieWithTamperedData(): void
    {
        if (!function_exists('mcrypt_list_algorithms')) {
            $this->markTestSkipped(('mcrypt not available.'));
        }
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            // mcrypt is deprecated
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        $value = 'foo';
        $expires = time() + 86400;
        $secret = 'password';
        $algorithm = 0;
        $mode = 0;
        $encodedValue = \Slim\Http\Util::encodeSecureCookie($value, $expires, $secret, $algorithm, $mode);
        $encodedValueParts = explode('|', $encodedValue);
        $encodedValueParts[1] = $encodedValueParts[1] . 'changed';
        $encodedValue = implode('|', $encodedValueParts);
        $decodedValue = \Slim\Http\Util::decodeSecureCookie($encodedValue, $secret, $algorithm, $mode);
        $this->assertFalse($decodedValue);
    }

    public function testSetCookieHeaderWithNameAndValue(): void
    {
        $name = 'foo';
        $value = 'bar';
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, $value);
        $this->assertEquals('foo=bar', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueWhenCookieAlreadySet(): void
    {
        $name = 'foo';
        $value = 'bar';
        $header = array('Set-Cookie' => 'one=two');
        \Slim\Http\Util::setCookieHeader($header, $name, $value);
        $this->assertEquals("one=two\nfoo=bar", $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomain(): void
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain
        ));
        $this->assertEquals('foo=bar; domain=foo.com', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPath(): void
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => $path
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAsString(): void
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = '2 days';
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', strtotime($expires));
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat, $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAsInteger(): void
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = strtotime('2 days');
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', $expires);
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat, $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAsZero(): void
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = 0;
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAndSecure(): void
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = strtotime('2 days');
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', $expires);
        $secure = true;
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires,
            'secure' => $secure
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat . '; secure', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAndSecureAndHttpOnly(): void
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = strtotime('2 days');
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', $expires);
        $secure = true;
        $httpOnly = true;
        $header = array();
        \Slim\Http\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires,
            'secure' => $secure,
            'httponly' => $httpOnly
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat . '; secure; HttpOnly', $header['Set-Cookie']);
    }

    /**
     * Test serializeCookies and decrypt with string expires
     *
     * In this test a cookie with a string typed value for 'expires' is set,
     * which should be parsed by `strtotime` to a timestamp when it's added to
     * the headers; this timestamp should then be correctly parsed, and the
     * value correctly decrypted, by `decodeSecureCookie`.
     */
    public function testSerializeCookiesAndDecryptWithStringExpires(): void
    {
        if (!function_exists('mcrypt_list_algorithms')) {
            $this->markTestSkipped(('mcrypt not available.'));
        }
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            // mcrypt is deprecated
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        $value = 'bar';

        $headers = new \Slim\Http\Headers();

        $settings = array(
            'cookies.encrypt' => true,
            'cookies.secret_key' => 'secret',
            'cookies.cipher' => 0,
            'cookies.cipher_mode' => 0
        );

        $cookies = new \Slim\Http\Cookies();
        $cookies->set('foo',  array(
            'value' => $value,
            'expires' => '1 hour'
        ));

        \Slim\Http\Util::serializeCookies($headers, $cookies, $settings);

        $encrypted = $headers->get('Set-Cookie');
        $encrypted = strstr($encrypted, ';', true);
        $encrypted = urldecode(substr(strstr($encrypted, '='), 1));

        $decrypted = \Slim\Http\Util::decodeSecureCookie(
            $encrypted,
            $settings['cookies.secret_key'],
            $settings['cookies.cipher'],
            $settings['cookies.cipher_mode']
        );

        $this->assertEquals($value, $decrypted);
        $this->assertTrue($value !== $encrypted);
    }

    public function testDeleteCookieHeaderWithSurvivingCookie(): void
    {
        $header = array('Set-Cookie' => "foo=bar\none=two");
        \Slim\Http\Util::deleteCookieHeader($header, 'foo');
        $this->assertEquals(1, preg_match("@^one=two\nfoo=; expires=@", $header['Set-Cookie']));
    }

    public function testDeleteCookieHeaderWithoutSurvivingCookie(): void
    {
        $header = array('Set-Cookie' => "foo=bar");
        \Slim\Http\Util::deleteCookieHeader($header, 'foo');
        $this->assertEquals(1, preg_match("@foo=; expires=@", $header['Set-Cookie']));
    }

    public function testDeleteCookieHeaderWithMatchingDomain(): void
    {
        $header = array('Set-Cookie' => "foo=bar; domain=foo.com");
        \Slim\Http\Util::deleteCookieHeader($header, 'foo', array(
            'domain' => 'foo.com'
        ));
        $this->assertEquals(1, preg_match("@foo=; domain=foo.com; expires=@", $header['Set-Cookie']));
    }

    public function testDeleteCookieHeaderWithoutMatchingDomain(): void
    {
        $header = array('Set-Cookie' => "foo=bar; domain=foo.com");
        \Slim\Http\Util::deleteCookieHeader($header, 'foo', array(
            'domain' => 'bar.com'
        ));
        $this->assertEquals(1, preg_match("@foo=bar; domain=foo\.com\nfoo=; domain=bar\.com@", $header['Set-Cookie']));
    }

    /**
     * Test parses Cookie: HTTP header
     */
    public function testParsesCookieHeader(): void
    {
        $header = 'foo=bar; one=two; colors=blue';
        $result = \Slim\Http\Util::parseCookieHeader($header);
        $this->assertEquals(3, count($result));
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('two', $result['one']);
        $this->assertEquals('blue', $result['colors']);
    }

    /**
     * Test parses Cookie: HTTP header with `=` in the cookie value
     */
    public function testParsesCookieHeaderWithEqualSignInValue(): void
    {
        $header = 'foo=bar; one=two=; colors=blue';
        $result = \Slim\Http\Util::parseCookieHeader($header);
        $this->assertEquals(3, count($result));
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('two=', $result['one']);
        $this->assertEquals('blue', $result['colors']);
    }

    public function testParsesCookieHeaderWithCommaSeparator(): void
    {
        $header = 'foo=bar, one=two, colors=blue';
        $result = \Slim\Http\Util::parseCookieHeader($header);
        $this->assertEquals(3, count($result));
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('two', $result['one']);
        $this->assertEquals('blue', $result['colors']);
    }

    public function testPrefersLeftmostCookieWhenManyCookiesWithSameName(): void
    {
        $header = 'foo=bar; foo=beer';
        $result = \Slim\Http\Util::parseCookieHeader($header);
        $this->assertEquals('bar', $result['foo']);
    }
}
