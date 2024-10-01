<?php
/*
Plugin Name: WP Pinterest
Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-pinterest/
Description: Créez des épingles à partir de vos images dans les actions groupées ou exportez un fichier CSV pour l'importer dans votre compte
Version: 1.0
Author: Kevin Benabdelhak
Author URI: https://kevin-benabdelhak.fr/
Contributors: kevinbenabdelhak
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'options.php';
require_once plugin_dir_path(__FILE__) . 'actions-groupees.php';
require_once plugin_dir_path(__FILE__) . 'recuperer-tableau.php';
require_once plugin_dir_path(__FILE__) . 'recuperer-media.php';
require_once plugin_dir_path(__FILE__) . 'enregistrer-epingle.php';