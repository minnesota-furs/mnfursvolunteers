<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestV2Layout extends Component
{
    public ?string $title;
    public ?string $ogTitle;
    public ?string $ogDescription;
    public ?string $ogImage;
    public ?string $ogUrl;
    public ?string $ogType;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $title = null,
        ?string $ogTitle = null,
        ?string $ogDescription = null,
        ?string $ogImage = null,
        ?string $ogUrl = null,
        ?string $ogType = null
    ) {
        $this->title = $title;
        $this->ogTitle = $ogTitle ?? $title;
        $this->ogDescription = $ogDescription;
        $this->ogImage = $ogImage;
        $this->ogUrl = $ogUrl ?? request()->url();
        $this->ogType = $ogType ?? 'website';
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guestv2');
    }
}
