<?php

namespace Odontoprev\BraspagIntegration\Test\Unit\Helper;

use Odontoprev\BraspagIntegration\Helper\BraspagIntegrationHelper;
use Odontoprev\BraspagIntegration\Model\PaymentAccountDebitFactory;
use Odontoprev\BraspagIntegration\Model\ResourceModel\PaymentAccountDebitRepository;
use Odontoprev\BraspagIntegration\Model\Ui\PaymentAccountDebit;
use Odontoprev\BraspagIntegration\Model\Ui\ConfigProvider;

final class BraspagIntegrationHelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BraspagIntegrationHelper
     */
    protected $helper;

    private $numExistingKeys = 2;

    private $dataKeyBillet = ['size' => 26, 'nameKey' => 'billet'];

    private $dataKeyAccountDebit = ['size' => 40, 'nameKey' => 'accountDebit'];
    
    protected function setUp()
    {
        $this->resourceConnection = $this->getMockBuilder(
            '\Magento\Framework\App\ResourceConnection'
        )
           ->disableOriginalConstructor()
           ->setMethods(['rowCount', 'query', 'getConnection'])
           ->getMock();
        
        
        $this->resourceConnection->method('query')
            ->will($this->returnSelf());

        $this->resourceConnection->method('getConnection')
            ->will($this->returnSelf());

        $factory = $this->getMockBuilder(
                '\Odontoprev\BraspagIntegration\Model\PaymentAccountDebitFactory'
            )
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder(
                '\Odontoprev\BraspagIntegration\Model\ResourceModel\PaymentAccountDebitRepository'
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new BraspagIntegrationHelper(
            $this->resourceConnection, 
            $factory,
            $repository
        );
    }

    public function testGenerateCheckDigitAgency3311()
    {
        $digit = $this->helper->calculateAgencyCheckDigit('3311');
        
        $this->assertEquals('1', $digit);
    }

    public function testGenerateCheckDigitAgency1495()
    {
        $digit = $this->helper->calculateAgencyCheckDigit('1495');
        
        $this->assertEquals('8', $digit);
    }

    public function testGenerateCheckDigitAgency6565()
    {
        $digit = $this->helper->calculateAgencyCheckDigit('6565');
        
        $this->assertEquals('0', $digit);
    }

    public function testModule11()
    {
        $digit = $this->helper->calculateModule11('261533', 7);
        
        $this->assertEquals('9', $digit);
    }

    public function testKeysBraspagIntegration()
    {
        $keys = $this->helper->keysBraspagIntegration(false);

        $this->assertEquals(true, is_array($keys));

        $this->assertEquals($this->numExistingKeys, count($keys));
    }

    public function testKeysBraspagIntegrationSize()
    {
        $keys = $this->helper->keysBraspagIntegration(false);

        $key = current($keys);

        $this->assertEquals($this->dataKeyBillet['size'], strlen($key));

        next($keys);

        $key = current($keys);

        $this->assertEquals($this->dataKeyAccountDebit['size'], strlen($key));
    }

    public function testWithKeysBraspagIntegration()
    {
        $keys = $this->helper->keysBraspagIntegration(true);

        $this->assertEquals(true, is_array($keys));
        $this->assertEquals($this->numExistingKeys, count($keys));
    }

    public function testWithKeysBraspagIntegrationSize()
    {
        $keys = $this->helper->keysBraspagIntegration(true);

        $value = current($keys);

        $this->assertEquals($this->dataKeyBillet['size'], strlen($value));

        next($keys);

        $value = current($keys);

        $this->assertEquals($this->dataKeyAccountDebit['size'], strlen($value));
    }

    public function testWithKeysBraspagIntegrationNameKey()
    {
        $keys = $this->helper->keysBraspagIntegration(true);

        $arrayKeys  = array_keys($keys);
        $nameKey    = current($arrayKeys);

        $this->assertEquals($this->dataKeyBillet['nameKey'], $nameKey);

        next($arrayKeys);

        $nameKey = current($arrayKeys);

        $this->assertEquals($this->dataKeyAccountDebit['nameKey'], $nameKey);
    }

    public function testGetCodeSendAccountDebit237() 
    {
        $code = $this->helper->getCodeSendAccountDebit('237');

        $this->assertEquals(61, $code);
    }

    public function testGetCodeSendAccountDebit341() 
    {
        $code = $this->helper->getCodeSendAccountDebit('341');

        $this->assertEquals(62, $code);
    }

    public function testGetCodeSendAccountDebit033() 
    {
        $code = $this->helper->getCodeSendAccountDebit('033');

        $this->assertEquals(54, $code);
    }
}
