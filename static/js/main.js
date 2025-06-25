class CookieBanner {
    constructor() {
        this.banner = document.getElementById('cookie-banner');
        this.resetBtn = document.getElementById('cookie-reset-btn');
        if (!this.banner) return;

        this.acceptBtn = this.banner.querySelector('.cookie-btn.accept');
        this.rejectBtn = this.banner.querySelector('.cookie-btn.details');
        this.originalPadding = document.body.style.paddingBottom || '';

        this.init();
        this.bindResetButton();
    }

    init() {
        const cookieStatus = localStorage.getItem('cookieConsent');

        if (cookieStatus === 'accepted' || cookieStatus === 'rejected') {
            this.hideBanner(true); // 已经选择过，直接隐藏
            return;
        }

        this.showBanner();

        this.acceptBtn?.addEventListener('click', () => this.handleConsent('accepted'));
        this.rejectBtn?.addEventListener('click', () => this.handleConsent('rejected'));
    }

    showBanner() {
        this.banner.style.display = 'block';
        const bannerHeight = this.banner.offsetHeight;

        if (bannerHeight > 0) {
            document.body.style.transition = 'padding-bottom 0.4s ease-in-out';
            document.body.style.paddingBottom = `${bannerHeight}px`;

            requestAnimationFrame(() => {
                this.banner.style.transform = 'translateY(0)';
            });
        }
    }

    hideBanner(skipAnimation = false) {
        const bannerHeight = this.banner.offsetHeight;

        document.body.style.paddingBottom = this.originalPadding || '';

        if (skipAnimation) {
            this.banner.style.display = 'none';
            this.banner.style.transform = `translateY(${bannerHeight}px)`;
            return;
        }

        this.banner.style.transition = 'transform 0.4s ease-in-out';
        this.banner.style.transform = `translateY(${bannerHeight}px)`;

        const onTransitionEnd = () => {
            this.banner.style.display = 'none';
            this.banner.removeEventListener('transitionend', onTransitionEnd);
        };

        this.banner.addEventListener('transitionend', onTransitionEnd);
    }

    handleConsent(type) {
        localStorage.setItem('cookieConsent', type); // 'accepted' or 'rejected'
        this.hideBanner();
    }

    bindResetButton() {
        if (!this.resetBtn) return;

        this.resetBtn.addEventListener('click', () => {
            localStorage.removeItem('cookieConsent');
            this.showBanner();
        });
    }
}
