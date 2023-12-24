<?php
function oc_db_schema() {
	$tables = [];

	$tables[] = [
		'name'    => 'address',
		'field'   => [
			[
				'name'           => 'address_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'firstname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'lastname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'company',
				'type' => 'varchar(60)'
			],
			[
				'name' => 'address_1',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'address_2',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'city',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'postcode',
				'type' => 'varchar(10)'
			],
			[
				'name'    => 'country_id',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'zone_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'custom_field',
				'type' => 'text'
			],
			[
				'name' => 'default',
				'type' => 'tinyint(1)'
			]
		],
		'primary' => [
			'address_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'country_id',
				'table' => 'country',
				'field' => 'country_id'
			],
			[
				'key'   => 'zone_id',
				'table' => 'zone',
				'field' => 'zone_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'address_format',
		'field'   => [
			[
				'name'           => 'address_format_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'address_format',
				'type' => 'text'
			]
		],
		'primary' => [
			'address_format_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'api',
		'field'   => [
			[
				'name'           => 'api_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'username',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'key',
				'type' => 'text'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'api_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'api_ip',
		'field'   => [
			[
				'name'           => 'api_ip_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'api_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			]
		],
		'primary' => [
			'api_ip_id'
		],
		'foreign' => [
			[
				'key'   => 'api_id',
				'table' => 'api',
				'field' => 'api_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'api_session',
		'field'   => [
			[
				'name'           => 'api_session_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'api_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'session_id',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'api_session_id'
		],
		'foreign' => [
			[
				'key'   => 'api_id',
				'table' => 'api',
				'field' => 'api_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'attribute',
		'field'   => [
			[
				'name'           => 'attribute_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'attribute_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'attribute_id'
		],
		'foreign' => [
			[
				'key'   => 'attribute_group_id',
				'table' => 'attribute_group',
				'field' => 'attribute_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'attribute_description',
		'field'   => [
			[
				'name' => 'attribute_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'attribute_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'attribute_id',
				'table' => 'attribute',
				'field' => 'attribute_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'attribute_group',
		'field'   => [
			[
				'name'           => 'attribute_group_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'attribute_group_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'attribute_group_description',
		'field'   => [
			[
				'name' => 'attribute_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'attribute_group_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'attribute_group_id',
				'table' => 'attribute_group',
				'field' => 'attribute_group_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'banner',
		'field'   => [
			[
				'name'           => 'banner_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			]
		],
		'primary' => [
			'banner_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'banner_image',
		'field'   => [
			[
				'name'           => 'banner_image_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'banner_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'title',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'link',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			],
			[
				'name'    => 'sort_order',
				'type'    => 'int(3)'
			]
		],
		'primary' => [
			'banner_image_id'
		],
		'foreign' => [
			[
				'key'   => 'banner_id',
				'table' => 'banner',
				'field' => 'banner_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'cart',
		'field'   => [
			[
				'name'           => 'cart_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'api_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'session_id',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'subscription_plan_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'option',
				'type' => 'text'
			],
			[
				'name' => 'quantity',
				'type' => 'int(5)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'cart_id'
		],
		'foreign' => [
			[
				'key'   => 'api_id',
				'table' => 'api',
				'field' => 'api_id'
			],
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'session_id',
				'table' => 'session',
				'field' => 'session_id'
			],
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'subscription_plan_id',
				'table' => 'subscription_plan',
				'field' => 'subscription_plan_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'category',
		'field'   => [
			[
				'name'           => 'category_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			],
			[
				'name'    => 'parent_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'top',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'column',
				'type' => 'int(3)'
			],
			[
				'name'    => 'sort_order',
				'type'    => 'int(3)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'category_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'category_description',
		'field'   => [
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'description',
				'type' => 'text'
			],
			[
				'name' => 'meta_title',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'meta_description',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'meta_keyword',
				'type' => 'varchar(255)'
			]
		],
		'primary' => [
			'category_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'category_filter',
		'field'   => [
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'filter_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'category_id',
			'filter_id'
		],
		'foreign' => [
			[
				'key'   => 'category_id',
				'table' => 'category',
				'field' => 'category_id'
			],
			[
				'key'   => 'filter_id',
				'table' => 'filter',
				'field' => 'filter_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'category_path',
		'field'   => [
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'path_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'level',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'category_id',
			'path_id'
		],
		'foreign' => [
			[
				'key'   => 'category_id',
				'table' => 'category',
				'field' => 'category_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'category_to_layout',
		'field'   => [
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'layout_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'category_id',
			'store_id'
		],
		'foreign' => [
			[
				'key'   => 'category_id',
				'table' => 'category',
				'field' => 'category_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'layout_id',
				'table' => 'layout',
				'field' => 'layout_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'category_to_store',
		'field'   => [
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'store_id',
				'type'    => 'int(11)'
			]
		],
		'primary' => [
			'category_id',
			'store_id',
		],
		'foreign' => [
			[
				'key'   => 'category_id',
				'table' => 'category',
				'field' => 'category_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'country',
		'field'   => [
			[
				'name'           => 'country_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'iso_code_2',
				'type' => 'varchar(2)'
			],
			[
				'name' => 'iso_code_3',
				'type' => 'varchar(3)'
			],
			[
				'name' => 'address_format_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'postcode_required',
				'type' => 'tinyint(1)'
			],
			[
				'name'    => 'status',
				'type'    => 'tinyint(1)'
			]
		],
		'primary' => [
			'country_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'coupon',
		'field'   => [
			[
				'name'           => 'coupon_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(20)'
			],
			[
				'name' => 'type',
				'type' => 'char(1)'
			],
			[
				'name' => 'discount',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'logged',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'shipping',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'total',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'date_start',
				'type' => 'date'
			],
			[
				'name' => 'date_end',
				'type' => 'date'
			],
			[
				'name' => 'uses_total',
				'type' => 'int(11)'
			],
			[
				'name' => 'uses_customer',
				'type' => 'int(11)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'coupon_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'coupon_category',
		'field'   => [
			[
				'name' => 'coupon_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'coupon_id',
			'category_id'
		],
		'foreign' => [
			[
				'key'   => 'coupon_id',
				'table' => 'coupon',
				'field' => 'coupon_id'
			],
			[
				'key'   => 'category_id',
				'table' => 'category',
				'field' => 'category_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'coupon_history',
		'field'   => [
			[
				'name'           => 'coupon_history_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'coupon_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'amount',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'coupon_history_id'
		],
		'foreign' => [
			[
				'key'   => 'coupon_id',
				'table' => 'coupon',
				'field' => 'coupon_id'
			],
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			],
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'coupon_product',
		'field'   => [
			[
				'name'           => 'coupon_product_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'coupon_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'coupon_product_id'
		],
		'foreign' => [
			[
				'key'   => 'coupon_id',
				'table' => 'coupon',
				'field' => 'coupon_id'
			],
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'currency',
		'field'   => [
			[
				'name'           => 'currency_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'title',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(3)'
			],
			[
				'name' => 'symbol_left',
				'type' => 'varchar(12)'
			],
			[
				'name' => 'symbol_right',
				'type' => 'varchar(12)'
			],
			[
				'name' => 'decimal_place',
				'type' => 'int(1)'
			],
			[
				'name' => 'value',
				'type' => 'double(15,8)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'currency_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer',
		'field'   => [
			[
				'name'           => 'customer_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_group_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'store_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'firstname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'lastname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'telephone',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'fax',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'password',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'cart',
				'type' => 'text'
			],
			[
				'name' => 'wishlist',
				'type' => 'text'
			],
			[
				'name' => 'newsletter',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'address_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'custom_field',
				'type' => 'text'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'safe',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'token',
				'type' => 'text'
			],
			[
				'name' => 'code',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_activity',
		'field'   => [
			[
				'name'           => 'customer_activity_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'key',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'data',
				'type' => 'text'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_activity_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_affiliate',
		'field'   => [
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'company',
				'type' => 'varchar(60)'
			],
			[
				'name' => 'website',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'tracking',
				'type' => 'varchar(64)'
			],
			[
				'name'    => 'commission',
				'type'    => 'decimal(4,2)'
			],
			[
				'name' => 'tax',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'payment',
				'type' => 'varchar(6)'
			],
			[
				'name' => 'cheque',
				'type' => 'varchar(100)'
			],
			[
				'name' => 'paypal',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'bank_name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'bank_branch_number',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'bank_swift_code',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'bank_account_name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'bank_account_number',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'custom_field',
				'type' => 'text'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_affiliate_report',
		'field'   => [
			[
				'name'           => 'customer_affiliate_report_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'country',
				'type' => 'varchar(2)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_affiliate_report_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_approval',
		'field'   => [
			[
				'name'           => 'customer_approval_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'type',
				'type' => 'varchar(9)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_approval_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_group',
		'field'   => [
			[
				'name'           => 'customer_group_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'approval',
				'type' => 'int(1)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'customer_group_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_group_description',
		'field'   => [
			[
				'name' => 'customer_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'description',
				'type' => 'text'
			]
		],
		'primary' => [
			'customer_group_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_history',
		'field'   => [
			[
				'name'           => 'customer_history_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'comment',
				'type' => 'text'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_history_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_ip',
		'field'   => [
			[
				'name'           => 'customer_ip_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'country',
				'type' => 'varchar(2)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_ip_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_login',
		'field'   => [
			[
				'name'           => 'customer_login_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'total',
				'type' => 'int(4)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_login_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_online',
		'field'   => [
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'url',
				'type' => 'text'
			],
			[
				'name' => 'referer',
				'type' => 'text'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'ip'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_reward',
		'field'   => [
			[
				'name'           => 'customer_reward_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'customer_id',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'order_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'description',
				'type' => 'text'
			],
			[
				'name'    => 'points',
				'type'    => 'int(8)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_reward_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_search',
		'field'   => [
			[
				'name'           => 'customer_search_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'keyword',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'sub_category',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'description',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'products',
				'type' => 'int(11)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_search_id'
		],
		'foreign' => [
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			],
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'category_id',
				'table' => 'category',
				'field' => 'category_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_transaction',
		'field'   => [
			[
				'name'           => 'customer_transaction_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'description',
				'type' => 'text'
			],
			[
				'name' => 'amount',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_transaction_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'customer_wishlist',
		'field'   => [
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'customer_id',
			'product_id'
		],
		'foreign' => [
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'custom_field',
		'field'   => [
			[
				'name'           => 'custom_field_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'type',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'value',
				'type' => 'text'
			],
			[
				'name' => 'validation',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'location',
				'type' => 'varchar(10)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'custom_field_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'custom_field_customer_group',
		'field'   => [
			[
				'name' => 'custom_field_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'required',
				'type' => 'tinyint(1)'
			]
		],
		'primary' => [
			'custom_field_id',
			'customer_group_id'
		],
		'foreign' => [
			[
				'key'   => 'custom_field_id',
				'table' => 'custom_field',
				'field' => 'custom_field_id'
			],
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'custom_field_description',
		'field'   => [
			[
				'name' => 'custom_field_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			]
		],
		'primary' => [
			'custom_field_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'custom_field_id',
				'table' => 'custom_field',
				'field' => 'custom_field_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'custom_field_value',
		'field'   => [
			[
				'name'           => 'custom_field_value_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'custom_field_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'custom_field_value_id'
		],
		'foreign' => [
			[
				'key'   => 'custom_field_id',
				'table' => 'custom_field',
				'field' => 'custom_field_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'custom_field_value_description',
		'field'   => [
			[
				'name' => 'custom_field_value_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'custom_field_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			]
		],
		'primary' => [
			'custom_field_value_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			],
			[
				'key'   => 'custom_field_id',
				'table' => 'custom_field',
				'field' => 'custom_field_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'download',
		'field'   => [
			[
				'name'           => 'download_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'filename',
				'type' => 'varchar(160)'
			],
			[
				'name' => 'mask',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'download_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'download_description',
		'field'   => [
			[
				'name' => 'download_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'download_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'event',
		'field'   => [
			[
				'name'           => 'event_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'code',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'trigger',
				'type' => 'text'
			],
			[
				'name' => 'action',
				'type' => 'text'
			],
			[
				'name'    => 'status',
				'type'    => 'tinyint(1)'
			],
			[
				'name'    => 'sort_order',
				'type'    => 'int(3)'
			]
		],
		'primary' => [
			'event_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'extension',
		'field'   => [
			[
				'name'           => 'extension_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'extension',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'type',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(128)'
			]
		],
		'primary' => [
			'extension_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'extension_install',
		'field'   => [
			[
				'name'           => 'extension_install_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'extension_download_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'filename',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'extension_install_id'
		],
		'foreign' => [
			[
				'key'   => 'extension_id',
				'table' => 'extension',
				'field' => 'extension_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'extension_path',
		'field'   => [
			[
				'name'           => 'extension_path_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'extension_install_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'path',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'extension_path_id'
		],
		'foreign' => [
			[
				'key'   => 'extension_install_id',
				'table' => 'extension_install',
				'field' => 'extension_install_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'filter',
		'field'   => [
			[
				'name'           => 'filter_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'filter_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'filter_id'
		],
		'foreign' => [
			[
				'key'   => 'filter_group_id',
				'table' => 'filter_group',
				'field' => 'filter_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'filter_description',
		'field'   => [
			[
				'name' => 'filter_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'filter_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'filter_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			],
			[
				'key'   => 'filter_group_id',
				'table' => 'filter_group',
				'field' => 'filter_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'filter_group',
		'field'   => [
			[
				'name'           => 'filter_group_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'filter_group_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'filter_group_description',
		'field'   => [
			[
				'name' => 'filter_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'filter_group_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'filter_group_id',
				'table' => 'filter_group',
				'field' => 'filter_group_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'gdpr',
		'field'   => [
			[
				'name'           => 'gdpr_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'action',
				'type' => 'varchar(6)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'gdpr_id'
		],
		'foreign' => [
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'geo_zone',
		'field'   => [
			[
				'name'           => 'geo_zone_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'description',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'geo_zone_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'googleshopping_category',
		'field'   => [
			[
				'name' => 'google_product_category',
				'type' => 'varchar(10)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'google_product_category',
			'store_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'googleshopping_product',
		'field'   => [
			[
				'name'           => 'product_advertise_google_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'has_issues',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'destination_status',
				'type' => 'enum(\'pending\',\'approved\',\'disapproved\')'
			],
			[
				'name' => 'impressions',
				'type' => 'int(11)'
			],
			[
				'name' => 'clicks',
				'type' => 'int(11)'
			],
			[
				'name' => 'conversions',
				'type' => 'int(11)'
			],
			[
				'name' => 'cost',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'conversion_value',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'google_product_category',
				'type' => 'varchar(10)'
			],
			[
				'name' => 'condition',
				'type' => 'enum(\'new\',\'refurbished\',\'used\')'
			],
			[
				'name' => 'adult',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'multipack',
				'type' => 'int(11)'
			],
			[
				'name' => 'is_bundle',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'condition',
				'type' => 'enum(\'new\',\'refurbished\',\'used\')'
			],
			[
				'name' => 'color',
				'type' => 'int(11)'
			],
			[
				'name' => 'gender',
				'type' => 'enum(\'male\',\'female\',\'unisex\')'
			],
			[
				'name' => 'size_type',
				'type' => 'enum(\'regular\',\'petite\',\'plus\',\'big and tall\',\'maternity\')'
			],
			[
				'name' => 'size_system',
				'type' => 'enum(\'AU\',\'BR\',\'CN\',\'DE\',\'EU\',\'FR\',\'IT\',\'JP\',\'MEX\',\'UK\',\'US\')'
			],
			[
				'name' => 'size',
				'type' => 'int(11)'
			],
			[
				'name' => 'is_modified',
				'type' => 'tinyint(1)'
			]
		],
		'primary' => [
			'product_advertise_google_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'googleshopping_product_status',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'product_variation_id',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'destination_statuses',
				'type' => 'text'
			],
			[
				'name' => 'data_quality_issues',
				'type' => 'text'
			],
			[
				'name' => 'item_level_issues',
				'type' => 'text'
			],
			[
				'name' => 'google_expiration_date',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'store_id',
			'product_variation_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'googleshopping_product_target',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'advertise_google_target_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'advertise_google_target_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'googleshopping_target',
		'field'   => [
			[
				'name' => 'advertise_google_target_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'campaign_name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'country',
				'type' => 'varchar(2)'
			],
			[
				'name' => 'budget',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'feeds',
				'type' => 'text'
			],
			[
				'name' => 'status',
				'type' => 'enum(\'paused\',\'active\')'
			],
			[
				'name' => 'date_added',
				'type' => 'date'
			],
			[
				'name' => 'roas',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'advertise_google_target_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'information',
		'field'   => [
			[
				'name'           => 'information_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'bottom',
				'type'    => 'int(1)'
			],
			[
				'name'    => 'sort_order',
				'type'    => 'int(3)'
			],
			[
				'name'    => 'status',
				'type'    => 'tinyint(1)'
			]
		],
		'primary' => [
			'information_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'information_description',
		'field'   => [
			[
				'name' => 'information_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'title',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'description',
				'type' => 'mediumtext'
			],
			[
				'name' => 'meta_title',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'meta_description',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'meta_keyword',
				'type' => 'varchar(255)'
			]
		],
		'primary' => [
			'information_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'information_to_layout',
		'field'   => [
			[
				'name' => 'information_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'layout_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'information_id',
			'store_id'
		],
		'foreign' => [
			[
				'key'   => 'information_id',
				'table' => 'information',
				'field' => 'information_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'layout_id',
				'table' => 'layout',
				'field' => 'layout_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'information_to_store',
		'field'   => [
			[
				'name' => 'information_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'information_id',
			'store_id'
		],
		'foreign' => [
			[
				'key'   => 'information_id',
				'table' => 'information',
				'field' => 'information_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'language',
		'field'   => [
			[
				'name'           => 'language_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(5)'
			],
			[
				'name' => 'locale',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'image',
				'type' => 'varchar(64)'
			],
			[
				'name'    => 'sort_order',
				'type'    => 'int(3)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			]
		],
		'primary' => [
			'language_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'layout',
		'field'   => [
			[
				'name'           => 'layout_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'layout_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'layout_module',
		'field'   => [
			[
				'name'           => 'layout_module_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'layout_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'position',
				'type' => 'varchar(14)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'layout_module_id'
		],
		'foreign' => [
			[
				'key'   => 'layout_id',
				'table' => 'layout',
				'field' => 'layout_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'layout_route',
		'field'   => [
			[
				'name'           => 'layout_route_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'layout_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'route',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'layout_route_id'
		],
		'foreign' => [
			[
				'key'   => 'layout_id',
				'table' => 'layout',
				'field' => 'layout_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'length_class',
		'field'   => [
			[
				'name'           => 'length_class_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'value',
				'type' => 'decimal(15,8)'
			]
		],
		'primary' => [
			'length_class_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'length_class_description',
		'field'   => [
			[
				'name' => 'length_class_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'title',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'unit',
				'type' => 'varchar(4)'
			]
		],
		'primary' => [
			'length_class_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'length_class_id',
				'table' => 'length_class',
				'field' => 'length_class_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'location',
		'field'   => [
			[
				'name'           => 'location_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'address',
				'type' => 'text'
			],
			[
				'name' => 'telephone',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'geocode',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'open',
				'type' => 'text'
			],
			[
				'name' => 'comment',
				'type' => 'text'
			]
		],
		'primary' => [
			'location_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'manufacturer',
		'field'   => [
			[
				'name'           => 'manufacturer_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'manufacturer_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'manufacturer_to_store',
		'field'   => [
			[
				'name' => 'manufacturer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'manufacturer_id',
			'store_id'
		],
		'foreign' => [
			[
				'key'   => 'manufacturer_id',
				'table' => 'manufacturer',
				'field' => 'manufacturer_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'marketing',
		'field'   => [
			[
				'name'           => 'marketing_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'description',
				'type' => 'text'
			],
			[
				'name' => 'code',
				'type' => 'varchar(64)'
			],
			[
				'name'    => 'clicks',
				'type'    => 'int(5)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'marketing_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'modification',
		'field'   => [
			[
				'name'           => 'modification_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'     => 'extension_install_id',
				'type'     => 'int(11)',
				'not_null' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'author',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'version',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'link',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'xml',
				'type' => 'text'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'modification_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'module',
		'field'   => [
			[
				'name'           => 'module_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'setting',
				'type' => 'text'
			]
		],
		'primary' => [
			'module_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'option',
		'field'   => [
			[
				'name'           => 'option_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'type',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'option_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'option_description',
		'field'   => [
			[
				'name' => 'option_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			]
		],
		'primary' => [
			'option_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'option_value',
		'field'   => [
			[
				'name'           => 'option_value_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'option_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'option_value_id'
		],
		'foreign' => [
			[
				'key'   => 'option_id',
				'table' => 'option',
				'field' => 'option_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'option_value_description',
		'field'   => [
			[
				'name' => 'option_value_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'option_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			]
		],
		'primary' => [
			'option_value_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			],
			[
				'key'   => 'option_id',
				'table' => 'option',
				'field' => 'option_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order',
		'field'   => [
			[
				'name'           => 'order_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'invoice_no',
				'type'    => 'int(11)'
			],
			[
				'name' => 'invoice_prefix',
				'type' => 'varchar(26)'
			],
			[
				'name'    => 'store_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'store_name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'store_url',
				'type' => 'varchar(255)'
			],
			[
				'name'    => 'customer_id',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'customer_group_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'firstname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'lastname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'telephone',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'fax',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'custom_field',
				'type' => 'text'
			],
			[
				'name' => 'payment_address_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'payment_firstname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'payment_lastname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'payment_company',
				'type' => 'varchar(60)'
			],
			[
				'name' => 'payment_address_1',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'payment_address_2',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'payment_city',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'payment_postcode',
				'type' => 'varchar(10)'
			],
			[
				'name' => 'payment_country',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'payment_country_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'payment_zone',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'payment_zone_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'payment_address_format',
				'type' => 'text'
			],
			[
				'name' => 'payment_custom_field',
				'type' => 'text'
			],
			[
				'name' => 'payment_method',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'shipping_address_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'shipping_firstname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'shipping_lastname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'shipping_company',
				'type' => 'varchar(60)'
			],
			[
				'name' => 'shipping_address_1',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'shipping_address_2',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'shipping_city',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'shipping_postcode',
				'type' => 'varchar(10)'
			],
			[
				'name' => 'shipping_country',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'shipping_country_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'shipping_zone',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'shipping_zone_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'shipping_address_format',
				'type' => 'text'
			],
			[
				'name' => 'shipping_custom_field',
				'type' => 'text'
			],
			[
				'name' => 'shipping_method',
				'type' => 'text'
			],
			[
				'name' => 'comment',
				'type' => 'text'
			],
			[
				'name'    => 'total',
				'type'    => 'decimal(15,4)'
			],
			[
				'name'    => 'order_status_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'affiliate_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'commission',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'marketing_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'tracking',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'currency_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'currency_code',
				'type' => 'varchar(3)'
			],
			[
				'name'    => 'currency_value',
				'type'    => 'decimal(15,8)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'forwarded_ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'user_agent',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'accept_language',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'order_id'
		],
		'foreign' => [
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			],
			[
				'key'   => 'payment_country_id',
				'table' => 'country',
				'field' => 'country_id'
			],
			[
				'key'   => 'payment_zone_id',
				'table' => 'zone',
				'field' => 'zone_id'
			],
			[
				'key'   => 'shipping_country_id',
				'table' => 'country',
				'field' => 'country_id'
			],
			[
				'key'   => 'shipping_zone_id',
				'table' => 'zone',
				'field' => 'zone_id'
			],
			[
				'key'   => 'order_status_id',
				'table' => 'order_status',
				'field' => 'order_status_id'
			],
			[
				'key'   => 'affiliate_id',
				'table' => 'customer_affiliate',
				'field' => 'customer_id'
			],
			[
				'key'   => 'marketing_id',
				'table' => 'marketing',
				'field' => 'marketing_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			],
			[
				'key'   => 'currency_id',
				'table' => 'currency',
				'field' => 'currency_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_history',
		'field'   => [
			[
				'name'           => 'order_history_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'order_status_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'notify',
				'type'    => 'tinyint(1)'
			],
			[
				'name' => 'comment',
				'type' => 'text'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'order_history_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			],
			[
				'key'   => 'order_status_id',
				'table' => 'order_status',
				'field' => 'order_status_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_option',
		'field'   => [
			[
				'name'           => 'order_option_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'order_product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'product_option_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'product_option_value_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'value',
				'type' => 'text'
			],
			[
				'name' => 'type',
				'type' => 'varchar(32)'
			]
		],
		'primary' => [
			'order_option_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			],
			[
				'key'   => 'order_product_id',
				'table' => 'order_product',
				'field' => 'order_product_id'
			],
			[
				'key'   => 'product_option_id',
				'table' => 'product_option',
				'field' => 'product_option_id'
			],
			[
				'key'   => 'product_option_value_id',
				'table' => 'product_option_value',
				'field' => 'product_option_value_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_product',
		'field'   => [
			[
				'name'           => 'order_product_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'model',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'quantity',
				'type' => 'int(4)'
			],
			[
				'name'    => 'price',
				'type'    => 'decimal(15,4)'
			],
			[
				'name'    => 'total',
				'type'    => 'decimal(15,4)'
			],
			[
				'name'    => 'tax',
				'type'    => 'decimal(15,4)'
			],
			[
				'name' => 'reward',
				'type' => 'int(8)'
			]
		],
		'primary' => [
			'order_product_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			],
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'master_id',
				'table' => 'product',
				'field' => 'product_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_recurring',
		'field'   => [
			[
				'name'           => 'order_recurring_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'reference',
				'type' => 'varchar(255)'
			],
			[
				'name'    => 'product_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'product_name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'product_quantity',
				'type' => 'int(11)'
			],
			[
				'name' => 'recurring_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'recurring_name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'recurring_description',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'recurring_frequency',
				'type' => 'varchar(25)'
			],
			[
				'name' => 'recurring_cycle',
				'type' => 'smallint(6)'
			],
			[
				'name' => 'recurring_duration',
				'type' => 'smallint(6)'
			],
			[
				'name' => 'recurring_price',
				'type' => 'decimal(10,4)'
			],
			[
				'name' => 'trial',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'trial_frequency',
				'type' => 'varchar(25)'
			],
			[
				'name' => 'trial_cycle',
				'type' => 'smallint(6)'
			],
			[
				'name' => 'trial_duration',
				'type' => 'smallint(6)'
			],
			[
				'name' => 'trial_price',
				'type' => 'decimal(10,4)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(4)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'order_recurring_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			],
			[
				'key'   => 'product_id',
				'table' => 'order_product',
				'field' => 'product_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_recurring_history',
		'field'   => [
			[
				'name'           => 'order_recurring_history_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_recurring_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'notify',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'comment',
				'type' => 'text'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'order_recurring_history_id'
		],
		'foreign' => [
			[
				'key'   => 'order_recurring_id',
				'table' => 'order_recurring',
				'field' => 'order_recurring_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_recurring_transaction',
		'field'   => [
			[
				'name'           => 'order_recurring_transaction_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_recurring_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'reference',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'type',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'amount',
				'type' => 'decimal(10,4)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'order_recurring_transaction_id'
		],
		'foreign' => [
			[
				'key'   => 'order_recurring_id',
				'table' => 'order_recurring',
				'field' => 'order_recurring_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_shipment',
		'field'   => [
			[
				'name'           => 'order_shipment_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'shipment_courier_id',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'tracking_number',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'order_shipment_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_shipping_courier',
		'field'   => [
			[
				'name'           => 'shipping_courier_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'           => 'shipping_courier_code',
				'type'           => 'varchar(255)'
			],
			[
				'name'           => 'shipping_courier_name',
				'type'           => 'varchar(255)'
			]
		],
		'primary' => [
			'shipping_courier_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_status',
		'field'   => [
			[
				'name'           => 'order_status_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			]
		],
		'primary' => [
			'order_status_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_total',
		'field'   => [
			[
				'name'           => 'order_total_id',
				'type'           => 'int(10)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'title',
				'type' => 'varchar(255)'
			],
			[
				'name'    => 'value',
				'type'    => 'decimal(15,4)'
			],
			[
				'name' => 'sort_order',
				'type' => 'int(3)'
			]
		],
		'primary' => [
			'order_total_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'order_voucher',
		'field'   => [
			[
				'name'           => 'order_voucher_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'voucher_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'description',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(10)'
			],
			[
				'name' => 'from_name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'from_email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'to_name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'to_email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'voucher_theme_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'message',
				'type' => 'text'
			],
			[
				'name' => 'amount',
				'type' => 'decimal(15,4)'
			]
		],
		'primary' => [
			'order_voucher_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			],
			[
				'key'   => 'voucher_id',
				'table' => 'voucher',
				'field' => 'voucher_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product',
		'field'   => [
			[
				'name'           => 'product_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'model',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'sku',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'upc',
				'type' => 'varchar(12)'
			],
			[
				'name' => 'ean',
				'type' => 'varchar(14)'
			],
			[
				'name' => 'jan',
				'type' => 'varchar(13)'
			],
			[
				'name' => 'isbn',
				'type' => 'varchar(17)'
			],
			[
				'name' => 'mpn',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'location',
				'type' => 'varchar(128)'
			],
			[
				'name'    => 'quantity',
				'type'    => 'int(4)'
			],
			[
				'name' => 'stock_status_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'manufacturer_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'shipping',
				'type'    => 'tinyint(1)'
			],
			[
				'name'    => 'price',
				'type'    => 'decimal(15,4)'
			],
			[
				'name'    => 'points',
				'type'    => 'int(8)'
			],
			[
				'name' => 'tax_class_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'date_available',
				'type' => 'date'
			],
			[
				'name'    => 'weight',
				'type'    => 'decimal(15,8)'
			],
			[
				'name'    => 'weight_class_id',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'length',
				'type'    => 'decimal(15,8)'
			],
			[
				'name'    => 'width',
				'type'    => 'decimal(15,8)'
			],
			[
				'name'    => 'height',
				'type'    => 'decimal(15,8)'
			],
			[
				'name'    => 'length_class_id',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'subtract',
				'type'    => 'tinyint(1)'
			],
			[
				'name'    => 'minimum',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'sort_order',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'status',
				'type'    => 'tinyint(1)'
			],
			[
				'name'    => 'viewed',
				'type'    => 'int(5)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'product_id'
		],
		'foreign' => [
			[
				'key'   => 'stock_status_id',
				'table' => 'stock_status',
				'field' => 'stock_status_id'
			],
			[
				'key'   => 'manufacturer_id',
				'table' => 'manufacturer',
				'field' => 'manufacturer_id'
			],
			[
				'key'   => 'tax_class_id',
				'table' => 'tax_class',
				'field' => 'tax_class_id'
			],
			[
				'key'   => 'weight_class_id',
				'table' => 'weight_class',
				'field' => 'weight_class_id'
			],
			[
				'key'   => 'length_class_id',
				'table' => 'length_class',
				'field' => 'length_class_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_attribute',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'attribute_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'text',
				'type' => 'text'
			]
		],
		'primary' => [
			'product_id',
			'attribute_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'attribute_id',
				'table' => 'attribute',
				'field' => 'attribute_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_description',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'description',
				'type' => 'text'
			],
			[
				'name' => 'tag',
				'type' => 'text'
			],
			[
				'name' => 'meta_title',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'meta_description',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'meta_keyword',
				'type' => 'varchar(255)'
			]
		],
		'primary' => [
			'product_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_discount',
		'field'   => [
			[
				'name'           => 'product_discount_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_group_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'quantity',
				'type'    => 'int(4)'
			],
			[
				'name'    => 'priority',
				'type'    => 'int(5)'
			],
			[
				'name'    => 'price',
				'type'    => 'decimal(15,4)'
			],
			[
				'name' => 'date_start',
				'type' => 'date'
			],
			[
				'name' => 'date_end',
				'type' => 'date'
			]
		],
		'primary' => [
			'product_discount_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_filter',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'filter_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'filter_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'filter_id',
				'table' => 'filter',
				'field' => 'filter_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_image',
		'field'   => [
			[
				'name'           => 'product_image_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			],
			[
				'name'    => 'sort_order',
				'type'    => 'int(3)'
			]
		],
		'primary' => [
			'product_image_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_option',
		'field'   => [
			[
				'name'           => 'product_option_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'option_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'value',
				'type' => 'text'
			],
			[
				'name' => 'required',
				'type' => 'tinyint(1)'
			]
		],
		'primary' => [
			'product_option_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'option_id',
				'table' => 'option',
				'field' => 'option_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_option_value',
		'field'   => [
			[
				'name'           => 'product_option_value_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'product_option_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'option_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'option_value_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'quantity',
				'type' => 'int(3)'
			],
			[
				'name' => 'subtract',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'price',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'price_prefix',
				'type' => 'varchar(1)'
			],
			[
				'name' => 'points',
				'type' => 'int(8)'
			],
			[
				'name' => 'points_prefix',
				'type' => 'varchar(1)'
			],
			[
				'name' => 'weight',
				'type' => 'decimal(15,8)'
			],
			[
				'name' => 'weight_prefix',
				'type' => 'varchar(1)'
			]
		],
		'primary' => [
			'product_option_value_id'
		],
		'foreign' => [
			[
				'key'   => 'product_option_id',
				'table' => 'product_option',
				'field' => 'product_option_id'
			],
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'option_id',
				'table' => 'option',
				'field' => 'option_id'
			],
			[
				'key'   => 'option_value_id',
				'table' => 'option_value',
				'field' => 'option_value_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_recurring',
		'field'   => [
			[
				'name'           => 'product_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'recurring_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_group_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'recurring_id',
			'customer_group_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'recurring_id',
				'table' => 'recurring',
				'field' => 'recurring_id'
			],
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_related',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'related_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'related_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'related_id',
				'table' => 'product',
				'field' => 'product_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_reward',
		'field'   => [
			[
				'name'           => 'product_reward_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'product_id',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'customer_group_id',
				'type'    => 'int(11)'
			],
			[
				'name'    => 'points',
				'type'    => 'int(8)'
			]
		],
		'primary' => [
			'product_reward_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_special',
		'field'   => [
			[
				'name'           => 'product_special_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_group_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'priority',
				'type'    => 'int(5)'
			],
			[
				'name'    => 'price',
				'type'    => 'decimal(15,4)'
			],
			[
				'name' => 'date_start',
				'type' => 'date'
			],
			[
				'name' => 'date_end',
				'type' => 'date'
			]
		],
		'primary' => [
			'product_special_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_to_category',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'category_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'category_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'category_id',
				'table' => 'category',
				'field' => 'category_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_to_download',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'download_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'download_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'download_id',
				'table' => 'download',
				'field' => 'download_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_to_layout',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'layout_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'store_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'layout_id',
				'table' => 'layout',
				'field' => 'layout_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'product_to_store',
		'field'   => [
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'store_id',
				'type'    => 'int(11)'
			]
		],
		'primary' => [
			'product_id',
			'store_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'return',
		'field'   => [
			[
				'name'           => 'return_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'firstname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'lastname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'telephone',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'product',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'model',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'quantity',
				'type' => 'int(4)'
			],
			[
				'name' => 'opened',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'return_reason_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'return_action_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'return_status_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'comment',
				'type' => 'text'
			],
			[
				'name' => 'date_ordered',
				'type' => 'date'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'return_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			],
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			],
			[
				'key'   => 'return_reason_id',
				'table' => 'return_reason',
				'field' => 'return_reason_id'
			],
			[
				'key'   => 'return_action_id',
				'table' => 'return_action',
				'field' => 'return_action_id'
			],
			[
				'key'   => 'return_status_id',
				'table' => 'return_status',
				'field' => 'return_status_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'return_action',
		'field'   => [
			[
				'name'           => 'return_action_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'language_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			]
		],
		'primary' => [
			'return_action_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'return_history',
		'field'   => [
			[
				'name'           => 'return_history_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'return_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'return_status_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'notify',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'comment',
				'type' => 'text'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'return_history_id'
		],
		'foreign' => [
			[
				'key'   => 'return_id',
				'table' => 'return',
				'field' => 'return_id'
			],
			[
				'key'   => 'return_status_id',
				'table' => 'return_status',
				'field' => 'return_status_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'return_reason',
		'field'   => [
			[
				'name'           => 'return_reason_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'language_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			]
		],
		'primary' => [
			'return_reason_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'return_status',
		'field'   => [
			[
				'name'           => 'return_status_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'language_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			]
		],
		'primary' => [
			'return_status_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'review',
		'field'   => [
			[
				'name'           => 'review_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'product_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'author',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'text',
				'type' => 'text'
			],
			[
				'name' => 'rating',
				'type' => 'int(1)'
			],
			[
				'name'    => 'status',
				'type'    => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'review_id'
		],
		'foreign' => [
			[
				'key'   => 'product_id',
				'table' => 'product',
				'field' => 'product_id'
			],
			[
				'key'   => 'customer_id',
				'table' => 'customer',
				'field' => 'customer_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'seo_url',
		'field'   => [
			[
				'name'           => 'seo_url_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'query',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'keyword',
				'type' => 'varchar(255)'
			]
		],
		'primary' => [
			'seo_url_id'
		],
		'foreign' => [
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'session',
		'field'   => [
			[
				'name' => 'session_id',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'data',
				'type' => 'text'
			],
			[
				'name' => 'expire',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'session_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'setting',
		'field'   => [
			[
				'name'           => 'setting_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'store_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'key',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'value',
				'type' => 'text'
			],
			[
				'name'    => 'serialized',
				'type'    => 'tinyint(1)'
			]
		],
		'primary' => [
			'setting_id'
		],
		'foreign' => [
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'statistics',
		'field'   => [
			[
				'name'           => 'statistics_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'code',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'value',
				'type' => 'decimal(15,4)'
			]
		],
		'primary' => [
			'statistics_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'stock_status',
		'field'   => [
			[
				'name'           => 'stock_status_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			]
		],
		'primary' => [
			'stock_status_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'store',
		'field'   => [
			[
				'name'           => 'store_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'url',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'ssl',
				'type' => 'varchar(255)'
			]
		],
		'primary' => [
			'store_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'tax_class',
		'field'   => [
			[
				'name'           => 'tax_class_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'title',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'description',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'tax_class_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'tax_rate',
		'field'   => [
			[
				'name'           => 'tax_rate_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'geo_zone_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			],
			[
				'name'    => 'rate',
				'type'    => 'decimal(15,4)'
			],
			[
				'name' => 'type',
				'type' => 'char(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'tax_rate_id'
		],
		'foreign' => [
			[
				'key'   => 'geo_zone_id',
				'table' => 'geo_zone',
				'field' => 'geo_zone_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'tax_rate_to_customer_group',
		'field'   => [
			[
				'name' => 'tax_rate_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'customer_group_id',
				'type' => 'int(11)'
			]
		],
		'primary' => [
			'tax_rate_id',
			'customer_group_id'
		],
		'foreign' => [
			[
				'key'   => 'tax_rate_id',
				'table' => 'tax_rate',
				'field' => 'tax_rate_id'
			],
			[
				'key'   => 'customer_group_id',
				'table' => 'customer_group',
				'field' => 'customer_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'tax_rule',
		'field'   => [
			[
				'name'           => 'tax_rule_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'tax_class_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'tax_rate_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'based',
				'type' => 'varchar(10)'
			],
			[
				'name'    => 'priority',
				'type'    => 'int(5)'
			]
		],
		'primary' => [
			'tax_rule_id'
		],
		'foreign' => [
			[
				'key'   => 'tax_class_id',
				'table' => 'tax_class',
				'field' => 'tax_class_id'
			],
			[
				'key'   => 'tax_rate_id',
				'table' => 'tax_rate',
				'field' => 'tax_rate_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'theme',
		'field'   => [
			[
				'name'           => 'theme_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'route',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'code',
				'type' => 'mediumtext'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'theme_id'
		],
		'foreign' => [
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'translation',
		'field'   => [
			[
				'name'           => 'translation_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'store_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'route',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'key',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'value',
				'type' => 'text'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'translation_id'
		],
		'foreign' => [
			[
				'key'   => 'store_id',
				'table' => 'store',
				'field' => 'store_id'
			],
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'upload',
		'field'   => [
			[
				'name'           => 'upload_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'filename',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'upload_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'user',
		'field'   => [
			[
				'name'           => 'user_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'user_group_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'username',
				'type' => 'varchar(20)'
			],
			[
				'name' => 'password',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'firstname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'lastname',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'email',
				'type' => 'varchar(96)'
			],
			[
				'name'    => 'image',
				'type'    => 'varchar(255)'
			],
			[
				'name'    => 'code',
				'type'    => 'varchar(40)'
			],
			[
				'name'    => 'ip',
				'type'    => 'varchar(40)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'user_id'
		],
		'foreign' => [
			[
				'key'   => 'user_group_id',
				'table' => 'user_group',
				'field' => 'user_group_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'user_group',
		'field'   => [
			[
				'name'           => 'user_group_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'permission',
				'type' => 'text'
			]
		],
		'primary' => [
			'user_group_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'user_login',
		'field'   => [
			[
				'name'           => 'user_login_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'user_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'ip',
				'type' => 'varchar(40)'
			],
			[
				'name' => 'user_agent',
				'type' => 'varchar(255)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'user_login_id'
		],
		'foreign' => [
			[
				'key'   => 'user_id',
				'table' => 'user',
				'field' => 'user_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'voucher',
		'field'   => [
			[
				'name'           => 'voucher_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(10)'
			],
			[
				'name' => 'from_name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'from_email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'to_name',
				'type' => 'varchar(64)'
			],
			[
				'name' => 'to_email',
				'type' => 'varchar(96)'
			],
			[
				'name' => 'voucher_theme_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'message',
				'type' => 'text'
			],
			[
				'name' => 'amount',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'status',
				'type' => 'tinyint(1)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'voucher_id'
		],
		'foreign' => [
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'voucher_history',
		'field'   => [
			[
				'name'           => 'voucher_history_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'voucher_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'order_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'amount',
				'type' => 'decimal(15,4)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'voucher_history_id'
		],
		'foreign' => [
			[
				'key'   => 'voucher_id',
				'table' => 'voucher',
				'field' => 'voucher_id'
			],
			[
				'key'   => 'order_id',
				'table' => 'order',
				'field' => 'order_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'voucher_theme',
		'field'   => [
			[
				'name'           => 'voucher_theme_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'image',
				'type' => 'varchar(255)'
			]
		],
		'primary' => [
			'voucher_theme_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'voucher_theme_description',
		'field'   => [
			[
				'name' => 'voucher_theme_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(32)'
			]
		],
		'primary' => [
			'voucher_theme_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'weight_class',
		'field'   => [
			[
				'name'           => 'weight_class_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name'    => 'value',
				'type'    => 'decimal(15,8)'
			]
		],
		'primary' => [
			'weight_class_id'
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'weight_class_description',
		'field'   => [
			[
				'name' => 'weight_class_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'language_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'title',
				'type' => 'varchar(32)'
			],
			[
				'name' => 'unit',
				'type' => 'varchar(4)'
			]
		],
		'primary' => [
			'weight_class_id',
			'language_id'
		],
		'foreign' => [
			[
				'key'   => 'language_id',
				'table' => 'language',
				'field' => 'language_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'zone',
		'field'   => [
			[
				'name'           => 'zone_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'country_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'name',
				'type' => 'varchar(128)'
			],
			[
				'name' => 'code',
				'type' => 'varchar(32)'
			],
			[
				'name'    => 'status',
				'type'    => 'tinyint(1)'
			]
		],
		'primary' => [
			'zone_id'
		],
		'foreign' => [
			[
				'key'   => 'country_id',
				'table' => 'country',
				'field' => 'country_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	$tables[] = [
		'name'    => 'zone_to_geo_zone',
		'field'   => [
			[
				'name'           => 'zone_to_geo_zone_id',
				'type'           => 'int(11)',
				'auto_increment' => true
			],
			[
				'name' => 'country_id',
				'type' => 'int(11)'
			],
			[
				'name'    => 'zone_id',
				'type'    => 'int(11)'
			],
			[
				'name' => 'geo_zone_id',
				'type' => 'int(11)'
			],
			[
				'name' => 'date_added',
				'type' => 'datetime'
			],
			[
				'name' => 'date_modified',
				'type' => 'datetime'
			]
		],
		'primary' => [
			'zone_to_geo_zone_id'
		],
		'foreign' => [
			[
				'key'   => 'country_id',
				'table' => 'country',
				'field' => 'country_id'
			],
			[
				'key'   => 'zone_id',
				'table' => 'zone',
				'field' => 'zone_id'
			],
			[
				'key'   => 'geo_zone_id',
				'table' => 'geo_zone',
				'field' => 'geo_zone_id'
			]
		],
		'engine'  => 'MyISAM',
		'charset' => 'utf8mb4',
		'collate' => 'utf8mb4_general_ci'
	];

	return $tables;
}
