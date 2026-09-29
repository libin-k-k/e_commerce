import { Link } from '@inertiajs/react';

export default function CategoryScrollSection({ items = [] }) {
    return (
        <section className="section section--tight category-strip" aria-label="Quick categories">
            <div className="category-scroll">
                {items.map((item) => (
                    <Link
                        key={item.slug}
                        href={item.href ?? `/products?category=${item.slug}`}
                        className={`category-scroll__item is-tone-${item.tone ?? 'all'}`}
                    >
                        <span
                            className={`category-scroll__avatar${item.variant === 'grid' ? ' category-scroll__avatar--grid' : ''}`}
                        >
                            {item.image ? (
                                <img className="category-scroll__image" src={item.image} alt="" loading="lazy" />
                            ) : (
                                item.short ?? item.name.slice(0, 2)
                            )}
                        </span>
                        <p className="category-scroll__label">{item.name}</p>
                    </Link>
                ))}
            </div>
        </section>
    );
}
