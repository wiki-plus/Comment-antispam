<?php
function wikiplus_validate_comment($comment_data)
{
    // 1. Block usage of <a> tag in the comment
    if (strpos($comment_data['comment_content'], '<a') !== false) {
        wp_die('استفاده از تگ لینک در کامنت‌ها مجاز نیست.');
    }

    // 2. Block usage of http and https in the comment
    if (strpos($comment_data['comment_content'], 'http://') !== false || strpos($comment_data['comment_content'], 'https://') !== false) {
        wp_die('قرار دادن لینک‌های وب در کامنت‌ها مجاز نیست.');
    }

    // 3. Remove website field from the comment form
    add_filter('comment_form_default_fields', 'remove_website_field');
    function remove_website_field($fields)
    {
        if (isset($fields['url'])) {
            unset($fields['url']);
        }
        return $fields;
    }

    // 4. Prevent multiple comments within one minute from the same IP
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $last_comment_time = get_transient('last_comment_' . $ip_address);

    if ($last_comment_time && (time() - $last_comment_time) < 60) {
        wp_die('شما تنها می‌توانید هر دقیقه یک کامنت ارسال کنید.');
    }

    set_transient('last_comment_' . $ip_address, time(), 60);

    // 5. Block spam keywords in the comment
    $spam_keywords = array('free', 'buy now', 'discount', 'viagra', 'casino');
    foreach ($spam_keywords as $keyword) {
        if (stripos($comment_data['comment_content'], $keyword) !== false) {
            wp_die('کامنت شما حاوی کلمات نامناسب است.');
        }
    }

    // 6. Limit the length of the comment (minimum and maximum)
    $min_length = 10;
    $max_length = 500;
    if (strlen($comment_data['comment_content']) < $min_length) {
        wp_die('کامنت شما بسیار کوتاه است.');
    }
    if (strlen($comment_data['comment_content']) > $max_length) {
        wp_die('کامنت شما بسیار طولانی است.');
    }

    return $comment_data;
}

add_filter('preprocess_comment', 'wikiplus_validate_comment');
