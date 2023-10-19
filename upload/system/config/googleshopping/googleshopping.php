<?php
$_['advertise_google_push_limit'] = 1000; // No more than 1000
$_['advertise_google_report_limit'] = 1000; // No more than 1000
$_['advertise_google_product_status_limit'] = 1000; // No more than 1000

// An empty array means it is always required.
// An array with values means it is required only in these specific cases
$_['advertise_google_country_required_fields'] = [
    'google_product_category' => [
        'countries'      => [],
        'selected_field' => null
    ],
    'condition' => [
        'countries'      => [],
        'selected_field' => null
    ],
    'adult' => [
        'countries'      => [],
        'selected_field' => null
    ],
    'multipack' => [
        'countries' => [
			'AU',
			'BR',
			'CZ',
			'FR',
			'DE',
			'IT',
			'JP',
			'NL',
			'ES',
			'CH',
			'GB',
			'US'
		],
        'selected_field' => null
    ],
    'is_bundle' => [
        'countries' => [
			'AU',
			'BR',
			'CZ',
			'FR',
			'DE',
			'IT',
			'JP',
			'NL',
			'ES',
			'CH',
			'GB',
			'US'
		],
        'selected_field' => null
    ],
    'age_group' => [
        'countries' => [
			'BR',
			'FR',
			'DE',
			'JP',
			'GB',
			'US'
		],
        'selected_field' => [
            'google_product_category' => [
				'1604',
				'178',
				'3032',
				'201',
				'187'
			]
        ]
    ],
    'color' => [
        'countries' => [
			'BR',
			'FR',
			'DE',
			'JP',
			'GB',
			'US'
		],
        'selected_field' => [
            'google_product_category' => [
				'1604',
				'178',
				'3032',
				'201',
				'187'
			]
        ]
    ],
    'gender' => [
        'countries' => [
			'BR',
			'FR',
			'DE',
			'JP',
			'GB',
			'US'
		],
        'selected_field' => [
            'google_product_category' => [
				'1604',
				'178',
				'3032',
				'201',
				'187'
			]
        ]
    ],
    'size' => [
        'countries' => [
			'BR',
			'FR',
			'DE',
			'JP',
			'GB',
			'US'
		],
        'selected_field' => [
            'google_product_category' => [
				'1604',
				'187'
			]
        ]
    ],
    'size_type' => [
        'countries' => [
			'BR',
			'FR',
			'DE',
			'JP',
			'GB',
			'US'
		],
        'selected_field' => [
            'google_product_category' => [
				'1604',
				'187'
			]
        ]
    ],
    'size_system' => [
        'countries' => [
			'BR',
			'FR',
			'DE',
			'JP',
			'GB',
			'US'
		],
        'selected_field' => [
            'google_product_category' => [
				'1604',
				'187'
			]
        ]
    ]
];

$_['advertise_google_tax_usa_states'] = [
    '21132' => 'Alaska',
    '21133' => 'Alabama',
    '21135' => 'Arkansas',
    '21136' => 'Arizona',
    '21137' => 'California',
    '21138' => 'Colorado',
    '21139' => 'Connecticut',
    '21140' => 'District of Columbia',
    '21141' => 'Delaware',
    '21142' => 'Florida',
    '21143' => 'Georgia',
    '21144' => 'Hawaii',
    '21145' => 'Iowa',
    '21146' => 'Idaho',
    '21147' => 'Illinois',
    '21148' => 'Indiana',
    '21149' => 'Kansas',
    '21150' => 'Kentucky',
    '21151' => 'Louisiana',
    '21152' => 'Massachusetts',
    '21153' => 'Maryland',
    '21154' => 'Maine',
    '21155' => 'Michigan',
    '21156' => 'Minnesota',
    '21157' => 'Missouri',
    '21158' => 'Mississippi',
    '21159' => 'Montana',
    '21160' => 'North Carolina',
    '21161' => 'North Dakota',
    '21162' => 'Nebraska',
    '21163' => 'New Hampshire',
    '21164' => 'New Jersey',
    '21165' => 'New Mexico',
    '21166' => 'Nevada',
    '21167' => 'New York',
    '21168' => 'Ohio',
    '21169' => 'Oklahoma',
    '21170' => 'Oregon',
    '21171' => 'Pennsylvania',
    '21172' => 'Rhode Island',
    '21173' => 'South Carolina',
    '21174' => 'South Dakota',
    '21175' => 'Tennessee',
    '21176' => 'Texas',
    '21177' => 'Utah',
    '21178' => 'Virginia',
    '21179' => 'Vermont',
    '21180' => 'Washington',
    '21182' => 'Wisconsin',
    '21183' => 'West Virginia',
    '21184' => 'Wyoming'
];

