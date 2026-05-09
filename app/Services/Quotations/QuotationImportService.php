<?php

namespace App\Services\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

final class QuotationImportService
{
    private array $headerMap = [
        'ngày'                      => 'date',
        'ngay'                      => 'date',
        'ngày báo giá'              => 'date',
        'ngày tạo'                  => 'date',
        'ngay tao'                  => 'date',
        'date'                      => 'date',
        'quotation date'            => 'date',
        'nhân viên'                 => 'staff_name',
        'nhan vien'                 => 'staff_name',
        'nhân viên sale'            => 'staff_name',
        'sale'                      => 'staff_name',
        'nguồn'                     => 'source',
        'nguon'                     => 'source',
        'công ty'                   => 'company_name',
        'cong ty'                   => 'company_name',
        'tên công ty'               => 'company_name',
        'ten cong ty'               => 'company_name',
        'địa chỉ xhđ'              => 'address',
        'địa chỉ xuất hóa đơn'     => 'address',
        'dia chi xhd'               => 'address',
        'địa chỉ làm'              => 'work_address',
        'dia chi lam'               => 'work_address',
        'địa chỉ làm việc'         => 'work_address',
        'tỉnh thành'               => 'province',
        'tinh thanh'                => 'province',
        'tỉnh/thành'               => 'province',
        'ngành nghề'               => 'industry',
        'nganh nghe'                => 'industry',
        'ngành'                    => 'industry',
        'dịch vụ'                  => 'service',
        'dich vu'                   => 'service',
        'khách hàng'               => 'contact_person',
        'khach hang'                => 'contact_person',
        'người liên hệ'            => 'contact_person',
        'nguoi lien he'             => 'contact_person',
        'tình hình làm việc'       => 'work_description',
        'tinh hinh lam viec'        => 'work_description',
        'nội dung công việc'       => 'work_description',
        'noi dung'                  => 'work_description',
        'tình hình'                => 'status',
        'tinh hinh'                 => 'status',
        'tình trạng'               => 'status',
        'tinh trang'                => 'status',
        'giá trị gốc'              => 'original_value',
        'gia tri goc'               => 'original_value',
        'giá gốc'                  => 'original_value',
        'gia tri chua vat'          => 'value_inc_vat',
        'giá có vat'               => 'value_inc_vat',
        'gia co vat'                => 'value_inc_vat',
        'hoa hồng kh'              => 'commission_value',
        'hoa hong kh'               => 'commission_value',
        'hoa hồng'                 => 'commission_value',
        'thuế hh'                  => 'commission_tax',
        'thue hh'                   => 'commission_tax',
        'tiền thuế'                => 'commission_tax',
        'giá trị hđ (chưa vat)'   => 'total_value',
        'giá trị hd (chua vat)'    => 'total_value',
        'giá trị hđ (có vat)'     => 'total_value',
        'giá trị hd (co vat)'      => 'total_value',
        'giá trị hđ có vat'       => 'total_value',
        'gia tri hd co vat'        => 'total_value',
        'giá trị hđ'               => 'total_value',
        'tổng tiền'                => 'total_value',
        'tong tien'                 => 'total_value',
        'ghi chú'                  => 'notes',
        'ghi chu'                   => 'notes',
    ];

    public function getAvailableFields(): array
    {
        return [
            ''                  => '-- Bỏ qua --',
            'date'              => 'Ngày',
            'staff_name'        => 'Nhân viên sale',
            'source'            => 'Nguồn',
            'company_name'      => 'Công ty',
            'address'           => 'Địa chỉ XHĐ',
            'work_address'      => 'Địa chỉ làm',
            'province'          => 'Tỉnh thành',
            'industry'          => 'Ngành nghề',
            'service'           => 'Dịch vụ',
            'contact_person'    => 'Khách hàng',
            'work_description'  => 'Tình hình làm việc',
            'status'            => 'Tình hình',
            'original_value'    => 'Giá trị gốc',
            'value_inc_vat'     => 'Giá trị chưa VAT',
            'commission_value'  => 'Hoa hồng KH',
            'commission_tax'    => 'Thuế HH',
            'total_value'       => 'Giá trị HĐ (có VAT)',
            'notes'             => 'Ghi chú',
        ];
    }

