<?php

namespace Odontoprev\Agreement\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Agreement extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('checkout_agreement_customer', 'agreement_id');
    }
}
