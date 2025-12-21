<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

class CreateCustomerPayload
{
    public function __construct(
        public readonly string $lastName,
        public readonly ?string $email = null,
        public readonly ?string $firstName = null,
        public readonly ?string $phone = null,
        public readonly ?string $nationalityCode = null,
        public readonly ?string $preferredLanguageCode = null,
        public readonly ?string $birthDate = null,
        public readonly ?array $address = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->lastName)) {
            throw new \InvalidArgumentException('LastName is required');
        }

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'Email' => $this->email,
            'FirstName' => $this->firstName,
            'LastName' => $this->lastName,
            'Phone' => $this->phone,
            'NationalityCode' => $this->nationalityCode,
            'PreferredLanguageCode' => $this->preferredLanguageCode,
            'BirthDate' => $this->birthDate,
            'Address' => $this->address,
        ], fn($value) => $value !== null);
    }
}
