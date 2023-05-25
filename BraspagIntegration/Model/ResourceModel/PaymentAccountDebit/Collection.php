<?php
namespace Odontoprev\BraspagIntegration\Model\ResourceModel\PaymentAccountDebit;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Odontoprev\BraspagIntegration\Model\PaymentAccountDebit',
            'Odontoprev\BraspagIntegration\Model\ResourceModel\PaymentAccountDebit'
        );
    }
}