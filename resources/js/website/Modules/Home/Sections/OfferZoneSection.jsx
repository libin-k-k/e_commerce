import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const pad = (value) => String(value).padStart(2, '0');

export default function OfferZoneSection({ offers = [], offerPage = null }) {
    const page = offerPage ?? {
        title: 'Offer Zone',
        subtitle: 'Limited time offers just for you!',
        theme: 'classic',
        countdownEnabled: false,
        endsAt: null,
        enabled: true,
    };

    if (!page.enabled || offers.length === 0) {
        return null;
    }

    const theme = page.theme || 'classic';

    return (
        <section className={`section offer-zone is-theme-${theme}`} aria-labelledby="offer-zone-title">
            <div className="section__header">
                <div className="section__heading">
                    <h2 id="offer-zone-title" className="section__title">
                        <span className="section__title-icon" aria-hidden="true">
                            <BoltIcon />
                        </span>
                        {page.title || 'Offer Zone'}
                    </h2>
                    {page.subtitle ? <p className="section__subtitle">{page.subtitle}</p> : null}
                    <OfferCountdown endsAt={page.endsAt} enabled={page.countdownEnabled} />
                </div>
                <Link href="/offers" className="section__link">
                    View all
                    <ChevronIcon />
                </Link>
            </div>

            <div className="offer-grid">
                {offers.map((offer) => (
                    <Link
                        key={offer.slug}
                        href={`/products?category=${offer.slug}`}
                        className={`offer-card is-tone-${offer.tone ?? 'all'}`}
                    >
                        <span className="offer-card__badge">{offer.badge}</span>
                        {offer.image ? (
                            <img
                                className="offer-card__media"
                                src={offer.image}
                                alt=""
                                loading="lazy"
                            />
                        ) : (
                            <span className="offer-card__media" aria-hidden="true" />
                        )}
                        <span className="offer-card__footer">
                            <span className="offer-card__label">{offer.name}</span>
                            <span className="offer-card__arrow" aria-hidden="true">
                                <ArrowIcon />
                            </span>
                        </span>
                    </Link>
                ))}
            </div>
        </section>
    );
}

function OfferCountdown({ endsAt, enabled }) {
    const [label, setLabel] = useState('');

    useEffect(() => {
        if (!enabled || !endsAt) {
            setLabel('');

            return undefined;
        }

        const tick = () => {
            const remaining = Math.max(0, Math.floor((new Date(endsAt).getTime() - Date.now()) / 1000));
            const hours = pad(Math.floor(remaining / 3600));
            const minutes = pad(Math.floor((remaining % 3600) / 60));
            const seconds = pad(remaining % 60);
            setLabel(`${hours}h : ${minutes}m : ${seconds}s`);
        };

        tick();
        const timer = window.setInterval(tick, 1000);

        return () => window.clearInterval(timer);
    }, [endsAt, enabled]);

    if (!enabled || !endsAt || label === '') {
        return null;
    }

    return (
        <p className="offer-zone__timer" role="timer" aria-label="Offer ends in">
            Ends in {label}
        </p>
    );
}

function BoltIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M13 2L4 14h6l-1 8 10-14h-6l1-6z" />
        </svg>
    );
}

function ChevronIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6l6 6-6 6" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function ArrowIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
