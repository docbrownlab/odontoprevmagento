<?php

namespace Odontoprev\BraspagIntegration\Console\Command;

use Magento\Framework\App\State;
use Odontoprev\Sales\Helper\Payment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Odontoprev\Sales\Model\OrderRepository;
use Odontoprev\Sales\Helper\Braspag as BraspagHelper;
use Odontoprev\Sales\Helper\Payment as PaymentHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Odontoprev\Sales\Mail\Template\TransportBuilder;

/**
 * Class CheckPaidBillets
 *
 * @package Odontoprev\BraspagIntegration\Console\Command
 */
class CheckPaidBilletsCommand extends Command
{

    /**
     * @var State
     */
    private $state;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var BraspagHelper
     */
    private $braspagHelper;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var array Lista de labels dos status
     */
    private $braspagStatusLabels = [
        BraspagHelper::NOT_FINISHED => 'Not finished',
        BraspagHelper::AUTHORIZED => 'Authorized',
        BraspagHelper::PAYMENT_CONFIRMED => 'Payment confirmed',
        BraspagHelper::DENIED => 'Denied',
        BraspagHelper::VOIDED => 'Voided',
        BraspagHelper::REFUNDED => 'Refunded',
        BraspagHelper::PENDING => 'Pending',
        BraspagHelper::ABORTED => 'Aborted',
        BraspagHelper::SCHEDULED => 'Scheduled'
    ];

    public function __construct(
        State $state,
        OrderRepository $orderRepository,
        BraspagHelper $braspagHelper,
        PaymentHelper $paymentHelper,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder
    )
    {
        $this->state = $state;
        $this->orderRepository = $orderRepository;
        $this->braspagHelper = $braspagHelper;
        $this->paymentHelper = $paymentHelper;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;

        parent::__construct();
    }

    /**
     * Sets config for cli command
     */
    protected function configure()
    {
        $this->setName('odontoprev:billet:check-paid-billets')
            ->setDescription('Check billets status');
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

        $since = new \DateTime('-1 day');
        $since->setTime(0, 0, 0);

        $output->writeln("Checking billets paid since {$since->format('Y-m-d H:i:s')}");

        $orders = $this->orderRepository->getPaidBillets($since);

        foreach ($orders as $order)
        {
            $output->writeln('----------');
            $output->writeln("Checking order {$order->getId()}");
            $paymentId = $this->braspagHelper->getBraspagPaymentId($order);

            try {
                $braspagData = $this->paymentHelper->getBraspagSales($paymentId);
                $paymentStatus = $braspagData->Payment->Status;

                $paymentStatusLabel = $this->braspagStatusLabels[$paymentStatus];

                $output->writeln("Status: {$paymentStatusLabel}");

                if ($paymentStatus == BraspagHelper::AUTHORIZED) {
                    /*
                     * Authorized: boleto gerado, mas não pago
                     * Status não deveria ser "Complete"
                     */

                    $output->writeln([
                        '<error>Billet was not paid, but has "Complete" status</error>',
                        '<error>Notifying administrators</error>'
                    ]);

                    $this->sendEmail([
                        'proposalId' => $order->getIncrementId(),
                        'orderId' => $order->getId()
                    ]);

                    $output->writeln('Mail sent.');
                }
            } catch (\Exception $e) {
                $output->writeln("<error>Unable to fetch order data: {$e->getMessage()}</error>");
            }

            $output->writeln('----------');
        }
    }


    private function getStoreName()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/name',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getStoreUrl()
    {
        return $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getStoreEmail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            ScopeInterface::SCOPE_STORE
        );
    }

    private function sendEmail(array $data)
    {
        $from = [
            'email' => $this->getStoreEmail(),
            'name' => $this->getStoreName()
        ];

        $message = <<<MESSAGE
O pedido {$data['proposalId']} está com o status 'Complete',
porém não há confirmação da Braspag sobre o pagamento.

<a href="{$this->getStoreUrl()}/admin/sales/order/view/order_id/{$data['orderId']}/">Ver pedido no Magento</a>
MESSAGE;
;

        $template_vars = [
            'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
            'message' => $message
        ];

        $template_options = [
            'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('invalid_paid_billet_email')
            ->setTemplateOptions($template_options)
            ->setTemplateVars($template_vars)
            ->setFrom($from)
            ->addTo(
                [
                    'Eduardo Alvarez' => 'eduardo.alvarez@justdigital.com.br',
                    'Fábio Silva' => 'fabio.silva@odontoprev.com.br',
                    'Roberta Raffaelli' => 'roberta.raffaelli@odontoprev.com.br',
                    'Marcelo Oliveira' => 'marcelo.oliveira@odontoprev.com.br'
                ]
            );

        $transport->getTransport()->sendMessage();
    }
}
