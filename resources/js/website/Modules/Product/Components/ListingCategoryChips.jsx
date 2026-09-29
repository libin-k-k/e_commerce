import { useStorefrontActions } from '../../../Shared/Layouts/StorefrontLayout';

const TONES = ['fashion', 'footwear', 'kids', 'beauty', 'home', 'all'];

export default function ListingCategoryChips({ chips, onSelect }) {
    const { openCategories } = useStorefrontActions();
    const items = chips.items ?? [];

    if (items.length === 0) {
        return null;
    }

    const allActive = chips.activeSlug === null;

    return (
        <nav className="listing-chips" aria-label="Browse categories">
            <button
                type="button"
                className={`listing-chip is-tone-all${allActive ? ' is-active' : ''}`}
                aria-pressed={allActive}
                onClick={() => onSelect(null)}
            >
                <span className="listing-chip__media" aria-hidden="true">
                    <GridIcon />
                </span>
                <span className="listing-chip__label">All</span>
            </button>

            {items.map((item, index) => {
                const active = chips.activeSlug === item.slug;

                return (
                    <button
                        key={item.slug}
                        type="button"
                        className={`listing-chip is-tone-${TONES[index % TONES.length]}${active ? ' is-active' : ''}`}
                        aria-pressed={active}
                        onClick={() => onSelect(item.slug)}
                    >
                        <span className="listing-chip__media" aria-hidden="true">
                            {item.image ? <img src={item.image} alt="" loading="lazy" /> : null}
                        </span>
                        <span className="listing-chip__label">{item.name}</span>
                    </button>
                );
            })}

            <button type="button" className="listing-chip listing-chip--more" onClick={openCategories}>
                <span className="listing-chip__media" aria-hidden="true">
                    <DotsIcon />
                </span>
                <span className="listing-chip__label">More</span>
            </button>
        </nav>
    );
}

function GridIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="8" height="8" rx="2" stroke="currentColor" strokeWidth="2" />
            <rect x="13" y="3" width="8" height="8" rx="2" stroke="currentColor" strokeWidth="2" />
            <rect x="3" y="13" width="8" height="8" rx="2" stroke="currentColor" strokeWidth="2" />
            <rect x="13" y="13" width="8" height="8" rx="2" stroke="currentColor" strokeWidth="2" />
        </svg>
    );
}

function DotsIcon() {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="7" cy="7" r="2.6" />
            <circle cx="17" cy="7" r="2.6" />
            <circle cx="7" cy="17" r="2.6" />
            <circle cx="17" cy="17" r="2.6" />
        </svg>
    );
}
