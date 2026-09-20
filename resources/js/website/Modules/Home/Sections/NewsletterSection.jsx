export default function NewsletterSection() {
    return (
        <section className="section newsletter" aria-labelledby="newsletter-title">
            <div className="newsletter__inner">
                <div>
                    <h2 id="newsletter-title" className="newsletter__title">
                        Get offer alerts
                    </h2>
                    <p className="newsletter__text">
                        Be first to know about new drops and weekend deals.
                    </p>
                </div>
                <form
                    className="newsletter__form"
                    onSubmit={(event) => {
                        event.preventDefault();
                    }}
                >
                    <label className="visually-hidden" htmlFor="newsletter-email">
                        Email address
                    </label>
                    <input
                        id="newsletter-email"
                        className="input"
                        type="email"
                        name="email"
                        placeholder="you@example.com"
                        autoComplete="email"
                        required
                    />
                    <button type="submit" className="btn btn--primary">
                        Subscribe
                    </button>
                </form>
            </div>
        </section>
    );
}
