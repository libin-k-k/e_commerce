import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function AppPromoBanner() {
    const { appName } = usePage().props;
    const [hidden, setHidden] = useState(false);

    useEffect(() => {
        if (sessionStorage.getItem('app-promo-hidden') === '1') {
            setHidden(true);
        }
    }, []);

    const dismiss = () => {
        sessionStorage.setItem('app-promo-hidden', '1');
        setHidden(true);
    };

    return (
        <div className={`app-promo${hidden ? ' is-hidden' : ''}`} role="region" aria-label="Promotion">
            <div className="app-promo__content">
                <p className="app-promo__text">
                    Extra 35% off · Download the {appName} app for exclusive deals
                </p>
                <Link href="/products" className="btn btn--primary btn--compact">
                    Download Now
                </Link>
            </div>
            <button type="button" className="app-promo__close" aria-label="Dismiss promo" onClick={dismiss}>
                <CloseIcon />
            </button>
        </div>
    );
}

function CloseIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}
