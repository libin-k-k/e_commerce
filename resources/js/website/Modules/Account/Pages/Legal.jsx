import AccountShell from '../Components/AccountShell';
import AccountSubpageHeader from '../Components/AccountSubpageHeader';

export default function Legal({ seo, document, menu = [], profile, active }) {
    const sections = document?.sections ?? [];

    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountSubpageHeader title={document?.title ?? 'Policy'} />
            <article className="account-panel legal-doc">
                {document?.updatedAt ? (
                    <p className="legal-doc__updated">Last updated {document.updatedAt}</p>
                ) : null}
                {sections.map((section) => (
                    <section key={section.heading} className="legal-doc__section">
                        <h2 className="legal-doc__heading">{section.heading}</h2>
                        {section.paragraphs.map((paragraph) => (
                            <p key={paragraph} className="legal-doc__text">{paragraph}</p>
                        ))}
                    </section>
                ))}
            </article>
        </AccountShell>
    );
}
