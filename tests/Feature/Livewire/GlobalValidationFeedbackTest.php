<?php

namespace Tests\Feature\Livewire;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class GlobalValidationFeedbackTest extends TestCase
{
    public function test_admin_scripts_load_global_livewire_validation_feedback(): void
    {
        $html = view('admin.partials.scripts')->render();

        $this->assertStringContainsString('assets/js/livewire-validation-feedback.js', $html);
    }

    public function test_server_rendered_forms_receive_a_validation_summary(): void
    {
        $errors = (new ViewErrorBag)->put('default', new MessageBag([
            'email' => ['Email không hợp lệ.'],
            'name' => ['Vui lòng nhập họ tên.'],
        ]));

        $html = view('admin.partials.validation-summary', compact('errors'))->render();

        $this->assertStringContainsString('data-validation-summary', $html);
        $this->assertStringContainsString('Email không hợp lệ.', $html);
        $this->assertStringContainsString('Vui lòng nhập họ tên.', $html);
    }

    public function test_validation_summary_is_hidden_without_errors(): void
    {
        $errors = new ViewErrorBag;

        $html = view('admin.partials.validation-summary', compact('errors'))->render();

        $this->assertSame('', trim($html));
    }
}
