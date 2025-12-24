<?php

use Shelfwood\PhpPms\Tests\Support\MewsFixtures;

describe('MewsFixtures Utility', function () {
    describe('Webhook Payload Loading', function () {
        it('loads service-order-updated webhook payload', function () {
            $payload = MewsFixtures::webhookPayload('service-order-updated');

            expect($payload)->toBeArray()
                ->and($payload)->toHaveKeys(['EnterpriseId', 'IntegrationId', 'Events'])
                ->and($payload['EnterpriseId'])->toBe(MewsFixtures::ENTERPRISE_ID)
                ->and($payload['Events'])->toHaveCount(1)
                ->and($payload['Events'][0]['Discriminator'])->toBe('ServiceOrderUpdated')
                ->and($payload['Events'][0]['Value']['Id'])->toBe(MewsFixtures::RESERVATION_ID);
        });

        it('loads resource-updated webhook payload', function () {
            $payload = MewsFixtures::webhookPayload('resource-updated');

            expect($payload['Events'][0]['Discriminator'])->toBe('ResourceUpdated')
                ->and($payload['Events'][0]['Value']['Id'])->toBe(MewsFixtures::RESOURCE_ID);
        });

        it('loads batch-webhook payload with multiple events', function () {
            $payload = MewsFixtures::webhookPayload('batch-webhook');

            expect($payload['Events'])->toHaveCount(3)
                ->and($payload['Events'][0]['Discriminator'])->toBe('ServiceOrderUpdated')
                ->and($payload['Events'][1]['Discriminator'])->toBe('ResourceUpdated')
                ->and($payload['Events'][2]['Discriminator'])->toBe('MessageAdded');
        });

        it('loads malformed-webhook payload', function () {
            $payload = MewsFixtures::webhookPayload('malformed-webhook');

            expect($payload['Events'][0])->toHaveKey('Discriminator')
                ->and($payload['Events'][0])->not->toHaveKey('Value');
        });

        it('throws exception for non-existent webhook', function () {
            MewsFixtures::webhookPayload('non-existent');
        })->throws(\RuntimeException::class, 'Webhook fixture not found');
    });

    describe('API Response Loading', function () {
        it('loads reservation-getbyid response', function () {
            $response = MewsFixtures::apiResponse('reservation-getbyid');

            expect($response)->toBeArray()
                ->and($response['Id'])->toBe(MewsFixtures::RESERVATION_ID)
                ->and($response['ServiceId'])->toBe(MewsFixtures::SERVICE_ID)
                ->and($response['AssignedResourceId'])->toBe(MewsFixtures::RESOURCE_ID)
                ->and($response['CustomerId'])->toBe(MewsFixtures::CUSTOMER_ID)
                ->and($response['State'])->toBe('Confirmed')
                ->and($response['Number'])->toBe('52');
        });

        it('loads resource-getbyid response', function () {
            $response = MewsFixtures::apiResponse('resource-getbyid');

            expect($response['Id'])->toBe(MewsFixtures::RESOURCE_ID)
                ->and($response['ServiceId'])->toBe(MewsFixtures::SERVICE_ID)
                ->and($response['Name'])->toBe('Superior Apartment')
                ->and($response['State'])->toBe('Clean')
                ->and($response['IsActive'])->toBeTrue();
        });

        it('loads service-getbyid response', function () {
            $response = MewsFixtures::apiResponse('service-getbyid');

            expect($response['Id'])->toBe(MewsFixtures::SERVICE_ID)
                ->and($response['Name'])->toBe('Accommodation')
                ->and($response['Type'])->toBe('Accommod')
                ->and($response['IsActive'])->toBeTrue();
        });

        it('loads resourcecategoryassignment-getforresource response', function () {
            $response = MewsFixtures::apiResponse('resourcecategoryassignment-getforresource');

            expect($response['ResourceId'])->toBe(MewsFixtures::RESOURCE_ID)
                ->and($response['CategoryId'])->toBe(MewsFixtures::CATEGORY_ID)
                ->and($response['IsActive'])->toBeTrue();
        });

        it('loads customer-getbyid response', function () {
            $response = MewsFixtures::apiResponse('customer-getbyid');

            expect($response['Id'])->toBe(MewsFixtures::CUSTOMER_ID)
                ->and($response['FirstName'])->toBe('John')
                ->and($response['LastName'])->toBe('Doe')
                ->and($response['Email'])->toBe('john.doe@example.com')
                ->and($response['Phone'])->toBe('+31612345678');
        });

        it('throws exception for non-existent response', function () {
            MewsFixtures::apiResponse('non-existent');
        })->throws(\RuntimeException::class, 'API response fixture not found');
    });

    describe('API Request Loading', function () {
        it('loads services-getavailability request', function () {
            $request = MewsFixtures::apiRequest('services-getavailability');

            expect($request)->toBeArray()
                ->and($request)->toHaveKeys(['ServiceId', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc', 'Metrics'])
                ->and($request['Metrics'])->toBeArray()->not()->toBeEmpty();
        });

        it('throws exception for non-existent request', function () {
            MewsFixtures::apiRequest('non-existent');
        })->throws(\RuntimeException::class, 'API request fixture not found');
    });

    describe('Signature Generation', function () {
        it('generates valid HMAC-SHA256 signature', function () {
            $payload = ['test' => 'data'];
            $secret = 'test-secret';

            $signature = MewsFixtures::generateSignature($payload, $secret);

            $expected = hash_hmac('sha256', json_encode($payload), $secret);
            expect($signature)->toBe($expected);
        });

        it('generates different signatures for different payloads', function () {
            $secret = 'test-secret';
            $sig1 = MewsFixtures::generateSignature(['data' => 'one'], $secret);
            $sig2 = MewsFixtures::generateSignature(['data' => 'two'], $secret);

            expect($sig1)->not->toBe($sig2);
        });

        it('generates different signatures for different secrets', function () {
            $payload = ['test' => 'data'];
            $sig1 = MewsFixtures::generateSignature($payload, 'secret1');
            $sig2 = MewsFixtures::generateSignature($payload, 'secret2');

            expect($sig1)->not->toBe($sig2);
        });

        it('generates consistent signature for same payload and secret', function () {
            $payload = ['test' => 'data'];
            $secret = 'test-secret';

            $sig1 = MewsFixtures::generateSignature($payload, $secret);
            $sig2 = MewsFixtures::generateSignature($payload, $secret);

            expect($sig1)->toBe($sig2);
        });
    });

    describe('Signature Verification', function () {
        it('verifies valid signature', function () {
            $payload = ['test' => 'data'];
            $secret = 'test-secret';
            $signature = MewsFixtures::generateSignature($payload, $secret);

            expect(MewsFixtures::verifySignature($payload, $signature, $secret))->toBeTrue();
        });

        it('rejects invalid signature', function () {
            $payload = ['test' => 'data'];
            $secret = 'test-secret';

            expect(MewsFixtures::verifySignature($payload, 'invalid-signature', $secret))->toBeFalse();
        });

        it('rejects signature with wrong secret', function () {
            $payload = ['test' => 'data'];
            $signature = MewsFixtures::generateSignature($payload, 'secret1');

            expect(MewsFixtures::verifySignature($payload, $signature, 'secret2'))->toBeFalse();
        });

        it('rejects signature for modified payload', function () {
            $originalPayload = ['test' => 'data'];
            $secret = 'test-secret';
            $signature = MewsFixtures::generateSignature($originalPayload, $secret);

            $modifiedPayload = ['test' => 'modified'];

            expect(MewsFixtures::verifySignature($modifiedPayload, $signature, $secret))->toBeFalse();
        });
    });

    describe('UUID Constants', function () {
        it('has all required UUID constants', function () {
            expect(MewsFixtures::ENTERPRISE_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::INTEGRATION_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::SERVICE_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::RESOURCE_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::CATEGORY_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::RESERVATION_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::CUSTOMER_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::BLOCK_ID)->toBeString()->toHaveLength(36);
            expect(MewsFixtures::MESSAGE_ID)->toBeString()->toHaveLength(36);
        });

        it('UUIDs match values in fixtures', function () {
            $payload = MewsFixtures::webhookPayload('service-order-updated');
            expect($payload['EnterpriseId'])->toBe(MewsFixtures::ENTERPRISE_ID);

            $reservation = MewsFixtures::apiResponse('reservation-getbyid');
            expect($reservation['Id'])->toBe(MewsFixtures::RESERVATION_ID);

            $resource = MewsFixtures::apiResponse('resource-getbyid');
            expect($resource['Id'])->toBe(MewsFixtures::RESOURCE_ID);
        });
    });

    describe('Helper Methods', function () {
        it('lists available webhook fixtures', function () {
            $webhooks = MewsFixtures::availableWebhooks();

            expect($webhooks)->toBeArray()
                ->and($webhooks)->toContain('service-order-updated')
                ->and($webhooks)->toContain('resource-updated')
                ->and($webhooks)->toContain('batch-webhook')
                ->and($webhooks)->toContain('malformed-webhook');
        });

        it('lists available response fixtures', function () {
            $responses = MewsFixtures::availableResponses();

            expect($responses)->toBeArray()
                ->and($responses)->toContain('reservation-getbyid')
                ->and($responses)->toContain('resource-getbyid')
                ->and($responses)->toContain('service-getbyid')
                ->and($responses)->toContain('customer-getbyid');
        });

        it('lists available request fixtures', function () {
            $requests = MewsFixtures::availableRequests();

            expect($requests)->toBeArray()
                ->and($requests)->toContain('services-getavailability')
                ->and($requests)->toContain('rates-getpricing');
        });
    });
});
