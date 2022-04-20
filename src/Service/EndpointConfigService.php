<?php declare(strict_types=1);

namespace App\Service;

use Nette\Neon\Neon;

class EndpointConfigService
{
    private $endpoints;

    public function __construct()
    {
        $this->endpoints = Neon::decode(@file_get_contents('../endpoints.neon'))['endpoints'];
    }

    public function getEndpoints(): array
    {
        return $this->endpoints;
    }
}