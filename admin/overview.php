<?php

namespace modules\translations\admin;

use m\module;
use m\registry;
use m\view;
use m\i18n;
use m\config;
use m\form;
use modules\admin\admin\overview_data;

class overview extends module {

    protected $css = ['/css/translations.css'];

    public function _init()
    {
        if (!isset($this->view->overview) || !isset($this->view->overview_item)) {
            return false;
        }

        $modules_path = config::get('root_path') . '/m-framework/modules/';

        $i18n_path = config::get('root_path') . config::get('i18n_path');

        if ($this->alias == 'site') {
            $i18n_path .= $this->site->id . '/';
        }
        else if (!empty($this->get->overview)) {

            $module_i18n_path = $modules_path . $this->get->overview . '/client/i18n/';

            if (is_dir($module_i18n_path)) {
                $i18n_path = $module_i18n_path;

                i18n::init('/m-framework/modules/' . $this->get->overview . '/admin/i18n/');

                if (is_file($modules_path . $this->get->overview . '/module.json')) {

                    $module_json = json_decode(file_get_contents($modules_path . '/' . $this->get->overview . '/module.json'), true);

                    view::set('page_title', '<h1><i class="fa fa-language"></i> *Clients translations of module* `' . i18n::get($module_json['title']) . '`</h1>');
                    registry::set('title', i18n::get('Clients translations of module') . ' `' . i18n::get($module_json['title']) . '`');

                    registry::set('breadcrumbs', [
                        '/' . $this->conf->admin_panel_alias . '/translations' => '*Translations*',
                        '' => '*Clients translations of module*'
                    ]);
                }
            }
            else {
                return view::set('content', $this->view->div_notice->prepare([
                    'text' => i18n::get('This module haven\'t a client translations'),
                ]));
            }
        }

        $i18n_path .= $this->language . '.php';

        $translations_arr = [];

        if (is_file($i18n_path)) {
            $i18n_arr = (array)include($i18n_path);

            if (!empty($i18n_arr)) {

//                natcasesort($i18n_arr);

                foreach ($i18n_arr as $key => $value) {

                    if (empty($key)) {
                        continue;
                    }

                    $translations_arr[] = $this->view->overview_item->prepare((object)[
                        'name_code' => urlencode(str_replace('\'', '&#039;', $key)),
                        'name' => str_replace('\'', '&#039;', $key),
                        'value' => str_replace('~', '&#126;', $value),
                    ]);
                }
            }
        }

        return view::set('content', $this->view->overview->prepare([
            'items' => implode("\n", $translations_arr),
        ]));
    }
}
