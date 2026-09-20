import { Link } from '@inertiajs/react';
import ProductCard from '../../../Shared/Components/ProductCard';

export default function FeaturedProductsSection({ products = [] }) {
    return (
        <section className="section" aria-labelledby="featured-title">
            <div className="section__header">
                <h2 id="featured-title" className="section__title">
                    Popular picks
                </h2>
                <Link href="/products" className="section__link">
                    See more
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
