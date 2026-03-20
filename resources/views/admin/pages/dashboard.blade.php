@extends('admin.layouts.app')

@section('title', 'Bảng điều khiển CRM')
@section('page_title', 'Bảng điều khiển CRM')

@php
    $breadcrumbs = [
        ['label' => 'Quản trị', 'url' => route('admin.dashboard')],
        ['label' => 'Bảng điều khiển'],
    ];
@endphp

@section('content')
    <div class="row g-3 mt-1">
        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start flex-wrap gap-3 p-6 flex-row-reverse">
                        <div class="btn-icon bg-glow-primary rounded-pill mb-3"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1">Tổng khách hàng</span>
                            <h3 class="h6 mb-1 fs-5 mb-5">8,430</h3>
                        </div>
                    </div>
                    <div id="total-customers"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start flex-wrap gap-3 p-6 flex-row-reverse">
                        <div class="btn-icon bg-glow-orange rounded-pill btn-lg mb-3"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1">Tổng doanh thu</span>
                            <h3 class="h6 mb-1 fs-5 mb-5">$31,475.00</h3>
                        </div>
                    </div>
                    <div id="total-revenue"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start flex-wrap gap-3 p-6 flex-row-reverse">
                        <div class="btn-icon bg-glow-success rounded-pill btn-lg mb-3"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1">Tổng giao dịch</span>
                            <h3 class="h6 mb-1 fs-5 mb-5">1,250</h3>
                        </div>
                    </div>
                    <div id="total-deals"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0 position-relative z-1">
                    <div class="d-flex align-items-start flex-wrap gap-3 p-6 flex-row-reverse">
                        <div class="btn-icon bg-glow-info rounded-pill btn-lg mb-3"></div>
                        <div class="flex-fill">
                            <span class="fz-13px fw-medium d-block mb-1">Tỉ lệ chuyển đổi</span>
                            <h3 class="h6 mb-1 fs-5 mb-5">24.5%</h3>
                        </div>
                    </div>
                    <div id="conversion-ratio"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xxl-7 col-xl-12">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6 position-relative">
                    <div class="d-flex justify-content-between flex-wrap gap-5 mb-6">
                        <h3 class="h6 mb-0 fs-4 fw-semibold">Tổng quan doanh thu</h3>
                        <select class="form-select form-select-sm w-auto">
                            <option selected>Tháng này</option>
                            <option>Tháng trước</option>
                            <option>3 tháng gần nhất</option>
                        </select>
                    </div>
                    <div id="revenue-analysis"></div>
                </div>
            </div>
        </div>

        <div class="col-xxl-5 col-xl-12">
            <div class="card shadow-custom rounded-custom h-100">
                <div class="card-body p-6 position-relative center-apex-chart">
                    <div class="d-flex justify-content-between flex-wrap gap-5 mb-6">
                        <h3 class="h6 mb-0 fs-4 fw-semibold">Hiệu suất đội ngũ</h3>
                        <select class="form-select form-select-sm w-auto">
                            <option selected>Tháng này</option>
                            <option>Tháng trước</option>
                            <option>3 tháng gần nhất</option>
                        </select>
                    </div>
                    <div id="team-performance"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-7">
                    <h3 class="pure-card-title d-flex align-items-center gap-2 m-0">Tổng quan nguồn khách hàng tiềm năng</h3>
                </div>
                <div class="pure-card-body pb-3">
                    <div class="table-responsive table-check-parent">
                        <table class="table text-nowrap align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-medium">STT</th>
                                    <th class="fw-medium">Khách tiềm năng</th>
                                    <th class="fw-medium">Số điện thoại</th>
                                    <th class="fw-medium">Công ty</th>
                                    <th class="fw-medium">Trạng thái</th>
                                    <th class="fw-medium">Giá trị</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="rounded-pill" src="{{ asset('assets/images/01.jpg') }}" width="30" height="30" alt="Ảnh người dùng">
                                            <div class="ms-3"><h3 class="h6 mb-0 text-custom-body">Skly Herd</h3></div>
                                        </div>
                                    </td>
                                    <td><span class="text-custom-body">+880123456789</span></td>
                                    <td><span class="text-custom-body">Diplus Kia</span></td>
                                    <td><span class="badge fs-3 bg-label-success">Đã liên hệ</span></td>
                                    <td><span class="text-custom-body">$34,000</span></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="rounded-pill" src="{{ asset('assets/images/02.jpg') }}" width="30" height="30" alt="Ảnh người dùng">
                                            <div class="ms-3"><h3 class="h6 mb-0 text-custom-body">Alice Smith</h3></div>
                                        </div>
                                    </td>
                                    <td><span class="text-custom-body">+880198765432</span></td>
                                    <td><span class="text-custom-body">TechNova Ltd.</span></td>
                                    <td><span class="badge fs-3 bg-label-warning">Đang chờ</span></td>
                                    <td><span class="text-custom-body">$22,500</span></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="rounded-pill" src="{{ asset('assets/images/03.jpg') }}" width="30" height="30" alt="Ảnh người dùng">
                                            <div class="ms-3"><h3 class="h6 mb-0 text-custom-body">Michael Lee</h3></div>
                                        </div>
                                    </td>
                                    <td><span class="text-custom-body">+880112233445</span></td>
                                    <td><span class="text-custom-body">Creative Hub</span></td>
                                    <td><span class="badge fs-3 bg-label-info">Mới</span></td>
                                    <td><span class="text-custom-body">$18,000</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard-crm.js') }}"></script>
@endpush
