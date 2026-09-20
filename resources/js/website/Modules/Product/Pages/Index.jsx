import ProductCard from '../../../Shared/Components/ProductCard';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';

export default function Index({ seo, products = [] }) {
    return (
        <StorefrontLayout seo={seo} current="products">
            <section className="section" aria-labelledby="products-title">
                <div className="section__header">
                    <h1 id="products-title" className="section__title">
                        All products
                    </h1>
                </div>
                <div className="product-grid">
                    {products.map((product) => (
                        <ProductCard key={product.slug} product={product} titleAs="h2" />
                    ))}
                </div>
            </section>
        </StorefrontLayout>
    );
}
