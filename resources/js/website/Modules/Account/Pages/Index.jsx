import { Link } from '@inertiajs/react';
import AccountShell from '../Components/AccountShell';
import AccountHeroSection from '../Sections/AccountHeroSection';
import AccountMenuSection from '../Sections/AccountMenuSection';

export default function Index({ seo, profile, menu = [], active = 'index' }) {
    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountHeroSection profile={profile} />
            <AccountMenuSection menu={menu} />
            <section className="account-desktop-welcome" aria-label="Account overview">
                <h2 className="account-desktop-welcome__title">Welcome back</h2>
                <p className="account-desktop-welcome__text">
                    Manage your profile, delivery addresses, orders, and store policies from the
                    menu on the left.
                </p>
                <div className="account-desktop-welcome__cards">
                    {menu.map((item) => (
                        <Link key={item.key} href={item.href} className="account-desktop-card">
                            <span className="account-desktop-card__title">{item.title}</span>
                            <span className="account-desktop-card__desc">{item.description}</span>
                        </Link>
                    ))}
                </div>
            </section>
        </AccountShell>
    );
}
