import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ProductCard from '../../../Shared/Components/ProductCard';

const pad = (value) => String(value).padStart(2, '0');

function secondsUntilMidnight() {
    const now = new Date();
    const midnight = new Date(now);
    midnight.setHours(24, 0, 0, 0);

    return Math.max(0, Math.floor((midnight - now) / 1000));
}

function useMidnightCountdown() {
    const [remaining, setRemaining] = useState(null);

    useEffect(() => {
        setRemaining(secondsUntilMidnight());
        const timer = window.setInterval(() => setRemaining(secondsUntilMidnight()), 1000);

        return () => window.clearInterval(timer);
    }, []);

    return remaining;
}

export default function FlashDealsSection({ products = [], banner = null }) {
    const remaining = useMidnightCountdown();

    if (products.length === 0) {
        return null;
    }

    const hours = remaining === null ? '--' : pad(Math.floor(remaining / 3600));
    const minutes = remaining === null ? '--' : pad(Math.floor((remaining % 3600) / 60));
    const seconds = remaining === null ? '--' : pad(remaining % 60);
    const bannerImage = banner ? (banner.web_image ?? banner.mobile_image) : null;

    return (
        <section className={`flash-deals${banner ? ' has-banner' : ''}`} aria-labelledby="flash-deals-title">
            <div className="flash-deals__main">
                <div className="flash-deals__header">
                    <span className="flash-deals__bolt" aria-hidden="true">
                        <BoltIcon />
                    </span>
                    <div className="flash-deals__heading">
                        <h2 id="flash-deals-title" className="flash-deals__title">
                            Flash Deals
                        </h2>
                        <p className="flash-deals__subtitle">Limited time offers just for you!</p>
                    </div>
                    <p className="flash-deals__timer" role="timer" aria-label="Deals end at midnight">
                        <ClockIcon />
                        <span>
                            {hours}h : {minutes}m : {seconds}s
                        </span>
                    </p>
                    <Link href="/offers" className="flash-deals__link">
                        View All
                        <ArrowIcon />
                    </Link>
                </div>

                <div className="flash-deals__grid">
                    {products.map((product) => (
                        <ProductCard key={product.slug} product={product} titleAs="h3" variant="compact" />
                    ))}
                </div>
            </div>

            {banner ? (
                <Link href={banner.href} className="flash-deals__promo">
                    {bannerImage ? <img className="flash-deals__promo-image" src={bannerImage} alt="" loading="lazy" /> : null}
                    <span className="flash-deals__promo-copy">
                        <span className="flash-deals__promo-title">{banner.title}</span>
                        {banner.text ? <span className="flash-deals__promo-text">{banner.text}</span> : null}
                        <span className="flash-deals__promo-cta">
                            {banner.cta}
                            <ArrowIcon />
                        </span>
                    </span>
                </Link>
            ) : null}
        </section>
    );
}

function BoltIcon() {
    return (
        <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M13.5 2L4 13.5h6.5L9 22l10-12h-6.6L13.5 2z" />
        </svg>
    );
}

function ClockIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="13" r="8" stroke="currentColor" strokeWidth="2" />
            <path d="M12 9v4l2.5 2M9 2h6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}

function ArrowIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