$_['advertise_google_google_product_categories'] = [
    '0'    => 'Other (Not on the list)',
    '1604' => 'Apparel & Accessories > Clothing',
    '178'  => 'Apparel & Accessories > Clothing Accessories > Sunglasses',
    '3032' => 'Apparel & Accessories > Handbags, Wallets & Cases > Handbags',
    '201'  => 'Apparel & Accessories > Jewelry > Watches',
    '187'  => 'Apparel & Accessories > Shoes',
    '784'  => 'Media > Books',
    '839'  => 'Media > DVDs & Videos',
    '855'  => 'Media > Music & Sound Recordings',
    '1279' => 'Software > Video Game Software'
];

$_['advertise_google_size_systems'] = [
	'AU',
	'BR',
	'CN',
	'DE',
	'EU',
	'FR',
	'IT',
	'JP',
	'MEX',
	'UK',
	'US'
];

$_['advertise_google_reporting_intervals'] = [
    'TODAY',
    'YESTERDAY',
    'LAST_7_DAYS',
    'LAST_WEEK',
    'LAST_WEEK_SUN_SAT',
    'LAST_BUSINESS_WEEK',
    'LAST_14_DAYS',
    'LAST_30_DAYS',
    'THIS_WEEK_MON_TODAY',
    'THIS_WEEK_SUN_TODAY',
    'THIS_MONTH'
];

$_['advertise_google_reporting_intervals_default'] = 'LAST_30_DAYS';

// https://support.google.com/adwords/answer/2454022?hl=en&co=ADWORDS.IsAWNCustomer%3Dfalse
$_['advertise_google_countries'] = [
    'AR' => "Argentina",
    'AU' => "Australia",
    'AT' => "Austria",
    'BE' => "Belgium",
    'BR' => "Brazil",
    'CA' => "Canada",
    'CL' => "Chile",
    'CO' => "Colombia",
    'CZ' => "Czechia",
    'DK' => "Denmark",
    'FR' => "France",
    'DE' => "Germany",
    'HK' => "Hong Kong",
    'IN' => "India",
    'ID' => "Indonesia",
    'IE' => "Ireland",
    'IL' => "Israel",
    'IT' => "Italy",
    'JP' => "Japan",
    'MY' => "Malaysia",
    'MX' => "Mexico",
    'NL' => "Netherlands",
    'NZ' => "New Zealand",
    'NO' => "Norway",
    'PH' => "Philippines",
    'PL' => "Poland",
    'PT' => "Portugal",
    'RU' => "Russia",
    'SA' => "Saudi Arabia",
    'SG' => "Singapore",
    'ZA' => "South Africa",
    'KR' => "South Korea",
    'ES' => "Spain",
    'SE' => "Sweden",
    'CH' => "Switzerland",
    'TW' => "Taiwan",
    'TH' => "Thailand",
    'TR' => "Turkey",
    'UA' => "Ukraine",
    'AE' => "United Arab Emirates",
    'GB' => "United Kingdom",
    'US' => "United States",
    'VN' => "Vietnam"
];

