<style>
.app-footer-redesign {
    border-top: 1px solid var(--bs-border-color, #e9ecef);
    padding: 18px 24px;
    background: var(--bs-card-bg, #fff);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    text-align: center;
}
.app-footer-copy {
    color: var(--bs-secondary-color, #6c757d);
    font-size: .9rem;
    line-height: 1.55;
    max-width: min(100%, 760px);
}
.app-footer-copy .footer-company {
    color: color-mix(in srgb, var(--bs-primary, #0d6efd) 78%, currentColor);
    font-weight: 700;
}
.app-footer-copy .footer-segment {
    display: inline;
}
@media (max-width: 640px) {
    .app-footer-redesign {
        padding: 14px 16px;
    }
    .app-footer-copy {
        font-size: .84rem;
        line-height: 1.45;
    }
    .app-footer-copy .footer-segment {
        display: block;
    }
}
</style>

<footer class="app-footer app-footer-redesign">
    <p class="app-footer-copy mb-0">
        <span class="footer-segment">Bản quyền © <span id="footer-year"></span></span>
        <span class="footer-segment footer-company">Công ty TNHH Dịch vụ và Kỹ thuật Môi trường Bảo Châu</span>
        <span class="footer-segment">Đã đăng ký mọi quyền.</span>
    </p>
</footer>
