<?php

namespace Domain\Connections\Cubilis\Dtos;

use Illuminate\Support\Carbon;

class AvailStatusMessageDto
{
    public function __construct(
        public readonly string $invCode,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly int $bookingLimit,
        public readonly float $amount,
        public readonly ?int $minLos,
        public readonly ?int $maxLos,
        public readonly bool $open,
    ) {}

    /**
     * Create from a SimpleXMLElement representing <AvailStatusMessage>
     */
    public static function fromSimpleXml(\SimpleXMLElement $message): self
    {
        $control = $message->StatusApplicationControl;
        $attrs  = $control->attributes();
        $invCode = (string) ($attrs['InvCode'] ?? '');
        $start   = Carbon::parse((string) ($attrs['Start'] ?? now()));
        $end     = Carbon::parse((string) ($attrs['End'] ?? now()));
        $bookingLimit = (int) ($message->attributes()['BookingLimit'] ?? 0);

        $rateNode = $message->BestAvailableRates->BestAvailableRate;
        $amount   = (float) ($rateNode->attributes()['Amount'] ?? 0);

        $minLos = null;
        $maxLos = null;
        foreach ($message->LengthsOfStay->LengthOfStay as $los) {
            $lattrs = $los->attributes();
            $type   = (string) ($lattrs['MinMaxMessageType'] ?? '');
            $time   = (int) ($lattrs['Time'] ?? 0);
            if ($type === 'SetMinLOS') {
                $minLos = $time;
            }
            if ($type === 'SetMaxLOS') {
                $maxLos = $time;
            }
        }

        $status = (string) ($attrs['Status'] ?? '');
        $open   = $bookingLimit > 0 && $status === 'Open';

        return new self(
            $invCode,
            $start,
            $end,
            $bookingLimit,
            $amount,
            $minLos,
            $maxLos,
            $open
        );
    }
}
