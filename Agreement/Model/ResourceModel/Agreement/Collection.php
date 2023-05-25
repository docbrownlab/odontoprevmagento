<?php

namespace Odontoprev\Agreement\Model\ResourceModel\Agreement;

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
            'Odontoprev\Agreement\Model\Agreement',
            'Odontoprev\Agreement\Model\ResourceModel\Agreement'
        );
    }
}
