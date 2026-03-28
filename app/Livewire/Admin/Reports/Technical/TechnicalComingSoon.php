<?php

namespace App\Livewire\Admin\Reports\Technical;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class TechnicalComingSoon extends Component
{
    public string $page_title = 'Đang phát triển';

    public function mount(): void
    {
        $routeName = Route::currentRouteName();
        $this->page_title = match($routeName) {
            'app.reports.technical.vehicle'   => 'Lịch xe',
            'app.reports.technical.materials' => 'Vật tư',
            default                           => 'Đang phát triển',
        };
    }

    public function render()
    {
        return view('livewire.admin.reports.technical.technical-coming-soon')
            ->layout('admin.layouts.app');
    }
}
