import { Link, router, useHttp, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useId, useMemo, useState } from 'react';

export const MIN_QUERY_LENGTH = 2;

const RECENT_KEY = 'recent-searches';
const RECENT_LIMIT = 5;
const RECENT_EVENT = 'recent-searches:change';
const SUGGESTED_LIMIT = 6;
const TILE_LIMIT = 8;
const DEBOUNCE_MS = 220;

export const suggestionTerm = (query) => {
    const term = query.trim();

    return term.length >= MIN_QUERY_LENGTH ? term : '';
};

export const productsSearchHref = (term) =>
    term ? `/products?${new URLSearchParams({ q: term })}` : '/products';

const readRecent = () => {
    try {
        const stored = JSON.parse(window.localStorage.getItem(RECENT_KEY) ?? '[]');

        return Array.isArray(stored)
            ? stored.filter((term) => typeof term === 'string' && term !== '').slice(0, RECENT_LIMIT)
            : [];
    } catch {
        return [];
    }
};

const writeRecent = (terms) => {
    try {
        window.localStorage.setItem(RECENT_KEY, JSON.stringify(terms));
    } catch {
        // Storage can be unavailable (private mode); recent searches are best-effort.
    }

    window.dispatchEvent(new Event(RECENT_EVENT));
};

/**
 * Recent search terms kept in localStorage and shared by every search surface.
 */
export function useRecentSearches() {
    const [recent, setRecent] = useState([]);

    useEffect(() => {
        const sync = () => setRecent(readRecent());

        sync();
        window.addEventListener(RECENT_EVENT, sync);
        window.addEventListener('storage', sync);

        return () => {
            window.removeEventListener(RECENT_EVENT, sync);
            window.removeEventListener('storage', sync);
        };
    }, []);

    const add = useCallback((term) => {
        const value = term.trim();

        if (!value) {
            return;
        }

        const others = readRecent().filter((item) => item.toLowerCase() !== value.toLowerCase());
        writeRecent([value, ...others].slice(0, RECENT_LIMIT));
    }, []);

    const remove = useCallback((term) => writeRecent(readRecent().filter((item) => item !== term)), []);

    const clear = useCallback(() => writeRecent([]), []);

    return { recent, add, remove, clear };
}

/**
 * Debounced product suggestions for the typed query. An empty term returns
 * the featured products, flagged with `matched: false`.
 */
export function useProductSuggestions(query, enabled) {
    const http = useHttp();
    const [result, setResult] = useState(null);
    const term = suggestionTerm(query);

    useEffect(() => {
        if (!enabled) {
            return undefined;
        }

        let current = true;
        const timer = window.setTimeout(
            () => {
                http.get(`/products/suggestions?${new URLSearchParams({ q: term })}`)
                    .then((data) => {
                        if (current) {
                            setResult(data);
                        }
                    })
                    .catch(() => {});
            },
            term ? DEBOUNCE_MS : 0,
        );

        return () => {
            current = false;
            window.clearTimeout(timer);
        };
    }, [term, enabled]);

    const fresh = result !== null && result.query === term;

    return { term, result: fresh ? result : null, loading: enabled && !fresh };
}

function useDiscoverLinks() {
    const { categoryMenu = [] } = usePage().props;

    return useMemo(() => {
        const mains = (Array.isArray(categoryMenu) ? categoryMenu : []).filter((main) => main.href);
        const children = mains.flatMap((main) => main.children ?? []).filter((child) => child.href);
        const suggested = children.slice(0, SUGGESTED_LIMIT);
        const tiles = [...mains, ...children.slice(SUGGESTED_LIMIT)]
            .filter((item) => item.image)
            .slice(0, TILE_LIMIT);

        return { suggested, tiles };
    }, [categoryMenu]);
}

export function RecentSearches({ recent, onSelect, onRemove, onClear }) {
    const titleId = useId();

    if (recent.length === 0) {
        return null;
    }

    return (
        <section className="search-section" aria-labelledby={titleId}>
            <div className="search-section__head">
                <h3 id={titleId} className="search-section__title">
                    Recent Searches
                </h3>
                <button type="button" className="search-section__action" onClick={onClear}>
                    Clear All
                </button>
            </div>
            <ul className="search-recent">
                {recent.map((term) => (
                    <li key={term} className="search-recent__item">
                        <button type="button" className="search-recent__term" onClick={() => onSelect(term)}>
                            <ClockIcon className="search-recent__icon" />
                            <span>{term}</span>
                        </button>
                        <button
                            type="button"
                            className="search-recent__remove"
                            aria-label={`Remove ${term} from recent searches`}
                            onClick={() => onRemove(term)}
                        >
                            <CloseIcon />
                        </button>
                    </li>
                ))}
            </ul>
        </section>
    );
}

