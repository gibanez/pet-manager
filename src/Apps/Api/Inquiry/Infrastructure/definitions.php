<?php

declare(strict_types=1);

use Gaby\Apps\Api\Inquiry\Domain\InquiryRepositoryInterface;
use Gaby\Apps\Api\Inquiry\Infrastructure\Persistence\SqliteInquiryRepository;
use function DI\autowire;

return [
    InquiryRepositoryInterface::class => autowire(SqliteInquiryRepository::class),
];
