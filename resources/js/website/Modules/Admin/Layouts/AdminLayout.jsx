import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const navItems = [
    { key: 'dashboard', href: '/admin/dashboard', label: 'Dashboard' },
    { key: 'banners', href: '/admin/banners', label: 'Banners' },
    { key: 'categories', href: '/admin/categories', label: 'Categories' },
    { key: 'products', href: '/admin/products', label: 'Products' },
];

export default function AdminLayout({ title, current = 'dashboard', children }) {
    const { appName, auth, flash } = usePage().props;
    const [navOpen, setNavOpen] = useState(false);

    useEffect(() => {
        setNavOpen(false);
    }, [current, title]);

    useEffect(() => {
        if (!navOpen) {
            return undefined;
        }

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                setNavOpen(false);
            }
        };

        document.addEventListener('keydown', onKeyDown);
        document.body.classList.add('admin-nav-locked');

        return () => {
            document.removeEventListener('keydown', onKeyDown);
            document.body.classList.remove('admin-nav-locked');
        };
    }, [navOpen]);

    const logout = () => {
        setNavOpen(false);
        router.post('/admin/logout');
    };

    return (
        <div className={`admin-shell${navOpen ? ' is-nav-open' : ''}`}>
            <button
                type="button"
                className="admin-nav-backdrop"
                aria-label="Close menu"
                tabIndex={navOpen ? 0 : -1}
                onClick={() => setNavOpen(false)}
            />

            <aside className="admin-sidebar" aria-label="Admin navigation" id="admin-sidebar">
                <div className="admin-sidebar__head">
                    <p className="admin-sidebar__brand">{appName} Admin</p>
                    <button
                        type="button"
                        className="admin-sidebar__close"
                        aria-label="Close menu"
                        onClick={() => setNavOpen(false)}
                    >
                        Close
                    </button>
                </div>
                <nav className="admin-sidebar__nav">
                    {navItems.map((item) => (
                        <Link
                            key={item.key}
                            href={item.href}
                            className={`admin-sidebar__link${current === item.key ? ' is-active' : ''}`}
                            onClick={() => setNavOpen(false)}
                        >
                            {item.label}
                        </Link>
                    ))}
                </nav>
                <button type="button" className="admin-sidebar__logout" onClick={logout}>
                    Log out
                </button>
            </aside>

            <div className="admin-main">
                <header className="admin-topbar">
                    <div className="admin-topbar__lead">
                        <button
                            type="button"
                            className="admin-topbar__menu"
                            aria-label={navOpen ? 'Close menu' : 'Open menu'}
                            aria-expanded={navOpen}
                            aria-controls="admin-sidebar"
                            onClick={() => setNavOpen((open) => !open)}
                        >
                            <span className="admin-topbar__menu-bars" aria-hidden="true" />
                        </button>
                        <h1 className="admin-topbar__title">{title}</h1>
                    </div>
                    <p className="admin-topbar__user">{auth?.user?.name ?? 'Admin'}</p>
                </header>
                {flash?.success ? <p className="admin-flash">{flash.success}</p> : null}
                <div className="admin-content">{children}</div>
            </div>
        </div>
    );
}
