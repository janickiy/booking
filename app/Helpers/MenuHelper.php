<?php

/**
 * @param $cats
 * @param $parent_id
 * @param bool $only_parent
 * @return null|string
 */
function build_tree($cats, $parent_id, $only_parent = false)
{
    if (is_array($cats) && isset($cats[$parent_id])) {
        $tree = '<ul>';
        if ($only_parent == false) {
            foreach ($cats[$parent_id] as $cat) {
                $title = App\Helpers\StringHelpers::ObjectToArray($cat['title']);

                $tree .= '<li>' . $title['ru'] . ' <a title="Добавить подкатегорию" href="' . URL::route('admin.menu.create', ['id' => $cat['id']]) . '"> <span class="fa fa-plus"></span> </a> <a title="Редактировать" href="' . URL::route('admin.menu.edit', ['id' => $cat['id']]) . '"> <span class="fa fa-pencil"></span> </a> <a title="Удалить" href="' . URL::route('admin.menu.delete', ['id'=> $cat['id']]) . '"> <span class="fa fa-trash-o"></span> </a>';
                $tree .= build_tree($cats, $cat['id']);
                $tree .= '</li>';
            }
        } elseif (is_numeric($only_parent)) {

            $cat = $cats[$parent_id][$only_parent];

            $title = App\Helpers\StringHelpers::ObjectToArray($cat['title']);

            $tree .= '<li>' . $title['ru'] . ' #' . $cat['id'];
            $tree .= build_tree($cats, $cat['id']);
            $tree .= '</li>';
        }
        $tree .= '</ul>';
    } else return null;
    return $tree;
}

/**
 * @param $tmp
 * @param $cur_id
 * @return int
 */
function find_parent($tmp, $cur_id)
{
    if ($tmp[$cur_id][0]['parent_id'] != 0) {
        return find_parent($tmp, $tmp[$cur_id][0]['parent_id']);
    }
    return (int)$tmp[$cur_id][0]['id'];
}

/**
 * @param $option
 * @param $parent_id
 * @param int $lvl
 * @return mixed
 */
function ShowTree(&$option, $parent_id, &$lvl = 0)
{
    $lvl++;
    $catalogs = \App\Models\Menu::where('parent_id', $parent_id)->orderBy('item_order')->get();

    foreach ($catalogs as $catalog) {
        $indent = '';
        for ($i = 1; $i < $lvl; $i++) $indent .= '-';

        $title = App\Helpers\StringHelpers::ObjectToArray($catalog->title);

        $option[$catalog->id] = $indent . " " . $title['ru'];
        ShowTree($option, $catalog->id, $lvl);
        $lvl--;
    }

    return $option;
}

/**
 * @param $items
 * @param $parent_id
 * @return mixed
 */
function ShowTreeMenus($parent_id = 0)
{
    $menus = \App\Models\Menu::orderBy('item_order')->remember(360)->status()->get();

    if ($menus) {
        foreach ($menus as $menu) {
            $array_with_elements[$menu->parent_id][] = $menu;
        }

        return add_children($array_with_elements, $parent_id);
    } else
        return null;


}

/**
 * @param $array_with_elements
 * @param $level
 * @return array
 */
function add_children($array_with_elements, $level){

    $nested_array = [];

    if (isset($array_with_elements[$level])) {
        foreach($array_with_elements[$level] as $row){
            $obj = new stdClass();
            $obj->url = $row->menu_type != 'url' && isset($row->itemurl->urlPath) ? preg_replace("/^http:|https:/i", "", url($row->itemurl->urlPath)) : $row->url;
            $obj->title = isset($row->title->{config('app.locale')}) && !empty($row->title->{config('app.locale')}) ? $row->title->{config('app.locale')} : $row->title->ru;
            $obj->item_order = $row->item_order;
            $obj->id = $row->id;
            $obj->parent_id = $row->parent_id;
            $obj->menu_type = $row->menu_type;

            if (isset($array_with_elements[$row->id])){
                $obj->children = add_children($array_with_elements, $row->id);
            }

            $nested_array[] = $obj;
        }
    }

    return $nested_array;
}


/**
 * @param $topbar
 * @param $parent_id
 * @return array
 */
function topbarMenu(&$topbar, $parent_id)
{
    if (is_numeric($parent_id)) {
        $result = \App\Models\Menu::where('id', $parent_id)->orderBy('item_order');

        if ($result->count() > 0) {
            $catalog = $result->first();

            $title = App\Helpers\StringHelpers::ObjectToArray($catalog->title);
            $topbar[] = [$catalog->id, $title['ru']];

            topbarMenu($topbar, $catalog->parent_id);
        }
    }

    sort($topbar);

    return $topbar;
}

/**
 * @param $parent_id
 * @return mixed
 */
function getMenus($parent_id)
{
    if (is_numeric($parent_id)) {
        return \App\Models\Menu::where('parent_id', $parent_id)->status()->orderBy('item_order')->get();
    }
}

/**
 * @param $parent_id
 * @return mixed
 */
function getParrentMenuName($parent_id)
{
    if (is_numeric($parent_id)) {
        $row = \App\Models\Menu::where('id', $parent_id)->status()->orderBy('item_order')->first();
        return $row->title();
    }
}

