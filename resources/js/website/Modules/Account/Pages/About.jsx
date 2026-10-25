import AccountShell from '../Components/AccountShell';
import AccountSubpageHeader from '../Components/AccountSubpageHeader';
import AccountMenuSection from '../Sections/AccountMenuSection';
import SocialAccountsSection from '../Sections/SocialAccountsSection';

export default function About({ seo, about, menu = [], profile, active = 'about' }) {
    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountSubpageHeader title="About" />
            <section className="account-panel about-page" aria-label="About the store">
                <h2 className="about-page__title">{about?.title}</h2>
                {(about?.intro ?? []).map((paragraph) => (
                    <p key={paragraph} className="about-page__text">{paragraph}</p>
                ))}
            </section>
            <SocialAccountsSection socials={about?.socials ?? []} />
            <AccountMenuSection menu={menu} />
        </AccountShell>
    );
}
