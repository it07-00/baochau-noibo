<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class HomeBoard extends Component
{
    public function render()
    {
        $user = auth()->user();

        $showSales      = $user->hasAnyRole(['kinh-doanh', 'tp-kinh-doanh', 'giam-doc']);
        $showConsulting = $user->hasAnyRole(['tu-van', 'giam-doc']);
        $showTechnical  = $user->hasAnyRole(['ky-thuat', 'giam-doc']);

        return view('livewire.admin.home-board', compact('showSales', 'showConsulting', 'showTechnical'))
            ->layout('admin.layouts.app', ['title' => 'Trang chủ']);
    }
}
