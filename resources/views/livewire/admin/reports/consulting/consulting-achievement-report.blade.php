@include('livewire.admin.reports.partials.dept-race-board', [
    'boardTitle'         => 'Đường Đua Tư Vấn',
    'boardSubtitle'      => 'Chiến Binh Tư Vấn',
    'colLeftTitle'       => '📊 Tỷ Lệ Hoàn Thành',
    'colRightTitle'      => '🏆 Số HĐ Hoàn Thành',
    'completionRankings' => $completionRankings,
    'rateRankings'       => $rateRankings,
    'years'              => $years,
    'year'               => $year,
    'wireYearModel'      => 'year',
])
