import { Link } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import ProductCard from '../../../Shared/Components/ProductCard';

const ALL_PRODUCTS_HREF = '/products';

export default function CategoryProductRails({ categories = [] }) {
    const trackRef = useRef(null);
    const [atStart, setAtStart] = useState(true);
    const [atEnd, setAtEnd] = useState(false);

    useEffect(() => {
        const track = trackRef.current;

        if (!track) {
            return undefined;
        }

        const syncEdges = () => {
            const maxScroll = track.scrollWidth - track.clientWidth;

            setAtStart(track.scrollLeft <= 4);
            setAtEnd(maxScroll <= 4 || track.scrollLeft >= maxScroll - 4);
        };

        syncEdges();
        track.addEventListener('scroll', syncEdges, { passive: true });
        window.addEventListener('resize', syncEdges);

        return () => {
            track.removeEventListener('scroll', syncEdges);
            window.removeEventListener('resize', syncEdges);
        };
    }, [categories.length]);

    const scrollByPanel = (direction) => {
        const track = trackRef.current;

        if (!track) {
            return;
        }

        const panel = track.querySelector('.category-rails__panel');
        const distance = panel ? panel.getBoundingClientRect().width : track.clientWidth;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        track.scrollBy({
            left: direction * (distance + 12),
            behavior: reduceMotion ? 'auto' : 'smooth',
        });
    };

    const onTrackKeyDown = (event) => {
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            scrollByPanel(1);
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            scrollByPanel(-1);
        }
    };

    if (categories.length === 0) {
        return null;
    }

    return (
        <section className="section category-rails" aria-labelledby="category-rails-title" aria-roledescription="carousel">
            <div className="section__header">
                <div className="section__heading">
                    <h2 id="category-rails-title" className="section__title">
                        Shop by category
                    </h2>
                    <p className="section__subtitle">Scroll sideways for the next category</p>
                </div>
                <Link href={ALL_PRODUCTS_HREF} className="section__link">
                    View all products
                    <ChevronIcon />
                </Link>
            </div>

            <div className="category-rails__scroller">
                {categories.length > 1 ? (
                    <button
                        type="button"
                        className="category-rails__nav category-rails__nav--prev"
                        aria-label="Previous category"
                        disabled={atStart}
                        onClick={() => scrollByPanel(-1)}
                    >
                        <ChevronIcon direction="left" />
                    </button>
                ) : null}

                <div
                    className="category-rails__track"
                    ref={trackRef}
                    tabIndex={0}
                    aria-label="Product categories"
                    onKeyDown={onTrackKeyDown}
                >
                    {categories.map((category) => (
                        <article key={category.slug} className="category-rails__panel" aria-label={category.name}>
                            <div className="category-rails__panel-header">
                                <h3 className="category-rails__name">{category.name}</h3>
                                <Link href={category.href ?? ALL_PRODUCTS_HREF} className="btn btn--outline category-rails__view">
                                    View all
                                </Link>
                            </div>
                            <div className="category-rails__grid">
                                {category.products.map((product) => (
                                    <ProductCard key={product.slug} product={product} titleAs="h4" />
                                ))}
                            </div>
                        </article>
                    ))}
                </div>

                {categories.length > 1 ? (
                    <button
                        type="button"
                        className="category-rails__nav category-rails__nav--next"
                        aria-label="Next category"
                        disabled={atEnd}
                        onClick={() => scrollByPanel(1)}
                    >
                        <ChevronIcon />
                    </button>
                ) : null}
            </div>
        </section>
    );
}

function ChevronIcon({ direction = 'right' }) {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d={direction === 'left' ? 'M15 6l-6 6 6 6' : 'M9 6l6 6-6 6'}
                stroke="currentColor"
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
