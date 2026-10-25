<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Application\CreateInquiry;

final class CreateInquiryCommand
{
    /** @var string */
    public $name;

    /** @var string */
    public $email;

    /** @var string */
    public $message;

    public function __construct(string $name, string $email, string $message)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
    }
}
