<?php
namespace App\Controllers\Front;

use App\Core\Controller;

class ContactController extends Controller
{
    public function index()
    {
        $channels = [
            [
                'title' => 'Contact us',
                'text' => 'Vehicle inquiries, export quotes, and general support.',
                'email' => 'sales@eisenwheels.com',
                'phone' => '090 3350 8523',
                'titleKey' => 'contact.channel.sales.title',
                'textKey' => 'contact.channel.sales.text',
            ],
        ];

        $subjects = [
            ['value' => 'general', 'label' => 'General inquiry', 'key' => 'contact.subject.general'],
            ['value' => 'auction', 'label' => 'Japan auction sourcing', 'key' => 'contact.subject.auction'],
            ['value' => 'shipping', 'label' => 'Shipping & logistics', 'key' => 'contact.subject.shipping'],
            ['value' => 'dealer', 'label' => 'Dealer partnership', 'key' => 'contact.subject.dealer'],
        ];

        $this->view('front/contact', [
            'channels' => $channels,
            'subjects' => $subjects,
        ]);
    }
}
