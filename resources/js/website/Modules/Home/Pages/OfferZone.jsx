import { Head, Link } from '@inertiajs/react';
import ProductCard from '../../../Shared/Components/ProductCard';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import OfferZoneBannerSection from '../Sections/OfferZoneBannerSection';

export default function OfferZone({
    seo,
    products = [],
    banners = [],
    stats = {},
    highlights = [],
}) {
    return (
        <StorefrontLayout seo={seo} current="offers">
            <Head title={seo?.title} />
            <section className="offer-page">
                <OfferZoneBannerSection banners={banners} stats={stats} />

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
