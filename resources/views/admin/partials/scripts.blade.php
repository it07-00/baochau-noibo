<!-- Theme Required Page Scripts -->
<script src="{{ asset('assets/libs/global/global.min.js') }}?v={{ config('app.version') }}" data-navigate-once></script>
<script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}?v={{ config('app.version') }}" data-navigate-once></script>
<script src="{{ asset('assets/libs/datatables/datatables.min.js') }}?v={{ config('app.version') }}" data-navigate-once></script>
<script src="{{ asset('assets/js/appSettings.js') }}?v={{ config('app.version') }}" data-navigate-once></script>
<script src="{{ asset('assets/js/main.js') }}?v={{ config('app.version') }}" data-navigate-once></script>
<script src="{{ asset('assets/js/sweetalert2.js') }}?v={{ config('app.version') }}" data-navigate-once></script>
<script src="{{ asset('assets/js/ckeditor.js') }}?v={{ config('app.version') }}" data-navigate-once></script>

<script>
    // ── Bootstrap Modal Singleton & Backdrop Cleanup for Livewire ──
    (function() {
        if (window.bootstrap && window.bootstrap.Modal) {
            const OriginalModal = window.bootstrap.Modal;
            const ModalWrapper = function(element, config) {
                return OriginalModal.getOrCreateInstance(element, config);
            };
            ModalWrapper.prototype = OriginalModal.prototype;
            Object.setPrototypeOf(ModalWrapper, OriginalModal);
            window.bootstrap.Modal = ModalWrapper;
        }

        function cleanupModalBackdrops() {
            const openModals = Array.from(document.querySelectorAll('.modal')).filter(modal => {
                return modal.classList.contains('show') || modal.style.display === 'block' || window.getComputedStyle(modal).display === 'block';
            });
            if (openModals.length === 0) {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }

        document.addEventListener('hidden.bs.modal', cleanupModalBackdrops);
        document.addEventListener('hide.bs.modal', cleanupModalBackdrops);

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Livewire !== 'undefined') {
                Livewire.hook('request', ({ fail, respond }) => {
                    respond(() => {
                        setTimeout(cleanupModalBackdrops, 300);
                    });
                });
                document.addEventListener('livewire:navigating', () => {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                });
            }
        });
    })();

    // ── Strip diacritics helper (dùng cho Alpine x-show search) ──
    window.__strip = function(s) {
        return s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    };

    // ── Money format helper ─────────────────────────────────────────
    (function () {
        function formatMoney(val) {
            let num = String(val).replace(/\D/g, '');
            if (num === '') return '';
            return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function formatMoneyInput(el, preserveCursor = false) {
            if (!el || !el.classList || !el.classList.contains('money-input')) return;

            let currentValue = el.value ?? '';
            let formatted = formatMoney(currentValue);
            if (formatted === currentValue) return;

            if (preserveCursor && document.activeElement === el) {
                let cursor = el.selectionStart ?? currentValue.length;
                let digitsBeforeCursor = String(currentValue).slice(0, cursor).replace(/\D/g, '').length;

                el.value = formatted;

                let newCursor = formatted.length;
                if (digitsBeforeCursor > 0) {
                    let seen = 0;
                    for (let i = 0; i < formatted.length; i++) {
                        if (/\d/.test(formatted[i])) {
                            seen++;
                        }
                        if (seen >= digitsBeforeCursor) {
                            newCursor = i + 1;
                            break;
                        }
                    }
                }

                if (typeof el.setSelectionRange === 'function') {
                    el.setSelectionRange(newCursor, newCursor);
                }
                return;
            }

            el.value = formatted;
        }

        function formatMoneyInputs(root) {
            if (!root) return;

            if (root.classList && root.classList.contains('money-input')) {
                formatMoneyInput(root, document.activeElement === root);
            }

            if (typeof root.querySelectorAll === 'function') {
                root.querySelectorAll('.money-input').forEach(function (el) {
                    formatMoneyInput(el, document.activeElement === el);
                });
            }
        }

        let isFormatting = false;

        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('money-input') || isFormatting) return;

            isFormatting = true;
            formatMoneyInput(e.target, true);
            isFormatting = false;
        });

        document.addEventListener('blur', function (e) {
            if (!e.target.classList.contains('money-input')) return;
            formatMoneyInput(e.target, false);
        }, true);

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Livewire !== 'undefined') {
                Livewire.hook('morph.updated', ({ el }) => {
                    formatMoneyInputs(el);
                });
            }
            formatMoneyInputs(document);
        });

        document.addEventListener('shown.bs.modal', function () {
            setTimeout(function () {
                formatMoneyInputs(document);
            }, 50);
        });
    })();

    // Toast configuration
    if (!window.Toast) {
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    }

    // Success flash from session
    @if(session('status'))
        window.Toast.fire({ icon: 'success', title: "{{ session('status') }}" });
    @endif

    // Error flash from session
    @if(session('error'))
        window.Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
    @endif

    // Listen for Livewire events
    if (!window._swalListenersRegistered) {
        window._swalListenersRegistered = true;

        window.addEventListener('swal:toast', event => {
            window.Toast.fire({
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
    }
</script>
