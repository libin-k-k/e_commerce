import { router } from '@inertiajs/react';
import { useEffect, useId, useRef, useState } from 'react';
import {
    CloseIcon,
    DiscoverSections,
    ProductSuggestions,
    RecentSearches,
    SearchIcon,
    productsSearchHref,
    suggestionTerm,
    useProductSuggestions,
    useRecentSearches,
} from './SearchPanel';

export default function SearchScreen({ open, onClose }) {
    const titleId = useId();
    const inputId = useId();
    const inputRef = useRef(null);
    const [query, setQuery] = useState('');
    const { recent, add, remove, clear } = useRecentSearches();
    const { term, result, loading } = useProductSuggestions(query, open && suggestionTerm(query) !== '');

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        const focusTimer = window.setTimeout(() => inputRef.current?.focus(), 60);
        document.body.classList.add('is-drawer-open');
        window.addEventListener('keydown', onKeyDown);

        return () => {
            window.clearTimeout(focusTimer);
            document.body.classList.remove('is-drawer-open');
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [open, onClose]);

    const search = (value) => {
        const q = value.trim();

        if (!q) {
            inputRef.current?.focus();
            return;
        }

        add(q);
        onClose();
        router.visit(productsSearchHref(q));
    };

    const submit = (event) => {
        event.preventDefault();
        search(query);
    };

    return (
        <div
            className={`search-screen${open ? ' is-open' : ''}`}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-hidden={!open}
            inert={!open}
        >
            <h2 id={titleId} className="visually-hidden">
                Search products
            </h2>
            <div className="search-screen__bar">
                <button type="button" className="search-screen__back" aria-label="Close search" onClick={onClose}>
                    <BackIcon />
                </button>
                <form className="search-screen__form" role="search" onSubmit={submit}>
                    <label className="visually-hidden" htmlFor={inputId}>
                        Search products
                    </label>
                    <SearchIcon className="search-screen__icon" />
                    <input
                        ref={inputRef}
                        id={inputId}
                        className="search-screen__input"
                        type="search"
                        enterKeyHint="search"
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Search for products, brands and more..."
                        autoComplete="off"
                    />
                    {query !== '' ? (
                        <button
                            type="button"
                            className="search-screen__clear"
                            aria-label="Clear search"
                            onClick={() => {
                                setQuery('');
                                inputRef.current?.focus();
                            }}
                        >
                            <CloseIcon />
                        </button>
                    ) : null}
                </form>
                <button type="button" className="search-screen__cancel" onClick={onClose}>
                    Cancel
                </button>
            </div>

            <div className="search-screen__body">
                {term ? (
                    <ProductSuggestions term={term} result={result} loading={loading} onNavigate={onClose} />
                ) : (
                    <>
                        <RecentSearches recent={recent} onSelect={search} onRemove={remove} onClear={clear} />
                        <DiscoverSections onNavigate={onClose} />
                    </>
                )}
            </div>
        </div>
    );
}

function BackIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 5l-7 7 7 7" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
