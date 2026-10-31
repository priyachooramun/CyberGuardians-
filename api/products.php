<?php
/**
 * JSON product search  live search box in the header and the shop sort/filter
 */
require_once __DIR__ . '/../includes/bootstrap.php';

header('Cache-Control: no-store');

$v       = new Validator($_GET);
$section = $v->clean('section');
$filter  = $v->clean('filter');
$sort    = $v->clean('sort');
$limit   = filter_var($_GET['limit'] ?? 12, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]) ?: 12;

$products = Product::search([
    'q'        => mb_substr($v->clean('q'), 0, 80),
    'section'  => isset(SECTIONS[$section]) ? $section : '',
    'category' => mb_substr($v->clean('category'), 0, 50),
    'filter'   => in_array($filter, Product::FILTERS, true) ? $filter : '',
    'sort'     => isset(Product::SORTS[$sort]) ? $sort : 'newest',
    'limit'    => $limit,
]);

// Only public fields are returned (no internal flags)
json_response([
    'success'  => true,
    'count'    => count($products),
    'products' => array_map(static fn(array $p) => [
        'id'              => (int)$p['product_id'],
        'name'            => $p['name'],
        'brand'           => $p['brand'],
        'section'         => $p['section'],
        'category'        => $p['category'],
        'price'           => (float)$p['price'],
        'final_price'     => (float)$p['final_price'],
        'price_formatted' => money($p['final_price']),
        'on_sale'         => (bool)$p['is_on_sale'],
        'in_stock'        => (int)$p['stock'] > 0,
        'image'           => product_image($p['image']),
        'url'             => url('shop/product.php', ['id' => $p['product_id']]),
    ], $products),
]);
