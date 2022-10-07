<?php
$_['paypal_setting'] = [
    'partner'            => [
        'production' => [
            'partner_id' => 'TY2Q25KP2PX9L',
            'client_id'  => 'AbjxI4a9fMnew8UOMoDFVwSh7h1aeOBaXpd2wcccAnuqecijKIylRnNguGRWDrEPrTYraBQApf_-O3_4'
        ],
        'sandbox'    => [
            'partner_id' => 'EJNHWRJJNB38L',
            'client_id'  => 'AfeIgIr-fIcEucsVXvdq21Ufu0wAALWhgJdVF4ItUK1IZFA9I4JIRdfyJ9vWrd9oi0B6mBGtJYDrlYsG'
        ]
    ],
    'order_status'       => [
        'completed' => [
            'code' => 'completed',
            'name' => 'text_completed_status',
            'id'   => 5
        ],
        'denied'    => [
            'code' => 'denied',
            'name' => 'text_denied_status',
            'id'   => 8
        ],
        'failed'    => [
            'code' => 'failed',
            'name' => 'text_failed_status',
            'id'   => 10
        ],
        'pending'   => [
            'code' => 'pending',
            'name' => 'text_pending_status',
            'id'   => 1
        ],
        'refunded'  => [
            'code' => 'refunded',
            'name' => 'text_refunded_status',
            'id'   => 11
        ],
        'reversed'  => [
            'code' => 'reversed',
            'name' => 'text_reversed_status',
            'id'   => 12
        ],
        'voided'    => [
            'code' => 'voided',
            'name' => 'text_voided_status',
            'id'   => 16
        ]
    ],
    'checkout'           => [
        'express' => [
            'status'       => true,
            'button_align' => 'right',
            'button_size'  => 'large',
            'button_color' => 'gold',
            'button_shape' => 'rect',
            'button_label' => 'paypal'
        ],
        'card'    => [
            'status'          => false,
            'form_align'      => 'right',
            'form_size'       => 'large',
            'secure_status'   => true,
            'secure_scenario' => [
                'undefined'        => 1,
                'error'            => 0,
                'skipped_by_buyer' => 0,
                'failure'          => 0,
                'bypassed'         => 0,
                'attempted'        => 1,
                'unavailable'      => 0,
                'card_ineligible'  => 1
            ]
        ],
        'message' => [
            'status'             => true,
            'message_align'      => 'right',
            'message_size'       => 'large',
            'message_layout'     => 'text',
            'message_text_color' => 'black',
            'message_text_size'  => '12',
            'message_flex_color' => 'blue',
            'message_flex_ratio' => '8x1'
        ]
    ],
    'currency'           => [
        'AUD' => [
            'code'           => 'AUD',
            'name'           => 'text_currency_aud',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'BRL' => [
            'code'           => 'BRL',
            'name'           => 'text_currency_brl',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'CAD' => [
            'code'           => 'CAD',
            'name'           => 'text_currency_cad',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'CZK' => [
            'code'           => 'CZK',
            'name'           => 'text_currency_czk',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'DKK' => [
            'code'           => 'DKK',
            'name'           => 'text_currency_dkk',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'EUR' => [
            'code'           => 'EUR',
            'name'           => 'text_currency_eur',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'HKD' => [
            'code'           => 'HKD',
            'name'           => 'text_currency_hkd',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'HUF' => [
            'code'           => 'HUF',
            'name'           => 'text_currency_huf',
            'decimal_place'  => 0,
            'express_status' => true,
            'card_status'    => true
        ],
        'INR' => [
            'code'           => 'INR',
            'name'           => 'text_currency_inr',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'ILS' => [
            'code'           => 'ILS',
            'name'           => 'text_currency_ils',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'JPY' => [
            'code'           => 'JPY',
            'name'           => 'text_currency_jpy',
            'decimal_place'  => 0,
            'express_status' => true,
            'card_status'    => true
        ],
        'MYR' => [
            'code'           => 'MYR',
            'name'           => 'text_currency_myr',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'MXN' => [
            'code'           => 'MXN',
            'name'           => 'text_currency_mxn',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'TWD' => [
            'code'           => 'TWD',
            'name'           => 'text_currency_twd',
            'decimal_place'  => 0,
            'express_status' => true,
            'card_status'    => false
        ],
        'NZD' => [
            'code'           => 'NZD',
            'name'           => 'text_currency_nzd',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'NOK' => [
            'code'           => 'NOK',
            'name'           => 'text_currency_nok',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'PHP' => [
            'code'           => 'PHP',
            'name'           => 'text_currency_php',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'PLN' => [
            'code'           => 'PLN',
            'name'           => 'text_currency_pln',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'GBP' => [
            'code'           => 'GBP',
            'name'           => 'text_currency_gbp',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'RUB' => [
            'code'           => 'RUB',
            'name'           => 'text_currency_rub',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'SGD' => [
            'code'           => 'SGD',
            'name'           => 'text_currency_sgd',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'SEK' => [
            'code'           => 'SEK',
            'name'           => 'text_currency_sek',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'CHF' => [
            'code'           => 'CHF',
            'name'           => 'text_currency_chf',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ],
        'THB' => [
            'code'           => 'THB',
            'name'           => 'text_currency_thb',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => false
        ],
        'USD' => [
            'code'           => 'USD',
            'name'           => 'text_currency_usd',
            'decimal_place'  => 2,
            'express_status' => true,
            'card_status'    => true
        ]
    ],
    'button_align'       => [
        'left'   => [
            'code' => 'left',
            'name' => 'text_align_left'
        ],
        'center' => [
            'code' => 'center',
            'name' => 'text_align_center'
        ],
        'right'  => [
            'code' => 'right',
            'name' => 'text_align_right'
        ]
    ],
    'button_size'        => [
        'small'      => [
            'code' => 'small',
            'name' => 'text_small'
        ],
        'medium'     => [
            'code' => 'medium',
            'name' => 'text_medium'
        ],
        'large'      => [
            'code' => 'large',
            'name' => 'text_large'
        ],
        'responsive' => [
            'code' => 'responsive',
            'name' => 'text_responsive'
        ]
    ],
    'button_color'       => [
        'gold'   => [
            'code' => 'gold',
            'name' => 'text_gold'
        ],
        'blue'   => [
            'code' => 'blue',
            'name' => 'text_blue'
        ],
        'silver' => [
            'code' => 'silver',
            'name' => 'text_silver'
        ],
        'white'  => [
            'code' => 'white',
            'name' => 'text_white'
        ],
        'black'  => [
            'code' => 'black',
            'name' => 'text_black'
        ]
    ],
    'button_shape'       => [
        'pill' => [
            'code' => 'pill',
            'name' => 'text_pill'
        ],
        'rect' => [
            'code' => 'rect',
            'name' => 'text_rect'
        ]
    ],
    'button_label'       => [
        'checkout'    => [
            'code' => 'checkout',
            'name' => 'text_checkout'
        ],
        'pay'         => [
            'code' => 'pay',
            'name' => 'text_pay'
        ],
        'buynow'      => [
            'code' => 'buynow',
            'name' => 'text_buy_now'
        ],
        'paypal'      => [
            'code' => 'paypal',
            'name' => 'text_pay_pal'
        ],
        'installment' => [
            'code' => 'installment',
            'name' => 'text_installment'
        ]
    ],
    'button_width'       => [
        'small'      => '200px',
        'medium'     => '250px',
        'large'      => '350px',
        'responsive' => ''
    ],
    'form_align'         => [
        'left'   => [
            'code' => 'left',
            'name' => 'text_align_left'
        ],
        'center' => [
            'code' => 'center',
            'name' => 'text_align_center'
        ],
        'right'  => [
            'code' => 'right',
            'name' => 'text_align_right'
        ]
    ],
    'form_size'          => [
        'medium'     => [
            'code' => 'medium',
            'name' => 'text_medium'
        ],
        'large'      => [
            'code' => 'large',
            'name' => 'text_large'
        ],
        'responsive' => [
            'code' => 'responsive',
            'name' => 'text_responsive'
        ]
    ],
    'form_width'         => [
        'medium'     => '250px',
        'large'      => '350px',
        'responsive' => ''
    ],
    'secure_scenario'    => [
        'undefined'        => [
            'code'        => 'undefined',
            'name'        => 'text_3ds_undefined',
            'error'       => 'error_3ds_undefined',
            'recommended' => 1
        ],
        'error'            => [
            'code'        => 'error',
            'name'        => 'text_3ds_error',
            'error'       => 'error_3ds_undefined',
            'recommended' => 0
        ],
        'skipped_by_buyer' => [
            'code'        => 'skipped_by_buyer',
            'name'        => 'text_3ds_skipped_by_buyer',
            'error'       => 'error_3ds_skipped_by_buyer',
            'recommended' => 0
        ],
        'failure'          => [
            'code'        => 'failure',
            'name'        => 'text_3ds_failure',
            'error'       => 'error_3ds_failure',
            'recommended' => 0
        ],
        'bypassed'         => [
            'code'        => 'bypassed',
            'name'        => 'text_3ds_bypassed',
            'error'       => 'error_3ds_bypassed',
            'recommended' => 0
        ],
        'attempted'        => [
            'code'        => 'attempted',
            'name'        => 'text_3ds_attempted',
            'error'       => 'error_3ds_attempted',
            'recommended' => 1
        ],
        'unavailable'      => [
            'code'        => 'unavailable',
            'name'        => 'text_3ds_unavailable',
            'error'       => 'error_3ds_unavailable',
            'recommended' => 0
        ],
        'card_ineligible'  => [
            'code'        => 'card_ineligible',
            'name'        => 'text_3ds_card_ineligible',
            'error'       => 'error_3ds_card_ineligible',
            'recommended' => 1
        ]
    ],
    'message_align'      => [
        'left'   => [
            'code' => 'left',
            'name' => 'text_align_left'
        ],
        'center' => [
            'code' => 'center',
            'name' => 'text_align_center'
        ],
        'right'  => [
            'code' => 'right',
            'name' => 'text_align_right'
        ]
    ],
    'message_size'       => [
        'small'      => [
            'code' => 'small',
            'name' => 'text_small'
        ],
        'medium'     => [
            'code' => 'medium',
            'name' => 'text_medium'
        ],
        'large'      => [
            'code' => 'large',
            'name' => 'text_large'
        ],
        'responsive' => [
            'code' => 'responsive',
            'name' => 'text_responsive'
        ]
    ],
    'message_width'      => [
        'small'      => '200px',
        'medium'     => '250px',
        'large'      => '350px',
        'responsive' => ''
    ],
    'message_layout'     => [
        'text' => [
            'code' => 'text',
            'name' => 'text_text'
        ],
        'flex' => [
            'code' => 'flex',
            'name' => 'text_flex'
        ]
    ],
    'message_text_color' => [
        'black' => [
            'code' => 'black',
            'name' => 'text_black'
        ],
        'white' => [
            'code' => 'white',
            'name' => 'text_white'
        ]
    ],
    'message_text_size'  => [
        '10',
        '11',
        '12',
        '13',
        '14',
        '15',
        '16'
    ],
    'message_flex_color' => [
        'blue'  => [
            'code' => 'blue',
            'name' => 'text_blue'
        ],
        'black' => [
            'code' => 'black',
            'name' => 'text_black'
        ],
        'white' => [
            'code' => 'white',
            'name' => 'text_white'
        ]
    ],
    'message_flex_ratio' => [
        '1x1',
        '1x4',
        '8x1',
        '20x1'
    ]
];