<?php

namespace App\Modules\Account\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

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
                'key' => 'about',
                'title' => 'About',
                'description' => 'Our story and social accounts',
                'href' => '/account/about',
            ],
            [
                'key' => 'faq',
                'title' => 'FAQ',
                'description' => 'Common shopping questions',
                'href' => '/account/faq',
            ],
            [
                'key' => 'privacy',
                'title' => 'Privacy policy',
                'description' => 'How we handle your information',
                'href' => '/privacy-policy',
            ],
            [
                'key' => 'terms',
                'title' => 'Terms and conditions',
                'description' => 'Rules for using the store',
                'href' => '/terms-and-conditions',
            ],
            [
                'key' => 'shipping',
                'title' => 'Shipping policy',
                'description' => 'Delivery times and charges',
                'href' => '/shipping-policy',
            ],
            [
                'key' => 'refund',
                'title' => 'Refund policy',
                'description' => 'Cancellations and refunds',
                'href' => '/refund-policy',
            ],
        ];
    }

    /**
     * Account links shown to a guest, including sign-in.
     *
     * @return list<array{key: string, title: string, description: string, href: string}>
     */
    public function menuForVisitor(): array
    {
        if (Auth::check()) {
            return $this->menu();
        }

        return [
            [
                'key' => 'login',
                'title' => 'Login',
                'description' => 'Sign in or create an account',
                'href' => '/login',
            ],
            ...$this->menu(),
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
                'isGuest' => false,
            ];
        }

        return [
            'name' => 'Guest',
            'email' => '',
            'phone' => '',
            'memberSince' => '',
            'isGuest' => true,
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
     * @return array{title: string, intro: list<string>, socials: list<array{key: string, name: string, handle: string, followers: string, href: string}>}
     */
    public function about(): array
    {
        $name = (string) config('app.name');
        $handle = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $name));

        return [
            'title' => 'About '.$name,
            'intro' => [
                $name.' is an online store for everyday essentials. Shop fashion, home, and accessories, then check out from your phone in a few taps.',
                'Orders are packed and shipped across India. You can follow each order from your account, and support chat is available when you need a hand.',
                'This is sample about copy. Replace it with your brand story before the store goes live.',
            ],
            'socials' => [
                [
                    'key' => 'instagram',
                    'name' => 'Instagram',
                    'handle' => '@'.$handle,
                    'followers' => '128.4K',
                    'href' => 'https://instagram.com/'.$handle,
                ],
                [
                    'key' => 'facebook',
                    'name' => 'Facebook',
                    'handle' => '@'.$handle,
                    'followers' => '86.2K',
                    'href' => 'https://facebook.com/'.$handle,
                ],
                [
                    'key' => 'telegram',
                    'name' => 'Telegram',
                    'handle' => '@'.$handle,
                    'followers' => '42.8K',
                    'href' => 'https://t.me/'.$handle,
                ],
                [
                    'key' => 'whatsapp',
                    'name' => 'WhatsApp',
                    'handle' => '+91 98765 43210',
                    'followers' => '19.6K',
                    'href' => 'https://wa.me/919876543210',
                ],
            ],
        ];
    }

    /**
     * @return list<array{id: string, title: string, body: string, href: string}>
     */
    public function policies(): array
    {
        return [
            [
                'id' => 'terms',
                'title' => 'Terms and conditions',
                'body' => 'By using this store you agree to shop for personal use, provide accurate account details, and follow applicable laws. Prices, offers, and stock can change without notice.',
                'href' => '/terms-and-conditions',
            ],
            [
                'id' => 'privacy',
                'title' => 'Privacy policy',
                'body' => 'We collect account, order, and device information needed to process purchases. Payment details are handled by our payment gateway. We do not sell personal data.',
                'href' => '/privacy-policy',
            ],
            [
                'id' => 'refund',
                'title' => 'Refund policy',
                'body' => 'Eligible products can be returned within 7 days of delivery. Approved refunds go back to the original payment method within 5-7 business days.',
                'href' => '/refund-policy',
            ],
            [
                'id' => 'shipping',
                'title' => 'Shipping policy',
                'body' => 'Orders usually ship within 24-48 hours. Metro delivery is typically 2-4 days. Other regions may take 5-7 days.',
                'href' => '/shipping-policy',
            ],
        ];
    }

    /**
     * Full policy used by payment-gateway review.
     *
     * @return array{title: string, updatedAt: string, sections: list<array{heading: string, paragraphs: list<string>}>}
     */
    public function legal(string $key): array
    {
        $name = (string) config('app.name');

        return match ($key) {
            'privacy' => [
                'title' => 'Privacy policy',
                'updatedAt' => '1 October 2026',
                'sections' => [
                    [
                        'heading' => 'Who we are',
                        'paragraphs' => [
                            $name.' ("we") operates this online store. This policy explains what information we collect when you browse, create an account, or pay for an order.',
                        ],
                    ],
                    [
                        'heading' => 'Information we collect',
                        'paragraphs' => [
                            'We collect your name, mobile number, email address, delivery addresses, and order history when you create an account or check out.',
                            'We also store basic device and log data, such as browser type and pages visited, to keep the site reliable and to understand which products people view.',
                        ],
                    ],
                    [
                        'heading' => 'Payments',
                        'paragraphs' => [
                            'Card, UPI, and net-banking payments are processed by our payment gateway. We do not store your full card number, CVV, or UPI PIN on our servers.',
                            'The gateway may collect payment details under its own privacy policy to authorize and settle the transaction.',
                        ],
                    ],
                    [
                        'heading' => 'How we use information',
                        'paragraphs' => [
                            'We use your information to create your account, deliver orders, send order updates, prevent fraud, and respond to support requests.',
                            'We do not sell personal information. We share it only with delivery partners, the payment gateway, and authorities when the law requires it.',
                        ],
                    ],
                    [
                        'heading' => 'Your choices',
                        'paragraphs' => [
                            'You can update your profile and addresses from your account. Contact support to ask for a copy of your data or to request deletion where the law allows it.',
                        ],
                    ],
                ],
            ],
            'terms' => [
                'title' => 'Terms and conditions',
                'updatedAt' => '1 October 2026',
                'sections' => [
                    [
                        'heading' => 'Agreement',
                        'paragraphs' => [
                            'By using '.$name.' you agree to these terms. If you do not agree, please do not create an account or place an order.',
                        ],
                    ],
                    [
                        'heading' => 'Accounts and orders',
                        'paragraphs' => [
                            'You must provide a valid name, mobile number, and delivery address. You are responsible for keeping your login details private.',
                            'An order is an offer to buy. We may accept, refuse, or cancel an order if an item is out of stock, the price was listed in error, or we suspect fraud.',
                        ],
                    ],
                    [
                        'heading' => 'Prices and payment',
                        'paragraphs' => [
                            'Prices are shown in Indian rupees and include applicable taxes unless the product page says otherwise. Offers can end without notice.',
                            'Payment is collected through our payment gateway at checkout. The order is confirmed after the gateway reports a successful payment, or when you choose cash on delivery where that option is shown.',
                        ],
                    ],
                    [
                        'heading' => 'Use of the store',
                        'paragraphs' => [
                            'You may shop for personal use. You may not misuse the site, attempt to access another customer\'s account, or interfere with checkout.',
                        ],
                    ],
                ],
            ],
            'shipping' => [
                'title' => 'Shipping policy',
                'updatedAt' => '1 October 2026',
                'sections' => [
                    [
                        'heading' => 'Where we ship',
                        'paragraphs' => [
                            $name.' ships to serviceable pin codes across India. If we cannot deliver to your pin code, checkout will tell you before you pay.',
                        ],
                    ],
                    [
                        'heading' => 'Dispatch and delivery time',
                        'paragraphs' => [
                            'In-stock orders are usually handed to our delivery partner within 24-48 hours of payment confirmation.',
                            'Metro cities are typically delivered in 2-4 business days. Other locations may take 5-7 business days. These times are estimates and can change during sales, weather, or courier delays.',
                        ],
                    ],
                    [
                        'heading' => 'Shipping charges',
                        'paragraphs' => [
                            'Any shipping fee is shown on the cart and checkout page before you pay. Free shipping, when offered, applies only to the orders described in that offer.',
                        ],
                    ],
                    [
                        'heading' => 'Tracking',
                        'paragraphs' => [
                            'After dispatch you can follow the order from Order history. We also send status updates to the mobile number or email on the order.',
                        ],
                    ],
                ],
            ],
            'refund' => [
                'title' => 'Refund policy',
                'updatedAt' => '1 October 2026',
                'sections' => [
                    [
                        'heading' => 'Cancellations',
                        'paragraphs' => [
                            'You can request cancellation from support before the order is dispatched. After dispatch, the order follows the return steps below instead of a cancellation.',
                        ],
                    ],
                    [
                        'heading' => 'Returns',
                        'paragraphs' => [
                            'Most items can be returned within 7 days of delivery if they are unused, with tags and original packaging. Perishable, personal-care, and made-to-order items may be marked non-returnable on the product page.',
                            'If we delivered a damaged or wrong item, contact support with a photo and we will arrange a replacement or a refund.',
                        ],
                    ],
                    [
                        'heading' => 'Refund timeline',
                        'paragraphs' => [
                            'After the return is picked up and checked, we start the refund within 2 business days. Prepaid refunds go back to the original payment method through the payment gateway and usually appear in 5-7 business days.',
                            'Cash on delivery refunds are sent to the bank account or UPI ID you share with support. We will email you when the refund is initiated.',
                        ],
                    ],
                    [
                        'heading' => 'Failed payments',
                        'paragraphs' => [
                            'If money is deducted but the order is not confirmed, the payment gateway usually reverses it automatically within 5-7 business days. Contact support with the payment reference if it does not return.',
                        ],
                    ],
                ],
            ],
            default => throw new InvalidArgumentException('Unknown policy ['.$key.'].'),
        };
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
