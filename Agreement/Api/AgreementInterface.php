<?php

namespace Odontoprev\Agreement\Api;

use Odontoprev\Agreement\Api\Data\AgreementInterface as AgreementInterfaceData;

interface AgreementInterface
{
    /**
     * Add/update the specified agreement.
     *
     * @param \Odontoprev\Agreement\Api\Data\AgreementInterface $agreement
     * @return void
     */
    public function save(AgreementInterfaceData $agreement);

    /**
     * Get agreement by Quote ID.
     *
     * @param string $quoteId
     * @return bool
     */
    public function exist($quoteId): bool;

    /**
     * Get agreement by Quote ID.
     *
     * @param string $quoteId
     * @return bool
     */
    public function get($quoteId): array;

    /**
     * Get ip of request.
     *
     * @return string
     */
    public function getIp(): string;
}