// https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
$_['advertise_google_languages'] = [
    'ab' => "Abkhazian",
    'aa' => "Afar",
    'af' => "Afrikaans",
    'ak' => "Akan",
    'sq' => "Albanian",
    'am' => "Amharic",
    'ar' => "Arabic",
    'an' => "Aragonese",
    'hy' => "Armenian",
    'as' => "Assamese",
    'av' => "Avaric",
    'ae' => "Avestan",
    'ay' => "Aymara",
    'az' => "Azerbaijani",
    'bm' => "Bambara",
    'ba' => "Bashkir",
    'eu' => "Basque",
    'be' => "Belarusian",
    'bn' => "Bengali",
    'bh' => "Bihari languages",
    'bi' => "Bislama",
    'bs' => "Bosnian",
    'br' => "Breton",
    'bg' => "Bulgarian",
    'my' => "Burmese",
    'ca' => "Catalan, Valencian",
    'ch' => "Chamorro",
    'ce' => "Chechen",
    'ny' => "Chichewa, Chewa, Nyanja",
    'zh' => "Chinese",
    'cv' => "Chuvash",
    'kw' => "Cornish",
    'co' => "Corsican",
    'cr' => "Cree",
    'hr' => "Croatian",
    'cs' => "Czech",
    'da' => "Danish",
    'dv' => "Divehi, Dhivehi, Maldivian",
    'nl' => "Dutch, Flemish",
    'dz' => "Dzongkha",
    'en' => "English",
    'eo' => "Esperanto",
    'et' => "Estonian",
    'ee' => "Ewe",
    'fo' => "Faroese",
    'fj' => "Fijian",
    'fl' => "Filipino",
    'fi' => "Finnish",
    'fr' => "French",
    'ff' => "Fulah",
    'gl' => "Galician",
    'ka' => "Georgian",
    'de' => "German",
    'el' => "Greek (modern)",
    'gn' => "Guaraní",
    'gu' => "Gujarati",
    'ht' => "Haitian, Haitian Creole",
    'ha' => "Hausa",
    'he' => "Hebrew (modern)",
    'hz' => "Herero",
    'hi' => "Hindi",
    'ho' => "Hiri Motu",
    'hu' => "Hungarian",
    'ia' => "Interlingua",
    'id' => "Indonesian",
    'ie' => "Interlingue",
    'ga' => "Irish",
    'ig' => "Igbo",
    'ik' => "Inupiaq",
    'io' => "Ido",
    'is' => "Icelandic",
    'it' => "Italian",
    'iu' => "Inuktitut",
    'ja' => "Japanese",
    'jv' => "Javanese",
    'kl' => "Kalaallisut, Greenlandic",
    'kn' => "Kannada",
    'kr' => "Kanuri",
    'ks' => "Kashmiri",
    'kk' => "Kazakh",
    'km' => "Central Khmer",
    'ki' => "Kikuyu, Gikuyu",
    'rw' => "Kinyarwanda",
    'ky' => "Kirghiz, Kyrgyz",
    'kv' => "Komi",
    'kg' => "Kongo",
    'ko' => "Korean",
    'ku' => "Kurdish",
    'kj' => "Kuanyama, Kwanyama",
    'la' => "Latin",
    'lb' => "Luxembourgish, Letzeburgesch",
    'lg' => "Ganda",
    'li' => "Limburgan, Limburger, Limburgish",
    'ln' => "Lingala",
    'lo' => "Lao",
    'lt' => "Lithuanian",
    'lu' => "Luba-Katanga",
    'lv' => "Latvian",
    'gv' => "Manx",
    'mk' => "Macedonian",
    'mg' => "Malagasy",
    'ms' => "Malay",
    'ml' => "Malayalam",
    'mt' => "Maltese",
    'mi' => "Maori",
    'mr' => "Marathi",
    'mh' => "Marshallese",
    'mn' => "Mongolian",
    'na' => "Nauru",
    'nv' => "Navajo, Navaho",
    'nd' => "North Ndebele",
    'ne' => "Nepali",
    'ng' => "Ndonga",
    'nb' => "Norwegian Bokmål",
    'nn' => "Norwegian Nynorsk",
    'no' => "Norwegian",
    'ii' => "Sichuan Yi, Nuosu",
    'nr' => "South Ndebele",
    'oc' => "Occitan",
    'oj' => "Ojibwa",
    'cu' => "Church Slavic, Church Slavonic, Old Church Slavonic, Old Slavonic, Old Bulgarian",
    'om' => "Oromo",
    'or' => "Oriya",
    'os' => "Ossetian, Ossetic",
    'pa' => "Panjabi, Punjabi",
    'pi' => "Pali",
    'fa' => "Persian",
    'pl' => "Polish",
    'ps' => "Pashto, Pushto",
    'pt' => "Portuguese",
    'qu' => "Quechua",
    'rm' => "Romansh",
    'rn' => "Rundi",
    'ro' => "Romanian, Moldavian, Moldovan",
    'ru' => "Russian",
    'sa' => "Sanskrit",
    'sc' => "Sardinian",
    'sd' => "Sindhi",
    'se' => "Northern Sami",
    'sm' => "Samoan",
    'sg' => "Sango",
    'sr' => "Serbian",
    'gd' => "Gaelic, Scottish Gaelic",
    'sn' => "Shona",
    'si' => "Sinhala, Sinhalese",
    'sk' => "Slovak",
    'sl' => "Slovenian",
    'so' => "Somali",
    'st' => "Southern Sotho",
    'es' => "Spanish, Castilian",
    'su' => "Sundanese",
    'sw' => "Swahili",
    'ss' => "Swati",
    'sv' => "Swedish",
    'ta' => "Tamil",
    'te' => "Telugu",
    'tg' => "Tajik",
    'th' => "Thai",
    'ti' => "Tigrinya",
    'bo' => "Tibetan",
    'tk' => "Turkmen",
    'tl' => "Tagalog",
    'tn' => "Tswana",
    'to' => "Tongan (Tonga Islands)",
    'tr' => "Turkish",
    'ts' => "Tsonga",
    'tt' => "Tatar",
    'tw' => "Twi",
    'ty' => "Tahitian",
    'ug' => "Uighur, Uyghur",
    'uk' => "Ukrainian",
    'ur' => "Urdu",
    'uz' => "Uzbek",
    've' => "Venda",
    'vi' => "Vietnamese",
    'vo' => "Volapük",
    'wa' => "Walloon",
    'cy' => "Welsh",
    'wo' => "Wolof",
    'fy' => "Western Frisian",
    'xh' => "Xhosa",
    'yi' => "Yiddish",
    'yo' => "Yoruba",
    'za' => "Zhuang, Chuang",
    'zu' => "Zulu"
];

