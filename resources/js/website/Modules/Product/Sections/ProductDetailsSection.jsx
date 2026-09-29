import { useState } from 'react';

const HIGHLIGHT_LIMIT = 6;

export default function ProductDetailsSection({ product, faqs = [] }) {
    const specs = product.specs ?? [];
    const rating = Number(product.rating ?? 0);
    const reviewCount = product.reviewCount ?? 0;

    const panels = [
        { id: 'description', label: 'Product Description', icon: 'description' },
        specs.length > 0 ? { id: 'specifications', label: 'Specifications', icon: 'specifications' } : null,
        {
            id: 'reviews',
            label: reviewCount > 0 ? `Customer Reviews (${reviewCount.toLocaleString('en-IN')})` : 'Customer Reviews',
            icon: 'reviews',
        },
        faqs.length > 0 ? { id: 'faqs', label: 'FAQs', icon: 'faqs' } : null,
    ].filter(Boolean);

    const [activeTab, setActiveTab] = useState(panels[0].id);
    const [openPanel, setOpenPanel] = useState(null);

    const renderPanel = (id) => {
        if (id === 'description') {
            return <DescriptionPanel product={product} highlights={specs.slice(0, HIGHLIGHT_LIMIT)} />;
        }

        if (id === 'specifications') {
            return (
                <dl className="pdp-specs">
                    {specs.map((spec) => (
                        <div key={spec.label} className="pdp-specs__row">
                            <dt>{spec.label}</dt>
                            <dd>{spec.value}</dd>
                        </div>
                    ))}
                </dl>
            );
        }

        if (id === 'reviews') {
            return (
                <div className="pdp-reviews">
                    <div className="pdp-reviews__score">
                        <p className="pdp-reviews__value">{rating > 0 ? rating.toFixed(1) : '–'}</p>
                        <Stars value={rating} />
                        <p className="pdp-reviews__caption">{rating > 0 ? `Rated ${rating.toFixed(1)} out of 5` : 'Not rated yet'}</p>
                    </div>
                    <p className="pdp-reviews__empty">
                        {reviewCount > 0
                            ? `${reviewCount.toLocaleString('en-IN')} customers have reviewed this product.`
                            : 'No written reviews yet.'}
                    </p>
                </div>
            );
        }

        return (
            <div className="pdp-faqs">
                {faqs.map((faq) => (
                    <details key={faq.question} className="pdp-faqs__item">
                        <summary className="pdp-faqs__question">
                            {faq.question}
                            <ChevronIcon />
                        </summary>
                        <p className="pdp-faqs__answer">{faq.answer}</p>
                    </details>
                ))}
            </div>
        );
    };

    return (
        <section className="pdp-details" aria-label="Product information">
            <div className="pdp-details__tabs" role="tablist" aria-label="Product information">
                {panels.map((panel) => (
                    <button
                        key={panel.id}
                        type="button"
                        role="tab"
                        id={`pdp-tab-${panel.id}`}
                        className={`pdp-details__tab${activeTab === panel.id ? ' is-active' : ''}`}
                        aria-selected={activeTab === panel.id}
                        aria-controls={`pdp-panel-${panel.id}`}
                        onClick={() => setActiveTab(panel.id)}
                    >
                        {panel.label}
                    </button>
                ))}
            </div>

            {panels.map((panel) => {
                const open = openPanel === panel.id;

                return (
                    <div
                        key={panel.id}
                        className={`pdp-details__item${open ? ' is-open' : ''}${activeTab === panel.id ? ' is-active' : ''}`}
                    >
                        <button
                            type="button"
                            className="pdp-details__toggle"
                            aria-expanded={open}
                            aria-controls={`pdp-panel-${panel.id}`}
                            onClick={() => setOpenPanel(open ? null : panel.id)}
                        >
                            <PanelIcon name={panel.icon} />
                            <span className="pdp-details__toggle-label">{panel.label}</span>
                            <ChevronIcon />
                        </button>
                        <div
                            id={`pdp-panel-${panel.id}`}
                            className="pdp-details__panel"
                            role="tabpanel"
                            aria-labelledby={`pdp-tab-${panel.id}`}
                        >
                            {renderPanel(panel.id)}
                        </div>
                    </div>
                );
            })}
        </section>
    );
}

function DescriptionPanel({ product, highlights }) {
    const { description, shortDescription } = product;
    const isHtml = typeof description === 'string' && description.includes('<');

    return (
        <div className={`pdp-description${highlights.length > 0 ? ' has-highlights' : ''}`}>
            <div className="pdp-description__copy">
                <h2 className="pdp-description__title">Product Description</h2>
                {shortDescription ? <p className="pdp-description__lead">{shortDescription}</p> : null}
                {description ? (
                    isHtml ? (
                        <div className="pdp-description__text pdp-details__html" dangerouslySetInnerHTML={{ __html: description }} />
                    ) : (
                        <p className="pdp-description__text">{description}</p>
                    )
                ) : shortDescription ? null : (
                    <p className="pdp-description__text">More details coming soon.</p>
                )}
            </div>

            {highlights.length > 0 ? (
                <dl className="pdp-highlights">
                    {highlights.map((spec) => (
                        <div key={spec.label} className="pdp-highlights__item">
                            <span className="pdp-highlights__icon" aria-hidden="true">
                                <HighlightIcon label={spec.label} />
                            </span>
                            <div>
                                <dt>{spec.label}</dt>
                                <dd>{spec.value}</dd>
                            </div>
                        </div>
                    ))}
                </dl>
            ) : null}
        </div>
    );
}

function Stars({ value }) {
    return (
        <span className="pdp-reviews__stars" aria-hidden="true">
            {[1, 2, 3, 4, 5].map((star) => (
                <svg
                    key={star}
                    className={star <= Math.round(value) ? 'is-filled' : ''}
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9L12 2.5z" />
                </svg>
            ))}
        </span>
    );
}

function HighlightIcon({ label }) {
    const key = label.toLowerCase();
    let path = 'M5 12h14M12 5v14';

    if (key.includes('sku') || key.includes('code')) {
        path = 'M3 12V4h8l10 10-8 8L3 12zM7.5 7.5h.01';
    } else if (key.includes('categor')) {
        path = 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z';
    } else if (key.includes('size')) {
        path = 'M3 8h18v8H3zM7 8v3M11 8v4M15 8v3M19 8v4';
    } else if (key.includes('color')) {
        path = 'M12 3a9 9 0 1 0 0 18c1.1 0 1.5-.8 1.5-1.5 0-1.4-1-1.6-1-2.8 0-1 .8-1.7 1.8-1.7H17a4 4 0 0 0 4-4C21 6.5 17 3 12 3z';
    } else if (key.includes('rating')) {
        path = 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3z';
    } else if (key.includes('availab')) {
        path = 'M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zM4 7.5l8 4.5 8-4.5M12 12v9';
    }

    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d={path} stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function PanelIcon({ name }) {
    const paths = {
        description: 'M7 3h7l5 5v13H7zM14 3v5h5M10 12h6M10 16h6',
        specifications: 'M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zM4 7.5l8 4.5 8-4.5M12 12v9',
        reviews: 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3z',
        faqs: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6M12 17h.01',
    };

    return (
        <svg className="pdp-details__toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d={paths[name]} stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function ChevronIcon() {
    return (
        <svg className="pdp-details__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 6l6 6-6 6" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
