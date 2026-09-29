import { useEffect, useState } from 'react';

const VISIBLE_CATEGORIES = 8;
const VISIBLE_COLORS = 9;

const formatCount = (count) => count.toLocaleString('en-IN');

export default function ListingSidebar({ tree, filters, options, activeCount, onApply, onCategory, onClear }) {
    const [showAllCategories, setShowAllCategories] = useState(false);
    const [showAllColors, setShowAllColors] = useState(false);

    const mains = tree.items ?? [];
    const colors = options.colors ?? [];
    const sizes = options.sizes ?? [];
    const ratings = options.ratings ?? [];
    const visibleMains = showAllCategories ? mains : mains.slice(0, VISIBLE_CATEGORIES);
    const visibleColors = showAllColors ? colors : colors.slice(0, VISIBLE_COLORS);

    const isOpenMain = (main) =>
        filters.category === main.slug || main.children.some((child) => child.slug === filters.category);

    return (
        <aside className="listing-sidebar" aria-label="Filters">
            {activeCount > 0 ? (
                <div className="listing-sidebar__clear">
                    <span>
                        {activeCount} {activeCount === 1 ? 'filter' : 'filters'} applied
                    </span>
                    <button type="button" onClick={onClear}>
                        Clear all
                    </button>
                </div>
            ) : null}

            <section className="listing-sidebar__section" aria-labelledby="sidebar-categories">
                <h2 id="sidebar-categories" className="listing-sidebar__title">
                    Categories
                </h2>
                <ul className="sidebar-list">
                    <li>
                        <SidebarOption active={!filters.category} count={tree.total} onClick={() => onCategory(null)}>
                            All Products
                        </SidebarOption>
                    </li>
                    {visibleMains.map((main) => (
                        <li key={main.slug}>
                            <SidebarOption
                                active={filters.category === main.slug}
                                count={main.count}
                                onClick={() => onCategory(main.slug)}
                            >
                                {main.name}
                            </SidebarOption>
                            {isOpenMain(main) && main.children.length > 0 ? (
                                <ul className="sidebar-list sidebar-list--nested">
                                    {main.children.map((child) => (
                                        <li key={child.slug}>
                                            <SidebarOption
                                                active={filters.category === child.slug}
                                                count={child.count}
                                                onClick={() => onCategory(child.slug)}
                                            >
                                                {child.name}
                                            </SidebarOption>
                                        </li>
                                    ))}
                                </ul>
                            ) : null}
                        </li>
                    ))}
                </ul>
                {mains.length > VISIBLE_CATEGORIES ? (
                    <ShowMoreButton expanded={showAllCategories} onClick={() => setShowAllCategories((value) => !value)} />
                ) : null}
            </section>

            <PriceRangeSection filters={filters} ceiling={options.priceCeiling ?? 1000} onApply={onApply} />

            {ratings.length > 0 ? (
                <section className="listing-sidebar__section" aria-labelledby="sidebar-rating">
                    <h2 id="sidebar-rating" className="listing-sidebar__title">
                        Rating
                    </h2>
                    <ul className="sidebar-list">
                        {ratings.map((rating) => (
                            <li key={rating.value}>
                                <SidebarOption
                                    active={filters.rating === rating.value}
                                    count={rating.count}
                                    bracketCount
                                    onClick={() => onApply({ rating: filters.rating === rating.value ? null : rating.value })}
                                >
                                    <Stars value={rating.value} />
                                    <span>{rating.value} & above</span>
                                </SidebarOption>
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}

            {colors.length > 0 ? (
                <section className="listing-sidebar__section" aria-labelledby="sidebar-color">
                    <h2 id="sidebar-color" className="listing-sidebar__title">
                        Color
                    </h2>
                    <div className="sidebar-colors">
                        {visibleColors.map((color) => {
                            const active = filters.color === color.name;

                            return (
                                <button
                                    key={color.name}
                                    type="button"
                                    className={`sidebar-color${active ? ' is-active' : ''}`}
                                    style={color.hex ? { '--swatch': color.hex } : undefined}
                                    title={color.name}
                                    aria-label={color.name}
                                    aria-pressed={active}
                                    onClick={() => onApply({ color: active ? null : color.name })}
                                />
                            );
                        })}
                        {colors.length > VISIBLE_COLORS ? (
                            <button type="button" className="sidebar-colors__more" onClick={() => setShowAllColors((value) => !value)}>
                                {showAllColors ? 'Less' : 'More'}
                            </button>
                        ) : null}
                    </div>
                </section>
            ) : null}

            {sizes.length > 0 ? (
                <section className="listing-sidebar__section" aria-labelledby="sidebar-size">
                    <h2 id="sidebar-size" className="listing-sidebar__title">
                        Size
                    </h2>
                    <div className="sidebar-sizes">
                        {sizes.map((size) => {
                            const active = filters.size === size;

                            return (
                                <button
                                    key={size}
                                    type="button"
                                    className={`sidebar-size${active ? ' is-active' : ''}`}
                                    aria-pressed={active}
                                    onClick={() => onApply({ size: active ? null : size })}
                                >
                                    {size}
                                </button>
                            );
                        })}
                    </div>
                </section>
            ) : null}
        </aside>
    );
}

function PriceRangeSection({ filters, ceiling, onApply }) {
    const appliedMin = filters.min_price ?? 0;
    const appliedMax = Math.min(filters.max_price ?? ceiling, ceiling);
    const [range, setRange] = useState([appliedMin, appliedMax]);
    const step = Math.max(1, Math.round(ceiling / 100));

    useEffect(() => {
        setRange([appliedMin, appliedMax]);
    }, [appliedMin, appliedMax]);

    const commit = (next = range) => {
        const [low, high] = [Math.min(next[0], next[1]), Math.max(next[0], next[1])];

        if (low === appliedMin && high === appliedMax) {
            return;
        }

        onApply({
            price: null,
            min_price: low > 0 ? low : null,
            max_price: high < ceiling ? high : null,
        });
    };

    const setLow = (value) => setRange(([, high]) => [Math.min(Number(value) || 0, high), high]);
    const setHigh = (value) => setRange(([low]) => [low, Math.max(Math.min(Number(value) || 0, ceiling), low)]);

    const onFieldKeyDown = (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            commit();
        }
    };

    return (
        <section className="listing-sidebar__section" aria-labelledby="sidebar-price">
            <h2 id="sidebar-price" className="listing-sidebar__title">
                Price Range
            </h2>
            <div
                className="price-range"
                style={{ '--from': `${(range[0] / ceiling) * 100}%`, '--to': `${(range[1] / ceiling) * 100}%` }}
            >
                <span className="price-range__track" aria-hidden="true" />
                <input
                    type="range"
                    className="price-range__input"
                    min={0}
                    max={ceiling}
                    step={step}
                    value={range[0]}
                    aria-label="Minimum price"
                    onChange={(event) => setLow(event.target.value)}
                    onPointerUp={() => commit()}
                    onKeyUp={() => commit()}
                />
                <input
                    type="range"
                    className="price-range__input"
                    min={0}
                    max={ceiling}
                    step={step}
                    value={range[1]}
                    aria-label="Maximum price"
                    onChange={(event) => setHigh(event.target.value)}
                    onPointerUp={() => commit()}
                    onKeyUp={() => commit()}
                />
            </div>
            <div className="price-range__fields">
                <label className="price-range__field">
                    <span aria-hidden="true">₹</span>
                    <input
                        type="number"
                        inputMode="numeric"
                        min={0}
                        max={ceiling}
                        value={range[0]}
                        aria-label="Minimum price in rupees"
                        onChange={(event) => setRange(([, high]) => [Number(event.target.value) || 0, high])}
                        onBlur={() => commit()}
                        onKeyDown={onFieldKeyDown}
                    />
                </label>
                <span className="price-range__dash" aria-hidden="true">
                    –
                </span>
                <label className="price-range__field">
                    <span aria-hidden="true">₹</span>
                    <input
                        type="number"
                        inputMode="numeric"
                        min={0}
                        max={ceiling}
                        value={range[1]}
                        aria-label="Maximum price in rupees"
                        onChange={(event) => setRange(([low]) => [low, Number(event.target.value) || 0])}
                        onBlur={() => commit()}
                        onKeyDown={onFieldKeyDown}
                    />
                </label>
            </div>
        </section>
    );
}

function SidebarOption({ active, count, bracketCount = false, onClick, children }) {
    return (
        <button type="button" className={`sidebar-option${active ? ' is-active' : ''}`} aria-pressed={active} onClick={onClick}>
            <span className="sidebar-option__check" aria-hidden="true">
                <CheckIcon />
            </span>
            <span className="sidebar-option__label">{children}</span>
            {count !== undefined ? (
                <span className="sidebar-option__count">{bracketCount ? `(${formatCount(count)})` : formatCount(count)}</span>
            ) : null}
        </button>
    );
}

function ShowMoreButton({ expanded, onClick }) {
    return (
        <button type="button" className="listing-sidebar__more" aria-expanded={expanded} onClick={onClick}>
            {expanded ? 'Show Less' : 'Show More'}
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path
                    d={expanded ? 'M6 15l6-6 6 6' : 'M6 9l6 6 6-6'}
                    stroke="currentColor"
                    strokeWidth="2.5"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
            </svg>
        </button>
    );
}

function Stars({ value }) {
    return (
        <span className="sidebar-stars" aria-hidden="true">
            {[1, 2, 3, 4, 5].map((star) => (
                <svg key={star} className={star <= value ? 'is-filled' : ''} width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9L12 2.5z" />
                </svg>
            ))}
        </span>
    );
}

function CheckIcon() {
    return (
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" strokeWidth="3.2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
