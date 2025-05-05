<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

// layout component for shared page structure
class Layout extends Component
{
    // component constructor
    public function __construct()
    {
    }

    // render the component view
    public function render(): View|Closure|string
    {
        return view('components.layout');
    }
}
