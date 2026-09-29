export default function ListingFilterBar({ filters, activeCount, hasSizes, onOpen }) {
    const sorted = filters.sort && filters.sort !== 'popular';
    const hasPrice = filters.price != null || filters.min_price != null || filters.max_price != null;

    return (
        <div className="listing-filters" role="toolbar" aria-label="Filter and sort products">
            <div className="listing-filters__scroll">
                <button
                    type="button"
                    className={`listing-filter${activeCount > 0 ? ' is-active' : ''}`}
                    onClick={() => onOpen('all')}
                >
                    <FiltersIcon />
                    Filters
                    {activeCount > 0 ? <span className="listing-filter__count">{activeCount}</span> : null}
                </button>
                <button
                    type="button"
                    className={`listing-filter${hasPrice ? ' is-active' : ''}`}
                    onClick={() => onOpen('price')}
                >
                    Price
                    <ChevronDownIcon />
                </button>
                <button
                    type="button"
                    className={`listing-filter${filters.rating ? ' is-active' : ''}`}
                    onClick={() => onOpen('rating')}
                >
                    Rating
                    <ChevronDownIcon />
                </button>
                {hasSizes ? (
                    <button
                        type="button"
                        className={`listing-filter${filters.size ? ' is-active' : ''}`}
                        onClick={() => onOpen('size')}
                    >
                        Size
                        <ChevronDownIcon />
                    </button>
                ) : null}
            </div>
            <button
                type="button"
                className={`listing-filter listing-filter--sort${sorted ? ' is-active' : ''}`}
                onClick={() => onOpen('sort')}
            >
                <SortIcon />
                Sort
            </button>
        </div>
    );
}

function FiltersIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 6h8M16 6h4M4 12h2M10 12h10M4 18h10M18 18h2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <circle cx="14" cy="6" r="2" stroke="currentColor" strokeWidth="2" />
            <circle cx="8" cy="12" r="2" stroke="currentColor" strokeWidth="2" />
            <circle cx="16" cy="18" r="2" stroke="currentColor" strokeWidth="2" />
        </svg>
    );
}

function SortIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 4v16M8 4L4 8M8 4l4 4M16 20V4M16 20l-4-4M16 20l4-4" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function ChevronDownIcon() {
    return (
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
