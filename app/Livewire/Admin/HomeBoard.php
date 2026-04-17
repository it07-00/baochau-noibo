<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class HomeBoard extends Component
{
    public function mount(): void
    {
        if (auth()->user()->hasAnyRole(['giam-doc', 'admin', 'quan-ly'])) {
            $this->redirect(route('app.dashboard'), navigate: true);
        }
    }

    public function render()
    {
        $user = auth()->user();

        $showSales      = $user->hasAnyRole(['kinh-doanh', 'tp-kinh-doanh']);
        $showConsulting = $user->hasRole('tu-van');
        $showTechnical  = $user->hasRole('ky-thuat');

        return view('livewire.admin.home-board', compact('showSales', 'showConsulting', 'showTechnical'))
            ->layout('admin.layouts.app', ['title' => 'Trang chủ']);
    }
}
