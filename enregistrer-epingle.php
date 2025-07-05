<?php 

if (!defined('ABSPATH')) {
    exit;
}


// Requête API : fonction AJAX pour enregistrer les images sur Pinterest (api-sandbox avec un token de type sandbox)
add_action('wp_ajax_save_to_pinterest', 'save_to_pinterest_function');

function save_to_pinterest_function() {
    if (!wp_verify_nonce($_POST['nonce'], 'pinterest_bulk_action_nonce')) {
        wp_die('Non autorisé', '', array('response' => 403));
    }

    if (isset($_POST['media_id']) && isset($_POST['board_id'])) {
        $selected_id = $_POST['media_id'];
        $board_id = $_POST['board_id'];

        $imageUrl = wp_get_attachment_url($selected_id);
        $title = get_the_title($selected_id);
        $description = get_post_meta($selected_id, '_wp_attachment_image_alt', true);
        
        $token = get_option('pinterest_add_pin_token'); 

        $data = array(
            'title' => $title ? $title : 'Mon Épingle',
            'link' => $imageUrl, 
            'description' => $description ? $description : 'Description de l\'épingle',
            'board_id' => $board_id, 
            'media_source' => array(
                'source_type' => 'image_url',
                'url' => $imageUrl
            )
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-sandbox.pinterest.com/v5/pins');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);

        if ($response === false) {
            $error_message = curl_error($ch);
            curl_close($ch);
            wp_send_json_error('Erreur cURL: ' . $error_message);
        }

        curl_close($ch);
        $responseData = json_decode($response, true);

        if (isset($responseData['id'])) {
            wp_send_json_success('Épingle ajoutée avec succès. Réponse de l\'API: ' . print_r($responseData, true));
        } else {
            wp_send_json_error('Erreur de l\'API: ' . print_r($responseData, true));
        }
    } else {
        wp_send_json_error('Aucun média ou board sélectionné.');
    }
}