$_['advertise_google_currencies'] = [
    "AED"  => "United Arab Emirates Dirham",
    "AFN"  => "Afghanistan Afghani",
    "ALL"  => "Albania Lek",
    "AMD"  => "Armenia Dram",
    "ANG"  => "Netherlands Antilles Guilder",
    "AOA"  => "Angola Kwanza",
    "ARS"  => "Argentina Peso",
    "AUD"  => "Australia Dollar",
    "AWG"  => "Aruba Guilder",
    "AZN"  => "Azerbaijan Manat",
    "BAM"  => "Bosnia and Herzegovina Convertible Marka",
    "BBD"  => "Barbados Dollar",
    "BDT"  => "Bangladesh Taka",
    "BGN"  => "Bulgaria Lev",
    "BHD"  => "Bahrain Dinar",
    "BIF"  => "Burundi Franc",
    "BMD"  => "Bermuda Dollar",
    "BND"  => "Brunei Darussalam Dollar",
    "BOB"  => "Bolivia Bolíviano",
    "BRL"  => "Brazil Real",
    "BSD"  => "Bahamas Dollar",
    "BTN"  => "Bhutan Ngultrum",
    "BWP"  => "Botswana Pula",
    "BYN"  => "Belarus Ruble",
    "BZD"  => "Belize Dollar",
    "CAD"  => "Canada Dollar",
    "CDF"  => "Congo/Kinshasa Franc",
    "CHF"  => "Switzerland Franc",
    "CLP"  => "Chile Peso",
    "CNY"  => "China Yuan Renminbi",
    "COP"  => "Colombia Peso",
    "CRC"  => "Costa Rica Colon",
    "CUC"  => "Cuba Convertible Peso",
    "CUP"  => "Cuba Peso",
    "CVE"  => "Cape Verde Escudo",
    "CZK"  => "Czech Republic Koruna",
    "DJF"  => "Djibouti Franc",
    "DKK"  => "Denmark Krone",
    "DOP"  => "Dominican Republic Peso",
    "DZD"  => "Algeria Dinar",
    "EGP"  => "Egypt Pound",
    "ERN"  => "Eritrea Nakfa",
    "ETB"  => "Ethiopia Birr",
    "EUR"  => "Euro Member Countries",
    "FJD"  => "Fiji Dollar",
    "FKP"  => "Falkland Islands (Malvinas) Pound",
    "GBP"  => "United Kingdom Pound",
    "GEL"  => "Georgia Lari",
    "GGP"  => "Guernsey Pound",
    "GHS"  => "Ghana Cedi",
    "GIP"  => "Gibraltar Pound",
    "GMD"  => "Gambia Dalasi",
    "GNF"  => "Guinea Franc",
    "GTQ"  => "Guatemala Quetzal",
    "GYD"  => "Guyana Dollar",
    "HKD"  => "Hong Kong Dollar",
    "HNL"  => "Honduras Lempira",
    "HRK"  => "Croatia Kuna",
    "HTG"  => "Haiti Gourde",
    "HUF"  => "Hungary Forint",
    "IDR"  => "Indonesia Rupiah",
    "ILS"  => "Israel Shekel",
    "IMP"  => "Isle of Man Pound",
    "INR"  => "India Rupee",
    "IQD"  => "Iraq Dinar",
    "IRR"  => "Iran Rial",
    "ISK"  => "Iceland Krona",
    "JEP"  => "Jersey Pound",
    "JMD"  => "Jamaica Dollar",
    "JOD"  => "Jordan Dinar",
    "JPY"  => "Japan Yen",
    "KES"  => "Kenya Shilling",
    "KGS"  => "Kyrgyzstan Som",
    "KHR"  => "Cambodia Riel",
    "KMF"  => "Comorian Franc",
    "KPW"  => "Korea (North) Won",
    "KRW"  => "Korea (South) Won",
    "KWD"  => "Kuwait Dinar",
    "KYD"  => "Cayman Islands Dollar",
    "KZT"  => "Kazakhstan Tenge",
    "LAK"  => "Laos Kip",
    "LBP"  => "Lebanon Pound",
    "LKR"  => "Sri Lanka Rupee",
    "LRD"  => "Liberia Dollar",
    "LSL"  => "Lesotho Loti",
    "LYD"  => "Libya Dinar",
    "MAD"  => "Morocco Dirham",
    "MDL"  => "Moldova Leu",
    "MGA"  => "Madagascar Ariary",
    "MKD"  => "Macedonia Denar",
    "MMK"  => "Myanmar (Burma) Kyat",
    "MNT"  => "Mongolia Tughrik",
    "MOP"  => "Macau Pataca",
    "MRU"  => "Mauritania Ouguiya",
    "MUR"  => "Mauritius Rupee",
    "MVR"  => "Maldives (Maldive Islands) Rufiyaa",
    "MWK"  => "Malawi Kwacha",
    "MXN"  => "Mexico Peso",
    "MYR"  => "Malaysia Ringgit",
    "MZN"  => "Mozambique Metical",
    "NAD"  => "Namibia Dollar",
    "NGN"  => "Nigeria Naira",
    "NIO"  => "Nicaragua Cordoba",
    "NOK"  => "Norway Krone",
    "NPR"  => "Nepal Rupee",
    "NZD"  => "New Zealand Dollar",
    "OMR"  => "Oman Rial",
    "PAB"  => "Panama Balboa",
    "PEN"  => "Peru Sol",
    "PGK"  => "Papua New Guinea Kina",
    "PHP"  => "Philippines Piso",
    "PKR"  => "Pakistan Rupee",
    "PLN"  => "Poland Zloty",
    "PYG"  => "Paraguay Guarani",
    "QAR"  => "Qatar Riyal",
    "RON"  => "Romania Leu",
    "RSD"  => "Serbia Dinar",
    "RUB"  => "Russia Ruble",
    "RWF"  => "Rwanda Franc",
    "SAR"  => "Saudi Arabia Riyal",
    "SBD"  => "Solomon Islands Dollar",
    "SCR"  => "Seychelles Rupee",
    "SDG"  => "Sudan Pound",
    "SEK"  => "Sweden Krona",
    "SGD"  => "Singapore Dollar",
    "SHP"  => "Saint Helena Pound",
    "SLL"  => "Sierra Leone Leone",
    "SOS"  => "Somalia Shilling",
    "SPL*" => "Seborga Luigino",
    "SRD"  => "Suriname Dollar",
    "STN"  => "São Tomé and Príncipe Dobra",
    "SVC"  => "El Salvador Colon",
    "SYP"  => "Syria Pound",
    "SZL"  => "Swaziland Lilangeni",
    "THB"  => "Thailand Baht",
    "TJS"  => "Tajikistan Somoni",
    "TMT"  => "Turkmenistan Manat",
    "TND"  => "Tunisia Dinar",
    "TOP"  => "Tonga Pa'anga",
    "TRY"  => "Turkey Lira",
    "TTD"  => "Trinidad and Tobago Dollar",
    "TVD"  => "Tuvalu Dollar",
    "TWD"  => "Taiwan New Dollar",
    "TZS"  => "Tanzania Shilling",
    "UAH"  => "Ukraine Hryvnia",
    "UGX"  => "Uganda Shilling",
    "USD"  => "United States Dollar",
    "UYU"  => "Uruguay Peso",
    "UZS"  => "Uzbekistan Som",
    "VEF"  => "Venezuela Bolívar",
    "VND"  => "Viet Nam Dong",
    "VUV"  => "Vanuatu Vatu",
    "WST"  => "Samoa Tala",
    "XAF"  => "Communauté Financière Africaine (BEAC) CFA Franc BEAC",
    "XCD"  => "East Caribbean Dollar",
    "XDR"  => "International Monetary Fund (IMF) Special Drawing Rights",
    "XOF"  => "Communauté Financière Africaine (BCEAO) Franc",
    "XPF"  => "Comptoirs Français du Pacifique (CFP) Franc",
    "YER"  => "Yemen Rial",
    "ZAR"  => "South Africa Rand",
    "ZMW"  => "Zambia Kwacha",
    "ZWD"  => "Zimbabwe Dollar"
];

