import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function OfferZoneBannerSection({ banners = [], stats = {} }) {
    const { appName } = usePage().props;
    const [index, setIndex] = useState(0);
    const total = banners.length;
    const count = stats.count ?? 0;

    useEffect(() => {
        setIndex(0);
    }, [total]);

    useEffect(() => {
        if (total < 2) {
            return undefined;
        }

        const timer = window.setInterval(() => {
            setIndex((current) => (current + 1) % total);
        }, 5500);

        return () => window.clearInterval(timer);
    }, [total]);

    const slide = total > 0
        ? banners[index]
        : {
            title: appName,
            text: 'Deals worth opening - every product with an active discount, in one place.',
            cta: 'Browse all products',
            href: '/products',
            web_image: null,
            mobile_image: null,
            style: 'dark-split',
        };

    const image = slide.web_image || slide.image || slide.mobile_image;

    return (
        <header
            className={`offer-page__hero fade-in is-${slide.style ?? 'dark-split'}${image ? ' has-media' : ''}`}
            aria-labelledby="offer-page-title"
        >
            {image ? (
                <img
                    className="offer-page__hero-media"
                    src={image}
                    alt=""
                    loading="eager"
                />
            ) : null}
            <div className="offer-page__hero-glow" aria-hidden="true" />
            <div className="offer-page__hero-inner">
                <div className="offer-page__hero-copy">
                    <p className="offer-page__eyebrow rise-in">Offer Zone</p>
                    <h1 id="offer-page-title" className="offer-page__title rise-in rise-in-delay-1">
                        {slide.title || appName}
                    </h1>
                    {slide.text ? (
                        <p className="offer-page__lead rise-in rise-in-delay-2">{slide.text}</p>
                    ) : null}
                    <div className="offer-page__meta rise-in rise-in-delay-3">
                        <span className="offer-page__stat">
                            <strong>{count}</strong> {stats.label || 'deals live'}
                        </span>
                        <Link href={slide.href || '/products'} className="offer-page__browse">
                            {slide.cta || 'Browse all products'}
                        </Link>
                    </div>
                </div>
                <aside className="offer-page__hero-aside" aria-hidden="true">
                    <div className="offer-page__hero-panel">
                        <p className="offer-page__hero-panel-label">Limited time</p>
                        <p className="offer-page__hero-panel-value">{count}</p>
                        <p className="offer-page__hero-panel-text">active deals across the store</p>
                    </div>
                </aside>
            </div>

            {total > 1 ? (
                <div className="offer-page__dots" role="tablist" aria-label="Offer banners">
                    {banners.map((item, i) => (
                        <button
                            key={item.id ?? i}
                            type="button"
                            className={`offer-page__dot${i === index ? ' is-active' : ''}`}
                            aria-label={`Show banner ${i + 1}`}
                            aria-selected={i === index}
                            onClick={() => setIndex(i)}
                        />
                    ))}
                </div>
            ) : null}
        </header>
    );
}
