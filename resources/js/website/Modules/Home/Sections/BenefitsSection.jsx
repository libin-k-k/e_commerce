export default function BenefitsSection({ benefits = [] }) {
    if (benefits.length === 0) {
        return null;
    }

    return (
        <section className="benefits-strip" aria-label="Why shop with us">
            <ul className="benefits-strip__list">
                {benefits.map((benefit) => (
                    <li key={benefit.title} className="benefits-strip__item">
                        <span className="benefits-strip__icon" aria-hidden="true">
                            <BenefitIcon name={benefit.icon} />
                        </span>
                        <span className="benefits-strip__copy">
                            <span className="benefits-strip__title">{benefit.title}</span>
                            <span className="benefits-strip__text">{benefit.text}</span>
                        </span>
                    </li>
                ))}
            </ul>
        </section>
    );
}

function BenefitIcon({ name }) {
    const common = {
        stroke: 'currentColor',
        strokeWidth: 1.8,
        strokeLinecap: 'round',
        strokeLinejoin: 'round',
    };

    return (
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            {name === 'delivery' ? (
                <>
                    <path d="M2 6h11v10H2zM13 9h4l4 4v3h-8z" {...common} />
                    <circle cx="6" cy="18" r="2" {...common} />
                    <circle cx="17" cy="18" r="2" {...common} />
                </>
            ) : null}
            {name === 'returns' ? (
                <>
                    <path d="M12 2l9 5v10l-9 5-9-5V7z" {...common} />
                    <path d="M3 7l9 5 9-5M12 12v10" {...common} />
                </>
            ) : null}
            {name === 'secure' ? (
                <>
                    <path d="M12 2l8 3v6c0 5-3.5 9-8 11-4.5-2-8-6-8-11V5z" {...common} />
                    <path d="M8.5 12l2.5 2.5 4.5-5" {...common} />
                </>
            ) : null}
            {name === 'support' ? (
                <>
                    <path d="M4 14v-2a8 8 0 0 1 16 0v2" {...common} />
                    <path d="M4 14h3v5H5a1 1 0 0 1-1-1zM20 14h-3v5h2a1 1 0 0 0 1-1z" {...common} />
                    <path d="M17 19c0 1.5-2 2-5 2" {...common} />
                </>
            ) : null}
        </svg>
    );
}
