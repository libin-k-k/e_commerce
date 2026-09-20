import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Header({ current = 'home', onOpenCategories, onOpenCart, onOpenWishlist }) {
    const { appName, cartCount = 0, wishlistCount = 0, auth } = usePage().props;
    const [query, setQuery] = useState('');

    const onSearch = (event) => {
        event.preventDefault();
        const q = query.trim();
        router.get('/products', q ? { q } : {});
    };

    return (
        <header className="site-header">
            <div className="site-header__inner">
                <button
                    type="button"
                    className="icon-btn site-header__menu"
                    aria-label="Open categories"
                    onClick={onOpenCategories}
                >
                    <MenuIcon />
                </button>

                <Link href="/" className="site-header__brand">
                    {appName}
                </Link>

                <nav className="site-header__nav" aria-label="Primary">
                    <Link
                        href="/"
                        className={`site-header__nav-link${current === 'home' ? ' is-active' : ''}`}
                        aria-label="Home"
                        title="Home"
                    >
                        <HomeNavIcon className="site-header__nav-icon" />
                    </Link>
                    <button
                        type="button"
                        className={`site-header__nav-link site-header__nav-btn${current === 'categories' ? ' is-active' : ''}`}
                        onClick={onOpenCategories}
                        aria-label="Categories"
                        title="Categories"
                    >
                        <CategoriesNavIcon className="site-header__nav-icon" />
                    </button>
                    <Link
                        href="/products"
                        className={`site-header__nav-link${current === 'products' ? ' is-active' : ''}`}
                        aria-label="Products"
                        title="Products"
                    >
                        <ProductsNavIcon className="site-header__nav-icon" />
                    </Link>
                </nav>

                <div className="site-header__tools">
                    <form className="site-header__search" role="search" onSubmit={onSearch}>
                        <label className="visually-hidden" htmlFor="app-search">
                            Search products
                        </label>
                        <div className="site-header__search-field">
                            <SearchIcon className="site-header__search-icon" />
                            <input
                                id="app-search"
                                className="site-header__search-input"
                                type="search"
                                name="q"
                                value={query}
                                onChange={(event) => setQuery(event.target.value)}
                                placeholder="Search for products, brands and more"
                                autoComplete="off"
                            />
                        </div>
                    </form>

                    <button
                        type="button"
                        className="site-header__location"
                        aria-label="Set delivery location"
                    >
                        <PinIcon className="site-header__location-icon" />
                        <span className="site-header__location-copy">
                            <span className="site-header__location-label">Deliver to</span>
                            <span className="site-header__location-value">
                                Add location for extra discount
                            </span>
                        </span>
                        <span className="site-header__location-chevron" aria-hidden="true">
                            {'>'}
                        </span>
                    </button>
                </div>

                <div className="site-header__actions">
                    <button
                        type="button"
                        className="site-header__action"
                        aria-label="Wishlist"
                        onClick={onOpenWishlist}
                    >
                        <span className="site-header__action-icon-wrap">
                            <HeartIcon />
                            {wishlistCount > 0 ? (
                                <span className="icon-btn__badge">{wishlistCount}</span>
                            ) : null}
                        </span>
                        <span className="site-header__action-label">Wishlist</span>
                    </button>
                    <button
                        type="button"
                        className="site-header__action site-header__action--cart"
                        aria-label="Open cart"
                        onClick={onOpenCart}
                    >
                        <span className="site-header__action-icon-wrap">
                            <CartIcon />
                            {cartCount > 0 ? (
                                <span className="icon-btn__badge">{cartCount}</span>
                            ) : null}
                        </span>
                        <span className="site-header__action-label">Cart</span>
                    </button>
                    <Link
                        href={auth?.user ? '/account' : '/login'}
                        className="site-header__action"
                        aria-label={auth?.user ? 'Account' : 'Sign in'}
                    >
                        <AccountIcon />
                        <span className="site-header__action-label">
                            {auth?.user ? 'Account' : 'Sign in'}
                        </span>
                    </Link>
                </div>
            </div>
        </header>
    );
}

function MenuIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}

function HomeNavIcon({ className }) {
    return (
        <svg className={className} width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M4 10.5L12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function CategoriesNavIcon({ className }) {
    return (
        <svg className={className} width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
            <rect x="13" y="3" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
            <rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
            <rect x="13" y="13" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
        </svg>
    );
}

function ProductsNavIcon({ className }) {
    return (
        <svg className={className} width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M4 9h16l-1.2 11H5.2L4 9z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinejoin="round"
            />
            <path
                d="M8 9V7a4 4 0 0 1 8 0v2"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
            />
        </svg>
    );
}

function HeartIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M6 6h15l-1.5 9h-12L6 6z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinejoin="round"
            />
            <path d="M6 6L5 3H2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <circle cx="9" cy="20" r="1.5" fill="currentColor" />
            <circle cx="17" cy="20" r="1.5" fill="currentColor" />
        </svg>
    );
}

function AccountIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="8" r="3.5" stroke="currentColor" strokeWidth="2" />
            <path
                d="M5 19c1.5-3.5 4-5 7-5s5.5 1.5 7 5"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
            />
        </svg>
    );
}

function SearchIcon({ className }) {
    return (
        <svg className={className} width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="7" stroke="currentColor" strokeWidth="2" />
            <path d="M20 20l-3.5-3.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}

function PinIcon({ className }) {
    return (
        <svg className={className} width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2c-3.9 0-7 3-7 7 0 5.2 7 13 7 13s7-7.8 7-13c0-4-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z" />
        </svg>
    );
}
