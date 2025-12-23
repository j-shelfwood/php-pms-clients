<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class Customer
{
    public function __construct(
        public readonly string $id,
        public readonly string $chainId,
        public readonly ?string $number,
        public readonly ?string $firstName,
        public readonly string $lastName,
        public readonly ?string $secondLastName,
        public readonly ?string $title,
        public readonly ?string $sex,
        public readonly ?string $nationalityCode,
        public readonly ?string $languageCode,
        public readonly ?string $birthDate,
        public readonly ?string $birthPlace,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $loyaltyCode,
        public readonly ?string $accountingCode,
        public readonly array $classifications,
        public readonly array $options,
        public readonly ?string $notes,
        public readonly ?string $carRegistrationNumber,
        public readonly ?string $taxIdentificationNumber,
        public readonly ?string $companyId,
        public readonly bool $isActive,
        public readonly string $createdUtc,
        public readonly string $updatedUtc,
        // Additional fields from API
        public readonly ?string $addressId,
        public readonly ?string $billingCode,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                id: $data['Id'] ?? throw new \InvalidArgumentException('Id is required'),
                chainId: $data['ChainId'] ?? throw new \InvalidArgumentException('ChainId required'),
                number: $data['Number'] ?? null,
                firstName: $data['FirstName'] ?? null,
                lastName: $data['LastName'] ?? throw new \InvalidArgumentException('LastName required'),
                secondLastName: $data['SecondLastName'] ?? null,
                title: $data['Title'] ?? null,
                sex: $data['Sex'] ?? null,
                nationalityCode: $data['NationalityCode'] ?? null,
                languageCode: $data['LanguageCode'] ?? null,
                birthDate: $data['BirthDate'] ?? null,
                birthPlace: $data['BirthPlace'] ?? null,
                email: $data['Email'] ?? null,
                phone: $data['Phone'] ?? null,
                loyaltyCode: $data['LoyaltyCode'] ?? null,
                accountingCode: $data['AccountingCode'] ?? null,
                classifications: $data['Classifications'] ?? [],
                options: $data['Options'] ?? [],
                notes: $data['Notes'] ?? null,
                carRegistrationNumber: $data['CarRegistrationNumber'] ?? null,
                taxIdentificationNumber: $data['TaxIdentificationNumber'] ?? null,
                companyId: $data['CompanyId'] ?? null,
                isActive: $data['IsActive'] ?? true,
                createdUtc: $data['CreatedUtc'] ?? '',
                updatedUtc: $data['UpdatedUtc'] ?? '',
                // Additional fields from API
                addressId: $data['AddressId'] ?? null,
                billingCode: $data['BillingCode'] ?? null,
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map Customer: {$e->getMessage()}", 0, $e);
        }
    }
}
