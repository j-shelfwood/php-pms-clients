<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

class UpdateCustomerPayload
{
    public function __construct(
        public readonly string $customerId,
        public readonly ?bool $isActive = null,
        public readonly ?string $notes = null,
        public readonly ?string $email = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $phone = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->customerId)) {
            throw new \InvalidArgumentException('CustomerId is required');
        }

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'CustomerId' => $this->customerId,
            'IsActive' => $this->isActive,
            'Notes' => $this->notes,
            'Email' => $this->email,
            'FirstName' => $this->firstName,
            'LastName' => $this->lastName,
            'Phone' => $this->phone,
        ], fn($value) => $value !== null);
    }
}
