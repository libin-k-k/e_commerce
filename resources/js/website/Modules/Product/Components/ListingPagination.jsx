const SIBLINGS = 1;

function pageItems(page, lastPage) {
    if (lastPage <= 7) {
        return Array.from({ length: lastPage }, (_, index) => index + 1);
    }

    const start = Math.max(2, page - SIBLINGS);
    const end = Math.min(lastPage - 1, page + SIBLINGS);
    const items = [1];

    if (start > 2) {
        items.push('gap-start');
    }

    for (let number = start; number <= end; number += 1) {
        items.push(number);
    }

    if (end < lastPage - 1) {
        items.push('gap-end');
    }

    items.push(lastPage);

    return items;
}

export default function ListingPagination({ pagination, total, onPage, onPerPage }) {
    if (total === 0) {
        return null;
    }

    const { page, lastPage, from, to, perPage, perPageOptions = [] } = pagination;

    return (
        <nav className="listing-pagination" aria-label="Pagination">
            <p className="listing-pagination__summary">
                Showing {from.toLocaleString('en-IN')}–{to.toLocaleString('en-IN')} of {total.toLocaleString('en-IN')}{' '}
                {total === 1 ? 'product' : 'products'}
            </p>

            {lastPage > 1 ? (
                <ol className="listing-pagination__pages">
                    <li>
                        <button
                            type="button"
                            className="listing-pagination__page"
                            aria-label="Previous page"
                            disabled={page <= 1}
                            onClick={() => onPage(page - 1)}
                        >
                            <ChevronIcon direction="left" />
                        </button>
                    </li>
                    {pageItems(page, lastPage).map((item) =>
                        typeof item === 'number' ? (
                            <li key={item}>
                                <button
                                    type="button"
                                    className={`listing-pagination__page${item === page ? ' is-active' : ''}`}
                                    aria-label={`Page ${item}`}
                                    aria-current={item === page ? 'page' : undefined}
                                    onClick={() => onPage(item)}
                                >
                                    {item}
                                </button>
                            </li>
                        ) : (
                            <li key={item} className="listing-pagination__gap" aria-hidden="true">
                                …
                            </li>
                        ),
                    )}
                    <li>
                        <button
                            type="button"
                            className="listing-pagination__page"
                            aria-label="Next page"
                            disabled={page >= lastPage}
                            onClick={() => onPage(page + 1)}
                        >
                            <ChevronIcon direction="right" />
                        </button>
                    </li>
                </ol>
            ) : null}

            <label className="listing-pagination__per-page">
                <span className="visually-hidden">Products per page</span>
                <select value={perPage} onChange={(event) => onPerPage(Number(event.target.value))}>
                    {perPageOptions.map((option) => (
                        <option key={option} value={option}>
                            Show {option} per page
                        </option>
                    ))}
                </select>
            </label>
        </nav>
    );
}

function ChevronIcon({ direction }) {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d={direction === 'left' ? 'M15 6l-6 6 6 6' : 'M9 6l6 6-6 6'}
                stroke="currentColor"
                strokeWidth="2.4"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
