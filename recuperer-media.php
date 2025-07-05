<?php 


if (!defined('ABSPATH')) {
    exit;
}

// requête json pour envoyer les données de l'img
add_action('wp_ajax_get_media_details', 'get_media_details_function');
function get_media_details_function() {
    if (!wp_verify_nonce($_POST['nonce'], 'get_media_details_nonce')) {
        wp_die('Non autorisé', '', array('response' => 403));
    }

    if (isset($_POST['media_id'])) {
        $media_id = intval($_POST['media_id']);
        $title = get_the_title($media_id);
        $media_url = wp_get_attachment_url($media_id);
        $description = get_post_meta($media_id, '_wp_attachment_image_alt', true);
        
        // récupère l'URL du post où l'image est téléversée
        $post_id = get_post($media_id)->post_parent; 
        $link = $post_id ? get_permalink($post_id) : $media_url; 

        wp_send_json_success(array(
            'title' => $title,
            'media_url' => $media_url,
            'description' => $description,
            'link' => $link,
        ));
    } else {
        wp_send_json_error('Aucun ID média spécifié.');
    }
}