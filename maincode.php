<?php

// Shortcode to display product inventory progress bar
function dynamic_inventory_progress_bar_shortcode($atts) {
    // Parse attributes with default product ID (current product)
    $atts = shortcode_atts(array(
        'product_id' => get_the_ID(), // Default to current product
    ), $atts, 'inventory_progress_bar');

    // Get the product
    $product = wc_get_product($atts['product_id']);

    if (!$product) {
        return '';
    }

    // Check if product is out of stock
    if (!$product->is_in_stock()) {
        return '<div class="amazing-card__progressbar out-of-stock">
                    <span class="amazing-card__progressbar__title amazing-card__progressbar__title--desktop">وضعیت موجودی</span>
                    <div class="progress position-relative">
                        <div class="progress-bar progress-bar-out-of-stock" role="progressbar">
                            <div class="progress__step">ناموجود</div>
                        </div>
                    </div>
                </div>';
    }

    // Get stock quantity and manage stock status
    $stock_quantity = $product->get_stock_quantity();
    $manage_stock = $product->get_manage_stock();

    // If stock is not managed (unlimited)
    if (!$manage_stock) {
        return '<div class="amazing-card__progressbar unlimited-stock">
                    <span class="amazing-card__progressbar__title amazing-card__progressbar__title--mob"> وضعیت موجودی</span>
                    <div class="progress position-relative">
                        <div class="progress-bar progress-bar-unlimited" role="progressbar">
                            <div class="progress__step">∞</div>
                        </div>
                    </div>
                </div>';
    }

    // Get total stock
    $total_stock = get_post_meta($product->get_id(), '_original_stock', true);
    
    // Fallback if original stock is not set
    if (!$total_stock) {
        $total_stock = $stock_quantity * 1.5; // Estimate initial stock
    }

    // Calculate percentage
    $percentage = $total_stock > 0 ? round(($stock_quantity / $total_stock) * 100) : 0;

    // Output the progress bar HTML
    ob_start();
    ?>
    <div class="niasbar amazing-card__progressbar">
        <span class="amazing-card__progressbar__title amazing-card__progressbar__title--desktop">تعداد باقی مانده</span>
        <div class="progress position-relative">
            <div class="progress-bar" role="progressbar" style="width: <?php echo esc_attr($percentage); ?>%">
                <div class="progress__step"><?php echo esc_html($stock_quantity); ?></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('inventory_progress_bar', 'dynamic_inventory_progress_bar_shortcode');

// Optionally track original stock when product is first created
function save_original_stock($post_id) {
    $product = wc_get_product($post_id);
    if ($product && !get_post_meta($post_id, '_original_stock', true)) {
        update_post_meta($post_id, '_original_stock', $product->get_stock_quantity());
    }
}
add_action('woocommerce_new_product', 'save_original_stock');
add_action('woocommerce_update_product', 'save_original_stock');
