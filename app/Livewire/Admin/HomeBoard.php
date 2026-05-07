<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use Livewire\Component;

class HomeBoard extends Component
{
    public function mount(): void
    {
        if (auth()->user()->hasAnyRole([Role::IT->value, Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::KE_TOAN->value])) {
            $this->redirect(route('app.dashboard'), navigate: true);
        }
    }

    public function render()
    {
        $user = auth()->user();

        $showSales      = $user->hasAnyRole([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]);
        $showConsulting = $user->hasRole(Role::TU_VAN->value);
        $showTechnical  = $user->hasRole(Role::KY_THUAT->value);

        return view('livewire.admin.home-board', compact('showSales', 'showConsulting', 'showTechnical'))
            ->layout('admin.layouts.app', ['title' => 'Trang chủ']);
    }
}
