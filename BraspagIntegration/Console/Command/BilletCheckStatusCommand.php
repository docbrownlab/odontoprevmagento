<?php

namespace Odontoprev\BraspagIntegration\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Odontoprev\Sales\Model\OrderRepository;
use Odontoprev\Sales\Helper\Braspag as BraspagHelper;

/**
 * Class BilletCheckStatus
 *
 * @package Odontoprev\BraspagIntegration\Console\Command
 */
class BilletCheckStatusCommand extends Command
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

    public function __construct(
        State $state,
        OrderRepository $orderRepository,
        BraspagHelper $braspagHelper
    )
    {
        $this->state = $state;
        $this->orderRepository = $orderRepository;
        $this->braspagHelper = $braspagHelper;

        parent::__construct();
    }

    /**
     * Sets config for cli command
     */
    protected function configure()
    {
        $this->setName('odontoprev:billet:check-status')
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

        $orders = $this->orderRepository->getProcessingBillets();
        $output->writeln('Checking billets status...');

        foreach ($orders as $order) {
            $paymentId = $this->braspagHelper->getBraspagPaymentId($order);

            if (! $paymentId) continue;

            $additionalInformation = $order->getPayment()->getAdditionalInformation();

            $currentStatus = $order->getStatus();

            $output->writeln([
                '----------',
                'Checking order status',
                "Order: {$order->getIncrementId()}",
                "Date: {$order->getCreatedAt()}",
                "Expiration date: {$additionalInformation['expiration_date']}",
                "Order status: {$currentStatus}",
                'Checking status...'
            ]);

            $this->braspagHelper->processPayment($paymentId);

            $updatedOrder = $this->orderRepository->get($order->getEntityId());

            $newStatus = $updatedOrder->getStatus();

            $output->writeln([
                "New order status: {$newStatus}",
                '----------'
            ]);
        }
    }
}
