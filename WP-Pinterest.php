<?php
/*
Plugin Name: WP Pinterest
Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-pinterest/
Description: Créez des épingles à partir de vos images dans les actions groupées ou exportez un fichier CSV pour l'importer dans votre compte
Version: 1.1
Author: Kevin Benabdelhak
Author URI: https://kevin-benabdelhak.fr/
Contributors: kevinbenabdelhak
*/

if (!defined('ABSPATH')) {
    exit;
}



if ( !class_exists( 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
}
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$monUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/kevinbenabdelhak/wp-pinterest/', 
    __FILE__,
    'wp-pinterest' 
);

$monUpdateChecker->setBranch('main');









require_once plugin_dir_path(__FILE__) . 'options.php';
require_once plugin_dir_path(__FILE__) . 'actions-groupees.php';
require_once plugin_dir_path(__FILE__) . 'recuperer-tableau.php';
require_once plugin_dir_path(__FILE__) . 'recuperer-media.php';
require_once plugin_dir_path(__FILE__) . 'enregistrer-epingle.php';
