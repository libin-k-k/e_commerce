export default function FaqSection({ faq = [] }) {
    return (
        <section className="account-panel" aria-label="Frequently asked questions">
            <div className="account-faq">
                {faq.map((item) => (
                    <details key={item.question} className="account-faq__item">
                        <summary className="account-faq__question">{item.question}</summary>
                        <p className="account-faq__answer">{item.answer}</p>
                    </details>
                ))}
            </div>
        </section>
    );
}
