import { router, usePage } from '@inertiajs/react';
import { useEffect, useId, useRef, useState } from 'react';
import { useStorefrontActions } from '../../../Shared/Layouts/StorefrontLayout';

export default function ListingHeader({
    title,
    total = null,
    query = '',
    onSearch,
    titleAs: TitleTag = 'h1',
    placeholder = 'Search in this list...',
    className = '',
}) {
    const { cartCount = 0, wishlistCount = 0 } = usePage().props;
    const { openCart, openWishlist, openSearch } = useStorefrontActions();
    const searchesInline = typeof onSearch === 'function';
    const searchId = useId();
    const inputRef = useRef(null);
    const [searchOpen, setSearchOpen] = useState(query !== '');
    const [value, setValue] = useState(query);

    useEffect(() => {
        setValue(query);
    }, [query]);

    useEffect(() => {
        if (searchOpen) {
            inputRef.current?.focus();
        }
    }, [searchOpen]);

    const goBack = () => {
        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        router.visit('/');
    };

    const submit = (event) => {
        event.preventDefault();
        onSearch(value.trim() || null);
    };

    return (
        <header className={`listing-header${className ? ` ${className}` : ''}`}>
            <div className="listing-header__bar">
                <button type="button" className="listing-header__icon listing-header__back" aria-label="Go back" onClick={goBack}>
                    <BackIcon />
                </button>
                <div className="listing-header__heading">
                    <TitleTag className="listing-header__title">{title}</TitleTag>
                    {total !== null ? (
                        <p className="listing-header__count">
                            {total.toLocaleString('en-IN')} {total === 1 ? 'product' : 'products'}
                        </p>
                    ) : null}
                </div>
                <div className="listing-header__actions">
                    {searchesInline ? (
                        <button
                            type="button"
                            className={`listing-header__icon${searchOpen ? ' is-active' : ''}`}
                            aria-label="Search products"
                            aria-expanded={searchOpen}
                            aria-controls={searchId}
                            onClick={() => setSearchOpen((open) => !open)}
                        >
                            <SearchIcon />
                        </button>
                    ) : (
                        <button type="button" className="listing-header__icon" aria-label="Search products" onClick={openSearch}>
                            <SearchIcon />
                        </button>
                    )}
                    <button type="button" className="listing-header__icon" aria-label="Wishlist" onClick={openWishlist}>
                        <HeartIcon />
                        {wishlistCount > 0 ? <span className="listing-header__badge">{wishlistCount}</span> : null}
                    </button>
                    <button type="button" className="listing-header__icon" aria-label="Open cart" onClick={openCart}>
                        <CartIcon />
                        {cartCount > 0 ? <span className="listing-header__badge">{cartCount}</span> : null}
                    </button>
                </div>
            </div>

            {searchesInline && searchOpen ? (
                <form id={searchId} className="listing-header__search" role="search" onSubmit={submit}>
                    <label className="visually-hidden" htmlFor={`${searchId}-input`}>
                        Search products
                    </label>
                    <SearchIcon className="listing-header__search-icon" />
                    <input
                        ref={inputRef}
                        id={`${searchId}-input`}
                        className="listing-header__search-input"
                        type="search"
                        value={value}
                        onChange={(event) => setValue(event.target.value)}
                        placeholder={placeholder}
                        autoComplete="off"
                    />
                </form>
            ) : null}
        </header>
    );
}

function BackIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 5l-7 7 7 7" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
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
