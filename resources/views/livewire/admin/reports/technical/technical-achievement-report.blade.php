@include('livewire.admin.reports.partials.dept-race-board', [
    'boardTitle'         => 'Đường Đua Kỹ Thuật',
    'boardSubtitle'      => 'Chiến Binh Kỹ Thuật',
    'colLeftTitle'       => '📊 Tỷ Lệ Hoàn Thành',
    'colRightTitle'      => '🏆 Số HĐ Hoàn Thành',
    'completionRankings' => $completionRankings,
    'rateRankings'       => $rateRankings,
    'years'              => $years,
    'year'               => $year,
    'wireYearModel'      => 'year',
])
