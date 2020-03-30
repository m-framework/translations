<?php

namespace modules\translations\models;

use m\model;
use m\core;
use m\config;
use m\registry;

class translations extends model
{
    public function _before_save()
    {
        $i18n_path = config::get('root_path') . config::get('i18n_path');

        $post = registry::get('post');

        if (registry::get('alias') == 'site') {

            if (is_dir($i18n_path) && !is_dir($i18n_path . registry::get('site')->id)) {
                mkdir($i18n_path . registry::get('site')->id, 0755, true);
            }

            $i18n_path .= registry::get('site')->id . '/';
        }
        else if (!empty($post->route) && preg_match('/.*?\/overview\/([a-z0-9\-\_]+)/si', stripslashes($post->route), $module_match))
        {
            if (!empty($module_match['1'])
                && is_dir(config::get('root_path') . '/m-framework/modules/' . $module_match['1'] . '/client/i18n/')) {
                $i18n_path = config::get('root_path') . '/m-framework/modules/' . $module_match['1'] . '/client/i18n/';
            }
        }

        $i18n_path .= registry::get('language') . '.php';

        $i18n_arr = is_file($i18n_path) ? @include($i18n_path) : [];
        $i18n_arr = (array)$i18n_arr;

        if (!empty($post->id)) {

            $post->id = (htmlspecialchars_decode(html_entity_decode($post->id, ENT_QUOTES | ENT_HTML5), ENT_QUOTES | ENT_HTML5));

            if (isset($i18n_arr[$post->id])) {
                unset($i18n_arr[$post->id]);
            }
        }

        if (!empty($post->name) && isset($post->value)) {

            $post->name = (htmlspecialchars_decode(html_entity_decode($post->name, ENT_QUOTES | ENT_HTML5), ENT_QUOTES | ENT_HTML5));
            $post->value = (htmlspecialchars_decode(html_entity_decode($post->value, ENT_QUOTES | ENT_HTML5), ENT_QUOTES | ENT_HTML5));

            $i18n_arr[$post->name] = $post->value;
        }

        file_put_contents($i18n_path, '<?php' . "\n" . 'return ' . var_export($i18n_arr, true) . ";\n");

        return true;
    }

    public function _before_destroy()
    {
        $i18n_path = config::get('root_path') . config::get('i18n_path') . registry::get('language') . '.php';

        $i18n_arr = (array)@include($i18n_path);

        $post = registry::get('post');

        if (!empty($post->id)) {

            $post->id = stripslashes(htmlspecialchars_decode(html_entity_decode($post->id, ENT_QUOTES | ENT_HTML5), ENT_QUOTES | ENT_HTML5));

            if (isset($i18n_arr[$post->id])) {
                unset($i18n_arr[$post->id]);
            }
        }

        file_put_contents($i18n_path, '<?php' . "\n" . 'return ' . var_export($i18n_arr, true) . ";\n");

        return true;
    }
}