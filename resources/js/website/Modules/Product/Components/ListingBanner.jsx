import { Link } from '@inertiajs/react';

export default function ListingBanner({ banner }) {
    const [lead, accent, ...rest] = (banner.title ?? '').split(' ');
    const image = banner.web_image ?? banner.mobile_image;

    return (
        <section className="listing-banner" aria-label={banner.title}>
            <div className="listing-banner__copy">
                <h2 className="listing-banner__title">
                    {lead}
                    {accent ? (
                        <>
                            {' '}
                            <span className="listing-banner__accent">{accent}</span>
                        </>
                    ) : null}
                    {rest.length > 0 ? ` ${rest.join(' ')}` : null}
                </h2>
                {banner.text ? <p className="listing-banner__text">{banner.text}</p> : null}
                <Link href={banner.href} className="listing-banner__cta">
                    {banner.cta}
                    <ArrowIcon />
                </Link>
            </div>
            {image ? (
                <picture className="listing-banner__media">
                    {banner.mobile_image ? (
                        <source media="(max-width: 767.98px)" srcSet={banner.mobile_image} />
                    ) : null}
                    <img src={image} alt="" loading="lazy" />
                </picture>
            ) : null}
        </section>
    );
}

function ArrowIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
