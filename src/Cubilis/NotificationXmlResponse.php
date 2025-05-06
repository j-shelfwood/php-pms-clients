<?php

namespace Domain\Connections\Cubilis;

class NotificationXmlResponse
{
    /**
     * Success response XML for OTA_HotelAvailNotifRS
     */
    public static function success(): string
    {
        return <<<'XML'
            <?xml version="1.0" encoding="utf-8"?>
            <OTA_HotelAvailNotifRS Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
                <Success />
            </OTA_HotelAvailNotifRS>
        XML;
    }

    /**
     * Authentication failure response XML (Code 507)
     */
    public static function authError(): string
    {
        return <<<'XML'
            <?xml version="1.0" encoding="utf-8"?>
            <OTA_HotelAvailNotifRS Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
                <Errors>
                    <Error Code="507" ShortText="Authentication failed" Type="2"/>
                </Errors>
            </OTA_HotelAvailNotifRS>
        XML;
    }

    /**
     * Generic error response XML for OTA_HotelAvailNotifRS
     *
     * @param  int  $code  HTTP status code
     * @param  string  $message  ShortText for the error
     */
    public static function error(int $code, string $message): string
    {
        return sprintf(
            '<?xml version="1.0" encoding="utf-8"?>'
            .'<OTA_HotelAvailNotifRS Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">'
            .'<Errors><Error Code="%d" ShortText="%s"/></Errors>'
            .'</OTA_HotelAvailNotifRS>',
            $code,
            htmlspecialchars($message, ENT_QUOTES | ENT_XML1)
        );
    }
}
