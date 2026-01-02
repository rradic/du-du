<?php
/**
 * Plugin Name: WooCommerce FURS Potrditev
 * Description: Vtičnik za davčno potrjevanje računov prek FURS API.
 * Version: 1.0
 * Author: Maj
 */

// prepreči direktni dostop
if (!defined('ABSPATH')) exit;

// vključimo nastavitve
include_once 'includes/settings.php';
include_once 'includes/api-functions.php';
include_once 'includes/hooks.php';
