<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id', 'document_type', 'title', 'file_path', 'file_name',
    'file_size', 'issued_date', 'expiry_date', 'notes',
])]
class EmployeeDocument extends Model
{
    use HasFactory;

    public const DOCUMENT_TYPES = [
        'cccd_truoc'       => 'CCCD mặt trước',
        'cccd_sau'         => 'CCCD mặt sau',
        'ho_khau'          => 'Sổ hộ khẩu',
        'bang_cap'         => 'Bằng cấp',
        'chung_chi'        => 'Chứng chỉ nghề',
        'kham_suc_khoe'    => 'Giấy khám sức khỏe',
        'anh_the'          => 'Ảnh thẻ 3x4 / 4x6',
        'hop_dong_scan'    => 'Hợp đồng LĐ (bản scan)',
        'quyet_dinh'       => 'Quyết định bổ nhiệm/điều chuyển',
        'so_yeu_ly_lich'   => 'Sơ yếu lý lịch',
        'khac'             => 'Giấy tờ khác',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? $this->document_type;
    }

    /**
     * Format file size for display.
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '—';
        if ($this->file_size < 1024) return $this->file_size . ' B';
        if ($this->file_size < 1048576) return round($this->file_size / 1024, 1) . ' KB';
        return round($this->file_size / 1048576, 1) . ' MB';
    }

    /**
     * Check if the file is an image (for preview).
     */
    public function getIsImageAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
    }
}
