export default function PoliciesSection({ policies = [] }) {
    return (
        <section className="account-panel" aria-label="Terms and policies">
            <div className="account-policies">
                {policies.map((policy) => (
                    <article key={policy.id} id={policy.id} className="account-policy">
                        <h2 className="account-policy__title">{policy.title}</h2>
                        <p className="account-policy__body">{policy.body}</p>
                    </article>
                ))}
            </div>
        </section>
    );
}
