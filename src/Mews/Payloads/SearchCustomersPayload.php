<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

class SearchCustomersPayload
{
    public function __construct(
        public readonly array $emails,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->emails)) {
            throw new \InvalidArgumentException('Emails cannot be empty');
        }

        foreach ($this->emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Invalid email format: {$email}");
            }
        }
    }

    public function toArray(): array
    {
        return [
            'Emails' => $this->emails,
        ];
    }
}
