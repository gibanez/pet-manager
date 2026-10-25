<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Application\CreateInquiry;

use Gaby\Apps\Api\Inquiry\Domain\Inquiry;
use Gaby\Apps\Api\Inquiry\Domain\InquiryRepositoryInterface;

final class CreateInquiryHandler
{
    /** @var InquiryRepositoryInterface */
    private $inquiries;

    public function __construct(InquiryRepositoryInterface $inquiries)
    {
        $this->inquiries = $inquiries;
    }

    public function handle(CreateInquiryCommand $command): Inquiry
    {
        $inquiry = Inquiry::create(
            $command->name,
            $command->email,
            $command->message,
            date('Y-m-d H:i:s')
        );

        $this->inquiries->save($inquiry);

        return $inquiry;
    }
}
