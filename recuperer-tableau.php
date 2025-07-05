<?php 


if (!defined('ABSPATH')) {
    exit;
}


//requête pour récupérer les tableaux (api pinterest classique avec un token de type prod)

add_action('wp_ajax_get_pinterest_boards', 'get_pinterest_boards_function');

function get_pinterest_boards_function() {
    if (!wp_verify_nonce($_POST['nonce'], 'pinterest_board_nonce')) {
        wp_die('Non autorisé', '', array('response' => 403));
    }

    $token = get_option('pinterest_access_token');

 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.pinterest.com/v5/boards');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ));

    $response = curl_exec($ch);

    if ($response === false) {
        wp_send_json_error('Erreur cURL: ' . curl_error($ch));
    }

    curl_close($ch);
    $responseData = json_decode($response, true);

    // vérification que la réponse contient des données
    if (isset($responseData['items'])) {
        wp_send_json_success($responseData);
    } else {
        wp_send_json_error('Erreur lors de la récupération des tableaux.');
    }
}