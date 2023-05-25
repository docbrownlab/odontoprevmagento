<?php

namespace Odontoprev\BraspagIntegration\Console\Command;

use Odontoprev\Sales\Model\OrderRepository;
use Odontoprev\Sales\Mail\Template\TransportBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\State;

/**
 * Envia email para usuários com boletos vencidos no dia anterior
 */
class NotifyExpiredBilletsCommand extends Command
{

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        OrderRepository $orderRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        State $state
    )
    {
        parent::__construct();

        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->state = $state;
    }

    /**
     * Sets config for cli command
     */
    protected function configure()
    {
        $this->setName('odontoprev:billet:notify-expired')
            ->setDescription('Notify expired billets via email');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode('adminhtml');
        }

        $orders = $this->orderRepository->getExpiredBillets();
        $totalItems = count($orders);

        $output->writeln("Orders found: {$totalItems}");

        foreach ($orders as $item) {
            $additionalInformation = $item->getPayment()->getAdditionalInformation();

            if (! isset($additionalInformation['expiration_date'])) {
                continue;
            }

            $output->writeln("Transaction ID: {$item->getEntityId()}");
            $output->writeln("Date: {$item->getCreatedAt()}");
            $output->writeln("Expiration date: {$additionalInformation['expiration_date']}");
            $output->writeln("Order status: {$item->getStatus()}");

            $mailData = [
                'Customer' => [
                    'Name' => $item->getCustomerFirstname(),
                    'Email' => $item->getCustomerEmail()
                ]
            ];

            $output->writeln('Sending email');
            try {
                $this->sendEmail($mailData);
                $output->writeln('Email sent');
            } catch (\Exception $e) {
                $output->writeln('Error sending email');
                $output->writeln($e->getMessage());
            }

            $output->writeln('---------------');
        }
    }

    private function sendEmail(array $data)
    {
        $from = [
            'email' => $this->getStoreEmail(),
            'name' => $this->getStoreName()
        ];

        $template_vars = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'odpv_cms_url' => $this->scopeConfig->getValue(
                'general_settings/odontoprev_drupal/host',
                ScopeInterface::SCOPE_STORE
            ),
            'social_media_link_facebook' => 'https://www.facebook.com/OdontoPrevOficial',
            'social_media_link_linkedin' => 'https://www.linkedin.com/company/odontoprev',
            'social_media_link_youtube' => 'https://www.youtube.com/user/OdontoPrevOficial',
            'customer_name' => $data['Customer']['Name'],
            'message'   => $this->getNotificationMessage()
        ];

        $template_options = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('expired_billet_email')
            ->setTemplateOptions($template_options)
            ->setTemplateVars($template_vars)
            ->setFrom($from)
            ->addTo(
                $data['Customer']['Email'],
                $data['Customer']['Name']
            );

        if (isset($data['Billet'])) {
            $transport->addAttachment($data['Billet']);
        }

        $transport->getTransport()->sendMessage();
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

    public function getNotificationMessage()
    {
        return <<<HTML
<p>Puxa! Acho que você se esqueceu de pagar seu boleto e por isso sua compra não foi concluída.</p>
<p>Não fique desprotegido. Volte quando quiser para ver as opções que a OdontoPrev oferece para você, customize seu plano e tenha uma boca saudável... Afinal, SUA BOCA FALA POR VOCÊ!</p>
<p>Caso já tenha efetuado o pagamento do boleto, desconsidere este e-mail.</p>
HTML;
    }
}
