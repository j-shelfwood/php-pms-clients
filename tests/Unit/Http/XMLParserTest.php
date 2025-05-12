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

    });
