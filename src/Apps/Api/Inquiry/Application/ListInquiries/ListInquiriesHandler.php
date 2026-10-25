<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Application\ListInquiries;

use Gaby\Apps\Api\Inquiry\Domain\InquiryRepositoryInterface;

final class ListInquiriesHandler
{
    /** @var InquiryRepositoryInterface */
    private $inquiries;

    public function __construct(InquiryRepositoryInterface $inquiries)
    {
        $this->inquiries = $inquiries;
    }

    /**
     * @return \Gaby\Apps\Api\Inquiry\Domain\Inquiry[]
     */
    public function handle(ListInquiriesQuery $query): array
    {
        return $this->inquiries->findAll();
    }
}
