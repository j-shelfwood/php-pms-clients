<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

class SearchCustomersPayload
{
    public function __construct(
        public readonly array $emails,
        public readonly array $extent = [
            'Customers' => true,
            'Documents' => false,
            'Addresses' => false,
        ],
        public readonly int $limitCount = 1000,
        public readonly ?string $cursor = null,
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

        if ($this->limitCount < 1 || $this->limitCount > 1000) {
            throw new \InvalidArgumentException('Limitation count must be between 1 and 1000');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'Emails' => $this->emails,
            'Extent' => $this->extent,
            'Limitation' => [
                'Count' => $this->limitCount,
                ...($this->cursor !== null ? ['Cursor' => $this->cursor] : []),
            ],
        ], fn($value) => $value !== null);
    }
}