    public function previewFile(UploadedFile $file): array
    {
        $result = ['headers' => [], 'preview' => [], 'columnMap' => [], 'errors' => []];

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

            $headerRow = null;
            $dataRows = [];
            foreach ($rows as $row) {
                $nonEmpty = array_filter($row, fn ($v) => $v !== null && $v !== '');
                if (empty($nonEmpty)) {
                    continue;
                }
                if ($headerRow === null) {
                    $headerRow = $row;
                } else {
                    $dataRows[] = $row;
                    if (count($dataRows) >= 5) {
                        break;
                    }
                }
            }

            if (! $headerRow) {
                $result['errors'][] = 'File không có dữ liệu.';
                return $result;
            }

            $result['headers'] = array_values(array_filter($headerRow, fn ($v) => $v !== null && $v !== ''));
            $numCols = count($headerRow);

            foreach ($headerRow as $colIdx => $header) {
                if ($header === null || $header === '') {
                    continue;
                }
                $normalized = mb_strtolower(trim((string) $header));
                $normalizedAscii = $this->normalizeImportHeader((string) $header);
                $result['columnMap'][$colIdx] = $this->headerMap[$normalized]
                    ?? $this->headerMap[$normalizedAscii]
                    ?? '';
            }

            foreach ($dataRows as $row) {
                $result['preview'][] = array_slice($row, 0, $numCols);
            }
        } catch (\Throwable $e) {
            $result['errors'][] = 'Không thể đọc file: ' . $e->getMessage();
        }

