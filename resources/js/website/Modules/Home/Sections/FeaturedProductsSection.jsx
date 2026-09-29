import { Link } from '@inertiajs/react';
import ProductCard from '../../../Shared/Components/ProductCard';

export default function FeaturedProductsSection({ products = [] }) {
    return (
        <section className="section" aria-labelledby="featured-title">
            <div className="section__header">
                <div className="section__heading">
                    <h2 id="featured-title" className="section__title">
                        Popular picks
                    </h2>
                    <p className="section__subtitle">Shop our most loved products</p>
                </div>
                <Link href="/products" className="section__link">
                    See more
                    <ChevronIcon />
                </Link>
            </div>

            <div className="product-grid">
                {products.map((product) => (
                    <ProductCard key={product.slug} product={product} titleAs="h3" />
                ))}
            </div>
        </section>
    );
}

function ChevronIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6l6 6-6 6" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
