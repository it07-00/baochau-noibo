<?php

namespace App\Livewire\Admin\InternalDocs;

use App\Enums\Permission;
use App\Models\InternalDoc;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class InternalDocManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 10;

    // For Create/Edit
    public $docId;
    public $title;
    public $newFiles = [];
    public $existingFiles = [];

    protected $listeners = ['deleteConfirmed' => 'delete'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->docId = null;
        $this->title = '';
        $this->newFiles = [];
        $this->existingFiles = [];
    }

    public function save()
    {
        abort_unless(
            auth()->user()->can($this->docId ? 'internal-docs.edit' : 'internal-docs.create'),
            403
        );

        \Log::info('InternalDocManager: Starting save process', [
            'title' => $this->title,
            'docId' => $this->docId,
            'newFilesCount' => count($this->newFiles)
        ]);

        $this->validate([
            'title' => 'required|string|max:255',
            'newFiles' => ($this->docId ? 'nullable' : 'required') . '|array|max:10',
            'newFiles.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:102400',
        ], [
            'newFiles.required' => 'Vui lòng đính kèm ít nhất 1 file.',
            'newFiles.max' => 'Tối đa 10 file mỗi lần.',
            'newFiles.*.mimes' => 'Chỉ chấp nhận file PDF, Word, Excel, JPG, PNG.',
            'newFiles.*.max' => 'Mỗi file không được vượt quá 100MB.',
        ]);

        try {
            $filesData = $this->existingFiles;
            $uploadDisk = config('filesystems.upload_disk', 'public');

            if ($this->newFiles) {
                foreach ($this->newFiles as $file) {
                    $path = $file->store('internal-docs', $uploadDisk);
                    \Log::info('InternalDocManager: Stored file', ['path' => $path]);

                    $filesData[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => Storage::disk($uploadDisk)->url($path),
                        'path' => $path,
                        'disk' => $uploadDisk,
                    ];
                }
            }

            if ($this->docId) {
                $doc = InternalDoc::find($this->docId);
                $doc->update([
                    'title' => $this->title,
                    'files' => $filesData
                ]);
                $this->dispatch('swal:success', ['message' => 'Cập nhật thành công!']);
            } else {
                InternalDoc::create([
                    'title' => $this->title,
                    'files' => $filesData
                ]);
                $this->dispatch('swal:success', ['message' => 'Thêm mới thành công!']);
            }

            $this->resetFields();
            $this->dispatch('closeModal');

            \Log::info('InternalDocManager: Save process completed successfully');
        } catch (\Exception $e) {
            \Log::error('InternalDocManager: Save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('swal:error', ['message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $doc = InternalDoc::findOrFail($id);
        $this->docId = $doc->id;
        $this->title = $doc->title;
        $this->existingFiles = $doc->files ?? [];
        $this->newFiles = [];
        $this->dispatch('openModal');
    }

    public function removeExistingFile($index)
    {
        unset($this->existingFiles[$index]);
        $this->existingFiles = array_values($this->existingFiles);
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->can(Permission::INTERNAL_DOCS_DELETE->value), 403);

        $doc = InternalDoc::findOrFail($id);

        // Delete physical files
        if ($doc->files) {
            foreach ($doc->files as $file) {
                if (isset($file['path'])) {
                    $disk = $file['disk'] ?? config('filesystems.upload_disk', 'public');

                    if (Storage::disk($disk)->exists($file['path'])) {
                        Storage::disk($disk)->delete($file['path']);
                    } elseif ($disk !== 'public' && Storage::disk('public')->exists($file['path'])) {
                        Storage::disk('public')->delete($file['path']);
                    }
                }
            }
        }

        $doc->delete();
        $this->dispatch('swal:success', ['message' => 'Xóa thành công!']);
    }

    public function render()
    {
        $docs = InternalDoc::query()
            ->when($this->search, function($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $uploadDisk = config('filesystems.upload_disk', 'public');

        $docs->getCollection()->transform(function ($doc) use ($uploadDisk) {
            $doc->files = collect($doc->files ?? [])->map(function ($file) use ($uploadDisk) {
                $path = $file['path'] ?? null;
                $disk = $file['disk'] ?? $uploadDisk;

                if ($path && Storage::disk($disk)->exists($path)) {
                    $file['resolved_url'] = Storage::disk($disk)->url($path);
                } elseif ($path && $disk !== 'public' && Storage::disk('public')->exists($path)) {
                    $file['resolved_url'] = Storage::disk('public')->url($path);
                } else {
                    $file['resolved_url'] = $file['url'] ?? null;
                }

                return $file;
            })->values()->all();

            return $doc;
        });

        return view('livewire.admin.internal-docs.internal-doc-manager', [
            'docs' => $docs
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Quy định']);
    }
}
