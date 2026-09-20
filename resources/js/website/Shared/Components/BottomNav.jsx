import { Link } from '@inertiajs/react';

const items = [
    { key: 'home', href: '/', label: 'Home', icon: HomeIcon, type: 'link' },
    { key: 'categories', label: 'Categories', icon: CategoriesIcon, type: 'categories' },
    { key: 'products', href: '/products', label: 'Mall', icon: MallIcon, type: 'link' },
    { key: 'help', href: '/help', label: 'Help', icon: HelpIcon, type: 'link' },
    { key: 'account', href: '/account', label: 'Account', icon: AccountIcon, type: 'link' },
];

export default function BottomNav({ current = 'home', onOpenCategories }) {
    return (
        <nav className="bottom-nav" aria-label="Mobile">
            {items.map(({ key, href, label, icon: Icon, type }) => {
                if (type === 'categories') {
                    return (
                        <button
                            key={key}
                            type="button"
                            className={`bottom-nav__item${current === key ? ' is-active' : ''}`}
                            onClick={onOpenCategories}
                        >
                            <Icon className="bottom-nav__icon" />
                            <span>{label}</span>
                        </button>
                    );
                }

                return (
                    <Link
                        key={key}
                        href={href}
                        className={`bottom-nav__item${current === key ? ' is-active' : ''}`}
                    >
                        <Icon className="bottom-nav__icon" />
                        <span>{label}</span>
                    </Link>
                );
            })}
        </nav>
    );
}

function HomeIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path
                d="M4 10.5L12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function CategoriesIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
            <rect x="13" y="3" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
            <rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
            <rect x="13" y="13" width="8" height="8" rx="1.5" stroke="currentColor" strokeWidth="2" />
        </svg>
    );
}

function MallIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden="true">
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

function HelpIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="2" />
            <path
                d="M9.5 9.5a2.5 2.5 0 1 1 3.6 2.2c-.8.4-1.6 1-1.6 2.3"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
            />
            <circle cx="12" cy="17" r="1" fill="currentColor" />
        </svg>
    );
}

function AccountIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
