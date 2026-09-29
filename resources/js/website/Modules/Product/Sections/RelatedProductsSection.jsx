import { Link } from '@inertiajs/react';
import ProductCard from '../../../Shared/Components/ProductCard';

export default function RelatedProductsSection({ products = [], seeAllHref = '/products' }) {
    if (products.length === 0) {
        return null;
    }

    return (
        <section className="pdp-related" aria-labelledby="pdp-related-title">
            <div className="pdp-related__header">
                <h2 id="pdp-related-title" className="pdp-related__title">
                    You May Also Like
                </h2>
                <Link href={seeAllHref} className="pdp-related__all">
                    See All
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                </Link>
            </div>
            <div className="pdp-related__list">
                {products.map((product) => (
                    <ProductCard key={product.slug} product={product} titleAs="h3" />
                ))}
            </div>
        </section>
    );
}
