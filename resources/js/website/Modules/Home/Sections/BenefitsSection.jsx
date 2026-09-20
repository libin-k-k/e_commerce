export default function BenefitsSection({ benefits = [] }) {
    return (
        <section className="section" aria-labelledby="benefits-title">
            <div className="container">
                <div className="section__header">
                    <h2 id="benefits-title" className="section__title">
                        Why shop with us
                    </h2>
                </div>
                <div className="benefits">
                    {benefits.map((benefit) => (
                        <article key={benefit.title} className="benefit-card">
                            <h3 className="benefit-card__title">{benefit.title}</h3>
                            <p className="benefit-card__text">{benefit.text}</p>
                        </article>
                    ))}
                </div>
            </div>
        </section>
    );
}
