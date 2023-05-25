<?php

namespace Odontoprev\Agreement\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface AgreementInterface extends ExtensibleDataInterface
{
    const AGREEMENT_ID = 'agreement_id';
    const QUOTE_ID = 'quote_id';
    const DATE_TIME = 'datetime';
    const IP = 'ip';

    /**
     * get agreementId
     *
     * @return int|null
     */
    public function getAgreementId();

    /**
     * Set agreementId
     *
     * @param int $agreementId
     * @return $this
     */
    public function setAgreementId($agreementId);

    /**
     * get quoteId
     *
     * @return int|null
     */
    public function getQuoteId();

    /**
     * Set quoteId
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * get dateTime
     *
     * @return string|null
     */
    public function getDateTime();

    /**
     * Set dateTime
     *
     * @param string $dateTime
     * @return $this
     */
    public function setDateTime($dateTime);

    /**
     * get ip
     *
     * @return string|null
     */
    public function getIp();

    /**
     * Set ip
     *
     * @param string $ip
     * @return $this
     */
    public function setIp($ip);
}
