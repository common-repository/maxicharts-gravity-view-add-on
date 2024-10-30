<?php
/*
 * Plugin Name: MaxiCharts Gravity View Add-on
 * Plugin URI: https://maxicharts.com
 * Description: Extends MaxiCharts : Add the possibility to filter gf entries on Gravity View approval status
 * Version: 1.2
 * Author: MaxiCharts
 * Author URI: http://www.termel.fr
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: maxicharts_gv
 * Domain Path: /languages
 */
if (! defined('ABSPATH')) {
    exit();
}

//require_once __DIR__ . '/libs/vendor/autoload.php';

if (! class_exists('mcharts_gv_plugin')) {

    class mcharts_gv_plugin
    {
        function __construct()
        {
            if (! class_exists('MAXICHARTSAPI')) {
                $msg = __('Please install MaxiCharts before');
                return $msg;
            }
            self::getLogger()->debug("Adding Module : " . __CLASS__);
            
            add_filter("mcharts_filter_gf_entries", array(
                $this,
                "mcharts_filter_gf_entries_upon_gv_approval_status"
            ), 10, 2);
        }

        static function getLogger()
        {
            if (class_exists('MAXICHARTSAPI')) {
                return MAXICHARTSAPI::getLogger('GV');
            }
        }

        function mcharts_filter_gf_entries_upon_gv_approval_status($entries, $atts)
        {
            if (class_exists('GravityView_frontend')) {
                self::getLogger()->debug("Class exists GravityView_frontend");
                if (method_exists(GravityView_frontend, 'is_entry_approved') /*function_exists('GravityView_frontend::is_entry_approved')*/) {
                    
                    self::getLogger()->debug("function exists is_entry_approved");
                } else {
                    self::getLogger()->debug("function DOES not exists is_entry_approved");
                    return $entries;
                }
            } else {
                self::getLogger()->debug("Class DOES not exists GravityView_frontend");
                return $entries;
            }
            
            self::getLogger()->debug($atts);
            extract(shortcode_atts(array(
                'gv_approve_status' => ''
            ), $atts));
            self::getLogger()->debug($gv_approve_status);
            $gv_approve_status_array = explode(";", str_replace(' ', '', $gv_approve_status));
            self::getLogger()->debug($gv_approve_status_array);
            self::getLogger()->debug("before: " . count($entries));
            $filteredEntries = array();
            if ($gv_approve_status == null || ! $gv_approve_status || empty($gv_approve_status) || empty($gv_approve_status_array)) {
                return $entries;
            }
            if (in_array('approve', $gv_approve_status_array)) {
                foreach ($entries as $entry) {
                    if (GravityView_frontend::is_entry_approved($entry, array())) {
                        $filteredEntries[] = $entry;
                    } else {
                        self::getLogger()->debug("filtering entry : " . $entry['id']);
                    }
                    // self::is_entry_approved( $entry, defined( 'GRAVITYVIEW_FUTURE_CORE_LOADED' ) ? $view->settings->as_atts() : $atts ) )
                }
            }
            self::getLogger()->debug("after: " . count($filteredEntries));
            return $filteredEntries;
        }
    }
}
new mcharts_gv_plugin();