export default function SocialAccountsSection({ socials = [] }) {
    if (socials.length === 0) {
        return null;
    }

    return (
        <section className="social-accounts" aria-label="Social media">
            <h2 className="social-accounts__title">Social media</h2>
            <p className="social-accounts__text">Follow the store for new drops and offer alerts.</p>
            <ul className="social-accounts__grid">
                {socials.map((account) => (
                    <li key={account.key}>
                        <a
                            className={`social-card is-${account.key}`}
                            href={account.href}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <span className="social-card__icon" aria-hidden="true">
                                <SocialIcon name={account.key} />
                            </span>
                            <span className="social-card__name">{account.name}</span>
                            <span className="social-card__handle">{account.handle}</span>
                            <span className="social-card__count">{account.followers}</span>
                            <span className="social-card__label">Followers</span>
                        </a>
                    </li>
                ))}
            </ul>
        </section>
    );
}

function SocialIcon({ name }) {
    if (name === 'facebook') {
        return (
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.6l.4-3H13v-2c0-.6.4-1 1-1z" />
            </svg>
        );
    }

    if (name === 'telegram') {
        return (
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M20.5 4.4 3.7 10.9c-1.1.4-1.1 1.1-.2 1.4l4.3 1.3 1.7 5.1c.2.6.1.8.7.8.4 0 .6-.2.8-.4l2.4-2.3 4.9 3.6c.9.5 1.5.2 1.7-.8l3.1-14.6c.3-1.2-.4-1.7-1.6-1.2zM9.4 13.7l8.8-5.5c.4-.3.8-.1.5.2l-7.5 6.8-.3 3.2-1.5-4.7z" />
            </svg>
        );
    }

    if (name === 'whatsapp') {
        return (
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M12 3.2A8.7 8.7 0 0 0 4.6 16.3L3.4 20.6l4.4-1.2A8.7 8.7 0 1 0 12 3.2zm5 12.2c-.2.6-1.2 1.1-1.7 1.2-.4 0-.9.2-3.1-.7-2.6-1.1-4.2-3.7-4.3-3.9-.1-.2-1-1.3-1-2.5s.6-1.8.9-2c.2-.2.5-.3.7-.3h.5c.2 0 .4 0 .6.5.2.6.7 2 .8 2.1.1.2.1.3 0 .5-.1.2-.2.3-.3.5l-.4.5c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1.2-.2.7-.8.9-1.1.2-.3.4-.2.6-.1.3.1 1.6.8 1.9.9.3.2.5.2.5.4.1.2.1.8-.1 1.3z" />
            </svg>
        );
    }

    return (
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M8 3h8a5 5 0 0 1 5 5v8a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8a5 5 0 0 1 5-5zm8.2 2.2h-1.5a3.4 3.4 0 0 0-3.4 3.4v1.2H9.2V12h2.1v6.2h2.4V12h2l.4-2.2h-2.4V8.8c0-.6.3-.9.9-.9h1.6V5.2zM16.8 7.4a.8.8 0 1 0 0-1.6.8.8 0 0 0 0 1.6z" />
        </svg>
    );
}
