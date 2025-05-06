<?php

namespace Domain\Connections\Cubilis;

use Domain\Connections\Cubilis\Dtos\AvailStatusMessageDto;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class AvailabilityNotificationHandler
{
    /**
     * Parse incoming OTA_HotelAvailNotifRQ XML payload into a collection of DTOs.
     *
     * @param string $xmlPayload
     * @return Collection<AvailStatusMessageDto>
     */
    public function parse(string $xmlPayload): Collection
    {
        // Suppress namespace warnings
        $xml = new SimpleXMLElement($xmlPayload);
        // Navigate to AvailStatusMessages
        $messagesNode = $xml->AvailStatusMessages->AvailStatusMessage ?? null;
        if (!$messagesNode) {
            return collect();
        }

        // Ensure array
        $items = [];
        if ($messagesNode instanceof SimpleXMLElement) {
            // Single message
            $items[] = $messagesNode;
        } else {
            // Multiple
            foreach ($messagesNode as $msg) {
                $items[] = $msg;
            }
        }

        // Map to DTOs
        return collect($items)
            ->map(fn(SimpleXMLElement $msg) => AvailStatusMessageDto::fromSimpleXml($msg));
    }
}
