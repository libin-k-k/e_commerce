export default function ProductDescriptionSection({
    description,
    specs = [],
    shortDescription = null,
}) {
    const isHtml = typeof description === 'string' && description.includes('<');

    return (
        <section className="pdp-details" aria-labelledby="pdp-details-title">
            <h2 id="pdp-details-title" className="pdp-details__title">
                Product details
            </h2>

            {shortDescription ? <p className="pdp-details__lead">{shortDescription}</p> : null}

            {description ? (
                isHtml ? (
                    <div
                        className="pdp-details__text pdp-details__html"
                        dangerouslySetInnerHTML={{ __html: description }}
                    />
                ) : (
                    <p className="pdp-details__text">{description}</p>
                )
            ) : (
                <p className="pdp-details__text">More details coming soon.</p>
            )}

            {specs.length > 0 ? (
                <>
                    <h3 className="pdp-details__subtitle">Specifications</h3>
                    <dl className="pdp-specs">
                        {specs.map((spec) => (
                            <div key={spec.label} className="pdp-specs__row">
                                <dt>{spec.label}</dt>
                                <dd>{spec.value}</dd>
                            </div>
                        ))}
                    </dl>
                </>
            ) : null}
        </section>
    );
}
