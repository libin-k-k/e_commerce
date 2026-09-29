import { Link, usePage } from '@inertiajs/react';

const MAX_LINKS = 8;

export default function CategoryBar({ onOpenCategories }) {
    const { categoryMenu = [] } = usePage().props;
    const links = categoryMenu.slice(0, MAX_LINKS);

    return (
        <nav className="category-bar" aria-label="Shop by category">
            <div className="category-bar__inner">
                <button type="button" className="category-bar__all" onClick={onOpenCategories}>
                    <MenuIcon />
                    All Categories
                    <ChevronDownIcon />
                </button>

                <ul className="category-bar__links">
                    {links.map((category) => (
                        <li key={category.id}>
                            <Link href={category.href} className="category-bar__link">
                                {category.name}
                            </Link>
                        </li>
                    ))}
                    {categoryMenu.length > 0 ? (
                        <li>
                            <button type="button" className="category-bar__link" onClick={onOpenCategories}>
                                More
                                <ChevronDownIcon />
                            </button>
                        </li>
                    ) : null}
                </ul>

                <div className="category-bar__extras">
                    <Link href="/offers" className="category-bar__extra">
                        <span className="category-bar__extra-icon category-bar__extra-icon--offer" aria-hidden="true">
                            <TagIcon />
                        </span>
                        Offers
                    </Link>
                    <Link href="/help" className="category-bar__extra">
                        <span className="category-bar__extra-icon" aria-hidden="true">
                            <HelpIcon />
                        </span>
                        Help
                    </Link>
                </div>
            </div>
        </nav>
    );
}

function MenuIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
        </svg>
    );
}

function ChevronDownIcon() {
    return (
        <svg className="category-bar__chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function TagIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9-9-9z"
                stroke="currentColor"
                strokeWidth="2.2"
                strokeLinejoin="round"
            />
            <circle cx="8" cy="8" r="1.6" fill="currentColor" />
        </svg>
    );
}

function HelpIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="2" />
            <path
                d="M9.5 9.5a2.5 2.5 0 0 1 4.9.7c0 1.7-2.4 2.1-2.4 3.8"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
            />
            <circle cx="12" cy="17" r="1.1" fill="currentColor" />
        </svg>
    );
}