        return $result;
    }

    public function runImport(array $columnMap, UploadedFile $file): array
    {
        $result = ['imported' => 0, 'skippedMissingCompany' => 0, 'skippedDuplicates' => 0, 'errors' => []];

        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $headerRowIdx = null;
        foreach ($rows as $i => $row) {
            $nonEmpty = array_filter($row, fn ($v) => $v !== null && $v !== '');
            if (! empty($nonEmpty)) {
                $headerRowIdx = $i;
                break;
            }
        }

        if ($headerRowIdx === null) {
            $result['errors'][] = 'File trống.';
            return $result;
        }

        $staffLookup = User::pluck('id', 'name')->toArray();

        DB::transaction(function () use ($rows, $headerRowIdx, $staffLookup, $columnMap, &$result) {
            foreach ($rows as $i => $row) {
                if ($i <= $headerRowIdx) {
                    continue;
                }
                $nonEmpty = array_filter($row, fn ($v) => $v !== null && $v !== '');
                if (empty($nonEmpty)) {
                    continue;
                }

                $data = [
                    'status'           => QuotationStatus::DANG_THEO_DOI->value,
                    'staff_id'         => auth()->id(),
                    'source'           => null,
                    'service'          => null,
                    'work_address'     => null,
                    'original_value'   => 0,
                    'value_inc_vat'    => 0,
                    'commission_tax'   => 0,
                    'commission_value' => 0,
                    'total_value'      => 0,
                ];

                foreach ($columnMap as $colIdx => $field) {
                    if ($field === '' || ! isset($row[$colIdx])) {
                        continue;
                    }
                    $val = $row[$colIdx];

                    if ($field === 'date') {
                        $data['date'] = $this->parseImportedDate($val);
                    } elseif ($field === 'staff_name') {
                        $staffName = trim((string) $val);
                        $data['staff_id'] = $staffLookup[$staffName] ?? auth()->id();
                    } elseif (in_array($field, ['original_value', 'value_inc_vat', 'commission_tax', 'commission_value', 'total_value'])) {
                        $data[$field] = (float) str_replace([',', '.', ' '], ['', '', ''], (string) $val);
                    } else {
                        $cleanValue = $val !== null ? trim((string) $val) : null;
                        $data[$field] = $cleanValue === '' ? null : $cleanValue;
                    }
                }

                if (empty($data['company_name'])) {
                    $result['skippedMissingCompany']++;
                    continue;
                }
                if (empty($data['date'])) {
                    $data['date'] = now()->format('Y-m-d');
                }

                $originalValue   = round((float) ($data['original_value'] ?? 0));
                $commissionValue = round((float) ($data['commission_value'] ?? 0));
                $commissionTax   = round((float) ($data['commission_tax'] ?? 0));
                $preVatValue     = round($originalValue + $commissionValue + $commissionTax);

                $data['original_value']   = $originalValue;
                $data['commission_value'] = $commissionValue;
                $data['commission_tax']   = $commissionTax;
                $data['value_inc_vat']    = round((float) ($data['value_inc_vat'] ?? 0));
                $data['total_value']      = round((float) ($data['total_value'] ?? 0));

                if ($data['value_inc_vat'] <= 0 && $preVatValue > 0) {
                    $data['value_inc_vat'] = $preVatValue;
                }
                if ($data['total_value'] <= 0 && $preVatValue > 0) {
                    $data['total_value'] = round($preVatValue * 1.08);
                }

                if ($this->isDuplicateImportedQuotation($data)) {
                    $result['skippedDuplicates']++;
                    continue;
                }

                Quotation::create($data);
                $result['imported']++;
            }
        });

        return $result;
    }

    private function normalizeImportHeader(string $header): string
    {
        return Str::of($header)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }

    private function parseImportedDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            $raw = trim((string) $value);
            $digits = preg_replace('/\D+/', '', $raw) ?? '';

            if (strlen($digits) === 8) {
                $ymd = $this->parseDateByFormatStrict('Ymd', $digits);
                if ($ymd) {
                    return $ymd->format('Y-m-d');
                }
                $dmy = $this->parseDateByFormatStrict('dmY', $digits);
                if ($dmy) {
                    return $dmy->format('Y-m-d');
                }
                $mdy = $this->parseDateByFormatStrict('mdY', $digits);
                if ($mdy) {
                    return $mdy->format('Y-m-d');
                }
            }

            $serial = (float) $value;
            if ($serial >= 1 && $serial <= 100000) {
                try {
                    return ExcelDate::excelToDateTimeObject($serial)->format('Y-m-d');
                } catch (\Throwable) {
                    // Fall through to string parsing below.
                }
            }
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $formats = [
            'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'd.m.Y', 'j.n.Y',
            'm/d/Y', 'n/j/Y', 'm-d-Y', 'n-j-Y', 'm.d.Y', 'n.j.Y',
            'Y-m-d', 'Y/m/d', 'Y.m.d',
            'd/m/y', 'j/n/y', 'd-m-y', 'j-n-y', 'd.m.y', 'j.n.y',
            'm/d/y', 'n/j/y', 'm-d-y', 'n-j-y', 'm.d.y', 'n.j.y',
            'd/m/Y H:i', 'd/m/Y H:i:s', 'd-m-Y H:i', 'd-m-Y H:i:s',
            'm/d/Y H:i', 'm/d/Y H:i:s', 'n/j/Y H:i', 'n/j/Y H:i:s',
            'Y-m-d H:i', 'Y-m-d H:i:s', 'Y/m/d H:i', 'Y/m/d H:i:s',
        ];

        foreach ($formats as $format) {
            $parsed = $this->parseDateByFormatStrict($format, $raw);
            if ($parsed instanceof \DateTimeInterface) {
                return $parsed->format('Y-m-d');
            }
        }

        $timestamp = strtotime($raw);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    private function parseDateByFormatStrict(string $format, string $value): ?\DateTimeInterface
    {
        $parsed = \DateTime::createFromFormat('!' . $format, $value);
        if (! $parsed) {
            return null;
        }

        $errors = \DateTime::getLastErrors();
        if (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return null;
        }

        return $parsed;
    }

    private function isDuplicateImportedQuotation(array $data): bool
    {
        $companyName   = trim((string) ($data['company_name'] ?? ''));
        $contactPerson = trim((string) ($data['contact_person'] ?? ''));
        $service       = trim((string) ($data['service'] ?? ''));

        return Quotation::query()
            ->whereDate('date', $data['date'])
            ->where('staff_id', (int) ($data['staff_id'] ?? 0))
            ->whereRaw('TRIM(COALESCE(company_name, "")) = ?', [$companyName])
            ->whereRaw('TRIM(COALESCE(contact_person, "")) = ?', [$contactPerson])
            ->whereRaw('TRIM(COALESCE(service, "")) = ?', [$service])
            ->where('original_value', round((float) ($data['original_value'] ?? 0)))
            ->where('commission_value', round((float) ($data['commission_value'] ?? 0)))
            ->where('commission_tax', round((float) ($data['commission_tax'] ?? 0)))
            ->exists();
    }
}