/*
 * These entries are defined based on this help article:
 * https://support.google.com/merchants/answer/160637?hl=en
 */
$_['advertise_google_targets'] = [
    [
        'country'    => 'AR',
        'languages'  => ['es'],
        'currencies' => ['ARS']
    ],
    [
        'country'    => 'AU',
        'languages'  => [
			'en',
			'zh'
		],
        'currencies' => ['AUD']
    ],
    [
        'country'    => 'AT',
        'languages'  => [
			'de',
			'en'
		],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'BE',
        'languages'  => [
			'fr',
			'nl',
			'en'
		],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'BR',
        'languages'  => ['pt'],
        'currencies' => ['BRL']
    ],
    [
        'country'    => 'CA',
        'languages'  => [
			'en',
			'fr',
			'zh'
		],
        'currencies' => ['CAD']
    ],
    [
        'country'    => 'CL',
        'languages'  => ['es'],
        'currencies' => ['CLP']
    ],
    [
        'country'    => 'CO',
        'languages'  => ['es'],
        'currencies' => ['COP']
    ],
    [
        'country'    => 'CZ',
        'languages'  => [
			'cs',
			'en'
		],
        'currencies' => ['CZK']
    ],
    [
        'country'    => 'DK',
        'languages'  => [
			'da',
			'en'
		],
        'currencies' => ['DKK']
    ],
    [
        'country'    => 'FR',
        'languages'  => ['fr'],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'DE',
        'languages'  => [
			'de',
			'en'
		],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'HK',
        'languages'  => [
			'zh',
			'en'
		],
        'currencies' => ['HKD']
    ],
    [
        'country'    => 'IN',
        'languages'  => ['en'],
        'currencies' => ['INR']
    ],
    [
        'country'    => 'ID',
        'languages'  => [
			'id',
			'en'
		],
        'currencies' => ['IDR']
    ],
    [
        'country'    => 'IE',
        'languages'  => ['en'],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'IL',
        'languages'  => [
			'he',
			'en'
		],
        'currencies' => ['ILS']
    ],
    [
        'country'    => 'IT',
        'languages'  => ['it'],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'JP',
        'languages'  => ['ja'],
        'currencies' => ['JPY']
    ],
    [
        'country'    => 'MY',
        'languages'  => [
			'en',
			'zh'
		],
        'currencies' => ['MYR']
    ],
    [
        'country'    => 'MX',
        'languages'  => [
			'es',
			'en'
		],
        'currencies' => ['MXN']
    ],
    [
        'country'    => 'NL',
        'languages'  => [
			'nl',
			'en'
		],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'NZ',
        'languages'  => ['en'],
        'currencies' => ['NZD']
    ],
    [
        'country'    => 'NO',
        'languages'  => [
			'no',
			'en'
		],
        'currencies' => ['NOK']
    ],
    [
        'country'    => 'PH',
        'languages'  => ['en'],
        'currencies' => ['PHP']
    ],
    [
        'country'    => 'PL',
        'languages'  => ['pl'],
        'currencies' => ['PLN']
    ],
    [
        'country'    => 'PT',
        'languages'  => ['pt'],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'RU',
        'languages'  => ['ru'],
        'currencies' => ['RUB']
    ],
    [
        'country'    => 'SA',
        'languages'  => [
			'ar',
			'en'
		],
        'currencies' => ['SAR']
    ],
    [
        'country'    => 'SG',
        'languages'  => [
			'en',
			'zh'
		],
        'currencies' => ['SGD']
    ],
    [
        'country'    => 'ZA',
        'languages'  => ['en'],
        'currencies' => ['ZAR']
    ],
    [
        'country'    => 'KR',
        'languages'  => [
			'ko',
			'en'
		],
        'currencies' => ['KRW']
    ],
    [
        'country'    => 'ES',
        'languages'  => ['es'],
        'currencies' => ['EUR']
    ],
    [
        'country'    => 'SE',
        'languages'  => [
			'sv',
			'en'
		],
        'currencies' => ['SEK']
    ],
    [
        'country'    => 'CH',
        'languages'  => [
			'en',
			'de',
			'fr',
			'it'
		],
        'currencies' => ['CHF']
    ],
    [
        'country'    => 'TW',
        'languages'  => [
			'zh',
			'en'
		],
        'currencies' => ['TWD']
    ],
    [
        'country'    => 'TH',
        'languages'  => [
			'th',
			'en'
		],
        'currencies' => ['THB']
    ],
    [
        'country'    => 'TR',
        'languages'  => [
			'tr',
			'en'
		],
        'currencies' => ['TRY']
    ],
    [
        'country'    => 'UA',
        'languages'  => [
			'uk',
			'ru'
		],
        'currencies' => ['UAH']
    ],
    [
        'country'    => 'AE',
        'languages'  => ['en'],
        'currencies' => ['AED']
    ],
    [
        'country'    => 'GB',
        'languages'  => ['en'],
        'currencies' => ['GBP']
    ],
    [
        'country'    => 'US',
        'languages'  => [
			'en',
			'es',
			'zh'
		],
        'currencies' => ['USD']
    ],
    [
        'country'    => 'VN',
        'languages'  => [
			'vi',
			'en'
		],
        'currencies' => ['VND']
    ]
];
