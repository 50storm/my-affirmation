<?php

namespace MyAffirmationUtility;

/**
 * Debug class
 */
class Debug
{
    public static function debug_vars($vars)
    {
        echo '<div style="background-color:yellow;">';
        echo '<h1>Debug vars</h1>';
        var_dump($vars);
        echo '</div>';
    }
}
/**
 * Validator Class
 */
class Validator
{
    /**
     * is_allowed_action function
     *
     * @param [type] $action
     * @return boolean
     */
    public static function is_allowed_action($action)
    {
        if (!self::notEmptyString($action)) {
            return false;
        }
        $allowed_actions = ['insert', 'update', 'delete' ];
        if (!in_array($action, $allowed_actions)) {
            return false;
        }
        return true;
    }

    /**
     * is_allowed_mode function
     *
     * @return boolean
     */
    public static function is_allowed_mode($mode)
    {
        if (!self::notEmptyString($mode)) {
            return false;
        }
        $allowed_modes = ['add', 'show' ];
        if (!in_array($mode, $allowed_modes)) {
            return false;
        }
        return true;
    }

    /**
     * notEmptyString function
     *
     * @param [type] $text
     * @return boolean
     */
    public static function notEmptyString($text)
    {
        if (is_numeric($text)) {
            return false;
        }
        if (empty($text)) {
            return false;
        }
        return true;
    }

    /**
     * is_number function
     *
     * @param [type] $number
     * @return boolean
     */
    public static function is_number($number)
    {
        if (is_numeric($number)) {
            return true;
        }
        return false;
    }

    /**
     * is_my_affirmation_plugin_page function
     *
     * @param [type] $page
     * @return boolean
     */
    public static function is_my_affirmation_plugin_page($page)
    {
        if (!self::notEmptyString($page)) {
            return false;
        }
        $allowed_modes = ['my_affirmation'];
        if (!in_array($page, $allowed_modes)) {
            return false;
        }
        return true;
    }
}
