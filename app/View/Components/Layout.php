<?php

// Šis fails nodod kopīgā lapas izkārtojuma iestatījumus Blade komponentam.

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Layout extends Component
{
    // Saņem izkārtojuma slēdžus, kurus skati izmanto galvenes, kājenes un platuma maiņai.
    public function __construct(
        public bool $hidePageHeader = false,
        public bool $hideFooter = false,
        public bool $stretchMain = false,
    )
    {
    }

    // Atgriež kopīgā izkārtojuma Blade skatu.
    public function render(): View|Closure|string
    {
        return view('components.layout');
    }
}