export function DiscoverSections({ onNavigate }) {
    const { suggested, tiles } = useDiscoverLinks();
    const suggestedId = useId();
    const categoriesId = useId();

    return (
        <>
            {suggested.length > 0 ? (
                <section className="search-section" aria-labelledby={suggestedId}>
                    <div className="search-section__head">
                        <h3 id={suggestedId} className="search-section__title">
                            Suggested Searches
                        </h3>
                    </div>
                    <ul className="search-chips">
                        {suggested.map((item) => (
                            <li key={item.href}>
                                <Link href={item.href} className="search-chip" onClick={onNavigate}>
                                    <SearchIcon className="search-chip__icon" />
                                    <span>{item.name}</span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}

            {tiles.length > 0 ? (
                <section className="search-section" aria-labelledby={categoriesId}>
                    <div className="search-section__head">
                        <h3 id={categoriesId} className="search-section__title">
                            Shop by Category
                        </h3>
                    </div>
                    <ul className="search-tiles">
                        {tiles.map((item) => (
                            <li key={item.href}>
                                <Link href={item.href} className="search-tile" onClick={onNavigate}>
                                    <span className="search-tile__media">
                                        <img src={item.image} alt="" loading="lazy" />
                                    </span>
                                    <span className="search-tile__name">{item.name}</span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}
        </>
    );
}

export function ProductSuggestions({ term, result, loading, onNavigate }) {
    const products = result?.products ?? [];
    const matched = result?.matched ?? term !== '';
    const title = matched ? 'Product Suggestions' : 'Featured Products';
    const titleId = useId();

    return (
        <section className="search-section search-section--products" aria-labelledby={titleId} aria-busy={loading}>
            <div className="search-section__head">
                <h3 id={titleId} className="search-section__title">
                    {title}
                </h3>
                <Link href={productsSearchHref(term)} className="search-section__link" onClick={onNavigate}>
                    View All
                    <ChevronIcon />
                </Link>
            </div>

            {loading && products.length === 0 ? (
                <ul className="search-products" aria-hidden="true">
                    {[0, 1, 2].map((index) => (
                        <li key={index} className="search-product search-product--skeleton">
                            <span className="search-product__media" />
                            <span className="search-product__body">
                                <span className="search-skeleton-line" />
                                <span className="search-skeleton-line search-skeleton-line--short" />
                            </span>
                        </li>
                    ))}
                </ul>
            ) : null}

            {!loading && products.length === 0 ? (
                <p className="search-empty">
                    {matched ? (
                        <>
                            No products match <strong>“{term}”</strong>. Try another word or browse a category.
                        </>
                    ) : (
                        'No products to show yet.'
                    )}
                </p>
            ) : null}

            {products.length > 0 ? (
                <ul className="search-products">
                    {products.map((product) => (
                        <SearchProductRow key={product.id} product={product} onNavigate={onNavigate} />
                    ))}
                </ul>
            ) : null}

            {matched && result && result.total > products.length ? (
                <Link href={productsSearchHref(term)} className="search-products__all" onClick={onNavigate}>
                    See all {result.total} results for “{term}”
                </Link>
            ) : null}
        </section>
    );
}

function SearchProductRow({ product, onNavigate }) {
    const [cartState, setCartState] = useState('idle');
    const href = `/products/${product.slug}`;
    const badge = product.badge ?? (product.isNew ? 'New' : null);

    const addToCart = () => {
        router.post(
            '/cart',
            { product_id: product.id, quantity: 1 },
            {
                preserveScroll: true,
                preserveState: true,
                onStart: () => setCartState('adding'),
                onSuccess: () => setCartState('added'),
                onError: () => setCartState('idle'),
                onFinish: () => setCartState((state) => (state === 'adding' ? 'idle' : state)),
            },
        );
    };

    return (
        <li className="search-product">
            <Link href={href} className="search-product__link" onClick={onNavigate}>
                <span className="search-product__media">
                    {product.image ? <img src={product.image} alt="" loading="lazy" /> : null}
                    {badge ? (
                        <span className={`search-product__badge${product.badge ? '' : ' search-product__badge--new'}`}>
                            {badge}
                        </span>
                    ) : null}
                </span>
                <span className="search-product__body">
                    <span className="search-product__name">{product.name}</span>
                    {product.categoryName ? (
                        <span className="search-product__category">{product.categoryName}</span>
                    ) : null}
                    {product.rating > 0 ? (
                        <span className="search-product__rating">
                            <StarIcon />
                            {Number(product.rating).toFixed(1)}
                        </span>
                    ) : null}
                    <span className="search-product__price">
                        <strong>{product.price}</strong>
                        {product.compareAtPrice ? <s>{product.compareAtPrice}</s> : null}
                    </span>
                </span>
            </Link>

            {product.hasVariants ? (
                <Link
                    href={href}
                    className="search-product__cart"
                    aria-label={`Choose options for ${product.name}`}
                    title="Choose options"
                    onClick={onNavigate}
                >
                    <CartIcon />
                </Link>
            ) : (
                <button
                    type="button"
                    className={`search-product__cart${cartState === 'added' ? ' is-added' : ''}`}
                    aria-label={cartState === 'added' ? `${product.name} added to cart` : `Add ${product.name} to cart`}
                    title={product.inStock ? 'Add to cart' : 'Out of stock'}
                    disabled={!product.inStock || cartState === 'adding'}
                    onClick={addToCart}
                >
                    {cartState === 'added' ? <CheckIcon /> : <CartIcon />}
                </button>
            )}
        </li>
    );
}

export function SearchIcon({ className }) {
    return (
        <svg className={className} width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="7" stroke="currentColor" strokeWidth="2" />
            <path d="M20 20l-3.5-3.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}

export function CloseIcon({ className }) {
    return (
        <svg className={className} width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}

function ClockIcon({ className }) {
    return (
        <svg className={className} width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="8.5" stroke="currentColor" strokeWidth="1.8" />
            <path d="M12 7.5V12l3 2" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function ChevronIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6l6 6-6 6" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function StarIcon() {
    return (
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9L12 2.8z" />
        </svg>
    );
}

function CartIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6h15l-1.5 9h-12L6 6z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" />
            <path d="M6 6L5 3H2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <circle cx="9" cy="20" r="1.5" fill="currentColor" />
            <circle cx="17" cy="20" r="1.5" fill="currentColor" />
        </svg>
    );
}

function CheckIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
