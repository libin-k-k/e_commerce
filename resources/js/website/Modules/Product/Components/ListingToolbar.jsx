import { Link } from '@inertiajs/react';

export default function ListingToolbar({ title, subtitle, total, category, sort, sorts = [], view, onSort, onViewChange }) {
    return (
        <div className="listing-toolbar">
            <div className="listing-toolbar__heading">
                <nav className="listing-breadcrumb" aria-label="Breadcrumb">
                    <ol>
                        <li>
                            <Link href="/">Home</Link>
                        </li>
                        {category ? (
                            <li>
                                <Link href="/products">All Products</Link>
                            </li>
                        ) : null}
                        {category?.parentSlug ? (
                            <li>
                                <Link href={`/products?category=${category.parentSlug}`}>{category.parentName}</Link>
                            </li>
                        ) : null}
                        <li aria-current="page">{title}</li>
                    </ol>
                </nav>
                <h1 className="listing-toolbar__title">{title}</h1>
                {subtitle ? <p className="listing-toolbar__subtitle">{subtitle}</p> : null}
            </div>

            <div className="listing-toolbar__controls">
                <p className="listing-toolbar__count">
                    {total.toLocaleString('en-IN')} {total === 1 ? 'product' : 'products'}
                </p>
                <label className="listing-toolbar__sort">
                    <span className="listing-toolbar__sort-label">Sort by</span>
                    <select className="listing-toolbar__select" value={sort} onChange={(event) => onSort(event.target.value)}>
                        {sorts.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </label>
                <div className="listing-toolbar__views" role="group" aria-label="Layout">
                    <button
                        type="button"
                        className={`listing-toolbar__view${view === 'grid' ? ' is-active' : ''}`}
                        aria-label="Grid view"
                        aria-pressed={view === 'grid'}
                        onClick={() => onViewChange('grid')}
                    >
                        <GridIcon />
                    </button>
                    <button
                        type="button"
                        className={`listing-toolbar__view${view === 'list' ? ' is-active' : ''}`}
                        aria-label="List view"
                        aria-pressed={view === 'list'}
                        onClick={() => onViewChange('list')}
                    >
                        <ListIcon />
                    </button>
                </div>
            </div>
        </div>
    );
}

function GridIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <rect x="3" y="3" width="8" height="8" rx="2" />
            <rect x="13" y="3" width="8" height="8" rx="2" />
            <rect x="3" y="13" width="8" height="8" rx="2" />
            <rect x="13" y="13" width="8" height="8" rx="2" />
        </svg>
    );
}

function ListIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6h12M9 12h12M9 18h12" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <circle cx="4.5" cy="6" r="1.5" fill="currentColor" />
            <circle cx="4.5" cy="12" r="1.5" fill="currentColor" />
            <circle cx="4.5" cy="18" r="1.5" fill="currentColor" />
        </svg>
    );
}
