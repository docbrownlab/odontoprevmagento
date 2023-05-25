<?php

namespace Odontoprev\Agreement\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Odontoprev\Agreement\Api\Data\AgreementInterface;

class Agreement extends AbstractModel implements IdentityInterface, AgreementInterface
{
    const CACHE_TAG = 'Odontoprev_Agreement_Agreement';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Odontoprev\Agreement\Model\ResourceModel\Agreement');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get agreementId
     *
     * @return int|null
     */
    public function getAgreementId() {
        return $this->_get(self::AGREEMENT_ID);
    }

    /**
     * Set agreementId
     *
     * @param int $agreementId
     * @return $this
     */
    public function setAgreementId($agreementId) {
        return $this->setData(self::AGREEMENT_ID, $agreementId);
    }

    /**
     * Get quoteId
     *
     * @return int|null
     */
    public function getQuoteId() {
        return $this->_get(self::QUOTE_ID);
    }

    /**
     * Set quoteId
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId) {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get dateTime
     *
     * @return string|null
     */
    public function getDateTime() {
        return $this->_get(self::DATE_TIME);
    }

    /**
     * Set dateTime
     *
     * @param string $dateTime
     * @return $this
     */
    public function setDateTime($dateTime) {
        return $this->setData(self::DATE_TIME, $dateTime);
    }

    /**
     * Get ip
     *
     * @return string|null
     */
    public function getIp() {
        return $this->_get(self::IP);
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return $this
     */
    public function setIp($ip) {
        return $this->setData(self::IP, $ip);
    }
}
