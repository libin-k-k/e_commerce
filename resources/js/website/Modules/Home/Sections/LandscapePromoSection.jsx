import { Link } from '@inertiajs/react';

export default function LandscapePromoSection({ banners = [], label = 'Promotional banners' }) {
    const webBanners = banners.filter((banner) => banner.web_image || banner.image);

    if (webBanners.length === 0) {
        return null;
    }

    return (
        <section className="section landscape-promos" aria-label={label}>
            <div className="landscape-promos__grid">
                {webBanners.map((banner) => (
                    <Link
                        key={banner.id ?? banner.web_image ?? banner.image}
                        href={banner.href ?? '/products'}
                        className={`landscape-card is-${banner.style ?? 'cinematic'}`}
                    >
                        <img
                            className="landscape-card__image"
                            src={banner.web_image || banner.image}
                            alt=""
                            loading="lazy"
                        />
                        <div className="landscape-card__content">
                            <h2 className="landscape-card__title">{banner.title}</h2>
                            {banner.text ? (
                                <p className="landscape-card__text">{banner.text}</p>
                            ) : null}
                            <span className="landscape-card__cta">{banner.cta ?? 'Explore'}</span>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
}
