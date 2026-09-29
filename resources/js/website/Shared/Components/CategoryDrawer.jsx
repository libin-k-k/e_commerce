import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useId, useMemo, useRef, useState } from 'react';

const TONES = ['fashion', 'footwear', 'kids', 'beauty', 'home', 'all'];

const toneFor = (index) => TONES[index % TONES.length];

const matches = (value, needle) => String(value ?? '').toLowerCase().includes(needle);

const itemCountLabel = (count) => {
    if (!count) {
        return 'Explore';
    }

    return `${count} item${count === 1 ? '' : 's'}`;
};

export default function CategoryDrawer({ open, onClose, onOpenCart, onOpenWishlist }) {
    const { categoryMenu = [], cartCount = 0, wishlistCount = 0 } = usePage().props;
    const titleId = useId();
    const searchId = useId();
    const searchRef = useRef(null);
    const [query, setQuery] = useState('');
    const categories = useMemo(
        () => (Array.isArray(categoryMenu) ? categoryMenu : []),
        [categoryMenu],
    );
    const [activeId, setActiveId] = useState(categories[0]?.id ?? null);

    const needle = query.trim().toLowerCase();
    const visibleCategories = useMemo(() => {
        if (!needle) {
            return categories;
        }

        return categories.filter(
            (category) =>
                matches(category.name, needle) ||
                (category.children ?? []).some((child) => matches(child.name, needle)),
        );
    }, [categories, needle]);

    useEffect(() => {
        if (open && categories.length > 0) {
            setActiveId((current) => current ?? categories[0].id);
        }
    }, [open, categories]);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.body.classList.add('is-drawer-open');
        window.addEventListener('keydown', onKeyDown);

        return () => {
            document.body.classList.remove('is-drawer-open');
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [open, onClose]);

    const active =
        visibleCategories.find((item) => item.id === activeId) ?? visibleCategories[0] ?? null;
    const activeHref = active?.href ?? (active ? `/products?category=${active.id}` : '/products');
    const subcategories = useMemo(() => {
        const children = active?.children ?? [];

        if (!needle || matches(active?.name, needle)) {
            return children;
        }

        return children.filter((child) => matches(child.name, needle));
    }, [active, needle]);

    const onSearch = (event) => {
        event.preventDefault();
        const q = query.trim();
        onClose();
        router.get('/products', q ? { q } : {});
    };

    return (
        <div
            className={`category-drawer${open ? ' is-open' : ''}`}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-hidden={!open}
        >
            <div className="category-drawer__header">
                <button
                    type="button"
                    className="category-drawer__back"
                    aria-label="Close categories"
                    onClick={onClose}
                >
                    <BackIcon />
                </button>
                <div className="category-drawer__heading-group">
                    <h2 id={titleId} className="category-drawer__title">
                        All Categories
                    </h2>
                    <p className="category-drawer__subtitle">Discover our wide range of products</p>
                </div>
                <div className="category-drawer__actions">
                    <button
                        type="button"
                        className="category-drawer__action"
                        aria-label="Search categories"
                        onClick={() => searchRef.current?.focus()}
                    >
                        <SearchIcon />
                    </button>
                    <button
                        type="button"
                        className="category-drawer__action"
                        aria-label="Wishlist"
                        onClick={onOpenWishlist}
                    >
                        <HeartIcon />
                        {wishlistCount > 0 ? (
                            <span className="category-drawer__badge">{wishlistCount}</span>
                        ) : null}
                    </button>
                    <button
                        type="button"
                        className="category-drawer__action"
                        aria-label="Open cart"
                        onClick={onOpenCart}
                    >
                        <CartIcon />
                        {cartCount > 0 ? (
                            <span className="category-drawer__badge">{cartCount}</span>
                        ) : null}
                    </button>
                </div>
            </div>

            <form className="category-drawer__search" role="search" onSubmit={onSearch}>
                <label className="visually-hidden" htmlFor={searchId}>
                    Search categories and products
                </label>
                <div className="category-drawer__search-field">
                    <SearchIcon className="category-drawer__search-icon" />
                    <input
                        ref={searchRef}
                        id={searchId}
                        className="category-drawer__search-input"
                        type="search"
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Search for categories, products and more..."
                        autoComplete="off"
                    />
                </div>
            </form>

            <div className="category-drawer__body">
                <aside className="category-drawer__sidebar" aria-label="Main categories">
                    {visibleCategories.map((category) => (
                        <button
                            key={category.id}
                            type="button"
                            className={`category-rail__item is-tone-${toneFor(categories.indexOf(category))}${active?.id === category.id ? ' is-active' : ''}`}
                            aria-pressed={active?.id === category.id}
                            onClick={() => setActiveId(category.id)}
                        >
                            <span className="category-rail__icon" aria-hidden="true">
                                {category.image ? (
                                    <img className="category-rail__image" src={category.image} alt="" loading="lazy" />
                                ) : null}
                            </span>
                            <span className="category-rail__label">{category.name}</span>
                            <ChevronIcon className="category-rail__chevron" />
                        </button>
                    ))}
                </aside>

                <div className="category-drawer__panel">
                    {active ? (
                        <>
                            <div className="category-drawer__section-head">
                                <h3 className="category-drawer__heading">Shop by Sub Category</h3>
                                <Link href={activeHref} className="category-drawer__view-all" onClick={onClose}>
                                    View All
                                    <ChevronIcon />
                                </Link>
                            </div>

                            {subcategories.length > 0 ? (
                                <div className="category-subgrid">
                                    {subcategories.map((item, index) => (
                                        <Link
                                            key={item.slug}
                                            href={item.href ?? `/products?category=${item.slug}`}
                                            className={`category-sub is-tone-${toneFor(index)}`}
                                            onClick={onClose}
                                        >
                                            <span className="category-sub__media" aria-hidden="true">
                                                {item.image ? (
                                                    <img className="category-sub__image" src={item.image} alt="" loading="lazy" />
                                                ) : null}
                                            </span>
                                            <span className="category-sub__copy">
                                                <span className="category-sub__label">{item.name}</span>
                                                <span className="category-sub__meta">
                                                    {itemCountLabel(item.itemCount)}
                                                </span>
                                            </span>
                                            <ChevronIcon className="category-sub__chevron" />
                                        </Link>
                                    ))}
                                </div>
                            ) : (
                                <p className="category-drawer__empty">No sub categories yet.</p>
                            )}
                        </>
                    ) : (
                        <div className="category-drawer__empty">
                            <p>No categories match &ldquo;{query.trim()}&rdquo;.</p>
                            <button type="button" className="btn btn--outline" onClick={onSearch}>
                                Search products instead
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

function BackIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M15 5l-7 7 7 7"
                stroke="currentColor"
                strokeWidth="2.2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function SearchIcon({ className }) {
    return (
        <svg className={className} width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="7" stroke="currentColor" strokeWidth="2" />
            <path d="M20 20l-3.5-3.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}

function HeartIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function CartIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6h15l-1.5 9h-12L6 6z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" />
            <path d="M6 6L5 3H2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <circle cx="9" cy="20" r="1.5" fill="currentColor" />
            <circle cx="17" cy="20" r="1.5" fill="currentColor" />
        </svg>
    );
}

function ChevronIcon({ className }) {
    return (
        <svg className={className} width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M9 6l6 6-6 6"
                stroke="currentColor"
                strokeWidth="2.2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
