<style>
.app-footer-redesign {
    border-top: 1px solid var(--bs-border-color, #e9ecef);
    padding: 18px 24px;
    background: var(--bs-card-bg, #fff);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.app-footer-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: .85rem;
    color: var(--bs-body-color);
    text-decoration: none;
}
.app-footer-brand img { height: 24px; width: auto; opacity: .85; }
.app-footer-copy {
    font-size: .8rem;
    color: var(--bs-secondary-color, #6c757d);
}
.app-footer-copy span { color: var(--bs-primary, #0d6efd); font-weight: 500; }
.app-footer-links {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: .8rem;
}
.app-footer-links a {
    color: var(--bs-secondary-color, #6c757d);
    text-decoration: none;
    transition: color .15s;
}
.app-footer-links a:hover { color: var(--bs-primary, #0d6efd); }
@media (max-width: 640px) {
    .app-footer-redesign { flex-direction: column; align-items: flex-start; gap: 8px; padding: 14px 16px; }
    .app-footer-links { display: none; }
}
</style>

<footer class="app-footer app-footer-redesign">
    <p class="app-footer-copy mb-0">
        Bản quyền © <span id="footer-year"></span>
        <span>Công ty TNHH Môi trường Bảo Châu</span>.
        Đã đăng ký mọi quyền.
    </p>

    <div class="app-footer-links">
        <a href="{{ route('app.profile.index') }}">Hồ sơ</a>
        <a href="{{ route('app.daily-reports.index') }}">Báo cáo ngày</a>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-link p-0 app-footer-links" style="font-size:.8rem;">Đăng xuất</button>
        </form>
    </div>
</footer>
