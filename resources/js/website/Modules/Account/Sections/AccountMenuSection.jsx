import { Link, router } from '@inertiajs/react';

export default function AccountMenuSection({ menu = [] }) {
    const logout = () => {
        router.post('/logout');
    };

    return (
        <section className="account-menu account-menu--mobile" aria-label="Account sections">
            <ul className="account-menu__list">
                {menu.map((item) => (
                    <li key={item.key}>
                        <Link href={item.href} className="account-menu__item">
                            <span className="account-menu__text">
                                <span className="account-menu__title">{item.title}</span>
                                <span className="account-menu__desc">{item.description}</span>
                            </span>
                            <span className="account-menu__chevron" aria-hidden="true">
                                ›
                            </span>
                        </Link>
                    </li>
                ))}
            </ul>
            <button type="button" className="account-menu__logout" onClick={logout}>
                Log out
            </button>
            <Link href="/account/faq" className="account-menu__help">
                Need help? Chat with support
            </Link>
        </section>
    );
}
