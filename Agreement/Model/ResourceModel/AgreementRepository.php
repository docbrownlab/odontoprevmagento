<?php

namespace Odontoprev\Agreement\Model\ResourceModel;

use Magento\Framework\Exception\CouldNotSaveException;
use Odontoprev\Agreement\Model\AgreementFactory;
use Odontoprev\Agreement\Api\AgreementInterface;
use Odontoprev\Agreement\Api\Data\AgreementInterface as AgreementInterfaceData;

class AgreementRepository implements AgreementInterface
{
    protected $_agreementFactory;

    public function __construct(AgreementFactory $agreementFactory)
    {
        $this->_agreementFactory = $agreementFactory;
    }

    /**
     * @param \Odontoprev\Agreement\Api\Data\AgreementInterface $agreement
     * @return array
     */
    public function save(AgreementInterfaceData $agreement)
    {
        $data = $agreement->getData();
        $data['datetime'] = date("Y-m-d H:i:s");
        
        try {
            $agr = $this->_agreementFactory->create();
            $agr->setData($data);

            return array($agr->save()->getData());
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __('An error occurred on the server.'),
                $e
            );
        }
    }

    /**
     * @param string $quoteId
     * @return bollean
     */
    public function exist($quoteId): bool
    {
        $data = $this->get($quoteId);

        return !empty($data);
    }

    /**
     * @param string $quoteId
     * @return array
     */
    public function get($quoteId): array
    {
        return $this->_agreementFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter(
                                            'quote_id',
                                            ['eq' => (int)$quoteId]
                                        )
                                        ->getData();
    }

    /**
     * Get ip of request.
     *
     * @return string
     */
    public function getIp(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }
}
