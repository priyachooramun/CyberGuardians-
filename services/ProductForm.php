<?php
/**
 * Validates the admin "Add / Edit product" form (shared by create.php and edit.php)

 */

declare(strict_types=1);

final class ProductForm
{
    /**
     * @return array{data: array, stock: int, variants: array, errors: array, new_image: ?string}
     */
    public static function handle(array $post, array $files, ?array $existing = null): array
    {
        $v = new Validator($post);
        $v->required('name', 'Product name')->maxLength('name', 100, 'Product name')
          ->in('section', array_keys(SECTIONS), 'section')
          ->required('description', 'Description')->maxLength('description', 3000, 'Description')
          ->money('price', 'Price')
          ->integer('stock', 'Stock quantity', 0, 100000)
          ->maxLength('brand', 100, 'Brand / author');

        $section  = $v->clean('section');
        $category = $v->clean('category');
        if (isset(SECTIONS[$section]) && !in_array($category, SECTIONS[$section], true)) {
            $v->add('category', 'Please choose a category that belongs to this section.');
        }

        $onSale    = !empty($post['is_on_sale']) ? 1 : 0;
        $salePrice = null;
        if ($onSale) {
            $v->money('sale_price', 'Sale price');
            $salePrice = $v->clean('sale_price');
            if (!isset($v->errors()['sale_price']) && !isset($v->errors()['price']) && (float)$salePrice >= (float)$v->clean('price')) {
                $v->add('sale_price', 'The sale price must be lower than the normal price.');
            }
        }

        $variants = array_slice(array_filter(array_map('trim', explode(',', $v->clean('variants'))), 'strlen'), 0, 20);
        foreach ($variants as $variant) {
            if (mb_strlen($variant) > 100) {
                $v->add('variants', 'Each option must be 100 characters or fewer.');
            }
        }

        // Only upload the picture when everything else is valid
        $image    = $existing['image'] ?? null;
        $newImage = null;
        if (!$v->fails()) {
            [$newImage, $uploadError] = ImageUpload::store($files['image'] ?? null);
            if ($uploadError) {
                security_log('upload_rejected', $uploadError);
                $v->add('image', $uploadError);
            } elseif ($newImage) {
                $image = $newImage;
            }
        }
        if (!empty($post['remove_image']) && !$newImage) {
            $image = null;
        }

        return [
            'data' => [
                'name'        => $v->clean('name'),
                'description' => $v->clean('description'),
                'price'       => round((float)$v->clean('price'), 2),
                'section'     => $section,
                'category'    => $category,
                'brand'       => $v->clean('brand') ?: null,
                'image'       => $image,
                'is_on_sale'  => $onSale,
                'sale_price'  => $onSale && $salePrice !== null ? round((float)$salePrice, 2) : null,
                'is_featured' => !empty($post['is_featured']) ? 1 : 0,
            ],
            'stock'     => (int)$v->clean('stock'),
            'variants'  => $variants,
            'errors'    => $v->errors(),
            'new_image' => $newImage,
        ];
    }
}
