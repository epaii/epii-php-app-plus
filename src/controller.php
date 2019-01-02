<?php

namespace epii\app;

use think\Db;

/**
 * Created by PhpStorm.
 * User: mrren
 * Date: 2018/12/29
 * Time: 5:22 PM
 */
class controller
{
    private $_as = [];
    private $_js_as = [];


    public function assign(string $key, $value)
    {
        $this->_as[$key] = $value;
        return $this;
    }

    public function assignArray(array $data)
    {
        if ($data)
            $this->_as = array_merge($this->_as, $data);
        return $this;
    }

    public function display(string $file, Array $args = null)
    {
        \epii\template\View::display($file, $args, null);

        exit;
    }

    public function adminUiDisplay(string $file, string $title = "",Array $js_arr=[])
    {


        \epii\admin\ui\EpiiAdminUi::showPage(\epii\template\View::fetch($file, $this->_as, null), array_merge($this->_js_as, ["title" => $title],$js_arr));
    }

    public function fetch(string $file, Array $args = null)
    {
        return \epii\template\View::fetch($file, $args, null);

    }

    public function adminUijsArgs($dataOrKey = null, $value = null)
    {
        if ($dataOrKey == null) {
            return $this->_js_as;
        }
        if (is_string($dataOrKey)) {
            if (is_null($value)) {
                return $this->_js_as[$dataOrKey];
            } else {
                $this->_js_as[$dataOrKey] = $value;
            }
        }
        if (is_array($dataOrKey)) {
            $this->_js_as = array_merge($this->_js_as, $dataOrKey);
        }
        return $this->_js_as;
    }

    public function adminUijsAppName($pathtoname = null)
    {
        $this->adminUijsArgs("appName", $pathtoname);
    }


    public function adminUiBaseDisplay(\epii\admin\ui\lib\i\epiiadmin\IEpiiAdminUi $adminUi)
    {
        \epii\admin\ui\EpiiAdminUi::showTopPage($adminUi);
        exit;
    }

    public function tableJsonData($table_name_or_query, $where, callable $row_callback = null, callable $callback = null)
    {
        $query_count = null;
        if (is_string($table_name_or_query)) {
            $query = Db::name($table_name_or_query);
            $query_count = Db::name($table_name_or_query);
            $query->order("id desc");
        } else {
            $query = $table_name_or_query;
            $query_count = clone $table_name_or_query;
        }
        $count = $query_count->where($where)->count();
        $list = $query->where($where)->limit(\epii\server\Args::getVal("offset"), \epii\server\Args::getVal("limit"))->select();
        $outdata = ["rows" => $row_callback ? array_map($row_callback, $list) : $list, "total" => $count];
        if ($callback) {
            $outdata['rows'] = $callback($outdata['rows']);
        }
        return json_encode($outdata, JSON_UNESCAPED_UNICODE);
    }

}