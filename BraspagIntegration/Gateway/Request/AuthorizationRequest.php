<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Odontoprev\BraspagIntegration\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\ScopeInterface;
use Odontoprev\Sales\Mail\Template\TransportBuilder;
use Odontoprev\SDK\DCMS\Billet\Client;
use Odontoprev\BraspagIntegration\Model\Billet;
use Magento\Framework\App\Filesystem\DirectoryList;
use Odontoprev\GeneralSettings\Model\EmailTemplate;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    private $directoryList;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DirectoryList $directoryList
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->directoryList = $directoryList;

        $apiBasePath = $this->scopeConfig->getValue(
            'general_settings/odontoprev_api_gateway/host',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        $token = $this->scopeConfig->getValue(
            'general_settings/odontoprev_api_gateway/access_token',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        $this->client = new Client($apiBasePath, $token);
    }

    public function getBodyEmailBoleto($storeId)
    {
        return $this->scopeConfig->getValue(
            'visualIdentityInformation/general/email_boleto',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    private function getProvider() {
        return $this->scopeConfig->getValue(
            'odontoprev/payment/provider',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $request = $this->request($order);

        $billet = $request['billet'];
        $response = $request['response'];

        $provider = $this->getProvider();

        if ('Successful' === $response->Payment->ReasonMessage) {
            $paymentLink = $response->Payment->Url;
            $expirationDate = $response->Payment->ExpirationDate;
            $this->saveLogPayment($response,'success');
            $payment->getPayment()->setAdditionalInformation('billet_url', $paymentLink);
            $payment->getPayment()->setAdditionalInformation('expiration_date', $expirationDate);
            // $this->sendConfirmationEmail($billet->getData($provider), $paymentLink, $order->getStoreId());
        } else {
            // Gravar retorno de erro 
            $this->saveLogPayment($response,'error');
            throw new \Exception($response->Payment->ProviderReturnMessage);
        }

        return [
            'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            )
        ];
    }
    
    
    function saveLogPayment($dados, $tipo){
        // save dados in database
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        //Insert Data into table
        $sql = "Insert Into logBraspagError Values ('".json_encode($dados)."','".$tipo."')";
        $connection->query($sql);

        return $dados;
    }

    private function request($order)
    {
        $billetConfig = [
            'merchant_id' => $this->scopeConfig->getValue('odontoprev/braspag_integration/merchant_id', ScopeInterface::SCOPE_STORE),
            'merchant_key' => $this->scopeConfig->getValue('odontoprev/braspag_integration/merchant_key', ScopeInterface::SCOPE_STORE),
            // 'merchant_id' => 'FA014678-5FE4-4CE4-8DCD-83342EA07569',
            // 'merchant_key' => 'QJ0YkonqHWcBZyW3EwvdJBj1eafM9WBQL6Cv3xgX',
            'demonstrative' => $this->scopeConfig->getValue('odontoprev/braspag_integration/demonstrative', ScopeInterface::SCOPE_STORE),
            'instructions' => $this->scopeConfig->getValue('odontoprev/braspag_integration/instructions', ScopeInterface::SCOPE_STORE),
            'assignor' => $this->scopeConfig->getValue('odontoprev/braspag_integration/assignor', ScopeInterface::SCOPE_STORE),
            'expiration_days' => $this->scopeConfig->getValue('odontoprev/braspag_integration/expiration_days', ScopeInterface::SCOPE_STORE),
        ];

        $billet = new Billet($order, $billetConfig);
        $provider = $this->getProvider();

        $result = [
            'response' => $this->client->generate($billet->getData($provider), $billet->getHeaders()),
            'billet' => $billet
        ];
        
        return $result;
    }

    private function sendConfirmationEmail(array $data, $billetUrl, $storeId)
    {
        
        $from = [
            'email' => $this->getStoreEmail(),
            'name' => $this->getStoreName()
        ];

        $emailTemplate = new EmailTemplate($this->scopeConfig, $this->directoryList);

        $body = $this->getBodyEmailBoleto($storeId);

        $vowels = ['{{NAME_USER}}'];

        $body = str_replace($vowels, $data['Customer']['Name'], $body);

        $vowels = ['{{URL_BILLET}}'];

        $body = str_replace($vowels, $billetUrl, $body);

        $message = $emailTemplate->getHtml($body, $storeId);

        $template_vars['message'] = $message;
        $template_vars['subject'] = 'Pedido recebido';

        $template_options = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('empty_email_template')
            ->setTemplateOptions($template_options)
            ->setTemplateVars($template_vars)
            ->setFrom($from)
            ->addTo(
                $data['Customer']['Email'],
                $data['Customer']['Name']
            );

        $ch = curl_init($billetUrl);
        curl_setopt_array($ch, [
            \CURLOPT_SSL_VERIFYPEER => false,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_FOLLOWLOCATION => true
        ]);
        $billet = curl_exec($ch);

        $billetId = $data['Payment']['BoletoNumber'];
        $billetFilename = "/tmp/billet{$billetId}.pdf";
        $billetPdf = fopen($billetFilename, 'w+');
        fwrite($billetPdf, $billet);
        fclose($billetPdf);

        if ($storeId != 11){
            $transport->addAttachment($billetFilename);
        }

        $transport->getTransport()->sendMessage();

        unlink($billetFilename);
    }

    public function getStoreName()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/name',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreUrl()
    {
        return $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreEmail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            ScopeInterface::SCOPE_STORE
        );
    }

    //descontinuado
    public function getOrderConfirmMessage($name)
    {
        return '<p>Olá' . $name . ',</p>
                <p>Seu pedido foi recebido com sucesso.</p>
                <p>Agora falta pouco!</p>
                <p>Faça o download do boleto e realize o pagamento online ou na agência bancária.</p>
                <p>Após a confirmação do pagamento te enviaremos uma notificação.</p>';
    }
}
