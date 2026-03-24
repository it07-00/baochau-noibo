<script src="{{ asset('assets/js/jquery.js') }}"></script>
<script src="{{ asset('assets/js/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/js/conca-sidebar.js') }}"></script>
<script src="{{ asset('assets/js/conca.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/js/ckeditor.js') }}"></script>

<script>
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
