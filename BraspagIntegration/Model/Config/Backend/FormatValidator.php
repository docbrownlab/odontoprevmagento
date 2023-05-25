<?php

namespace Odontoprev\BraspagIntegration\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

class FormatValidator extends Value
{
	const BANK_NAME = 0;
    const BANK_CODE = 1;
    const DEBIT_ACCOUNT_CODE = 2;

	public function beforeSave()
    {
    	$this->isEmpty();
    	$this->correctFormat($this->getData('field_config/label'));
        $this->setValue($this->getValue());

        parent::beforeSave();
    }

    public function isEmpty()
    {
    	if (empty($this->getValue())) {
    		throw new ValidatorException(__($this->getData('field_config/label') . ' is required.'));
    	}
    }

    private function correctFormat($label)
    {
    	$banks = explode(';', $this->getValue());

    	foreach ($banks as $line => $bank) {
    		$bank = explode(',', $bank);

    		if (!isset($bank[self::BANK_CODE])) {
    			if (empty($bank[self::BANK_NAME])) {
    				throw new ValidatorException(__('Field: ' . $label . '. Bank can not be empty and code was not informed.'));
    			}

    			if (!ctype_alpha($bank[self::BANK_NAME])) {
    				throw new ValidatorException(__('Field: ' . $label . '. Bank has to be alphabetic type.'));
    			}

    			throw new ValidatorException(__('Field: ' . $label . '. Code can not be empty.'));
    		}

			if (empty($bank[self::BANK_NAME]) && empty($bank[self::BANK_CODE])) {
				throw new ValidatorException(__('Field: ' . $label . '. Bank and code can not be empty.'));
			}

			if (empty($bank[self::BANK_NAME])) {
				throw new ValidatorException(__('Field: ' . $label . '. Bank can not be empty.'));
			}

			if (empty($bank[self::BANK_CODE])) {
				throw new ValidatorException(__('Field: ' . $label . '. Code can not be empty.'));
			}

			if (!is_numeric($bank[self::BANK_CODE])) {
				throw new ValidatorException(__('Field: ' . $label . '. Code has to be a number.'));
			}

            if (!isset($bank[self::DEBIT_ACCOUNT_CODE])) {
                throw new ValidatorException(__('Field: ' . $label . '. Debit account code can not be empty and code was not informed.'));
            }
    	}
    }
}
