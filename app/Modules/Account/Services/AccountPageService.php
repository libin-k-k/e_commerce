<?php

namespace App\Modules\Account\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AccountPageService
{
    /**
     * @return list<array{key: string, title: string, description: string, href: string}>
     */
    public function menu(): array
    {
        return [
            [
                'key' => 'profile',
                'title' => 'Profile',
                'description' => 'Name, phone, and email',
                'href' => '/account/profile',
            ],
            [
                'key' => 'addresses',
                'title' => 'Addresses',
                'description' => 'Delivery and billing addresses',
                'href' => '/account/addresses',
            ],
            [
                'key' => 'orders',
                'title' => 'Order history',
                'description' => 'Track and review past orders',
                'href' => '/account/orders',
            ],
            [
                'key' => 'faq',
                'title' => 'FAQ',
                'description' => 'Common shopping questions',
                'href' => '/account/faq',
            ],
            [
                'key' => 'policies',
                'title' => 'Terms & policies',
                'description' => 'Terms, privacy, and returns',
                'href' => '/account/policies',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profile(): array
    {
        $user = $this->currentUser();

        if ($user !== null) {
            return [
                'name' => $user->name,
                'email' => $user->email ?? '',
                'phone' => $user->mobile ?? '',
                'memberSince' => $user->created_at?->format('M Y') ?? '',
            ];
        }

        return [
            'name' => 'Guest',
            'email' => '',
            'phone' => '',
            'memberSince' => '',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function addresses(): array
    {
        $user = $this->currentUser();

        if ($user === null) {
            return [];
        }

        return $user->addresses
            ->map(function ($address) use ($user): array {
                return [
                    ...$address->toAccountArray(),
                    'name' => $user->name,
                    'phone' => $user->mobile,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function orders(): array
    {
        return [
            [
                'id' => 'OC-10428',
                'placedAt' => '12 Sep 2026',
                'status' => 'Delivered',
                'statusTone' => 'success',
                'total' => '₹79.00',
                'itemCount' => 1,
                'itemsSummary' => 'Wireless headphones',
                'href' => '/products/wireless-headphones',
            ],
            [
                'id' => 'OC-10391',
                'placedAt' => '28 Aug 2026',
                'status' => 'In transit',
                'statusTone' => 'info',
                'total' => '₹81.00',
                'itemCount' => 2,
                'itemsSummary' => 'Minimal desk lamp, Ceramic mug set',
                'href' => '/products/minimal-desk-lamp',
            ],
            [
                'id' => 'OC-10255',
                'placedAt' => '04 Aug 2026',
                'status' => 'Cancelled',
                'statusTone' => 'muted',
                'total' => '₹36.00',
                'itemCount' => 1,
                'itemsSummary' => 'Everyday tote bag',
                'href' => '/products/everyday-tote-bag',
            ],
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function faq(): array
    {
        return [
            [
                'question' => 'How do I track my order?',
                'answer' => 'Open Order history, select an order, and follow the tracking updates. You also get SMS and email alerts when the status changes.',
            ],
            [
                'question' => 'What is the return window?',
                'answer' => 'Most items can be returned within 7 days of delivery if they are unused and in original packaging. Some categories may have different rules listed on the product page.',
            ],
            [
                'question' => 'Which payment methods are supported?',
                'answer' => 'UPI, credit and debit cards, net banking, and cash on delivery where available.',
            ],
            [
                'question' => 'How do I change my delivery address?',
                'answer' => 'Go to Addresses, edit an existing address or add a new one, then mark it as default before placing your next order.',
            ],
            [
                'question' => 'When will I get my refund?',
                'answer' => 'Refunds are usually processed within 5-7 business days after the returned item is picked up and verified.',
            ],
        ];
    }

    /**
     * @return list<array{id: string, title: string, body: string}>
     */
    public function policies(): array
    {
        return [
            [
                'id' => 'terms',
                'title' => 'Terms of use',
                'body' => 'By using this store you agree to shop for personal use, provide accurate account details, and follow applicable laws. Prices, offers, and stock can change without notice. We may refuse or cancel orders in cases of pricing errors, suspected fraud, or supply limits.',
            ],
            [
                'id' => 'privacy',
                'title' => 'Privacy policy',
                'body' => 'We collect account, order, and device information needed to process purchases and improve the shopping experience. Payment details are handled by secure payment partners. We do not sell personal data. Contact support to request access or deletion where required by law.',
            ],
            [
                'id' => 'returns',
                'title' => 'Returns & refunds',
                'body' => 'Eligible products can be returned within 7 days of delivery. Items must be unused with tags and packaging intact. Once the return is approved and picked up, refunds are issued to the original payment method within 5-7 business days.',
            ],
            [
                'id' => 'shipping',
                'title' => 'Shipping policy',
                'body' => 'Orders usually ship within 24-48 hours. Metro delivery is typically 2-4 days; other regions may take 5-7 days. Delivery timelines are estimates and can vary during peak seasons or weather delays.',
            ],
        ];
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
