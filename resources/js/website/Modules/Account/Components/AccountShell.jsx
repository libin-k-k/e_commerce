import { Link, router } from '@inertiajs/react';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';

export default function AccountShell({
    seo,
    menu = [],
    active = 'index',
    profile = null,
    children,
}) {
    const logout = () => {
        router.post('/logout');
    };

    return (
        <StorefrontLayout seo={seo} current="account" hideHeaderOnMobile>
            <div className={`account-page${active === 'index' ? ' account-page--hub' : ''}`}>
                <div className="account-layout">
                    <aside className="account-sidebar" aria-label="Account navigation">
                        {profile ? (
                            <div className="account-sidebar__profile">
                                <p className="account-sidebar__name">{profile.name}</p>
                                <p className="account-sidebar__email">
                                    {profile.email || profile.phone || ''}
                                </p>
                            </div>
                        ) : null}
                        <nav className="account-sidebar__nav">
                            <Link
                                href="/account"
                                className={`account-sidebar__link${active === 'index' ? ' is-active' : ''}`}
                            >
                                Overview
                            </Link>
                            {menu.map((item) => (
                                <Link
                                    key={item.key}
                                    href={item.href}
                                    className={`account-sidebar__link${active === item.key ? ' is-active' : ''}`}
                                >
                                    {item.title}
                                </Link>
                            ))}
                            <Link href="/account/faq" className="account-sidebar__link account-sidebar__link--help">
                                Support chat
                            </Link>
                            <button
                                type="button"
                                className="account-sidebar__link account-sidebar__logout"
                                onClick={logout}
                            >
                                Log out
                            </button>
                        </nav>
                    </aside>
                    <div className="account-layout__main">{children}</div>
                </div>
            </div>
        </StorefrontLayout>
    );
}
