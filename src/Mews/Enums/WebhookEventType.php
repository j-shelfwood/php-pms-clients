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
 * @see https://mews-systems.gitbook.io/connector-api/events/wh-general
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
     * MessageAdded - Message/communication added
     *
     * Fired when a message is added to the system.
     * Event.Value.Id contains the message UUID.
     */
    case MessageAdded = 'MessageAdded';

    /**
     * ResourceBlockUpdated - Calendar availability block changes
     *
     * Fired when manual calendar blocks or restrictions are updated.
     * Event.Value.Id contains the resource block UUID.
     */
    case ResourceBlockUpdated = 'ResourceBlockUpdated';

    /**
     * CustomerAdded - Customer profile created
     *
     * Fired when a new customer is added to the system.
     * Event.Value.Id contains the customer UUID.
     */
    case CustomerAdded = 'CustomerAdded';

    /**
     * CustomerUpdated - Customer profile changes
     *
     * Fired when customer details are updated.
     * Event.Value.Id contains the customer UUID.
     */
    case CustomerUpdated = 'CustomerUpdated';

    /**
     * PaymentUpdated - Payment transaction changes
     *
     * Fired when payment status or details are updated.
     * Event.Value.Id contains the payment UUID.
     */
    case PaymentUpdated = 'PaymentUpdated';
}
