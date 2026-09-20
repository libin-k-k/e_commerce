import ProductCard from '../../../Shared/Components/ProductCard';

export default function RelatedProductsSection({ products = [] }) {
    if (products.length === 0) {
        return null;
    }

    return (
        <section className="section" aria-labelledby="related-title">
            <div className="section__header">
                <h2 id="related-title" className="section__title">
                    You may also like
                </h2>
            </div>
            <div className="product-grid">
                {products.map((product) => (
                    <ProductCard key={product.slug} product={product} titleAs="h3" />
                ))}
            </div>
        </section>
    );
}
