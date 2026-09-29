import { Link } from '@inertiajs/react';

export default function OfferZoneSection({ offers = [] }) {
    return (
        <section className="section offer-zone" aria-labelledby="offer-zone-title">
            <div className="section__header">
                <div className="section__heading">
                    <h2 id="offer-zone-title" className="section__title">
                        <span className="section__title-icon" aria-hidden="true">
                            <BoltIcon />
                        </span>
                        Offer Zone
                    </h2>
                    <p className="section__subtitle">Limited time offers just for you!</p>
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
