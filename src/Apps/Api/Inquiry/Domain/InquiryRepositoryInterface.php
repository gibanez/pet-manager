<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Domain;

interface InquiryRepositoryInterface
{
    public function save(Inquiry $inquiry): void;

    /**
     * @return Inquiry[]
     */
    public function findAll(): array;
}
