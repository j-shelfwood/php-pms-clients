<?php

use Shelfwood\PhpPms\Http\XmlParser;
use Shelfwood\PhpPms\Exceptions\ParseException;
use Shelfwood\PhpPms\Exceptions\ErrorDetails;


describe('XMLParserTest', function () {
    it('parses valid XML to array', function () {
        $xml = '<root><foo>bar</foo></root>';
        $result = XmlParser::parse($xml);
        expect($result['foo'])->toBe('bar');
    });

    it('throws on empty XML string', function () {
        expect(fn() => XMLParser::parse(''))->toThrow(\Shelfwood\PhpPms\Exceptions\XmlParsingException::class);
    });

    it('throws on malformed XML', function () {
        expect(fn() => XMLParser::parse('<not><closed>'))->toThrow(\Shelfwood\PhpPms\Exceptions\XmlParsingException::class);
    });

    it('detects OTA-style error', function () {
        $xml = '<Errors><Error Code="123" ShortText="Failure"/></Errors>';
        $arr = XmlParser::parse($xml);
        expect(XmlParser::hasError($arr))->toBeTrue();
    });

    it('extracts error details', function () {
        $xml = '<Errors><Error Code="123" ShortText="Failure"/></Errors>';
        $arr = XmlParser::parse($xml);
        $details = XmlParser::extractErrorDetails($arr);
        expect($details->code)->toBe('123')
            ->and($details->message)->toBe('Failure');
    });

    it('gets string, int, float, bool safely', function () {
        $data = [
            'str' => 'abc',
            'int' => '42',
            'float' => '3.14',
            'bool' => true,
            'empty' => [],
        ];
        expect(XmlParser::getString($data, 'str'))->toBe('abc')
            ->and(XmlParser::getInt($data, 'int'))->toBe(42)
            ->and(XmlParser::getFloat($data, 'float'))->toBe(3.14)
            ->and(XmlParser::getBool($data, 'bool'))->toBeTrue()
            ->and(XmlParser::getString($data, 'empty', 'default'))->toBe('default');
    });

    it('detects BookingManager error structure', function () {
        $xml = '<response><e><code>ERR001</code><message>Generic API Error</message></e></response>';
        $arr = XmlParser::parse($xml);
        expect(XmlParser::hasError($arr))->toBeTrue();
    });

    it('extracts BookingManager error details', function () {
        $xml = '<response><e><code>ERR001</code><message>Generic API Error</message></e></response>';
        $arr = XmlParser::parse($xml);
        $details = XmlParser::extractErrorDetails($arr);
        expect($details->code)->toBe('ERR001')
            ->and($details->message)->toBe('Generic API Error');
    });

    it('detects error structure with direct code children', function () {
        $xml = '<response><error><code>ERR002</code><message>Direct Error Message</message></error></response>';
        $arr = XmlParser::parse($xml);
        expect(XmlParser::hasError($arr))->toBeTrue();
    });

    it('extracts error details with direct code children', function () {
        $xml = '<response><error><code>ERR002</code><message>Direct Error Message</message></error></response>';
        $arr = XmlParser::parse($xml);
        $details = XmlParser::extractErrorDetails($arr);
        expect($details->code)->toBe('ERR002')
            ->and($details->message)->toBe('Direct Error Message');
    });

    it('handles error structure that hasError detects but extraction previously missed', function () {
        // This tests the exact scenario described in the user's issue
        $xml = '<response><error><code>TEST123</code><message>Test error message</message></error></response>';
        $arr = XmlParser::parse($xml);

        // hasError should detect this
        expect(XmlParser::hasError($arr))->toBeTrue();

        // extractErrorDetails should now properly extract the details
        $details = XmlParser::extractErrorDetails($arr);
        expect($details->code)->toBe('TEST123')
            ->and($details->message)->toBe('Test error message')
            ->and($details->rawResponseFragment)->toBeArray();
    });

    it('handles root-level error tags correctly', function () {
        $xml = file_get_contents(Tests\Helpers\TestHelpers::getMockFilePath('error-root.xml'));
        $arr = XmlParser::parse($xml);

        expect(XmlParser::hasError($arr))->toBeTrue();

        $details = XmlParser::extractErrorDetails($arr);
        expect($details->code)->toBe('303');
        expect($details->message)->toBe('Property is unavailable');
    });

    it('returns array for text-only elements without attributes', function () {
        $xml = '<message>Authentication failed</message>';
        $result = XmlParser::parse($xml);

        // Should return array, not string (fixes type violation)
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('#text')
            ->and($result['#text'])->toBe('Authentication failed');
    });

    it('returns array for empty elements', function () {
        $xml = '<empty></empty>';
        $result = XmlParser::parse($xml);

        // Empty root element should return array with empty string value
        expect($result)->toBeArray()
            ->and($result)->toHaveKey('#text')
            ->and($result['#text'])->toBe('');
    });

    it('returns array for nested text-only elements', function () {
        $xml = '<response><status>success</status><data>Test data</data></response>';
        $result = XmlParser::parse($xml);

        // Child elements with only text return as strings (not wrapped in arrays)
        expect($result)->toBeArray()
            ->and($result['status'])->toBe('success')
            ->and($result['data'])->toBe('Test data');
    });

    });
