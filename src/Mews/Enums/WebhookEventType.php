<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\Mews\Enums;

/**
 * Mews Connector API Webhook Event Types
 *
 * Discriminator values sent in webhook payload Events array to identify event type.
 * Webhooks contain an Events array where each event has a Discriminator field.
 *
 * @see https://mews-systems.gitbook.io/connector-api/webhooks
 */
enum WebhookEventType: string
{
    /**
     * ServiceOrderUpdated - Reservation/booking changes
     *
     * Fired when reservations are created, updated, or cancelled.
     * Event.Value.Id contains the reservation UUID.
     */
    case ServiceOrderUpdated = 'ServiceOrderUpdated';

    /**
     * ResourceUpdated - Property/resource changes
     *
     * Fired when property details, status, or configuration changes.
     * Event.Value.Id contains the resource UUID.
     */
    case ResourceUpdated = 'ResourceUpdated';

    /**
     * ResourceBlockUpdated - Calendar availability block changes
     *
     * Fired when manual calendar blocks or restrictions are updated.
     * Event.Value.Id contains the resource block UUID.
     */
    case ResourceBlockUpdated = 'ResourceBlockUpdated';
}
