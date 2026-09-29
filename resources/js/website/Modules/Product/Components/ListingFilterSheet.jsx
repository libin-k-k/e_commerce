import { useEffect, useId, useMemo, useState } from 'react';

const DEFAULT_SORT = 'popular';

const PANEL_TITLES = {
    all: 'Filters',
    price: 'Price',
    rating: 'Rating',
    size: 'Size',
    sort: 'Sort by',
};

export default function ListingFilterSheet({ panel, filters, options, onApply, onClose }) {
    const titleId = useId();
    const open = panel !== null;
    const [draft, setDraft] = useState(filters);

    const sections = useMemo(
        () => [
            { key: 'price', title: 'Price', options: options.prices ?? [] },
            { key: 'rating', title: 'Rating', options: options.ratings ?? [] },
            {
                key: 'size',
                title: 'Size',
                options: (options.sizes ?? []).map((size) => ({ value: size, label: size })),
            },
            { key: 'sort', title: 'Sort by', options: options.sorts ?? [] },
        ],
        [options],
    );

    useEffect(() => {
        if (open) {
            setDraft(filters);
        }
    }, [open, filters]);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.body.classList.add('is-sheet-open');
        window.addEventListener('keydown', onKeyDown);

        return () => {
            document.body.classList.remove('is-sheet-open');
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [open, onClose]);

    const isAll = panel === 'all';
    const visibleSections = sections.filter(
        (section) => section.options.length > 0 && (isAll ? true : section.key === panel),
    );

    const nextValue = (key, current, value) => {
        if (key === 'sort') {
            return value;
        }

        return current === value ? null : value;
    };

    const choose = (key, value) => {
        if (isAll) {
            setDraft((state) => ({ ...state, [key]: nextValue(key, state[key], value) }));
            return;
        }

        onApply({ [key]: nextValue(key, filters[key], value) });
        onClose();
    };

    const applyDraft = () => {
        onApply({ price: draft.price, rating: draft.rating, size: draft.size, sort: draft.sort });
        onClose();
    };

    const clearDraft = () => {
        setDraft((state) => ({ ...state, price: null, rating: null, size: null, sort: DEFAULT_SORT }));
    };

    const selected = isAll ? draft : filters;

    return (
        <div
            className={`filter-sheet${open ? ' is-open' : ''}`}
            role="dialog"
            aria-modal="true"
            aria-labelledby={titleId}
            aria-hidden={!open}
        >
            <button type="button" className="filter-sheet__backdrop" aria-label="Close filters" onClick={onClose} />
            <div className="filter-sheet__panel">
                <span className="filter-sheet__handle" aria-hidden="true" />
                <div className="filter-sheet__header">
                    <p id={titleId} className="filter-sheet__title">
                        {PANEL_TITLES[panel] ?? 'Filters'}
                    </p>
                    <button type="button" className="filter-sheet__close" aria-label="Close filters" onClick={onClose}>
                        <CloseIcon />
                    </button>
                </div>

                <div className="filter-sheet__body">
                    {visibleSections.map((section) => (
                        <fieldset key={section.key} className="filter-sheet__section">
                            {isAll ? <legend className="filter-sheet__legend">{section.title}</legend> : null}
                            <div className="filter-sheet__options">
                                {section.options.map((option) => {
                                    const active = selected[section.key] === option.value
                                        || (section.key === 'sort' && !selected.sort && option.value === DEFAULT_SORT);

                                    return (
                                        <button
                                            key={option.value}
                                            type="button"
                                            className={`filter-option${active ? ' is-active' : ''}`}
                                            aria-pressed={active}
                                            onClick={() => choose(section.key, option.value)}
                                        >
                                            {option.label}
                                        </button>
                                    );
                                })}
                            </div>
                        </fieldset>
                    ))}
                </div>

                {isAll ? (
                    <div className="filter-sheet__footer">
                        <button type="button" className="btn btn--outline" onClick={clearDraft}>
                            Clear all
                        </button>
                        <button type="button" className="btn btn--primary" onClick={applyDraft}>
                            Apply
                        </button>
                    </div>
                ) : null}
            </div>
        </div>
    );
}

function CloseIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}
