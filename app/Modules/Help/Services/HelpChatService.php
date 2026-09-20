<?php

namespace App\Modules\Help\Services;

class HelpChatService
{
    /**
     * @return array{welcome: string, quickReplies: list<array{id: string, label: string}>, replies: array<string, string>}
     */
    public function conversation(): array
    {
        return [
            'welcome' => 'Hi! I am your shopping assistant. Ask about orders, delivery, returns, or payments - or tap a topic below.',
            'quickReplies' => [
                ['id' => 'orders', 'label' => 'Track my order'],
                ['id' => 'delivery', 'label' => 'Delivery times'],
                ['id' => 'returns', 'label' => 'Returns & refunds'],
                ['id' => 'payments', 'label' => 'Payments'],
            ],
            'replies' => [
                'orders' => 'You can track orders from Account > My orders. Share your order ID here if you need help locating a shipment.',
                'delivery' => 'Most metro orders arrive in 2-4 days. Remote areas may take 5-7 days. You will get SMS and email updates once the package ships.',
                'returns' => 'Eligible items can be returned within 7 days of delivery. Open the order, tap Return, and follow the pickup steps. Refunds post in 5-7 business days after pickup.',
                'payments' => 'We accept UPI, cards, net banking, and cash on delivery where available. Failed payments are auto-reversed by your bank in 3-5 business days.',
                'default' => 'Thanks for your message. A support agent will follow up soon. Meanwhile try Orders, Delivery, Returns, or Payments from the suggestions.',
            ],
        ];
    }
}
