<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

class CreateCustomerPayload
{
    public function __construct(
        public readonly string $lastName,
        public readonly bool $overwriteExisting = false,
        public readonly ?string $email = null,
        public readonly ?string $firstName = null,
        public readonly ?string $title = null,
        public readonly ?string $secondLastName = null,
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
        $data = [
            'LastName' => $this->lastName,
            'OverwriteExisting' => $this->overwriteExisting,
        ];

        // Add optional fields if not null
        if ($this->email !== null) {
            $data['Email'] = $this->email;
        }
        if ($this->firstName !== null) {
            $data['FirstName'] = $this->firstName;
        }
        if ($this->title !== null) {
            $data['Title'] = $this->title;
        }
        if ($this->secondLastName !== null) {
            $data['SecondLastName'] = $this->secondLastName;
        }
        if ($this->phone !== null) {
            $data['Phone'] = $this->phone;
        }
        if ($this->nationalityCode !== null) {
            $data['NationalityCode'] = $this->nationalityCode;
        }
        if ($this->preferredLanguageCode !== null) {
            // Mews API expects LanguageCode field
            $data['LanguageCode'] = $this->preferredLanguageCode;
        }
        if ($this->birthDate !== null) {
            $data['BirthDate'] = $this->birthDate;
        }
        if ($this->address !== null) {
            $data['Address'] = $this->address;
        }

        return $data;
    }
}
