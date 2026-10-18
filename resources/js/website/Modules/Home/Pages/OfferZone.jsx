import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ProductCard from '../../../Shared/Components/ProductCard';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import OfferZoneBannerSection from '../Sections/OfferZoneBannerSection';

export default function OfferZone({
    seo,
    products = [],
    banners = [],
    stats = {},
    highlights = [],
    schedule = null,
    offerPage = null,
}) {
    const theme = offerPage?.theme || 'classic';

    return (
        <StorefrontLayout seo={seo} current="offers">
            <Head title={seo?.title} />
            <section className={`offer-page is-theme-${theme}`}>
                <OfferZoneBannerSection
                    banners={banners}
                    stats={stats}
                    eyebrow={offerPage?.eyebrow}
                    schedule={schedule}
                />

                {highlights.length > 0 ? (
                    <div className="offer-page__chips-wrap">
                        <div className="offer-page__chips" aria-label="Offer categories">
                            {highlights.map((offer) => (
                                <Link
                                    key={offer.slug}
                                    href={`/products?category=${offer.slug}`}
                                    className="offer-page__chip"
                                >
                                    <span className="offer-page__chip-badge">{offer.badge}</span>
                                    <span className="offer-page__chip-name">{offer.name}</span>
                                </Link>
                            ))}
                        </div>
                    </div>
                ) : null}

                {schedule?.countdownEnabled && schedule.endsAt ? (
                    <OfferCountdown endsAt={schedule.endsAt} />
                ) : null}

                <div className="offer-page__body">
                    {products.length === 0 ? (
                        <div className="offer-page__empty">
                            <p>No live offers right now. Check back soon.</p>
                            <Link href="/products" className="btn btn--primary">
                                Shop products
                            </Link>
                        </div>
                    ) : (
                        <div className="offer-page__grid product-grid">
                            {products.map((product) => (
                                <ProductCard key={product.slug} product={product} titleAs="h2" />
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </StorefrontLayout>
    );
}

function OfferCountdown({ endsAt }) {
    const [label, setLabel] = useState('');

    useEffect(() => {
        const tick = () => {
            const remaining = Math.max(0, Math.floor((new Date(endsAt).getTime() - Date.now()) / 1000));
            const hours = String(Math.floor(remaining / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
            const seconds = String(remaining % 60).padStart(2, '0');
            setLabel(`${hours}h : ${minutes}m : ${seconds}s`);
        };

        tick();
        const timer = window.setInterval(tick, 1000);

        return () => window.clearInterval(timer);
    }, [endsAt]);

    return (
        <p className="offer-page__countdown" role="timer" aria-label="Offer ends in">
            Ends in {label}
        </p>
    );
}
