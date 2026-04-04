<script src="{{ asset('assets/js/jquery.js') }}"></script>
<script src="{{ asset('assets/js/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/js/conca-sidebar.js') }}"></script>
<script src="{{ asset('assets/js/conca.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/js/ckeditor.js') }}"></script>

<script>
    // ── Strip diacritics helper (dùng cho Alpine x-show search) ──
    window.__strip = function(s) {
        return s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    };
    // ── Money format helper ─────────────────────────────────────────
    // Format số tiền VND: 71900000 → 71.900.000
    // Dùng: <input type="text" class="money-input" wire:model.defer="formData.value">
    // JS format hiển thị, PHP strip dots khi nhận value
    (function () {
        function formatMoney(val) {
            let num = String(val).replace(/\D/g, '');
            if (num === '') return '';
            return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        let isFormatting = false;

        // Format khi user gõ
        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('money-input') || isFormatting) return;

            isFormatting = true;
            let raw = e.target.value.replace(/\./g, '');
            let formatted = formatMoney(raw);
            let pos = e.target.selectionStart;
            let oldLen = e.target.value.length;

            e.target.value = formatted;

            // Adjust cursor
            let newLen = formatted.length;
            pos = pos + (newLen - oldLen);
            if (pos < 0) pos = 0;
            e.target.setSelectionRange(pos, pos);
            isFormatting = false;
        });

        // Format khi Livewire cập nhật DOM (morph)
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Livewire !== 'undefined') {
                Livewire.hook('morph.updated', ({ el }) => {
                    if (el.classList && el.classList.contains('money-input') && document.activeElement !== el) {
                        el.value = formatMoney(el.value);
                    }
                });
            }

            // Format các input đã có giá trị sẵn khi trang load
            document.querySelectorAll('.money-input').forEach(function (el) {
                if (el.value) el.value = formatMoney(el.value);
            });
        });

        // Format khi modal mở (cho giá trị pre-filled từ Livewire)
        document.addEventListener('shown.bs.modal', function () {
            setTimeout(function () {
                document.querySelectorAll('.money-input').forEach(function (el) {
                    if (el.value) el.value = formatMoney(el.value);
                });
            }, 50);
        });
    })();
    // Toast configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Success flash from session
    @if(session('status'))
        Toast.fire({ icon: 'success', title: "{{ session('status') }}" });
    @endif

    // Error flash from session
    @if(session('error'))
        Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
    @endif

    // Listen for Livewire events
    window.addEventListener('swal:toast', event => {
        Toast.fire({
            icon: event.detail[0].type || 'success',
            title: event.detail[0].message,
            showClass: {
                popup: 'animate__animated animate__fadeInRight'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutRight'
            }
        });
    });

    window.addEventListener('swal:confirm', event => {
        Swal.fire({
            title: event.detail[0].title || 'Xác nhận?',
            text: event.detail[0].message || '',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Call the component method back
                window.Livewire.find(event.detail[0].component).call(event.detail[0].method, event.detail[0].id);
            }
        });
    });

    window.addEventListener('swal:success', event => {
        Swal.fire({
            title: 'Thành công!',
            text: event.detail[0].message,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    });
</script>
