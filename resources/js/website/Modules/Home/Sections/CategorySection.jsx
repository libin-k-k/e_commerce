import { Link } from '@inertiajs/react';

export default function CategorySection({ categories = [] }) {
    return (
        <section className="section" aria-labelledby="categories-title">
            <div className="container">
                <div className="section__header">
                    <h2 id="categories-title" className="section__title">
                        Shop by category
                    </h2>
                    <Link href="/products" className="section__link">
                        View all
                    </Link>
                </div>

                <div className="category-grid">
                    {categories.map((category) => (
                        <Link
                            key={category.slug}
                            href={`/products?category=${category.slug}`}
                            className="category-card"
                        >
                            <p className="category-card__name">{category.name}</p>
                            <p className="category-card__meta">{category.count} items</p>
                        </Link>
                    ))}
                </div>
            </div>
        </section>
    );
}
