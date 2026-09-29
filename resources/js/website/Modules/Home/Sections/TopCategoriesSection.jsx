import { Link, usePage } from '@inertiajs/react';
import { useStorefrontActions } from '../../../Shared/Layouts/StorefrontLayout';

const MAX_TILES = 12;
const TONES = ['fashion', 'beauty', 'kids', 'home', 'footwear', 'all'];

export default function TopCategoriesSection() {
    const { categoryMenu = [] } = usePage().props;
    const { openCategories } = useStorefrontActions();

    const tiles = categoryMenu
        .flatMap((category) => category.children ?? [])
        .slice(0, MAX_TILES);

    if (tiles.length === 0) {
        return null;
    }

    return (
        <section className="top-categories" aria-labelledby="top-categories-title">
            <div className="top-categories__header">
                <div>
                    <h2 id="top-categories-title" className="top-categories__title">
                        Top Categories
                    </h2>
                    <p className="top-categories__subtitle">Shop from our most popular categories</p>
                </div>
                <button type="button" className="top-categories__link" onClick={openCategories}>
                    See More
                    <ArrowIcon />
                </button>
            </div>

            <ul className="top-categories__grid">
                {tiles.map((tile, index) => (
                    <li key={tile.slug}>
                        <Link href={tile.href} className={`top-categories__tile is-tone-${TONES[index % TONES.length]}`}>
                            <span className="top-categories__media" aria-hidden="true">
                                {tile.image ? <img src={tile.image} alt="" loading="lazy" /> : null}
                            </span>
                            <span className="top-categories__label">{tile.name}</span>
                        </Link>
                    </li>
                ))}
            </ul>
        </section>
    );
}

function ArrowIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
