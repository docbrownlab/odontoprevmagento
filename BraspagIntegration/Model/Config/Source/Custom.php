<?php
namespace Odontoprev\BraspagIntegration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Custom implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'production' => 'Production',
            'homolog' => 'Homolog',
        ];
    }
}
