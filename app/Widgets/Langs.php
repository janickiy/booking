<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;
use App\Models\Languages;

class Langs extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

        $languages = Languages::where('hide',0)->get();
        $config = $this->config;

        return view('widgets.languages', compact('config', 'languages'));
    }
}
