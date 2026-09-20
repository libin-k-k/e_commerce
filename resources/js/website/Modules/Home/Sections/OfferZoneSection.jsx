import { Link } from '@inertiajs/react';

export default function OfferZoneSection({ offers = [] }) {
    return (
        <section className="section offer-zone" aria-labelledby="offer-zone-title">
            <div className="section__header">
                <h2 id="offer-zone-title" className="section__title">
                    Offer Zone
                    <span className="section__title-icon" aria-hidden="true">
                        <BoltIcon />
                    </span>
                </h2>
                <Link href="/offers" className="section__link">
                    View all
                </Link>
            </div>

            <div className="offer-grid">
                {offers.map((offer) => (
                    <Link
                        key={offer.slug}
                        href={`/products?category=${offer.slug}`}
                        className="offer-card"
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
                        <p className="offer-card__label">{offer.name}</p>
                    </Link>
                ))}
            </div>
        </section>
    );
}

function BoltIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M13 2L4 14h6l-1 8 10-14h-6l1-6z" />
        </svg>
    );
}
